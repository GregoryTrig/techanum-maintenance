<?php
/**
 * Techanum Maintenance Settings Page
 *
 * Handles the plugin settings page, settings registration, sanitization,
 * media uploader integration and the Pro teaser box.
 *
 * @package TechanumMaintenance
 * @since   1.4.0
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

        add_settings_section(
            'techanum_maintenance_page',
            __( 'Σελίδα Συντήρησης', 'techanum-maintenance' ),
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

        add_settings_section(
            'techanum_admin_notices',
            __( 'Διαχείριση Ειδοποιήσεων', 'techanum-maintenance' ),
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
    }

    /**
     * Enqueue the WordPress media uploader on this settings page.
     *
     * @param string $hook_suffix Current admin page hook suffix.
     * @return void
     */
    public function enqueue_media_uploader( $hook_suffix ) {
        if ( 'settings_page_' . $this->page_slug !== $hook_suffix ) {
            return;
        }

        wp_enqueue_media();

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
        $logo_url = get_option( 'techanum_maintenance_logo', '' );
        $hidden   = empty( $logo_url ) ? ' style="display:none;"' : '';
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
        <?php
    }

    /**
     * Render the excluded roles field.
     *
     * @return void
     */
    public function render_excluded_roles_field() {
        $excluded_roles = get_option( 'techanum_excluded_roles', array() );
        $editable_roles  = get_editable_roles();

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
                    <?php echo esc_html( $role_data['name'] ); ?>
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
     * @return void
     */
    public function render_silent_roles_field() {
        $silent_roles = get_option( 'techanum_silent_roles', array() );
        $editable_roles = get_editable_roles();

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
                    <?php echo esc_html( $role_data['name'] ); ?>
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
     * Render the settings page.
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

            <div class="techanum-pro-teaser" style="margin-top: 24px; padding: 20px; border: 1px solid #ccd0d4; background: #f9fafb; border-radius: 6px;">
                <h2 style="margin-top: 0; margin-bottom: 8px; font-size: 1.25em;">
                    <?php esc_html_e( 'Techanum Maintenance Pro', 'techanum-maintenance' ); ?>
                </h2>
                <p style="margin: 0 0 12px;">
                    <?php esc_html_e( 'The Pro version offers advanced scheduling, a countdown timer, and maintenance page templates.', 'techanum-maintenance' ); ?>
                </p>
                <a href="https://techanum.com/maintenance/" target="_blank" rel="noopener noreferrer" class="button button-primary">
                    <?php esc_html_e( 'Learn more', 'techanum-maintenance' ); ?>
                </a>
            </div>
        </div>
        <?php
    }
}
