<?php
/**
 * Techanum Maintenance Mode Class
 *
 * Handles the maintenance mode functionality for the plugin.
 *
 * @package TechanumMaintenance
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Techanum_Maintenance_Mode
 */
class Techanum_Maintenance_Mode {

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'template_include', array( $this, 'maintenance_template' ), 999 );
    }

    /**
     * Check if maintenance mode is enabled.
     *
     * @return bool True if maintenance mode is enabled, false otherwise.
     */
    public function is_maintenance_enabled() {
        return (bool) get_option( 'techanum_maintenance_enabled', false );
    }

    /**
     * Filter the template to show maintenance page if enabled.
     *
     * @param string $template The path of the template to include.
     * @return string The modified template path.
     */
    public function maintenance_template( $template ) {
        if ( $this->is_maintenance_enabled() && ! current_user_can( 'administrator' ) && ! is_admin() ) {
            $maintenance_template = plugin_dir_path( dirname( __DIR__ ) ) . 'templates/maintenance-page.php';
            if ( file_exists( $maintenance_template ) ) {
                return $maintenance_template;
            }
        }

        return $template;
    }
}