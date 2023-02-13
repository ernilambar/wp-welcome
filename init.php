<?php
/**
 * Initialize
 *
 * @package WPWelcome
 */

namespace Nilambar\Welcome;

if ( ! class_exists( Init_1_0_4::class, false ) ) {

	class Init_1_0_4 {

		const VERSION = '1.0.4';

		const PRIORITY = 9995;

		public static $single_instance = null;

		public static function initiate() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
			return self::$single_instance;
		}

		private function __construct() {
			if ( ! defined( 'WP_WELCOME_LOADED' ) ) {
				define( 'WP_WELCOME_LOADED', self::PRIORITY );
			}

			add_action( 'init', array( $this, 'include_lib' ), self::PRIORITY );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
		}

		public function include_lib() {
			if ( class_exists( Welcome::class, false ) ) {
				return;
			}

			if ( ! defined( 'WP_WELCOME_VERSION' ) ) {
				define( 'WP_WELCOME_VERSION', self::VERSION );
			}

			if ( ! defined( 'WP_WELCOME_DIR' ) ) {
				define( 'WP_WELCOME_DIR', rtrim( get_template_directory(), '/' ) . '/vendor/ernilambar/wp-welcome' );
			}

			if ( ! defined( 'WP_WELCOME_URL' ) ) {
				define( 'WP_WELCOME_URL', rtrim( get_template_directory_uri(), '/' ) . '/vendor/ernilambar/wp-welcome' );
			}

			if ( ! class_exists( \WPTRT\Autoload\Loader::class, false ) ) {
				require_once __DIR__ . '/Loader.php';
			}

			$loader = new \WPTRT\Autoload\Loader();
			$loader->add( 'Nilambar\\Welcome\\', __DIR__ . '/src' );
			$loader->register();

			require_once __DIR__ . '/bootstrap.php';
			wp_welcome_bootstrap();
		}

		/**
		 * Load assets.
		 *
		 * @since 1.0.0
		 */
		public function load_assets() {
			wp_enqueue_style( 'wp-welcome-style', WP_WELCOME_URL . '/assets/wp-welcome.css', array(), WP_WELCOME_VERSION );

			wp_enqueue_script( 'wp-welcome-scripts', WP_WELCOME_URL . '/assets/wp-welcome.js', array( 'jquery' ), WP_WELCOME_VERSION, true );

			wp_localize_script(
				'wp-welcome-scripts',
				'WPW_OBJECT',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'storage_key'   => $this->get_unique_id( 'wpw-' ) . '-activetab',
					'admin_nonce'   => wp_create_nonce( 'wpw_installer_nonce' ),
					'i18n' => array(
						'activate'        => esc_html__( 'Activate', 'wp-welcome' ),
						'activated'       => esc_html__( 'Activated', 'wp-welcome' ),
						'install_now'     => esc_html__( 'Install Now', 'wp-welcome' ),
						'install_confirm' => esc_html__( 'Are you sure you want to install this plugin?', 'wp-welcome' ),
					),
				)
			);
		}

		/**
		 * Gets unique ID.
		 *
		 * @since 1.0.3
		 *
		 * @param string $prefix Prefix for the returned ID.
		 * @return string Unique ID.
		 */
		public function get_unique_id( $prefix = '' ) {
			static $wpw_counter = 0;
			return $prefix . (string) ++$wpw_counter;
		}
	}

	Init_1_0_4::initiate();
}
