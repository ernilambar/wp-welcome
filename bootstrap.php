<?php

function wp_welcome_bootstrap() {
	if ( is_admin() ) {
		do_action( 'wp_welcome_init' );
	}
}
