<?php
/**
 * Techanum Maintenance - Maintenance Page Template
 *
 * @package TechanumMaintenance
 * @license GPL-3.0-or-later
 * @link    https://techanum.com/maintenance/
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Headers are already sent by the class, but set them here as a safety measure.
if ( ! headers_sent() ) {
	status_header( 503 );
	header( 'Retry-After: 3600' );
}

// Retrieve custom settings.
$custom_logo    = get_option( 'techanum_maintenance_logo', '' );
$custom_message = get_option( 'techanum_maintenance_custom_message', '' );

// If a custom message is set, it takes priority; otherwise request one from the API.
if ( ! empty( $custom_message ) ) {
	$maintenance_message = $custom_message;
} else {
	// Guard: ensure the API class is available before instantiating it.
	if ( ! class_exists( 'Techanum_Antigravity_API' ) ) {
		require_once dirname( __DIR__ ) . '/includes/class-antigravity-api.php';
	}
	$antigravity_api     = new Techanum_Antigravity_API();
	$maintenance_message = $antigravity_api->get_dynamic_message();
}

/* translators: %s: site name */
$page_title = sprintf( __( 'Under Maintenance – %s', 'techanum-maintenance' ), get_bloginfo( 'name' ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $page_title ); ?></title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
			background: #f5f5f7;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			padding: 20px;
		}

		.maintenance-container {
			background: #ffffff;
			border-radius: 12px;
			box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
			padding: 60px 40px;
			max-width: 520px;
			width: 100%;
			text-align: center;
		}

		.maintenance-icon {
			font-size: 48px;
			margin-bottom: 24px;
		}

		.maintenance-logo {
			margin-bottom: 24px;
		}

		.maintenance-logo img {
			max-width: 150px;
			height: auto;
			display: block;
			margin: 0 auto;
		}

		.maintenance-title {
			font-size: 24px;
			font-weight: 600;
			color: #1d1d1f;
			margin-bottom: 16px;
		}

		.maintenance-message {
			font-size: 16px;
			color: #86868b;
			line-height: 1.6;
			margin-bottom: 32px;
		}

		.maintenance-footer {
			font-size: 13px;
			color: #aeaeb2;
			margin-top: 40px;
		}

		/* Responsive */
		@media (max-width: 600px) {
			.maintenance-container {
				padding: 40px 24px;
			}
			.maintenance-title {
				font-size: 20px;
			}
		}
	</style>
</head>
<body>
	<div class="maintenance-container">
		<?php if ( ! empty( $custom_logo ) ) : ?>
			<div class="maintenance-logo">
				<img
					src="<?php echo esc_url( $custom_logo ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				/>
			</div>
		<?php else : ?>
			<div class="maintenance-icon">🛠️</div>
		<?php endif; ?>
		<h1 class="maintenance-title">
			<?php esc_html_e( 'We are in scheduled maintenance', 'techanum-maintenance' ); ?>
		</h1>
		<p class="maintenance-message">
			<?php echo esc_html( $maintenance_message ); ?>
		</p>
		<p class="maintenance-footer">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</p>
	</div>
</body>
</html>
