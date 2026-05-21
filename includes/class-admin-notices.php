<?php
/**
 * Techanum Admin Notices Suppression Class
 *
 * Handles the suppression of admin notices for specific user roles.
 * Allows site administrators to configure which roles should not see
 * WordPress admin notices.
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
 * Class Techanum_Maintenance_Admin_Notices
 *
 * Suppresses admin notices for designated user roles.
 */
class Techanum_Maintenance_Admin_Notices {

	/**
	 * Constructor — register hooks in the admin area.
	 *
	 * Note: We do NOT call is_user_logged_in() here because the constructor
	 * runs during plugin load, before WordPress has fully initialised the
	 * authentication layer. The actual user/role check is deferred to the
	 * admin_init callback, where all auth functions are available.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_user_role_and_suppress' ) );
	}

	/**
	 * Check if the current user's role is in the silent roles list,
	 * and if so, output CSS to hide all admin notices.
	 *
	 * @return void
	 */
	public function check_user_role_and_suppress() {
		// Get the list of silent roles from options.
		$silent_roles = get_option( 'techanum_silent_roles', array() );

		// If no silent roles are configured, do nothing.
		if ( empty( $silent_roles ) ) {
			return;
		}

		// Get the current user's roles.
		$user = wp_get_current_user();
		if ( ! $user instanceof WP_User || 0 === $user->ID ) {
			return;
		}

		$user_roles = (array) $user->roles;

		// Administrators are never suppressed, regardless of saved options.
		if ( in_array( 'administrator', $user_roles, true ) ) {
			return;
		}

		// Check if any of the user's roles are in the silent roles list.
		$should_suppress = false;
		foreach ( $user_roles as $role ) {
			if ( in_array( $role, $silent_roles, true ) ) {
				$should_suppress = true;
				break;
			}
		}

		// If the user's role is in the silent list, add the CSS to hide notices.
		if ( $should_suppress ) {
			add_action( 'admin_head', array( $this, 'output_notice_suppression_css' ) );
		}
	}

	/**
	 * Output inline CSS to hide admin notices.
	 *
	 * @return void
	 */
	public function output_notice_suppression_css() {
		?>
		<style id="techanum-notice-suppression">
			.notice {
				display: none !important;
			}
		</style>
		<?php
	}
}
