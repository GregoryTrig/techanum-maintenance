<?php
/**
 * Techanum Settings Class
 *
 * Registers a settings page under the WordPress admin menu
 * where the user can upload a custom logo and write a custom
 * maintenance message that overrides the Gemini API output.
 *
 * @package TechanumMaintenance
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Techanum_Settings
 *
 * Handles the plugin settings page, field registration,
 * sanitization, and the WordPress Media Uploader integration.
 */
class Techanum_Settings {

	/**
	 * The slug used for the settings page.
	 *
	 * @var string
	 */
	private $page_slug = 'techanum-maintenance';

	/**
	 * The option group name for the Settings API.
	 *
	 * @var string
	 */
	private $option_group = 'techanum_maintenance_options';

	/**
	 * Constructor — register hooks only when in the admin area.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_uploader' ) );
	}

	/**
	 * Add a settings page under the "Settings" menu.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Techanum Maintenance Settings', 'techanum-maintenance' ),
			__( 'Techanum Maintenance', 'techanum-maintenance' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields via the Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		// --- Register settings ---
		register_setting(
			$this->option_group,
			'techanum_maintenance_active',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => false,
			)
		);

		register_setting(
			$this->option_group,
			'techanum_maintenance_logo',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			)
		);

		register_setting(
			$this->option_group,
			'techanum_maintenance_custom_message',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => '',
			)
		);

		register_setting(
			$this->option_group,
			'techanum_silent_roles',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_silent_roles' ),
				'default'           => array(),
			)
		);

		// --- Maintenance Mode Section ---
		add_settings_section(
			'techanum_maintenance_general',
			__( 'Maintenance Mode', 'techanum-maintenance' ),
			array( $this, 'render_general_section_description' ),
			$this->page_slug
		);

		add_settings_field(
			'techanum_maintenance_active',
			__( 'Enable Maintenance Mode', 'techanum-maintenance' ),
			array( $this, 'render_active_field' ),
			$this->page_slug,
			'techanum_maintenance_general'
		);

		// --- Section ---
		add_settings_section(
			'techanum_maintenance_customization',
			__( 'Customization', 'techanum-maintenance' ),
			array( $this, 'render_section_description' ),
			$this->page_slug
		);

		// --- Fields ---
		add_settings_field(
			'techanum_maintenance_logo',
			__( 'Logo', 'techanum-maintenance' ),
			array( $this, 'render_logo_field' ),
			$this->page_slug,
			'techanum_maintenance_customization'
		);

		add_settings_field(
			'techanum_maintenance_custom_message',
			__( 'Custom Message', 'techanum-maintenance' ),
			array( $this, 'render_message_field' ),
			$this->page_slug,
			'techanum_maintenance_customization'
		);

		// --- Admin Notices Management Section ---
		add_settings_section(
			'techanum_notices_management',
			__( 'Admin Notices Management', 'techanum-maintenance' ),
			array( $this, 'render_notices_section_description' ),
			$this->page_slug
		);

		// --- Admin Notices Management Fields ---
		add_settings_field(
			'techanum_silent_roles',
			__( 'Silent Roles', 'techanum-maintenance' ),
			array( $this, 'render_silent_roles_field' ),
			$this->page_slug,
			'techanum_notices_management'
		);
	}

	/**
	 * Enqueue the WordPress Media Uploader scripts
	 * only on this plugin's settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_media_uploader( $hook_suffix ) {
		if ( 'settings_page_' . $this->page_slug !== $hook_suffix ) {
			return;
		}

		// Φόρτωση του WordPress Media Uploader.
		wp_enqueue_media();

		// Inline script για το κουμπί media uploader.
		// Χρησιμοποιούμε το 'jquery' handle (πάντα διαθέσιμο στο admin)
		// ώστε το wp_add_inline_script να λειτουργεί σωστά.
		$inline_js = "
			jQuery( document ).ready( function( $ ) {
				var mediaUploader;

				$( '#techanum-upload-logo-btn' ).on( 'click', function( e ) {
					e.preventDefault();

					if ( mediaUploader ) {
						mediaUploader.open();
						return;
					}

					mediaUploader = wp.media({
						title:    '" . esc_js( __( 'Select Logo', 'techanum-maintenance' ) ) . "',
						button:   { text: '" . esc_js( __( 'Use this image', 'techanum-maintenance' ) ) . "' },
						multiple: false,
						library:  { type: 'image' }
					});

					mediaUploader.on( 'select', function() {
						var attachment = mediaUploader.state().get( 'selection' ).first().toJSON();
						$( '#techanum-maintenance-logo' ).val( attachment.url );
						$( '#techanum-logo-preview' ).attr( 'src', attachment.url ).show();
						$( '#techanum-remove-logo-btn' ).show();
					});

					mediaUploader.open();
				});

				$( '#techanum-remove-logo-btn' ).on( 'click', function( e ) {
					e.preventDefault();
					$( '#techanum-maintenance-logo' ).val( '' );
					$( '#techanum-logo-preview' ).hide();
					$( this ).hide();
				});
			});
		";

		wp_add_inline_script( 'jquery', $inline_js );
	}

	/**
	 * Render the general (Maintenance Mode) section description.
	 *
	 * @return void
	 */
	public function render_general_section_description() {
		echo '<p>' . esc_html__(
			'Enable or disable the maintenance mode. When active, visitors will see the maintenance page instead of your site.',
			'techanum-maintenance'
		) . '</p>';
	}

	/**
	 * Render the maintenance mode active toggle field.
	 *
	 * @return void
	 */
	public function render_active_field() {
		$is_active = (bool) get_option( 'techanum_maintenance_active', false );
		?>
		<label>
			<input
				type="checkbox"
				id="techanum-maintenance-active"
				name="techanum_maintenance_active"
				value="1"
				<?php checked( $is_active ); ?>
			/>
			<?php esc_html_e( 'Put the site in maintenance mode', 'techanum-maintenance' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'When enabled, all visitors (except administrators) will see the maintenance page.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the section description.
	 *
	 * @return void
	 */
	public function render_section_description() {
		echo '<p>' . esc_html__(
			'Customize the maintenance page appearance. A custom message will override the AI-generated message.',
			'techanum-maintenance'
		) . '</p>';
	}

	/**
	 * Render the logo upload field.
	 *
	 * @return void
	 */
	public function render_logo_field() {
		$logo_url = get_option( 'techanum_maintenance_logo', '' );
		$hidden   = empty( $logo_url ) ? ' style="display:none;"' : '';
		?>
		<div class="techanum-logo-field">
			<input
				type="hidden"
				id="techanum-maintenance-logo"
				name="techanum_maintenance_logo"
				value="<?php echo esc_url( $logo_url ); ?>"
			/>
			<button type="button" class="button" id="techanum-upload-logo-btn">
				<?php esc_html_e( 'Upload Logo', 'techanum-maintenance' ); ?>
			</button>
			<button type="button" class="button" id="techanum-remove-logo-btn"<?php echo $hidden; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php esc_html_e( 'Remove Logo', 'techanum-maintenance' ); ?>
			</button>
			<div style="margin-top: 10px;">
				<img
					id="techanum-logo-preview"
					src="<?php echo esc_url( $logo_url ); ?>"
					alt="<?php esc_attr_e( 'Logo preview', 'techanum-maintenance' ); ?>"
					style="max-width: 150px; height: auto;<?php echo empty( $logo_url ) ? ' display:none;' : ''; ?>"
				/>
			</div>
			<p class="description">
				<?php esc_html_e( 'Upload a logo to display on the maintenance page instead of the default icon.', 'techanum-maintenance' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the custom message textarea field.
	 *
	 * @return void
	 */
	public function render_message_field() {
		$message = get_option( 'techanum_maintenance_custom_message', '' );
		?>
		<textarea
			id="techanum-maintenance-custom-message"
			name="techanum_maintenance_custom_message"
			rows="4"
			cols="50"
			class="large-text"
			placeholder="<?php esc_attr_e( 'Leave empty to use the AI-generated message', 'techanum-maintenance' ); ?>"
		><?php echo esc_textarea( $message ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'If set, this message will be shown instead of the AI-generated one.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the admin notices management section description.
	 *
	 * @return void
	 */
	public function render_notices_section_description() {
		echo '<p>' . esc_html__(
			'Select which user roles should not see WordPress admin notices. Administrators will always see notifications.',
			'techanum-maintenance'
		) . '</p>';
	}

	/**
	 * Render the silent roles checkboxes field.
	 *
	 * @return void
	 */
	public function render_silent_roles_field() {
		$silent_roles = get_option( 'techanum_silent_roles', array() );

		// Get all editable roles (excludes administrator).
		$editable_roles = get_editable_roles();

		if ( empty( $editable_roles ) ) {
			echo '<p>' . esc_html__( 'No roles available to configure.', 'techanum-maintenance' ) . '</p>';
			return;
		}

		?>
		<fieldset>
			<?php foreach ( $editable_roles as $role_slug => $role_data ) : ?>
				<label style="display: block; margin-bottom: 8px;">
					<input
						type="checkbox"
						name="techanum_silent_roles[]"
						value="<?php echo esc_attr( $role_slug ); ?>"
						<?php checked( in_array( $role_slug, $silent_roles, true ), true ); ?>
					/>
					<?php echo esc_html( $role_data['name'] ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Users with the selected roles will not see any admin notices.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize the silent roles input.
	 *
	 * Validates that each submitted role slug is a valid WordPress role
	 * and returns an array of valid role slugs.
	 *
	 * @param mixed $value The submitted value from the checkbox field.
	 * @return array Array of valid role slugs or empty array.
	 */
	public function sanitize_silent_roles( $value ) {
		// If value is not an array, return empty array.
		if ( ! is_array( $value ) ) {
			return array();
		}

		// Get all valid role slugs in the system.
		$all_roles = wp_roles()->get_names();
		$valid_roles = array_keys( $all_roles );

		// Filter to only include valid roles.
		$sanitized = array_filter(
			$value,
			static function ( $role ) use ( $valid_roles ) {
				return in_array( $role, $valid_roles, true );
			}
		);

		// Return re-indexed array.
		return array_values( $sanitized );
	}

	/**
	 * Render the full settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( $this->option_group );
				do_settings_sections( $this->page_slug );
				submit_button( __( 'Save Settings', 'techanum-maintenance' ) );
				?>
			</form>
		</div>
		<?php
	}
}
