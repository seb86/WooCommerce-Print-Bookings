<?php
/**
 * WooCommerce Print Bookings - Admin.
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

if ( ! class_exists( 'WC_Print_Bookings_Admin' ) ) {
	class WC_Print_Bookings_Admin {

		/**
		 * Load the plugin.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'styles_and_scripts' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 49 );

			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 4 );
			//add_action( 'plugins_loaded', array( $this, 'updater' ) );
		} // END __construct()

		/**
		 * Plugin Updater
		 *
		 * @since  1.0.0
		 * @access public
		 * @static
		 */
		public static function updater() {
			if ( ! class_exists( 'SD_WC_Print_Bookings_Updater' ) ) {
				include( WC_Print_Bookings::plugin_path() . '/includes/admin/updater/class-updater.php' );
			}
		} // END updater()

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  mixed $links Plugin Row Meta
		 * @param  mixed $file  Plugin Base file
		 * @return array
		 */
		public function plugin_meta_links( $links, $file, $data, $status ) {
			if ( $file == WC_Print_Bookings::plugin_basename() ) {
				$links[ 1 ] = sprintf( __( 'Developed By %s', 'woocommerce-print-bookings' ), '<a href="' . $data[ 'AuthorURI' ] . '">' . $data[ 'Author' ] . '</a>' );
			}

			return $links;
		} // END plugin_meta_links()

		/**
		 * Admin Menu
		 *
		 * @since  1.0.0
		 * @access public
		 */
		public function admin_menu() {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				add_submenu_page( 'edit.php?post_type=wc_booking', __( 'Print Bookings', 'woocommerce-print-bookings' ), __( 'Print Bookings', 'woocommerce-print-bookings' ), 'manage_bookings', 'print-bookings', array( $this, 'wc_print_bookings_page' ) );
			}
		} // END admin_menu()

		/**
		 * Print Bookings Page
		 *
		 * @since  1.0.0
		 * @access public
		 */
		public function wc_print_bookings_page() {
			require_once( 'class-wc-print-bookings-page.php' );
			$page = new WC_Print_Bookings_Page();
			$page->output();
		} // END wc_print_bookings_page()

		/**
		 * Backend scripts
		 *
		 * @since  1.0.0
		 * @access public
		 */
		public function styles_and_scripts() {
			global $wp_scripts;

			$suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';

			// Checks if the page is the print bookings page before loading any styles and scripts.
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			wp_register_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );

			wp_register_script( 'wc_print_bookings_js', WC_Print_Bookings::plugin_url() . '/assets/js/wc-print-bookings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-tiptip' ), WC_Print_Bookings::$version, true );

			wp_register_script( 'jquery-print', WC_Print_Bookings::plugin_url() . '/assets/js/jQuery.print.min.js', array( 'jquery' ), WC_Print_Bookings::$version, true );

			if ( in_array( $screen_id, array( 'edit-wc_booking', 'wc_booking_page_print-bookings' ) ) ) {
				wp_enqueue_script( 'wc_print_bookings_js' );

				$params = array(
					'i18n_print_bookings' => esc_js( __( 'Print Bookings', 'woocommerce-print-bookings' ) ),
					'i18n_site_title'     => esc_js( get_bloginfo( 'name' ) ),
					'urls' => array(
						'print_bookings' => esc_url_raw( admin_url( 'edit.php?post_type=wc_booking&page=print-bookings' ) )
					),
				);

				wp_localize_script( 'wc_print_bookings_js', 'wc_print_bookings_js_params', $params );
			}

			if ( in_array( $screen_id, array( 'wc_booking_page_print-bookings' ) ) ) {
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_style( 'wc_print_bookings_style', WC_Print_Bookings::plugin_url() . '/assets/css/wc-print-bookings.css', null, WC_Print_Bookings::$version );
				wp_enqueue_script( 'jquery-print' );
			}
		} // END styles_and_scripts()

	} // END class

} // END if class exists

return new WC_Print_Bookings_Admin();
