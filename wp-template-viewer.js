( function( $ ) {

	var wp_tv_path;
	var code_container = $( '<div style="display:none" id="wp_tv_code_container"></div>' );

	$( document ).ready( function() {

		// Change .wp_tv_no_js to .wp_tv_js
		$( '.wp_tv_no_js' ).toggleClass( 'wp_tv_no_js wp_tv_js' );

		// Make links.
		$( ".wp_tv_path" ).removeClass( "ab-item ab-empty-item" ).wrap( '<a class="ab-item wp_tv_link" href=""></a>' );

		var plugin_title = $( '.wp_tv_file_list' );

		// Append empty code container.
		$( '#wp_tv_template_viewer' ).append( code_container );

		// Click event listener.
		$( ".wp_tv_link" ).on( "click", function( event ) {
			event.preventDefault();

			// show title and files (todo: create close button)
			plugin_title.show();

			// Hide all the things.
			code_container.empty();
			code_container.hide();

			wp_tv_path = $( this ).children( 'span' ).data( 'wp_tv_path' );

			$.post( wp_tv_ajax.wp_tv_ajaxurl, {
					action: 'wp_tv_display_template_file',
					wp_tv_nonce: wp_tv_ajax.wp_tv_nonce,
					wp_tv_file: wp_tv_path
				},
				function( response ) {

					code_container.show();

					if ( response.succes === true ) {
						code_container.html( response.file );
					} else {
						if ( response.file.length ) {
							code_container.html( response.file );
						}
					}
					var title = $( '#wp_tv_code_title' );

					if ( title.length ) {
						var targetOffset = title.offset().top;
						if ( $( '#wpadminbar' ).length ) {
							targetOffset = targetOffset - 30;
						}

						$( 'html,body' ).animate( {
							scrollTop: targetOffset
						} );
					}


				}, "json" );
		} );
	} );

} )( jQuery );