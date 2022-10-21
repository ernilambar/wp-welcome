<?php
/**
 * View class
 *
 * @package WPWelcome
 */

namespace Nilambar\Welcome;

use Nilambar\Welcome\Utils;

/**
 * View class.
 *
 * @since 1.0.0
 */
class View {

	/**
	 * Render page header.
	 *
	 * @since 1.0.0
	 *
	 * @param Welcome $obj Instance of Welcome.
	 */
	public static function render_header( $obj ) {
		echo '<div class="wpw-header">';

		$page_title = $obj->get_page_title();

		if ( $page_title ) {
			echo '<h1>' . wp_kses_post( $page_title ) . '</h1>';
		}

		$page_subtitle = $obj->get_page_subtitle();

		if ( $page_subtitle ) {
			echo '<p>' . wp_kses_post( $page_subtitle ) . '</p>';
		}

		$quick_links = $obj->get_quick_links();

		if ( ! empty( $quick_links ) ) {
			self::render_quick_links( $quick_links );
		}

		echo '</div><!-- .wpw-header -->';
	}

	/**
	 * Render quick links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Links list.
	 */
	public static function render_quick_links( $links ) {
		if ( ! empty( $links ) ) {
			echo '<div class="wpw-quick-links">';

			foreach ( $links as $link ) {
				$button_classes = '';

				if ( isset( $link['type'] ) ) {
					if ( 'primary' === $link['type'] ) {
						$button_classes = 'button button-primary';
					} elseif ( 'secondary' === $link['type'] ) {
						$button_classes = 'button button-secondary';
					}
				}

				echo '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $button_classes ) . '" target="_blank">' . esc_html( $link['text'] ) . '</a>';
			}

			echo '</div><!-- .wpw-quick-links -->';
		}
	}

	/**
	 * Render tab navigation.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $tabs Tabs list.
	 * @param Welcome $obj Instance of Welcome.
	 */
	public static function render_tab_navigation( $tabs, $obj ) {
		echo '<div class="wpw-tabs-nav">';

		$slug = $obj->get_slug();

		foreach ( $tabs as $tab ) {
			$attrs = array(
				'href'  => '#' . $slug . '-' . $tab['id'],
				'class' => array( 'tab-nav', 'tab-' . $tab['id'] ),
			);

			echo '<h3><a ' . Utils::render_attr( $attrs, false ) . '>' . esc_html( $tab['title'] ) . '</a></h3>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</div><!-- .wpw-tabs-nav -->';

	}

	/**
	 * Render tabs content.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $tabs Tabs list.
	 * @param Welcome $obj Instance of Welcome.
	 */
	public static function render_tabs_content( $tabs, $obj ) {
		$slug = $obj->get_slug();

		foreach ( $tabs as $tab ) {
			echo '<div id="' . esc_attr( $slug . '-' . $tab['id'] ) . '" class="wpw-tab-content">';

			self::render_tab( $tab );

			echo '</div><!-- .wpw-tab-content -->';
		}
	}

	/**
	 * Render tab content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tab Tab detail.
	 */
	public static function render_tab( $tab ) {
		do_action( 'wp_welcome_before_tab_content', $tab );

		switch ( $tab['type'] ) {
			case 'content':
				if ( isset( $tab['content'] ) ) {
					echo wp_kses_post( wpautop( $tab['content'] ) );
				}
				break;

			case 'custom':
				if ( isset( $tab['render_callback'] ) && is_callable( $tab['render_callback'] ) ) {
					$tab['render_callback']();
				}
				break;

			case 'grid':
				if ( isset( $tab['items'] ) && ! empty( $tab['items'] ) ) {
					self::render_grid_items( $tab['items'], array( 'grid_columns' => $tab['grid_columns'] ) );
				}
				break;

			case 'plugin':
				if ( isset( $tab['items'] ) && ! empty( $tab['items'] ) ) {
					self::render_plugin_items( $tab['items'] );
				}
				break;

			case 'comparison':
				if ( isset( $tab['items'] ) && ! empty( $tab['items'] ) ) {
					$headings = ( isset( $tab['headings'] ) && ! empty( $tab['headings'] ) ) ? $tab['headings'] : array();
					$upgrade  = ( isset( $tab['upgrade_button'] ) && ! empty( $tab['upgrade_button'] ) ) ? $tab['upgrade_button'] : array();
					self::render_comparison_table( $tab['items'], $headings, $upgrade );
				}
				break;

			default:
				break;
		}

		do_action( 'wp_welcome_after_tab_content', $tab );
	}

	/**
	 * Render grid items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Grid items list.
	 * @param array $args Grid extra arguments.
	 */
	public static function render_grid_items( $items, $args = array() ) {
		$cols = ( isset( $args['grid_columns'] ) && absint( $args['grid_columns'] ) > 0 ) ? absint( $args['grid_columns'] ) : 2;

		echo '<div class="wpw-grid wpw-col-' . esc_attr( $cols ) . '">';

		foreach ( $items as $key => $item ) {
			self::render_grid_item( $item );
		}

		echo '</div>';
	}

	/**
	 * Render grid item.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Grid item detail.
	 */
	private static function render_grid_item( $item ) {
		echo '<div class="wpw-box plain">';

		if ( isset( $item['title'] ) && ! empty( $item['title'] ) ) {
			echo '<h3>';

			if ( isset( $item['icon'] ) && ! empty( $item['icon'] ) ) {
				echo '<span class="' . esc_attr( $item['icon'] ) . '"></span>';
			}

			echo esc_html( $item['title'] );

			echo '</h3>';
		}

		if ( isset( $item['render_callback'] ) && is_callable( $item['render_callback'] ) ) {
			call_user_func( $item['render_callback'] );
		} else  {
			if ( isset( $item['description'] ) && ! empty( $item['description'] ) ) {
				echo '<p>' . wp_kses_post( $item['description'] ) . '</p>';
			}
		}

		if ( isset( $item['button_text'] ) && ! empty( $item['button_text'] ) && isset( $item['button_url'] ) && ! empty( $item['button_url'] ) ) {
			$button_target = ( isset( $item['is_new_tab'] ) && ( true === wp_validate_boolean( $item['is_new_tab'] ) ) ) ? '_blank' : '_self';
			$button_class  = '';
			if ( isset( $item['button_type'] ) && ! empty( $item['button_type'] ) ) {
				if ( 'primary' === $item['button_type'] ) {
					$button_class = 'button button-primary';
				} elseif ( 'secondary' === $item['button_type'] ) {
					$button_class = 'button button-secondary';
				}
			}

			echo '<p><a href="' . esc_url( $item['button_url'] ) . '" class="' . esc_attr( $button_class ) . '" target="' . esc_attr( $button_target ) . '">' . esc_html( $item['button_text'] ) . '</a></p>';
		}

		echo '</div><!-- .item -->';
	}

	/**
	 * Render plugin items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Plugin items list.
	 */
	public static function render_plugin_items( $items ) {
		echo '<div class="wpw-grid wpw-col-2">';

		foreach ( $items as $key => $item ) {
			self::render_plugin_item( $item );
		}

		echo '</div>';
	}

	/**
	 * Render plugin item.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Plugin item detail.
	 */
	public static function render_plugin_item( $item ) {
		echo '<div class="wpw-box wpw-box-plugin">';

		if ( isset( $item['name'] ) && ! empty( $item['name'] ) ) {
			echo '<h3>' . esc_html( $item['name'] ) . '</h3>';
		}

		if ( isset( $item['description'] ) && ! empty( $item['description'] ) ) {
			echo '<p>' . wp_kses_post( $item['description'] ) . '</p>';
		}

		$button_text    = esc_html__( 'Install Now', 'wp-welcome' );
		$button_classes = 'button button-primary install';

		if ( Helper::is_plugin_installed( $item['slug'] ) && ! Helper::is_plugin_active( $item['slug'] ) ) {
			$button_text    = esc_html__( 'Activate', 'wp-welcome' );
			$button_classes = 'button activate';
		} elseif ( Helper::is_plugin_active( $item['slug'] ) ) {
			$button_text    = esc_html__( 'Activated', 'wp-welcome' );
			$button_classes = 'button disabled';
		}

		echo '<div class="wpw-buttons">';

		echo '<a class="' . esc_attr( $button_classes ) . '" data-slug="' . esc_attr( $item['slug'] ) . '" href="#">' . esc_html( $button_text ) . '</a>';

		echo '<a href="' . esc_url( 'https://wordpress.org/plugins/' . $item['slug'] . '/' ) . '" target="_blank">' . esc_html__( 'View Details', 'wp-welcome' ) . '</a>';

		echo '</div><!-- .wpw-buttons -->';

		echo '</div><!-- .item -->';
	}

	/**
	 * Render comparison table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Table items.
	 * @param array $headings Headings detail.
	 * @param array $upgrade Upgrade button detail.
	 */
	public static function render_comparison_table( $items, $headings, $upgrade ) {
		$headings = wp_parse_args(
			$headings,
			array(
				'free' => esc_html__( 'Free', 'wp-welcome' ),
				'pro'  => esc_html__( 'Pro', 'wp-welcome' ),
			)
		);

		$upgrade = wp_parse_args(
			$upgrade,
			array(
				'text' => esc_html__( 'Upgrade to Pro', 'wp-welcome' ),
				'url'  => '',
			)
		);
		?>
		<table class="comparison-table">
			<thead>
				<tr>
					<th></th>
					<th><?php echo esc_html( $headings['free'] ); ?></th>
					<th><?php echo esc_html( $headings['pro'] ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $items as $item ) : ?>

					<tr>
						<td>
							<?php if ( isset( $item['title'] ) ) : ?>
								<h3><?php echo esc_html( $item['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( isset( $item['description'] ) ) : ?>
								<p><?php echo esc_html( $item['description'] ); ?></p>
							<?php endif; ?>
						</td>
						<td class="col-free">
							<?php
							if ( 'yes' === $item['free'] ) {
								echo '<span class="dashicons-before dashicons-yes yes"></span>';
							} elseif ( 'no' === $item['free'] ) {
								echo '<span class="dashicons-before dashicons-no-alt no"></span>';
							} else {
								echo esc_html( $item['free'] );
							}
							?>
						</td>
						<td class="col-pro">
							<?php
							if ( 'yes' === $item['pro'] ) {
								echo '<span class="dashicons-before dashicons-yes yes"></span>';
							} elseif ( 'no' === $item['pro'] ) {
								echo '<span class="dashicons-before dashicons-no-alt no"></span>';
							} else {
								echo esc_html( $item['pro'] );
							}
							?>
						</td>
					</tr>

				<?php endforeach; ?>

				<?php if ( ! empty( $upgrade['url'] ) ) : ?>
					<tr class="wpw-comparison-row-upgrade">
						<td></td>
						<td colspan="2">
							<a href="<?php echo esc_url( $upgrade['url'] ); ?>" target="_blank" class="button button-primary button-hero"><?php echo esc_html( $upgrade['text'] ); ?></a>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table><!-- .comparison-table -->
		<?php
	}

	/**
	 * Render sidebar box.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $args Sidebar box arguments.
	 * @param Welcome $obj Instance of Welcome.
	 */
	public static function render_sidebar_box( $args, $obj ) {
		$box_attrs = array(
			'class' => array( 'wpw-box' ),
		);

		if ( ! empty( $args['class'] ) ) {
			$box_attrs['class'][] = $args['class'];
		}

		echo '<div ' . Utils::render_attr( $box_attrs, false ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $args['title'] ) {
			echo '<h3>';

			if ( ! empty( $args['icon'] ) ) {
				echo '<span class="dashicons ' . esc_attr( $args['icon'] ) . '"></span>';
			}

			echo esc_html( $args['title'] );
			echo '</h3>';
		}

		if ( 'content' === $args['type'] ) {
			echo wp_kses_post( wpautop( $args['content'] ) );
		}

		if ( 'custom' === $args['type'] ) {
			if ( is_callable( $args['render_callback'] ) ) {
				call_user_func( $args['render_callback'], $obj );
			}
		}

		if ( ! empty( $args['button_text'] ) && ! empty( $args['button_url'] ) ) {
			$button_attrs = array(
				'href' => $args['button_url'],
			);

			if ( ! empty( $args['button_class'] ) ) {
				$button_attrs['class'] = $args['button_class'];
			}

			if ( true === $args['button_new_tab'] ) {
				$button_attrs['target'] = '_blank';
			}

			echo '<a ' . Utils::render_attr( $button_attrs, false ) . '>' . esc_html( $args['button_text'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</div><!-- .wpw-box -->';
	}
}
