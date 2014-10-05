( function( $ ) {

	var code_container = $( '<div style="display:none"></div>' );
	var animation_elm = document.createElement( 'div' );
	var in_progress = false;
	var show_line_numbers = false;
	var lines = 0;


	// function to scroll to element
	function wp_tv_scroll_to( el ) {
		if ( el.length ) {
			var targetOffset = el.offset().top;
			if ( $( '#wpadminbar' ).length ) {
				targetOffset = targetOffset - 30;
			}

			$( 'html,body' ).animate( {
				scrollTop: targetOffset
			} );
		}
	}


	// function to select content
	function wp_tv_select_text( element ) {
		var text = document.getElementById( element ),
			range, selection;
		if ( document.body.createTextRange ) { //ms
			range = document.body.createTextRange();
			range.moveToElementText( text );
			range.select();
		} else if ( window.getSelection ) { //all others
			range = document.createRange();
			range.selectNodeContents( text );
			window.getSelection().removeAllRanges();
			window.getSelection().addRange( range );
		}
	}


	// checks browser support for css animations
	function browser_supports_animation() {

		var animation = false,
			elm = animation_elm,
			domPrefixes = 'Webkit Moz O ms Khtml'.split( ' ' );

		// check unprefixed
		if ( elm.style.animationName !== undefined ) {
			animation = true;
		}

		if ( animation === false ) {
			for ( var i = 0; i < domPrefixes.length; i++ ) {
				if ( elm.style[ domPrefixes[ i ] + 'AnimationName' ] !== undefined ) {
					console.log( 'prefix ' + domPrefixes[ i ] );
					animation = true;
					break;
				}
			}
		}

		return animation;
	}


	// adds line number spans to an element
	function add_line_numbers( element ) {
		var code = $( 'code', element );

		if ( code.length ) {
			lines = ( 1 + code.text().split( '\n' ).length );
		} else {
			lines = ( 1 + element.text().split( '\n' ).length );
		}

		var line_spans = new Array( lines );
		line_spans = line_spans.join( '<span></span>' );

		var wrapper = $( '<span class="line-numbers-rows">' + line_spans + "</span>" );

		element.prepend( wrapper );
	}


	$( document ).ready( function() {

		// Change .wp_tv_no_js to .wp_tv_js (unhides links when there is Javascript)
		$( '.wp_tv_no_js' ).toggleClass( 'wp_tv_no_js wp_tv_js' );

		// Create links.
		$( "[data-wp_tv_file]" ).removeClass( "ab-item ab-empty-item" ).wrap( '<a class="ab-item" href=""></a>' );
		$( ".wp_tv_toggle" ).contents().unwrap().wrap( '<a class="wp_tv_toggle" href=""></a>' );
		$( ".wp_tv_toggle" ).parent().removeClass( "ab-empty-item" );
		$( ".wp_tv_close" ).contents().unwrap().wrap( '<a class="wp_tv_close" href=""></a>' );
		$( ".wp_tv_close" ).attr( {
			alt: wp_tv_ajax.wp_tv_close,
			title: wp_tv_ajax.wp_tv_close
		} );

		// get elements once
		var header = $( '.wp_tv_header' );
		var toggle_admin_bar = $( "#wpadminbar" ).find( ".wp_tv_toggle" );
		var toggle_footer = header.find( ".wp_tv_toggle" );
		var footer_files = $( '#wp_tv_template_viewer' ).find( '.wp_tv_files' );


		// Append empty code container.
		$( '#wp_tv_template_viewer' ).append( code_container );

		// close click event
		$( ".wp_tv_close" ).on( "click", function( event ) {
			event.preventDefault();
			footer_files.hide();
			header.hide();
			toggle_admin_bar.text( wp_tv_ajax.wp_tv_show_in_footer );
			toggle_footer.text( wp_tv_ajax.wp_tv_show );
			code_container.hide();
			code_container.empty();
		} );

		// show files click event
		$( ".wp_tv_toggle" ).on( "click", function( event ) {
			event.preventDefault();

			if ( footer_files.is( ":visible" ) ) {
				toggle_admin_bar.text( wp_tv_ajax.wp_tv_show_in_footer );
				toggle_footer.text( wp_tv_ajax.wp_tv_show );
				footer_files.slideUp( "fast" );

			} else {
				toggle_admin_bar.text( wp_tv_ajax.wp_tv_hide_in_footer );
				toggle_footer.text( wp_tv_ajax.wp_tv_hide );
				header.show();
				footer_files.show();
			}
			wp_tv_scroll_to( header );
		} );

		// select text click event
		$( '#wp_tv_template_viewer' ).on( 'click', '.wp_tv_select', function( event ) {
			event.preventDefault();
			var title = $( ".wp_tv_file_title" );
			if ( title.length ) {
				wp_tv_scroll_to( title );
				wp_tv_select_text( 'wp_tv_content' );
			}
		} );

		// show/hide line numbers click event
		$( '#wp_tv_template_viewer' ).on( 'click', '.wp_tv_lines', function( event ) {
			event.preventDefault();

			var pre = $( '#wp_tv_content' ).find( 'pre' );
			lines = 0;

			if ( pre.length ) {

				if ( !pre.hasClass( 'line-numbers' ) ) {
					show_line_numbers = true;
					pre.addClass( 'line-numbers' );
					add_line_numbers( pre );
					$( this ).text( wp_tv_ajax.wp_tv_hide_lines );

				} else {
					show_line_numbers = false;
					pre.removeClass( 'line-numbers large' );
					pre.find( '.line-numbers-rows' ).remove();
					$( this ).text( wp_tv_ajax.wp_tv_lines );
				}

				if ( show_line_numbers && ( lines >= 1000 ) ) {
					pre.addClass( 'large' );
				}
			}
		} );


		// file click event
		$( ".wp_tv_files" ).find( 'a' ).on( "click", function( event ) {

			event.preventDefault();

			// bail if an ajax request is already in progress
			if ( in_progress ) {
				return;
			}

			in_progress = true;

			// get the file path from link
			var file = $( this ).children( 'span' ).data( 'wp_tv_file' );

			// check if browser supports css animation for spinner (use fallback gif spinner in stylesheet)
			var support = browser_supports_animation() ? ' cssanimations' : ' no-cssanimations';

			// add  a spinner to links with the same data-wp_tv_file (both links in toolbar and footer)
			$( '[data-wp_tv_file="' + file + '"]' ).each( function() {
				if ( $.contains( footer_files.get( 0 ), this ) ) {
					$( this ).parent().append( $( '<span class="wp_tv_spinner' + support + '"></span>' ) );
				} else {
					$( this ).parent().prepend( $( '<span class="wp_tv_spinner' + support + '"></span>' ) );
				}
			} );

			// show header
			header.show();

			$.post(
				wp_tv_ajax.wp_tv_ajaxurl, {
					action: 'wp_tv_display_template_file',
					wp_tv_nonce: wp_tv_ajax.wp_tv_nonce,
					wp_tv_file: file
				},

				function( response ) {

					// remove content from container
					code_container.empty();

					// show the container if it was hidden
					code_container.show();

					if ( response.success === true ) {
						// show file content
						code_container.html( response.data );
					} else {
						if ( response.data.length ) {
							// show error
							code_container.html( response.data );
						}
					}

					if ( show_line_numbers ) {
						$( '.wp_tv_lines' ).trigger( "click" );
					}

					var title = $( '.wp_tv_file_title' );
					wp_tv_scroll_to( title );

					// remove spinner
					$( '.wp_tv_spinner' ).remove();

					// set in_progress back to false
					in_progress = false;
				}, "json" );

		} );
	} );

} )( jQuery );