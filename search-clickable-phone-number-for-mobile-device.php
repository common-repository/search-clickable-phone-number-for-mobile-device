<?php
/**
 * Plugin Name: Search Clickable Phone Number for Mobile Device
 * Plugin URI: http://www.brainvire.com
 * Description: Enables easy search of clickable phone numbers (wrapped with and without tel in anchor tag) from all posts, custom posts, pages, and comments in the website.
 * Version: 1.0.1
 * Author: brainvireinfo
 * Author URI: http://www.brainvire.com
 * License: GPL2
 *
 * @package SearchClickablePhoneNumber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin directory path.
define( 'SEARCH_PHONENO_DIR', plugin_dir_path( __FILE__ ) );
define( 'PHONENO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes plugin files.
include_once SEARCH_PHONENO_DIR . 'phone-number-search-form.php';

// Create custom menu.
add_action( 'admin_menu', 'spnrp_init_install' );

if ( ! function_exists( 'spnrp_init_install' ) ) {

	/**
	 * Initializes the installation process for the plugin.
	 *
	 * This function is responsible for setting up necessary database tables,
	 * default options, or other initialization tasks required for the plugin.
	 */
	function spnrp_init_install() {

		// Create new top level menu.

		add_menu_page(
			'Search phone number with regex pattern', // Page Title.
			'SP Number', // Menu Title.
			'manage_options', // Capability.
			'spnrp-phone-number', // Menu Slug.
			'spnrp_search_phone_number', // Callable function.
			PHONENO_PLUGIN_URL . 'images/search-phone-number-2.png' // Icon.
		);
	}
}

