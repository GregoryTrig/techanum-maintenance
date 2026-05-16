<?php
/**
 * Techanum Antigravity API Class
 *
 * Handles communication with the Google Gemini API (Antigravity)
 * to generate dynamic maintenance messages.
 *
 * @package TechanumMaintenance
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Techanum_Antigravity_API
 *
 * Connects to the Gemini API and returns a friendly,
 * AI-generated maintenance message in Greek.
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
	 * @return string The AI-generated message, or a fallback if the API is unavailable.
	 */
	public function get_dynamic_message() {
		// Πρώτα ελέγχουμε αν υπάρχει cached μήνυμα.
		$cached_message = get_transient( $this->cache_key );
		if ( false !== $cached_message ) {
			return $cached_message;
		}

		// Αν δεν υπάρχει API key, επιστρέφουμε fallback.
		$api_key = $this->get_api_key();
		if ( ! $api_key ) {
			return $this->get_fallback_message();
		}

		// Κλήση στο Gemini API.
		$message = $this->call_gemini_api( $api_key );

		if ( $message ) {
			// Αποθήκευση στο transient cache για 1 ώρα.
			set_transient( $this->cache_key, $message, $this->cache_duration );
			return $message;
		}

		return $this->get_fallback_message();
	}

	/**
	 * Send a POST request to the Gemini API.
	 *
	 * @param string $api_key The API key for authentication.
	 * @return string|false The generated text or false on failure.
	 */
	private function call_gemini_api( $api_key ) {
		$prompt = 'Γράψε ένα σύντομο, φιλικό μήνυμα στα ελληνικά που να ενημερώνει '
				. 'ότι ο ιστότοπος είναι σε συντήρηση και θα επιστρέψει σύντομα. '
				. 'Απάντησε μόνο με το μήνυμα, χωρίς εισαγωγικά ή επιπλέον σχόλια. '
				. 'Μέγιστο 2 προτάσεις.';

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

		// Έλεγχος σφαλμάτων δικτύου.
		if ( is_wp_error( $response ) ) {
			error_log( 'Techanum Maintenance - API Error: ' . $response->get_error_message() );
			return false;
		}

		// Έλεγχος HTTP status code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			error_log( 'Techanum Maintenance - API HTTP Error: ' . $response_code );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		// Εξαγωγή κειμένου από την απάντηση του Gemini.
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
