import './styles/wp-welcome.css';

( function ( $ ) {
	// Main wrapper;
	const $wrapper = $( '#wp-welcome-wrap' );

	const wpwInstallPlugin = ( plugin, $btn ) => {
		$.ajax( {
			url: WPW_OBJECT.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wpw_plugin_installer',
				plugin,
				nonce: WPW_OBJECT.admin_nonce,
			},
			beforeSend() {
				$btn.addClass( 'installing' );
			},
			complete( jqXHR ) {
				const response = JSON.parse( jqXHR.responseText );

				if ( true === response.success ) {
					$btn.html( WPW_OBJECT.i18n.activate );
					$btn.attr( 'class', 'button activate' );
				}

				$btn.removeClass( 'installing' );
			},
		} );
	};

	const wpwActivatePlugin = ( plugin, $btn ) => {
		$.ajax( {
			url: WPW_OBJECT.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wpw_plugin_activation',
				plugin,
				nonce: WPW_OBJECT.admin_nonce,
			},
			beforeSend() {
				$btn.addClass( 'installing' );
			},
			complete( jqXHR ) {
				const response = JSON.parse( jqXHR.responseText );

				if ( true === response.success ) {
					$btn.html( WPW_OBJECT.i18n.activated );
					$btn.attr( 'class', 'button disabled' );
				}

				$btn.removeClass( 'installing' );
			},
		} );
	};

	$( '.wpw-box-plugin a.button' ).on( 'click', function ( e ) {
		e.preventDefault();

		const $button = $( this );

		const plugin = $button.data( 'slug' );

		// Bail if plugin slug is empty.
		if ( ! plugin ) {
			return;
		}

		// Bail if button is disabled.
		if ( $button.hasClass( 'disabled' ) ) {
			return;
		}

		if ( $button.hasClass( 'install' ) ) {
			wpwInstallPlugin( plugin, $button );
		}

		if ( $button.hasClass( 'activate' ) ) {
			wpwActivatePlugin( plugin, $button );
		}
	} );

	// Tabs.
	$wrapper.find( '.wpw-tab-content' ).hide();

	let activeTab = '';

	if ( 'undefined' !== typeof localStorage ) {
		activeTab = localStorage.getItem( WPW_OBJECT.storage_key );
	}

	// Initial status for tab content.
	if ( null !== activeTab && $( `#${ activeTab }` ).length ) {
		$( `#${ activeTab }` ).hide().fadeIn( 'fast' );
		$( `.wpw-tabs-nav a[href="#${ activeTab }"]` ).addClass( 'active' );
	} else {
		$wrapper.find( '.wpw-tab-content' ).first().hide().fadeIn( 'fast' );
		$wrapper.find( '.wpw-tabs-nav a' ).first().addClass( 'active' );
	}

	$wrapper.find( '.wpw-tabs-nav a' ).on( 'click', function ( e ) {
		e.preventDefault();

		if ( $( this ).hasClass( 'active' ) ) {
			return;
		}

		$wrapper.find( '.wpw-tabs-nav a' ).removeClass( 'active' );
		$( e.target ).addClass( 'active' );

		// Get target.
		const targetGroup = $( e.target ).attr( 'href' );

		// Save active tab in local storage.
		if ( 'undefined' !== typeof localStorage ) {
			localStorage.setItem( WPW_OBJECT.storage_key, targetGroup.replace( '#', '' ) );
		}

		$wrapper.find( '.wpw-tab-content' ).hide();
		$( targetGroup ).fadeIn( 'fast' );
	} );
} )( jQuery );
