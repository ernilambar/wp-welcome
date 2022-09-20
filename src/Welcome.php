<?php
/**
 * Main class
 *
 * @package WPWelcome
 */

namespace Nilambar\Welcome;

use Nilambar\Welcome\Ajax;
use Nilambar\Welcome\Helper;
use Nilambar\Welcome\View;
use Nilambar\Welcome\Utils;

/**
 * Welcome class.
 *
 * @since 1.0.0
 */
class Welcome {

	/**
	 * Page settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $page = array();

	/**
	 * Admin notice settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $admin_notice = array();

	/**
	 * Quick links.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $quick_links = array();

	/**
	 * Tabs.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $tabs = array();

	/**
	 * Tab status.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $tab_status = false;

	/**
	 * Whether page is in top level menu.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $top_level_menu;

	/**
	 * Parent page.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $parent_page;

	/**
	 * Sidebar status.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $is_sidebar = false;

	/**
	 * Sidebar callback.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $sidebar_callback = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $mode, $slug ) {
		if ( ! in_array( $mode, array( 'plugin', 'theme' ), true ) ) {
			return;
		}

		if ( empty( $slug ) ) {
			return;
		}

		if ( 'theme' === $mode ) {
			$themeObject = wp_get_theme( $slug );

			if ( $themeObject->exists() ) {
				$this->product_name    = $themeObject->get( 'Name' );
				$this->product_version = $themeObject->get( 'Version' );
				$this->product_slug    = $themeObject->get_template();
			}
		} elseif ( 'plugin' === $mode ) {
			$plugin_details = Helper::get_plugin_information( $slug );

			if ( ! empty( $plugin_details ) ) {
				$this->product_name    = $plugin_details['Name'];
				$this->product_version = $plugin_details['Version'];
				$this->product_slug    = $slug;
			}
		}
	}

	/**
	 * Run now.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		if ( empty( $this->page ) ) {
			return;
		}

		if ( empty( $this->product_slug ) ) {
			return;
		}

		if ( count( $this->tabs ) > 1 ) {
			$this->tab_status = true;
		}

		// Create admin page.
		add_action( 'admin_menu', array( $this, 'create_menu_page' ) );

		if ( ! empty( $this->admin_notice ) ) {
			// Update notice dismiss status.
			add_action( 'admin_head', array( $this, 'update_notice_status' ) );

			// Admin notice.
			add_action( 'admin_notices', array( $this, 'add_admin_notice' ) );
		}

		// AJAX callbacks.
		add_action( 'wp_ajax_nopriv_wpw_plugin_installer', array( Ajax::class, 'install_plugin' ) );
		add_action( 'wp_ajax_wpw_plugin_installer', array( Ajax::class, 'install_plugin' ) );
		add_action( 'wp_ajax_nopriv_wpw_plugin_activation', array( Ajax::class, 'activate_plugin' ) );
		add_action( 'wp_ajax_wpw_plugin_activation', array( Ajax::class, 'activate_plugin' ) );
	}

	/**
	 * Add admin notice.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_notice() {
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ), 99 );
	}

	/**
	 * Update user notice dismiss status.
	 *
	 * @since 1.0.0
	 */
	public function update_notice_status() {
		if ( isset( $_GET[ 'wpw-dismiss-' . $this->product_slug ] ) && check_admin_referer( 'wpw-dismiss-' . get_current_user_id() ) ) {
			update_user_meta( get_current_user_id(), "wpw_dismissed_{$this->product_slug}", 1 );
		}
	}

	/**
	 * Display admin notice.
	 *
	 * @since 1.0.0
	 */
	public function display_admin_notice() {
		$screen_id = null;

		$current_screen = get_current_screen();

		if ( $current_screen ) {
			$screen_id = $current_screen->id;
		}

		$dismiss_status = get_user_meta( get_current_user_id(), "wpw_dismissed_{$this->product_slug}", true );

		if ( current_user_can( $this->page['capability'] ) && in_array( $screen_id, $this->admin_notice['screens'], true ) && 1 !== absint( $dismiss_status ) ) {
			echo '<div class="notice notice-' . esc_attr( $this->admin_notice['type'] ) . '">';
			$this->render_notice();
			echo '</div><!-- .notice -->';
		};
	}

	/**
	 * Render notice.
	 *
	 * @since 1.0.0
	 */
	public function render_notice() {
		echo '<p>' . wp_kses_post( $this->admin_notice['message'] ) . '</p>';
		echo '<p><a href="' . esc_url( $this->get_page_url() ) . '" class="button button-primary">' . esc_html( $this->admin_notice['button_text'] ) . '</a>&nbsp;&nbsp;<a href="' . esc_url( $this->get_dismiss_url() ) . '">' . esc_html__( 'Dismiss this notice', 'wp-welcome' ) . '</a></p>';
	}

	/**
	 * Set page settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Page arguments.
	 */
	public function set_page( $args = array() ) {
		$defaults = array(
			'page_title'     => sprintf( esc_html__( 'Welcome to %1$s - %2$s', 'wp-welcome' ), esc_html( $this->product_name ), esc_html( $this->product_version ) ),
			'page_subtitle'  => sprintf( esc_html__( '%1$s is now installed and ready to use. Thank you for choosing %1$s, cheers!', 'wp-welcome' ), esc_html( $this->product_name ) ),
			'menu_title'     => esc_html__( 'Admin Dashboard', 'wp-welcome' ),
			'capability'     => 'edit_theme_options',
			'menu_slug'      => 'wp-welcome',
			'top_level_menu' => false,
			'parent_page'    => 'options-general.php',
		);

		$this->page = wp_parse_args( $args, $defaults );

		$this->top_level_menu = $this->page['top_level_menu'];
		$this->parent_page    = $this->page['parent_page'];
	}

	/**
	 * Set admin notice.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Notice arguments.
	 */
	public function set_admin_notice( $args = array() ) {
		$defaults = array(
			'type'        => 'success',
			'message'     => sprintf( esc_html__( 'Welcome! %1$s is now installed and ready to use. Thank you for choosing %1$s.', 'wp-welcome' ), esc_html( $this->product_name ) ),
			'button_text' => sprintf( esc_html__( 'Get started with %1$s', 'wp-welcome' ), $this->product_name ),
			'screens'     => array( 'dashboard' ),
		);

		$this->admin_notice = wp_parse_args( $args, $defaults );
	}

	/**
	 * Set quick links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Quick links array.
	 */
	public function set_quick_links( $links ) {
		$output = array();

		if ( empty( $links ) ) {
			return $output;
		}

		foreach ( $links as $link ) {
			$defaults = array(
				'text' => esc_html__( 'Link', 'wp-welcome' ),
				'url'  => '#',
				'type' => 'primary',
			);

			$output[] = wp_parse_args( $link, $defaults );
		}

		$this->quick_links = $output;
	}

	/**
	 * Create menu page.
	 *
	 * @since 1.0.0
	 */
	public function create_menu_page() {
		if ( true === $this->top_level_menu ) {
			add_menu_page(
				$this->page['page_title'],
				$this->page['menu_title'],
				$this->page['capability'],
				$this->page['menu_slug'],
				array( $this, 'render_page' ),
				$this->page['menu_icon']
			);
		} else {
			add_submenu_page(
				$this->parent_page,
				$this->page['page_title'],
				$this->page['menu_title'],
				$this->page['capability'],
				$this->page['menu_slug'],
				array( $this, 'render_page' )
			);
		}
	}

	/**
	 * Render page.
	 *
	 * @since 1.0.0
	 */
	public function render_page() {
		echo '<div class="wrap wpw-wrap" id="wp-welcome-wrap">';

		View::render_header( $this );

		$main_attrs = array(
			'class' => array(
				'wpw-main',
			),
		);

		if ( true !== $this->is_sidebar ) {
			$main_attrs['class'][] = 'no-sidebar';
		}
		?>

		<div <?php Utils::render_attr( $main_attrs ); ?>>
			<div class="wpw-main-inner">
				<div class="wpw-main-content">

					<?php View::render_tab_navigation( $this->tabs ); ?>

					<div class="wpw-tabs-content-wrap">

						<?php View::render_tabs_content( $this->tabs ); ?>

					</div><!-- .wpw-tabs-content-wrap -->
				</div><!-- .wpw-main-content -->
				<?php
				if ( true === $this->is_sidebar ) {
					echo '<div class="wpw-main-sidebar">';

					if ( is_callable( $this->sidebar_callback ) ) {
						call_user_func( $this->sidebar_callback );
					}

					echo '</div><!-- .wpw-main-sidebar -->';
				}
				?>
			</div><!-- .wpw-main-inner -->
		</div><!-- .wpw-main -->

		<?php
		echo '</div><!-- .wrap -->';
	}

	/**
	 * Set sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Sidebar arguments.
	 */
	public function set_sidebar( $args ) {
		$defaults = array(
			'render_callback' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( is_callable( $args['render_callback'] ) ) {
			$this->is_sidebar       = true;
			$this->sidebar_callback = $args['render_callback'];
		}
	}

	/**
	 * Add tab.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Tab arguments.
	 */
	public function add_tab( $args ) {
		$defaults = array(
			'id'      => '',
			'title'   => esc_html__( 'Tab Title', 'wp-welcome' ),
			'type'    => 'content',
			'content' => '',
		);

		$this->tabs[] = wp_parse_args( $args, $defaults );
	}

	/**
	 * Return page URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string Page URL.
	 */
	public function get_page_url() {
		$parent = $this->parent_page;

		if ( true === $this->top_level_menu ) {
			$parent = 'admin.php';
		}

		$base_url = admin_url( $parent );

		$output = add_query_arg(
			array(
				'page' => $this->page['menu_slug'],
			),
			$base_url
		);

		return $output;
	}

	/**
	 * Return dismiss URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string Dismiss URL.
	 */
	protected function get_dismiss_url() {
		return wp_nonce_url( add_query_arg( 'wpw-dismiss-' . $this->product_slug, 'dismiss-notice' ), 'wpw-dismiss-' . get_current_user_id() );
	}

	/**
	 * Return quick links.
	 *
	 * @since 1.0.0
	 *
	 * @return array Quick links list.
	 */
	public function get_quick_links() {
		return $this->quick_links;
	}

	/**
	 * Return page title.
	 *
	 * @since 1.0.0
	 *
	 * @return array Page title.
	 */
	public function get_page_title() {
		return $this->page['page_title'];
	}

	/**
	 * Return page subtitle.
	 *
	 * @since 1.0.0
	 *
	 * @return array Page subtitle.
	 */
	public function get_page_subtitle() {
		return $this->page['page_subtitle'];
	}
}
