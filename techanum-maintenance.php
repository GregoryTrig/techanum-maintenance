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

// Αποτροπή άμεσης πρόσβασης
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Φόρτωση της κλάσης συντήρησης
if ( ! class_exists( 'Techanum_Maintenance_Mode' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-maintenance-mode.php';
}

// Φόρτωση της κλάσης Antigravity API
if ( ! class_exists( 'Techanum_Antigravity_API' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-antigravity-api.php';
}

// Φόρτωση των κλάσεων admin (μόνο στο admin)
if ( is_admin() ) {
	if ( ! class_exists( 'Techanum_Settings' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';
	}
	new Techanum_Settings();

	if ( ! class_exists( 'Techanum_Maintenance_Admin_Notices' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-notices.php';
	}
	new Techanum_Maintenance_Admin_Notices();
}

// Εκκίνηση του plugin
$techanum_maintenance = new Techanum_Maintenance_Mode();

/**
 * Ενεργοποίηση plugin - Προεπιλογές
 */
function techanum_maintenance_activate() {
    add_option( 'techanum_maintenance_active', false );
}
register_activation_hook( __FILE__, 'techanum_maintenance_activate' );

/**
 * Απενεργοποίηση plugin - Καθάρισμα
 */
function techanum_maintenance_deactivate() {
    delete_option( 'techanum_maintenance_active' );
}
register_deactivation_hook( __FILE__, 'techanum_maintenance_deactivate' );