<?php

if ( ! function_exists( 'wp_welcome_bootstrap' ) ) {
	function wp_welcome_bootstrap() {
		if ( is_admin() ) {
			do_action( 'wp_welcome_init' );
		}
	}
}
