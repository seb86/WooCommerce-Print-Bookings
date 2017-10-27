<?php
/*
 * Plugin Name: WooCommerce Print Bookings
 * Description: Select bookable products, booking status, product categories and the date and time of the bookings you wish to print out a summarized version in a printable table.
 * Version:     1.0.0
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Text Domain: woocommerce-print-bookings
 * Domain Path: /languages/
 *
 * Requires at least: 4.5
 * Tested up to: 4.8.2
 * WC requires at least: 2.6.14
 * WC tested up to: 3.2.1
 *
 * Copyright:   © 2017 Sébastien Dumont
 * License:     GNU General Public License v2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined('ABSPATH') ) {
	exit;
}

if ( ! class_exists( 'WC_Dependencies' ) ) {
	require_once( 'woo-dependencies/woo-dependencies.php' );
}

// Quit right now if WooCommerce is not active
if ( ! is_woocommerce_active() ){
	return;
}

if ( ! class_exists( 'WC_Print_Bookings' ) ) {
	class WC_Print_Bookings {

		/**
		 * @var WC_Print_Bookings - the single instance of the class.
		 *
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Plugin Version
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public static $version = '1.0.0';

		/**
		 * Required WooCommerce Version
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public $required_woo = '3.0.0';

		/**
		 * Required WooCommerce Bookings Version
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public $required_wcb = '1.10';

		/**
		 * Main WC_Print_Bookings Instance.
		 *
		 * Ensures only one instance of WC_Print_Bookings is loaded or can be loaded.
		 *
		 * @access public
		 * @static
		 * @see    WC_Print_Bookings()
		 * @return WC_Print_Bookings - Main instance
		 * @since  1.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-print-bookings' ) );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-print-bookings' ) );
		}

		/**
		 * WC_Print_Bookings Constructor.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return WC_Print_Bookings
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Include required files and load once WooCommerce has loaded first.
			add_action( 'woocommerce_loaded', array( $this, 'includes' ), 10 );
		}

		/*-----------------------------------------------------------------------------------*/
		/*  Helper Functions                                                                 */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Get the Plugin URL.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 */
		public static function plugin_url() {
			return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		} // END plugin_url()

		/**
		 * Get the Plugin Path.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 */
		public static function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		} // END plugin_path()

		/**
		 * Get the Plugin Base path name.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @return string
		 */
		public static function plugin_basename() {
			return plugin_basename( __FILE__ );
		} // END plugin_basename()

		/**
		 * Get the Plugin File name.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @return string
		 */
		public static function plugin_filename() {
			return __FILE__;
		} // END plugin_filename()

		/*-----------------------------------------------------------------------------------*/
		/*  Load Files                                                                       */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Check requirements on activation.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function load_plugin() {
			// Check we're running the required version of WooCommerce.
			if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $this->required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'wc_print_bookings_wc_admin_notice' ) );
				return false;
			}

			// Check we're running the required version of WooCommerce Bookings extension.
			if ( ! class_exists( 'WC_Bookings' ) || version_compare( WC_BOOKINGS_VERSION, $this->required_wcb, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'wc_print_bookings_admin_notice' ) );
				return false;
			}
		} // END load_plugin()

		/**
		 * Includes the WooCommerce Print Bookings classes.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function includes() {
			if ( is_admin() ) {
				include_once( $this->plugin_path() .'/includes/admin/class-wc-print-bookings-admin.php' );
			}
		} // END include()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function wc_print_bookings_wc_admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( '%1$s requires at least %2$s v%3$s in order to function. Please upgrade %2$s.', 'woocommerce-print-bookings' ), 'WooCommerce Print Bookings', 'WooCommerce', $this->required_woo ) . '</p></div>';
		} // END wc_print_bookings_wc_admin_notice()

		/**
		 * Display a warning message if minimum WooCommerce Bookings check fails.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function wc_print_bookings_admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( '%1$s requires %2$s v%3$s. Please install and activate %2$s first.', 'woocommerce-print-bookings' ), 'WooCommerce Print Bookings', 'WooCommerce Bookings', $this->required_wcb ) . '</p></div>';
		} // END wc_print_bookings_admin_notice()

		/*-----------------------------------------------------------------------------------*/
		/*  Localization                                                                     */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Make the plugin translation ready.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'woocommerce-print-bookings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END load_plugin_textdomain()

	} // END class

} // END if class exists

return WC_Print_Bookings::instance();
