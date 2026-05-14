<?php
/**
 * Techanum Maintenance Mode Class
 *
 * Handles the maintenance mode functionality for the plugin.
 *
 * @package TechanumMaintenance
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Techanum_Maintenance_Mode {

    /**
     * Option name for maintenance state.
     *
     * @var string
     */
    private $option_name = 'techanum_maintenance_active';

    /**
     * Constructor.
     */
    public function __construct() {
        // Προσθήκη του φίλτρου μόνο όταν είναι ενεργή η συντήρηση
        if ( $this->is_maintenance_active() ) {
            add_filter( 'template_include', array( $this, 'maintenance_template' ), 9999 );
        }
    }

    /**
     * Check if maintenance mode is enabled.
     *
     * @return bool
     */
    public function is_maintenance_active() {
        return (bool) get_option( $this->option_name, false );
    }

    /**
     * Filter the template to show maintenance page if enabled.
     *
     * @param string $template The path of the template to include.
     * @return string
     */
    public function maintenance_template( $template ) {
        // Επέτρεψε στους διαχειριστές να βλέπουν το κανονικό site
        if ( current_user_can( 'manage_options' ) ) {
            return $template;
        }

        // Μην επηρεάζεις το backend
        if ( is_admin() ) {
            return $template;
        }

        $maintenance_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/maintenance-page.php';
        if ( file_exists( $maintenance_template ) ) {
            status_header( 503 );
            header( 'Retry-After: 3600' );
            return $maintenance_template;
        }

        return $template;
    }
}