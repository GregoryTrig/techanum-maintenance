<?php
/**
 * Techanum Antigravity API Class
 *
 * Handles communication with the Google Gemini API (Antigravity)
 * to generate dynamic maintenance messages.
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
 * Class Techanum_Antigravity_API
 *
 * Connects to the Gemini API and returns a friendly,
 * AI-generated maintenance message.
 */
class Techanum_Antigravity_API {

	/**
	 * Gemini API endpoint.
	 *
	 * @var string
	 */
	private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

	/**
	 * Transient key for caching the generated message.
	 *
	 * @var string
	 */
	private $cache_key = 'techanum_maintenance_ai_message';

	/**
	 * Cache duration in seconds (1 hour).
	 *
	 * @var int
	 */
	private $cache_duration = HOUR_IN_SECONDS;

	/**
	 * Retrieve the API key from the WordPress option or wp-config.php constant.
	 *
	 * First tries to get the API key from the 'techanum_maintenance_api_key' option.
	 * If not set or empty, falls back to the TECHANUM_ANTIGRAVITY_API_KEY constant
	 * defined in wp-config.php.
	 *
	 * @return string|false The API key or false if not defined anywhere.
	 */
	private function get_api_key() {
		// First, try to get the API key from WordPress settings option.
		$api_key_option = get_option( 'techanum_maintenance_api_key', '' );
		if ( ! empty( $api_key_option ) ) {
			return trim( $api_key_option );
		}

		// Fallback to the wp-config.php constant.
		if ( defined( 'TECHANUM_ANTIGRAVITY_API_KEY' ) && TECHANUM_ANTIGRAVITY_API_KEY ) {
			return TECHANUM_ANTIGRAVITY_API_KEY;
		}

		return false;
	}

	/**
	 * Get a dynamic maintenance message via the Gemini API.
	 *
	 * Uses WordPress transients to cache the response for one hour
	 * so the API is not called on every single page load.
	 *
	 * The cache is intentionally NOT populated with the fallback message,
	 * so that entering an API key later will immediately trigger a real
	 * API call on the next page load instead of serving a stale fallback.
	 *
	 * @return string The AI-generated message, or a fallback if the API is unavailable.
	 */
	public function get_dynamic_message() {
		// First, check whether a cached message exists.
		$cached_message = get_transient( $this->cache_key );
		if ( false !== $cached_message ) {
			return $cached_message;
		}

		// If no API key is available, return the fallback message WITHOUT caching it,
		// so the next request will retry once a key has been saved.
		$api_key = $this->get_api_key();
		if ( ! $api_key ) {
			return $this->get_fallback_message();
		}

		// Call the Gemini API.
		$message = $this->call_gemini_api( $api_key );

		if ( $message ) {
			// Store the successful API result in the transient cache for 1 hour.
			set_transient( $this->cache_key, $message, $this->cache_duration );
			return $message;
		}

		// API call failed — return the fallback WITHOUT caching it so the next
		// request will retry the API rather than serving a stale fallback forever.
		return $this->get_fallback_message();
	}

	/**
	 * Send a POST request to the Gemini API.
	 *
	 * @param string $api_key The API key for authentication.
	 * @return string|false The generated text or false on failure.
	 */
	private function call_gemini_api( $api_key ) {
		$prompt = 'Write a short, friendly message in English informing that the website is under maintenance and will return soon. Reply only with the message, without quotes or extra comments. Maximum 2 sentences.';

		$request_url = add_query_arg( 'key', $api_key, $this->api_url );

		$body = wp_json_encode(
			array(
				'contents'         => array(
					array(
						'parts' => array(
							array(
								'text' => $prompt,
							),
						),
					),
				),
				'generationConfig' => array(
					'temperature'     => 0.9,
					'maxOutputTokens' => 200,
				),
			)
		);

		$response = wp_remote_post(
			$request_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => $body,
				'timeout' => 15,
			)
		);

		// Check for network errors.
		if ( is_wp_error( $response ) ) {
			error_log( 'Techanum Maintenance - API Error: ' . $response->get_error_message() );
			return false;
		}

		// Check the HTTP status code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$response_body_err = wp_remote_retrieve_body( $response );
			error_log( 'Techanum Maintenance - API HTTP Error: ' . $response_code . ' | Body: ' . $response_body_err );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		// Extract the text from the Gemini API response.
		if (
			isset( $data['candidates'][0]['content']['parts'][0]['text'] )
			&& ! empty( $data['candidates'][0]['content']['parts'][0]['text'] )
		) {
			return sanitize_text_field( $data['candidates'][0]['content']['parts'][0]['text'] );
		}

		error_log( 'Techanum Maintenance - Unexpected API response structure.' );
		return false;
	}

	/**
	 * Return the fallback maintenance message.
	 *
	 * Used when the API key is missing or the API call fails.
	 *
	 * @return string
	 */
	private function get_fallback_message() {
		return __(
			'We are performing scheduled maintenance. We will be back soon!',
			'techanum-maintenance'
		);
	}

	/**
	 * Clear the cached maintenance message.
	 *
	 * Useful when you want to force a fresh message from the API.
	 *
	 * @return void
	 */
	public function clear_cache() {
		delete_transient( $this->cache_key );
	}
}
