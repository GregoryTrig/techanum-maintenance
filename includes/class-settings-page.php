<?php
/**
 * Techanum Maintenance Settings Page
 *
 * Handles the plugin settings page, settings registration, sanitization,
 * and media uploader integration.
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
 * Class Techanum_Maintenance_Settings
 */
class Techanum_Maintenance_Settings {

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'techanum-maintenance';

	/**
	 * Option group used by the Settings API.
	 *
	 * @var string
	 */
	private $option_group = 'techanum_maintenance_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_uploader' ) );
		add_action( 'wp_ajax_techanum_generate_ai_message', array( $this, 'ajax_generate_ai_message' ) );
	}

	/**
	 * Add the settings page under Settings.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Techanum Maintenance', 'techanum-maintenance' ),
			__( 'Techanum Maintenance', 'techanum-maintenance' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
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
			'techanum_excluded_roles',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_excluded_roles' ),
				'default'           => array(),
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

		register_setting(
			$this->option_group,
			'techanum_maintenance_api_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_api_key' ),
				'default'           => '',
			)
		);

		register_setting(
			$this->option_group,
			'techanum_maintenance_ai_provider',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_ai_provider' ),
				'default'           => 'auto',
			)
		);

		// Custom provider settings.
		register_setting(
			$this->option_group,
			'techanum_maintenance_custom_base_url',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			)
		);

		register_setting(
			$this->option_group,
			'techanum_maintenance_custom_model',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// ── Section: Maintenance Page ──────────────────────────────────────────
		add_settings_section(
			'techanum_maintenance_page',
			__( 'Maintenance Page', 'techanum-maintenance' ),
			array( $this, 'render_maintenance_section_description' ),
			$this->page_slug
		);

		add_settings_field(
			'techanum_maintenance_active',
			__( 'Enable Maintenance Page', 'techanum-maintenance' ),
			array( $this, 'render_active_field' ),
			$this->page_slug,
			'techanum_maintenance_page'
		);

		add_settings_field(
			'techanum_maintenance_logo',
			__( 'Maintenance Logo', 'techanum-maintenance' ),
			array( $this, 'render_logo_field' ),
			$this->page_slug,
			'techanum_maintenance_page'
		);

		add_settings_field(
			'techanum_maintenance_custom_message',
			__( 'Custom Maintenance Message', 'techanum-maintenance' ),
			array( $this, 'render_message_field' ),
			$this->page_slug,
			'techanum_maintenance_page'
		);

		add_settings_field(
			'techanum_excluded_roles',
			__( 'Excluded Roles', 'techanum-maintenance' ),
			array( $this, 'render_excluded_roles_field' ),
			$this->page_slug,
			'techanum_maintenance_page'
		);

		// ── Section: Admin Notices ─────────────────────────────────────────────
		add_settings_section(
			'techanum_admin_notices',
			__( 'Admin Notices Management', 'techanum-maintenance' ),
			array( $this, 'render_notices_section_description' ),
			$this->page_slug
		);

		add_settings_field(
			'techanum_silent_roles',
			__( 'Silent Roles', 'techanum-maintenance' ),
			array( $this, 'render_silent_roles_field' ),
			$this->page_slug,
			'techanum_admin_notices'
		);

		// ── Section: API Settings ──────────────────────────────────────────────
		add_settings_section(
			'techanum_api_settings',
			__( 'API Settings', 'techanum-maintenance' ),
			array( $this, 'render_api_section_description' ),
			$this->page_slug
		);

		add_settings_field(
			'techanum_maintenance_ai_provider',
			__( 'AI Provider', 'techanum-maintenance' ),
			array( $this, 'render_ai_provider_field' ),
			$this->page_slug,
			'techanum_api_settings'
		);

		add_settings_field(
			'techanum_maintenance_api_key',
			__( 'API Key', 'techanum-maintenance' ),
			array( $this, 'render_api_key_field' ),
			$this->page_slug,
			'techanum_api_settings'
		);

		add_settings_field(
			'techanum_maintenance_custom_base_url',
			__( 'Custom Base URL', 'techanum-maintenance' ),
			array( $this, 'render_custom_base_url_field' ),
			$this->page_slug,
			'techanum_api_settings'
		);

		add_settings_field(
			'techanum_maintenance_custom_model',
			__( 'Custom Model', 'techanum-maintenance' ),
			array( $this, 'render_custom_model_field' ),
			$this->page_slug,
			'techanum_api_settings'
		);
	}

	/**
	 * Enqueue the WordPress media uploader and admin JS on this settings page.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_media_uploader( $hook_suffix ) {
		if ( 'settings_page_' . $this->page_slug !== $hook_suffix ) {
			return;
		}

		wp_enqueue_media();

		// Enqueue the "Generate with AI" button script.
		wp_enqueue_script(
			'techanum-admin',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/techanum-admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'techanum-admin',
			'techanumAdmin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'techanum_generate_ai_message' ),
			)
		);

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

				$( '#techanum-toggle-api-key' ).on( 'click', function() {
					var input = $( '#techanum-maintenance-api-key' );
					var isPassword = input.attr( 'type' ) === 'password';
					input.attr( 'type', isPassword ? 'text' : 'password' );
					$( this ).text( isPassword ? '" . esc_js( __( 'Hide', 'techanum-maintenance' ) ) . "' : '" . esc_js( __( 'Show', 'techanum-maintenance' ) ) . "' );
				});
			});
		";

		wp_add_inline_script( 'techanum-admin', $inline_js );
	}

	/**
	 * Render the maintenance section description.
	 *
	 * @return void
	 */
	public function render_maintenance_section_description() {
		echo '<p>' . esc_html__(
			'Configure the maintenance page behavior, logo and message that visitors see while the site is in maintenance mode.',
			'techanum-maintenance'
		) . '</p>';
	}

	/**
	 * Render the active checkbox field.
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
			<?php esc_html_e( 'Enable maintenance page', 'techanum-maintenance' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'When enabled, visitors will see the maintenance page instead of the normal site.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the logo upload field.
	 *
	 * @return void
	 */
	public function render_logo_field() {
		$logo_url    = get_option( 'techanum_maintenance_logo', '' );
		$has_logo    = ! empty( $logo_url );
		$btn_hidden  = $has_logo ? '' : ' style="display:none;"';
		$img_hidden  = $has_logo ? '' : ' display:none;';
		?>
		<div>
			<input
				type="hidden"
				id="techanum-maintenance-logo"
				name="techanum_maintenance_logo"
				value="<?php echo esc_url( $logo_url ); ?>"
			/>
			<button type="button" class="button" id="techanum-upload-logo-btn">
				<?php esc_html_e( 'Upload Logo', 'techanum-maintenance' ); ?>
			</button>
			<button type="button" class="button" id="techanum-remove-logo-btn"<?php echo $btn_hidden; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contains only a safe inline style attribute ?>>
				<?php esc_html_e( 'Remove Logo', 'techanum-maintenance' ); ?>
			</button>
			<div style="margin-top: 10px;">
				<img
					id="techanum-logo-preview"
					src="<?php echo esc_url( $logo_url ); ?>"
					alt="<?php esc_attr_e( 'Logo preview', 'techanum-maintenance' ); ?>"
					style="max-width: 150px; height: auto;<?php echo esc_attr( $img_hidden ); ?>"
				/>
			</div>
			<p class="description">
				<?php esc_html_e( 'Upload a logo to display on the maintenance page instead of the default icon.', 'techanum-maintenance' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the custom message field.
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
			<?php esc_html_e( 'If set, this message will override the AI-generated maintenance message.', 'techanum-maintenance' ); ?>
		</p>
		<p style="margin-top: 8px;">
			<button type="button" class="button" id="techanum-generate-ai-btn">
				<?php esc_html_e( 'Generate with AI', 'techanum-maintenance' ); ?>
			</button>
			<span
				id="techanum-ai-spinner"
				class="spinner"
				style="float: none; margin: 0 4px; vertical-align: middle;"
			></span>
		</p>
		<div
			id="techanum-ai-notice"
			style="display: none; margin-top: 8px; padding: 8px 12px; border-left-width: 4px; border-left-style: solid;"
		></div>
		<?php
	}

	/**
	 * Render the excluded roles field.
	 *
	 * Administrators are always excluded and are therefore not shown in this list.
	 *
	 * @return void
	 */
	public function render_excluded_roles_field() {
		$excluded_roles = get_option( 'techanum_excluded_roles', array() );
		if ( ! is_array( $excluded_roles ) ) {
			$excluded_roles = array();
		}

		$editable_roles = get_editable_roles();
		// Administrators are always excluded — remove them from the UI.
		unset( $editable_roles['administrator'] );

		if ( empty( $editable_roles ) ) {
			echo '<p>' . esc_html__( 'No roles available to configure.', 'techanum-maintenance' ) . '</p>';
			return;
		}
		?>
		<fieldset>
			<input type="hidden" name="techanum_excluded_roles_submitted" value="1" />
			<?php foreach ( $editable_roles as $role_slug => $role_data ) : ?>
				<label style="display: block; margin-bottom: 8px;">
					<input
						type="checkbox"
						name="techanum_excluded_roles[]"
						value="<?php echo esc_attr( $role_slug ); ?>"
						<?php checked( in_array( $role_slug, $excluded_roles, true ), true ); ?>
					/>
					<?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Users with the selected roles will bypass the maintenance page and see the normal site.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the admin notices section description.
	 *
	 * @return void
	 */
	public function render_notices_section_description() {
		echo '<p>' . esc_html__(
			'Choose which roles should not see WordPress admin notices. Administrators always retain notice visibility.',
			'techanum-maintenance'
		) . '</p>';
	}

	/**
	 * Render the silent roles checkbox field.
	 *
	 * Administrators are always visible and are therefore not shown in this list.
	 *
	 * @return void
	 */
	public function render_silent_roles_field() {
		$silent_roles = get_option( 'techanum_silent_roles', array() );
		if ( ! is_array( $silent_roles ) ) {
			$silent_roles = array();
		}

		$editable_roles = get_editable_roles();
		// Administrators always see notices — remove them from the UI.
		unset( $editable_roles['administrator'] );

		if ( empty( $editable_roles ) ) {
			echo '<p>' . esc_html__( 'No roles available to configure.', 'techanum-maintenance' ) . '</p>';
			return;
		}
		?>
		<fieldset>
			<input type="hidden" name="techanum_silent_roles_submitted" value="1" />
			<?php foreach ( $editable_roles as $role_slug => $role_data ) : ?>
				<label style="display: block; margin-bottom: 8px;">
					<input
						type="checkbox"
						name="techanum_silent_roles[]"
						value="<?php echo esc_attr( $role_slug ); ?>"
						<?php checked( in_array( $role_slug, $silent_roles, true ), true ); ?>
					/>
					<?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Users with the selected roles will not see admin notices in the dashboard.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize the silent roles option.
	 *
	 * @param mixed $value Submitted value.
	 * @return array
	 */
	public function sanitize_silent_roles( $value ) {
		if ( ! is_array( $value ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['techanum_silent_roles_submitted'] ) ) {
				return array();
			}
			return get_option( 'techanum_silent_roles', array() );
		}

		$all_roles   = wp_roles()->get_names();
		$valid_roles = array_keys( $all_roles );

		$sanitized = array_filter(
			$value,
			static function ( $role ) use ( $valid_roles ) {
				return 'administrator' !== $role && in_array( $role, $valid_roles, true );
			}
		);

		return array_values( $sanitized );
	}

	/**
	 * Sanitize the excluded roles option.
	 *
	 * @param mixed $value Submitted value.
	 * @return array
	 */
	public function sanitize_excluded_roles( $value ) {
		if ( ! is_array( $value ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['techanum_excluded_roles_submitted'] ) ) {
				return array();
			}
			return get_option( 'techanum_excluded_roles', array() );
		}

		$all_roles   = wp_roles()->get_names();
		$valid_roles = array_keys( $all_roles );

		$sanitized = array_filter(
			$value,
			static function ( $role ) use ( $valid_roles ) {
				return 'administrator' !== $role && in_array( $role, $valid_roles, true );
			}
		);

		return array_values( $sanitized );
	}

	/**
	 * Render the API settings section description.
	 *
	 * @return void
	 */
	public function render_api_section_description() {
		echo '<p>' . esc_html__(
			'Enter the API key for the AI service that dynamically generates maintenance messages. If you leave the field blank, an alternative message will be used.',
			'techanum-maintenance'
		) . '</p>';
	}

	/**
	 * Render the AI provider dropdown field.
	 *
	 * Allows the user to manually select their AI provider, or leave it on
	 * "Auto-detect" to let the router infer the provider from the API key prefix.
	 *
	 * @return void
	 */
	public function render_ai_provider_field() {
		$current = get_option( 'techanum_maintenance_ai_provider', 'auto' );

		$providers = array(
			'auto'      => __( 'Auto-detect (recommended)', 'techanum-maintenance' ),
			'openai'    => __( 'OpenAI (ChatGPT)', 'techanum-maintenance' ),
			'gemini'    => __( 'Google Gemini', 'techanum-maintenance' ),
			'anthropic' => __( 'Anthropic (Claude)', 'techanum-maintenance' ),
			'sharpapi'  => __( 'SharpAPI', 'techanum-maintenance' ),
			'edenai'    => __( 'Eden AI', 'techanum-maintenance' ),
			'aimlapi'   => __( 'AI/ML API', 'techanum-maintenance' ),
			'custom'    => __( 'Custom (OpenAI-compatible)', 'techanum-maintenance' ),
		);
		?>
		<select
			id="techanum-maintenance-ai-provider"
			name="techanum_maintenance_ai_provider"
		>
			<?php foreach ( $providers as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select your AI provider. "Auto-detect" identifies Anthropic keys (starting with "sk-ant-"), OpenAI keys (starting with "sk-"), and Google Gemini keys (starting with "AIza") automatically; all other keys default to AI/ML API. If you use SharpAPI or Eden AI, select the matching option explicitly. Choose "Custom" to connect to any OpenAI-compatible API (OpenRouter, Together AI, Ollama, etc.)', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the API key password field.
	 *
	 * @return void
	 */
	public function render_api_key_field() {
		$api_key   = get_option( 'techanum_maintenance_api_key', '' );
		$has_value = ! empty( $api_key );
		?>
		<div style="display: flex; align-items: center; gap: 8px;">
			<input
				type="password"
				id="techanum-maintenance-api-key"
				name="techanum_maintenance_api_key"
				value="<?php echo esc_attr( $api_key ); ?>"
				class="regular-text"
				autocomplete="new-password"
				placeholder="<?php echo $has_value ? esc_attr( '••••••••' ) : esc_attr__( 'Enter your API key', 'techanum-maintenance' ); ?>"
			/>
			<button type="button" class="button" id="techanum-toggle-api-key">
				<?php esc_html_e( 'Show', 'techanum-maintenance' ); ?>
			</button>
		</div>
		<p class="description">
			<?php esc_html_e( 'Enter the API key for the AI service that dynamically generates maintenance messages. If you leave the field blank, an alternative message will be used.', 'techanum-maintenance' ); ?>
			<?php if ( $has_value ) : ?>
				<br><span style="color: #46b450;">&#10003; <?php esc_html_e( 'An API key is currently saved.', 'techanum-maintenance' ); ?></span>
			<?php endif; ?>
		</p>
		<p class="description">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s is the link to recommended AI API providers */
					__( 'Looking for an API key? Check our <a href="%s" target="_blank" rel="noopener noreferrer">recommended AI API providers</a>.', 'techanum-maintenance' ),
					esc_url( 'https://techanum.com/ai-tools/' )
				)
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render the Custom Base URL field (shown only when provider is "custom").
	 *
	 * @return void
	 */
	public function render_custom_base_url_field() {
		$base_url = get_option( 'techanum_maintenance_custom_base_url', '' );
		?>
		<input
			type="url"
			id="techanum-maintenance-custom-base-url"
			name="techanum_maintenance_custom_base_url"
			value="<?php echo esc_url( $base_url ); ?>"
			class="regular-text"
			placeholder="https://openrouter.ai/api/v1"
		/>
		<p class="description">
			<?php esc_html_e( 'The base URL of your OpenAI-compatible API endpoint (e.g. https://openrouter.ai/api/v1 or http://localhost:11434/v1). The path /chat/completions will be appended automatically unless the URL already ends with it.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the Custom Model field (shown only when provider is "custom").
	 *
	 * @return void
	 */
	public function render_custom_model_field() {
		$model = get_option( 'techanum_maintenance_custom_model', '' );
		?>
		<input
			type="text"
			id="techanum-maintenance-custom-model"
			name="techanum_maintenance_custom_model"
			value="<?php echo esc_attr( $model ); ?>"
			class="regular-text"
			placeholder="openai/gpt-3.5-turbo"
		/>
		<p class="description">
			<?php esc_html_e( 'The model identifier to send in the request (e.g. openai/gpt-3.5-turbo, llama3, mistralai/mistral-7b-instruct). Must be supported by your chosen API endpoint.', 'techanum-maintenance' ); ?>
		</p>
		<?php
	}

	// ── AJAX handler ───────────────────────────────────────────────────────────

	/**
	 * AJAX handler: generate an AI maintenance message on demand.
	 *
	 * Hooked to wp_ajax_techanum_generate_ai_message.
	 * Returns JSON via wp_send_json_success / wp_send_json_error.
	 *
	 * @return void
	 */
	public function ajax_generate_ai_message() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'techanum_generate_ai_message' ) ) {
			error_log( 'Techanum Maintenance [AJAX] - Nonce verification failed.' );
			wp_send_json_error( array( 'error' => 'Security check failed. Please refresh the page and try again.' ) );
		}

		// Capability check.
		if ( ! current_user_can( 'manage_options' ) ) {
			error_log( 'Techanum Maintenance [AJAX] - Insufficient permissions.' );
			wp_send_json_error( array( 'error' => 'You do not have permission to perform this action.' ) );
		}

		// Ensure the AI router is loaded.
		if ( ! function_exists( 'techanum_call_ai_api' ) ) {
			$router_file = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai-router.php';
			if ( file_exists( $router_file ) ) {
				require_once $router_file;
			}
		}

		if ( ! function_exists( 'techanum_call_ai_api' ) ) {
			error_log( 'Techanum Maintenance [AJAX] - techanum_call_ai_api() not available.' );
			wp_send_json_error( array( 'error' => 'AI function is not available. Please check the plugin installation.' ) );
		}

		// Check that an API key is configured.
		$api_key = get_option( 'techanum_maintenance_api_key', '' );
		if ( empty( trim( $api_key ) ) ) {
			error_log( 'Techanum Maintenance [AJAX] - No API key configured.' );
			wp_send_json_error( array( 'error' => 'No API key is configured. Please enter your API key in the API Settings section and save before generating.' ) );
		}

		$prompt = 'Write a friendly maintenance message for a website, 2-3 sentences.';

		error_log( 'Techanum Maintenance [AJAX] - Calling AI API on demand.' );

		$result = techanum_call_ai_api( $prompt );

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			error_log( 'Techanum Maintenance [AJAX] - AI call returned WP_Error: ' . $error_message );
			wp_send_json_error( array( 'error' => $error_message ) );
		}

		if ( empty( $result ) ) {
			error_log( 'Techanum Maintenance [AJAX] - AI call returned empty result.' );
			wp_send_json_error( array( 'error' => 'The AI returned an empty response. Please check your API key and provider settings.' ) );
		}

		error_log( 'Techanum Maintenance [AJAX] - AI message generated successfully. Length: ' . strlen( $result ) . ' chars.' );

		wp_send_json_success( array( 'message' => $result ) );
	}

	// ── Option sanitizers ──────────────────────────────────────────────────────

	/**
	 * Sanitize the API key option.
	 *
	 * Trims whitespace and applies sanitize_text_field.
	 * Also clears the AI-message transient cache whenever the key changes,
	 * so the next page load immediately triggers a fresh API call instead
	 * of serving the previously cached fallback message.
	 *
	 * @param mixed $value Submitted value.
	 * @return string
	 */
	public function sanitize_api_key( $value ) {
		$new_key = trim( sanitize_text_field( (string) $value ) );
		$old_key = trim( (string) get_option( 'techanum_maintenance_api_key', '' ) );

		// If the key has changed (including being set for the first time),
		// bust the transient so the next visitor gets a fresh AI message.
		if ( $new_key !== $old_key ) {
			delete_transient( 'techanum_maintenance_ai_message' );
		}

		return $new_key;
	}

	/**
	 * Sanitize the AI provider option.
	 *
	 * Accepts only the known provider slugs (including 'custom'); falls back
	 * to "auto" for any unrecognised value. Also clears the AI-message
	 * transient cache when the provider changes so the next page load
	 * triggers a fresh API call.
	 *
	 * @param mixed $value Submitted value.
	 * @return string One of: auto, openai, gemini, sharpapi, edenai, aimlapi, custom.
	 */
	public function sanitize_ai_provider( $value ) {
		$allowed = array( 'auto', 'openai', 'gemini', 'anthropic', 'sharpapi', 'edenai', 'aimlapi', 'custom' );
		$new_val = in_array( $value, $allowed, true ) ? $value : 'auto';
		$old_val = get_option( 'techanum_maintenance_ai_provider', 'auto' );

		// Bust the cache whenever the provider changes.
		if ( $new_val !== $old_val ) {
			delete_transient( 'techanum_maintenance_ai_message' );
		}

		return $new_val;
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_provider = get_option( 'techanum_maintenance_ai_provider', 'auto' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( $this->option_group );

				// Before do_settings_sections we inject a small piece of CSS to
				// pre-hide the custom-provider rows if the provider is not "custom"
				// so there is no flash-of-visible-content before the JS runs.
				$hide_style = ( 'custom' !== $current_provider ) ? ' style="display:none;"' : '';
				echo '<style>
					.techanum-custom-provider-row' . ( 'custom' !== $current_provider ? ' { display: none; }' : '' ) . '
				</style>';

				do_settings_sections( $this->page_slug );
				submit_button( __( 'Save Settings', 'techanum-maintenance' ) );
				?>
			</form>

		</div>

		<script>
		jQuery( document ).ready( function( $ ) {
			// Attach the class to the <tr> elements that wrap the custom fields.
			// The Settings API renders each field inside a <tr> whose id attribute
			// is based on the field id; we target those rows directly.
			var $baseUrlRow  = $( '#techanum-maintenance-custom-base-url' ).closest( 'tr' );
			var $modelRow    = $( '#techanum-maintenance-custom-model' ).closest( 'tr' );

			$baseUrlRow.addClass( 'techanum-custom-provider-row' );
			$modelRow.addClass( 'techanum-custom-provider-row' );

			// Apply the initial visibility that matches the saved provider.
			var initialProvider = $( '#techanum-maintenance-ai-provider' ).val();
			if ( 'custom' !== initialProvider ) {
				$baseUrlRow.hide();
				$modelRow.hide();
			}

			// Re-apply whenever the dropdown changes.
			$( '#techanum-maintenance-ai-provider' ).on( 'change', function() {
				if ( 'custom' === $( this ).val() ) {
					$baseUrlRow.show();
					$modelRow.show();
				} else {
					$baseUrlRow.hide();
					$modelRow.hide();
				}
			} );
		} );
		</script>
		<?php
	}
}
