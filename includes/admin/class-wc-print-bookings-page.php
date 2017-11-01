<?php
/**
 * WooCommerce Print Bookings Page.
 *
 * @class    WC_Print_Bookings_Admin
 * @author   Sébastien Dumont
 * @category Admin
 * @package  WooCommerce Print Bookings
 * @license  GPL-2.0+
 * @since    1.0.0
 */
if ( ! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Print_Bookings_Page' ) ) {
	class WC_Print_Bookings_Page {

		/**
		 * Stores errors.
		 *
		 * @var array
		 */
		private $errors = array();

		/**
		 * Output the form.
		 *
		 * @access public
		 */
		public function output() {
			$this->errors = array();
			$step         = isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1;

			try {
				if ( ! empty( $_POST ) && ! check_admin_referer( 'get_bookings' ) ) {
					throw new Exception( __( 'Error - please <a href="javascript:history.back()">go back</a> and try again.', 'woocommerce-print-bookings' ) );
				}

				if ( ! empty( $_POST['get_bookings'] ) ) {
					$product_id = isset( $_POST['product_id'] ) ? array_map( 'intval', (array) $_POST['product_id'] ) : 0;

					if ( $product_id == 0 ) {
						throw new Exception( __( 'Error - Please <a href="javascript:history.back()">go back</a> and choose at least one bookable product.', 'woocommerce-print-bookings' ) );
					}

					$post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : array();
					$category    = isset( $_POST['product_category'] ) ? array_map( 'intval', (array) $_POST['product_category'] ) : array();
					$start_date  = isset( $_POST['booking_start_date'] ) ? wc_clean( $_POST['booking_start_date'] ) : '';

					if ( empty( $start_date ) ) {
						throw new Exception( __( 'Error - Please <a href="javascript:history.back()">go back</a> and select a start date for the bookings.', 'woocommerce-print-bookings' ) );
					}

					$end_date         = isset( $_POST['booking_end_date'] ) ? wc_clean( $_POST['booking_end_date'] ) : '';
					$booking_all_day  = isset( $_POST['booking_all_day'] ) ? "yes" : 'no';
					$start_time       = isset( $_POST['booking_start_time'] ) ? wc_clean( $_POST['booking_start_time'] ) : '';
					$end_time         = isset( $_POST['booking_end_time'] ) ? wc_clean( $_POST['booking_end_time'] ) : '';
					$show_order_notes = isset( $_POST['show_order_notes'] ) ? true : false;

					// If the end date was not set but an end time was then set end date the same as start date.
					if ( empty( $end_date ) && ! empty( $end_time ) ) {
						$end_date = $start_date;
					}

					// Ready to load the next page.
					$step +1;

					do_action( 'woocommerce_print_bookings_page_form_submitted', $product_id, $post_status, $category, $start_date, $end_date, $booking_all_day, $start_time, $end_time, $show_order_notes );
				}

				if ( $step == 2 && empty( $product_id ) ) {
					throw new Exception( __( 'Error - Oh dear, you refreshed the page. Please <a href="javascript:history.back()">go back</a> and re-select the bookings to print.', 'woocommerce-print-bookings' ) );
					wp_die();
					exit(1);
				}

			} catch ( Exception $e ) {
				$this->errors[] = $e->getMessage();
			}

			switch ( $step ) {
				case 1 :
					include( 'views/html-select-bookings.php' );
					break;
				case 2 :
					include( 'views/html-print-bookings.php' );
					break;
			}
		} // END output()

		/**
		 * Output any errors
		 *
		 * @access public
		 */
		public function show_errors() {
			foreach ( $this->errors as $error ) {
				echo '<div class="error"><p>' . $error . '</p></div>';
			}
		} // END show_errors()

		/**
		 * Checks if the bookable product has the category.
		 *
		 * @access public
		 * @param  array   $category
		 * @param  integer $product_id
		 * @return bool
		 */
		public function bookings_has_term( $category, $product_id ) {
			if ( has_term( $category, 'product_cat', $product_id ) ) {
				return true;
			}
			return false;
		} // bookings_has_term()

	} // END class

} // END if class exists
