<?php
/**
 * Plugin Name:       Techanum Maintenance
 * Plugin URI:        https://techanum.com/maintenance/
 * Description:       Replace the default WordPress maintenance page with a friendly, customizable under-construction page. Hide admin notices from non-admin users.
 * Version:           1.0
 * Author:            Gregory Triglidis
 * Author URI:        https://techanum.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       techanum-maintenance
 * Domain Path:       /languages
 *
 * @package TechanumMaintenance
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the plugin text domain for translations.
 *
 * Hooked to 'init' so that WordPress has fully set up the locale
 * before we attempt to load the .mo file.
 */
function techanum_maintenance_load_textdomain() {
	load_plugin_textdomain(
		'techanum-maintenance',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'techanum_maintenance_load_textdomain' );

/**
 * Bootstrap the plugin classes.
 *
 * All class files are required and instantiated inside the
 * 'plugins_loaded' hook so that WordPress core is fully available
 * (including is_admin(), current_user_can(), etc.) before any
 * class constructor runs.
 */
function techanum_maintenance_init() {

	// Load and initialise the maintenance mode class (front-end + back-end).
	if ( ! class_exists( 'Techanum_Maintenance_Mode' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-maintenance-mode.php';
	}
	new Techanum_Maintenance_Mode();

	// Load the Antigravity API class (used by the maintenance template).
	if ( ! class_exists( 'Techanum_Antigravity_API' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-antigravity-api.php';
	}

	// Load admin-only classes.
	if ( is_admin() ) {
		if ( ! class_exists( 'Techanum_Maintenance_Settings' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings-page.php';
		}
		new Techanum_Maintenance_Settings();

		if ( ! class_exists( 'Techanum_Maintenance_Admin_Notices' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-notices.php';
		}
		new Techanum_Maintenance_Admin_Notices();
	}
}
add_action( 'plugins_loaded', 'techanum_maintenance_init' );

/**
 * Plugin activation — set default options.
 */
function techanum_maintenance_activate() {
	add_option( 'techanum_maintenance_active', false );
}
register_activation_hook( __FILE__, 'techanum_maintenance_activate' );

/**
 * Plugin deactivation — clean up options.
 */
function techanum_maintenance_deactivate() {
	delete_option( 'techanum_maintenance_active' );
}
register_deactivation_hook( __FILE__, 'techanum_maintenance_deactivate' );
