<?php
/**
 * Helper class
 *
 * @package WPWelcome
 */

namespace Nilambar\Welcome;

/**
 * Helper class.
 *
 * @since 1.0.0
 */
class Helper {

	/**
	 * Check if plugin is activated.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return bool True if plugin is activated.
	 */
	public static function is_plugin_active( $plugin_slug ) {
		$status = false;

		$file = self::get_plugin_file( $plugin_slug );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$status = is_plugin_active( $file );

		return $status;
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return bool True if plugin is installed.
	 */
	public static function is_plugin_installed( $plugin_slug ) {
		$status = false;

		$plugins = self::get_all_plugins();

		if ( empty( $plugins ) ) {
			return $status;
		}

		$filenames = array_keys( $plugins );

		$filenames = array_map(
			function( $f ) {
				return dirname( plugin_basename( $f ) );
			},
			$filenames
		);

		$status = in_array( $plugin_slug, $filenames, true ) ? true : false;

		return $status;
	}

	/**
	 * Return all plugins in the site.
	 *
	 * @since 1.0.0
	 *
	 * @return array Plugins list.
	 */
	public static function get_all_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		return get_plugins();
	}

	/**
	 * Return plugin main file name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return string Plugin main file name.
	 */
	public static function get_plugin_file( $plugin_slug ) {
		$plugins = self::get_all_plugins();

		foreach ( $plugins as $plugin_file => $plugin_info ) {
			$slug = dirname( plugin_basename( $plugin_file ) );

			if ( $slug ) {
				if ( $slug === $plugin_slug ) {
					return $plugin_file;
				}
			}
		}

		return null;
	}

	/**
	 * Return plugin details.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Plugin slug.
	 * @return array Plugin details.
	 */
	public static function get_plugin_info( $slug ) {
		$output = array();

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => sanitize_file_name( $slug ),
				'fields' => array(
					'short_description' => true,
					'sections'          => false,
					'contributors'      => false,
					'banners'           => false,
					'versions'          => false,
					'requires'          => false,
					'downloaded'        => true,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
					'ratings'           => false,
					'icons'             => true,
				),
			)
		);

		if ( ! is_wp_error( $api ) ) {
			$output = $api;

		}

		return $output;
	}

	/**
	 * Return plugin local data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Plugin slug.
	 * @return array Plugin details.
	 */
	public static function get_plugin_information( $slug ) {
		$output = array();

		$plugins = self::get_all_plugins();

		$plugin_file = self::get_plugin_file( $slug );

		if ( isset( $plugins[ $plugin_file ] ) ) {
			$output = $plugins[ $plugin_file ];
		}

		return $output;
	}
}
