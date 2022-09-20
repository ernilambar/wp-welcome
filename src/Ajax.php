<?php
/**
 * Ajax class
 *
 * @package WPWelcome
 */

namespace Nilambar\Welcome;

use Nilambar\Welcome\Helper;

/**
 * Ajax class.
 *
 * @since 1.0.0
 */
class Ajax {

	/**
	 * Callback for plugin installation.
	 *
	 * @since 1.0.0
	 */
	public static function install_plugin() {
		$output = array();
		$error  = true;

		// Bail if no access.
		if ( ! current_user_can( 'install_plugins' ) ) {
			$output['message'] = esc_html__( 'Sorry, you are not allowed to install plugins for this site.', 'wp-welcome' );
			wp_send_json_error( $output );
		}

		$nonce  = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

		// Bail if no plugin slug.
		if ( empty( $plugin ) ) {
			$output['message'] = esc_html__( 'Invalid plugin slug.', 'wp-welcome' );
			wp_send_json_error( $output );
		}

		// Bail if nonce is not valid.
		if ( ! wp_verify_nonce( $nonce, 'wpw_installer_nonce' ) ) {
			$output['message'] = esc_html__( 'Nonce verification failed.', 'wp-welcome' );
			wp_send_json_error( $output );
		}

		// Include required files for plugin installation.
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		// Get plugin info.
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin,
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			)
		);

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$upgrader->install( $api->download_link );

		if ( $api->name ) {
			$error = false;

			$output['message'] = $api->name . ' successfully installed.';
		} else {
			$error = true;

			$output['message'] = 'There was an error installing ' . $api->name . '.';
		}

		// Add plugin slug in the response.
		$output['plugin'] = $plugin;

		if ( ! $error ) {
			wp_send_json_success( $output );
		} else {
			wp_send_json_error( $output );
		}
	}

	/**
	 * Callback for plugin activation.
	 *
	 * @since 1.0.0
	 */
	public static function activate_plugin() {
		$output = array();
		$error  = true;

		// Bail if no access.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$output['message'] = esc_html__( 'Sorry, you are not allowed to activate plugins for this site.', 'wp-welcome' );
			wp_send_json_error( $output );
		}

		$nonce  = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

		// Bail if no plugin slug.
		if ( empty( $plugin ) ) {
			$output['message'] = esc_html__( 'Invalid plugin slug.', 'wp-welcome' );
			wp_send_json_error( $output );
		}

		// Bail if nonce is not valid.
		if ( ! wp_verify_nonce( $nonce, 'wpw_installer_nonce' ) ) {
			$output['message'] = esc_html__( 'Nonce verification failed.', 'wp-welcome' );
			wp_send_json_error( $output );
		}

		// Include required files for plugin activation.
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		// Get plugin info.
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin,
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			)
		);

		if ( $api->name ) {
			$main_plugin_file = Helper::get_plugin_file( $plugin );

			$error = false;

			if ( $main_plugin_file ) {
				activate_plugin( $main_plugin_file, '', false, true );
				$output['message'] = $api->name . ' successfully activated.';
			}
		} else {
			$error = true;

			$output['message'] = 'There was an error activating ' . $api->name . '.';
		}

		// Add plugin slug in the response.
		$output['plugin'] = $plugin;

		if ( ! $error ) {
			wp_send_json_success( $output );
		} else {
			wp_send_json_error( $output );
		}
	}
}
