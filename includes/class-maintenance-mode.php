<?php
/**
 * Techanum Maintenance Mode Class
 *
 * Handles the maintenance mode functionality for the plugin.
 *
 * @package TechanumMaintenance
 * @license GPL-3.0-or-later
 * @link    https://techanum.com/maintenance/
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Techanum_Maintenance_Mode
 *
 * Intercepts front-end requests and serves the maintenance page
 * when maintenance mode is active, unless the current user is excluded.
 */
class Techanum_Maintenance_Mode {

	/**
	 * Option name for maintenance state.
	 *
	 * @var string
	 */
	private $option_name = 'techanum_maintenance_active';

	/**
	 * Constructor.
	 *
	 * Registers the template_include filter unconditionally so that the
	 * maintenance check always runs at the correct point in the request
	 * lifecycle (after authentication is fully initialised).
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'maintenance_template' ), 9999 );
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
	 * Check if the current user's role is in the excluded roles list.
	 *
	 * Administrators are always excluded regardless of the saved option.
	 *
	 * @return bool True if the user should bypass the maintenance page.
	 */
	private function is_user_excluded() {
		// Administrators always bypass maintenance mode.
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check if the user's role is in the excluded roles list.
		$excluded_roles = get_option( 'techanum_excluded_roles', array() );
		if ( empty( $excluded_roles ) || ! is_array( $excluded_roles ) ) {
			return false;
		}

		$user = wp_get_current_user();
		if ( ! ( $user instanceof WP_User ) || 0 === $user->ID ) {
			return false;
		}

		$user_roles = (array) $user->roles;

		foreach ( $user_roles as $role ) {
			if ( in_array( $role, $excluded_roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filter the template to show maintenance page if enabled.
	 *
	 * @param string $template The path of the template to include.
	 * @return string
	 */
	public function maintenance_template( $template ) {
		// Do not affect the admin backend.
		if ( is_admin() ) {
			return $template;
		}

		// Skip if maintenance mode is not active.
		if ( ! $this->is_maintenance_active() ) {
			return $template;
		}

		// Allow excluded users (admins + configured roles) to see the normal site.
		if ( $this->is_user_excluded() ) {
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
