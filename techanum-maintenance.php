<?php
/**
 * Plugin Name:       Techanum Maintenance
 * Plugin URI:        https://techanum.com/maintenance/
 * Description:       Replace the default WordPress maintenance page with a friendly, customizable under-construction page. Hide admin notices from non-admin users.
 * Version:           1.0.0
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

// Load the maintenance mode class.
if ( ! class_exists( 'Techanum_Maintenance_Mode' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-maintenance-mode.php';
}

// Load the Antigravity API class.
if ( ! class_exists( 'Techanum_Antigravity_API' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-antigravity-api.php';
}

// Load admin classes (only in the admin area).
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

// Initialise the plugin.
$techanum_maintenance = new Techanum_Maintenance_Mode();

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
