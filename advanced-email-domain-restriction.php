<?php
/**
 * Advanced Email Domain Restriction
 *
 * @package aedr
 *
 * Plugin Name: Advanced Email Domain Restriction
 * Plugin URI: https://wordpress.org/plugins/advanced-email-domain-restriction
 * Description: Allow email domains for user registrations with custom messages.
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Version: 1.1.0
 * Author: Md Siddiqur Rahman
 * Author URI: https://siddiqur.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       advanced-email-domain-restriction
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'AEDR_VERSION', '1.1.0' );
define( 'AEDR_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'AEDR_ROOT_PATH', plugin_dir_path( __FILE__ ) );

// Include the main class file.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-aedr-admin.php';


if ( ! function_exists( 'aedr_init' ) ) {
	/**
	 * Initialize the plugin
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function aedr_init() {
		$plugin = new AEDR_Admin();
		$plugin->run();
	}

	add_action( 'plugins_loaded', 'aedr_init' );
}
