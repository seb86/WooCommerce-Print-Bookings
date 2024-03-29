/* global wc_print_bookings_js_params */
jQuery( document ).ready( function($) {
	if ( 'undefined' === typeof wc_print_bookings_js_params ) {
		return;
	}

	// Add button to bookings screen.
	var $product_screen = $( '.edit-php.post-type-wc_booking' ),
			$title_action   = $product_screen.find( '.page-title-action:first' ),
			$blankslate     = $product_screen.find( '.woocommerce-BlankState' );

	if ( 0 === $blankslate.length ) {
		$title_action.after( '<a href="' + wc_print_bookings_js_params.urls.print_bookings + '" class="page-title-action">' + wc_print_bookings_js_params.i18n_print_bookings + '</a>' );
	}

	var $print_bookings_screen = $( '.wc_booking_page_print-bookings' ),
			$print_results_table   = $print_bookings_screen.find( 'table#print-bookings-table' ),
			$print_results_button  = $print_bookings_screen.find( 'a.print-table-results' ),
			$wp_footer             = $print_bookings_screen.find( '#wpfooter' );

	// TipTip
	var tiptip_args = {
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 200
	};

	$( '.tips, .help_tip, .woocommerce-help-tip' ).tipTip( tiptip_args ).css( 'cursor', 'help' );

	// Date Picker
	$( '.booking_start_date, .booking_end_date' ).datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		numberOfMonths: 1,
		showButtonPanel: true,
		showOtherMonths: true,
		selectOtherMonths: true
	});

	$( '#booking_all_day' ).change( function () {
		if ( $(this).is( ':checked' ) ) {
			$( '#booking_start_time, #booking_end_time' ).closest('p').hide();
		} else {
			$( '#booking_start_time, #booking_end_time' ).closest('p').show();
		}
	}).change();

	// Print Results
	if ( $print_results_table.length ) {
		$print_results_button.fadeIn('slow').show();

		$wp_footer.css( 'position', 'relative' );
	}

	$( 'a.print-table-results' ).click(function( e ) {
		e.preventDefault();

		$print_results_table.print({
			title: wc_print_bookings_js_params.i18n_site_title
		});

		return false;
	});

});
