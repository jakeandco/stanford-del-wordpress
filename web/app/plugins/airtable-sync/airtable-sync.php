<?php
/**
 * Plugin Name: Airtable Sync
 * Plugin URI: https://jakeandco.com
 * Description: Sync content and metadata between Airtable bases and WordPress.
 * Version: 1.0.0
 * Author: Jake and Co.
 * Author URI: https://jakeandco.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: airtable-sync
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'AIRTABLE_SYNC_VERSION', '1.0.0' );
define( 'AIRTABLE_SYNC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIRTABLE_SYNC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize the plugin.
 */
function airtable_sync_init() {
	// Load admin functionality
	if ( is_admin() ) {
		require_once AIRTABLE_SYNC_PLUGIN_DIR . 'includes/class-airtable-sync-admin.php';
		$admin = new Airtable_Sync_Admin();
		$admin->init();
	}
}
add_action( 'plugins_loaded', 'airtable_sync_init' );

/**
 * Activation hook.
 */
function airtable_sync_activate() {
	// Set default options on activation
	if ( ! get_option( 'airtable_sync_settings' ) ) {
		add_option( 'airtable_sync_settings', array(
			'api_key' => '',
			'base_id' => '',
			'table_mappings' => array()
		) );
	}
}
register_activation_hook( __FILE__, 'airtable_sync_activate' );

/**
 * Deactivation hook.
 */
function airtable_sync_deactivate() {
	// Clean up scheduled events if any
}
register_deactivation_hook( __FILE__, 'airtable_sync_deactivate' );
