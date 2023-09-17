<?php
/**
 * Bootstrap
 *
 * @package WPWelcome
 */

if ( ! function_exists( 'wp_welcome_bootstrap' ) ) {
	/**
	 * Bootstrap library.
	 *
	 * @since 1.0.0
	 */
	function wp_welcome_bootstrap() {
		if ( is_admin() ) {
			do_action( 'wp_welcome_init' );
		}
	}
}
