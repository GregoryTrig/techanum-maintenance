<?php
/**
 * Techanum AI Router
 *
 * Routes AI maintenance-message requests to the correct provider
 * (OpenAI, Google Gemini, SharpAPI, Eden AI, or AI/ML API) based on
 * either the user-selected provider setting or automatic key-prefix
 * detection.
 *
 * Also exposes the standalone helper function techanum_call_ai_api()
 * for use anywhere in the plugin.
 *
 * Preserves the same caching, fallback, and error-logging behaviour
 * as the original Techanum_Antigravity_API class.
 *
 * @package TechanumMaintenance
 * @license GPL-3.0-or-later
 * @link    https://techanum.com/maintenance/
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Standalone helper ──────────────────────────────────────────────────────────

/**
 * Call the configured AI API with a custom prompt.
 *
 * This is a thin wrapper around Techanum_AI_Router so that any part of the
 * plugin (or a child theme / another plugin) can request an AI-generated
 * string without needing to instantiate the router class directly.
 *
 * Uses the API key stored in the 'techanum_maintenance_api_key' option and
 * the provider stored in 'techanum_maintenance_ai_provider'.
 *
 * @since 1.1.0
 *
 * @param string $prompt The prompt to send to the AI.
 * @return string|WP_Error The generated text on success, or a WP_Error on failure.
 */
function techanum_call_ai_api( $prompt ) {
	if ( ! class_exists( 'Techanum_AI_Router' ) ) {
		$error_message = 'Techanum_AI_Router class is not available.';
		error_log( 'Techanum Maintenance [techanum_call_ai_api] - ' . $error_message );
		return new WP_Error( 'class_missing', $error_message );
	}

	$router = new Techanum_AI_Router();
	$result = $router->call_with_prompt( $prompt );

	if ( false === $result ) {
		$error_message = 'AI API call failed. Check the error log for details.';
		error_log( 'Techanum Maintenance [techanum_call_ai_api] - ' . $error_message );
		return new WP_Error( 'api_failure', $error_message );
	}

	return $result;
}

// ── Router class ───────────────────────────────────────────────────────────────

/**
 * Class Techanum_AI_Router
 *
 * Detects the active AI provider and dispatches the maintenance-message
 * request to the appropriate endpoint, then normalises the response.
 */
class Techanum_AI_Router {

	// ── Provider identifiers ───────────────────────────────────────────────

	const PROVIDER_AUTO    = 'auto';
	const PROVIDER_OPENAI  = 'openai';
	const PROVIDER_GEMINI  = 'gemini';
	const PROVIDER_SHARP   = 'sharpapi';
	const PROVIDER_EDEN    = 'edenai';
	const PROVIDER_AIML    = 'aimlapi';

	// ── Base URLs ──────────────────────────────────────────────────────────

	/**
	 * Provider base URLs for OpenAI-compatible endpoints.
	 *
	 * Each value is the root URL; the method appends /v1/chat/completions.
	 *
	 * @var array<string,string>
	 */
	private $base_urls = array(
		self::PROVIDER_OPENAI => 'https://api.openai.com',
		self::PROVIDER_SHARP  => 'https://api.sharpapi.com',
		self::PROVIDER_EDEN   => 'https://api.edenai.run/v2',
		self::PROVIDER_AIML   => 'https://api.aimlapi.com',
	);

	/**
	 * Google Gemini API endpoint (API key appended as a query parameter).
	 *
	 * @var string
	 */
	private $gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

	// ── Cache ──────────────────────────────────────────────────────────────

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

	// ── Public API ─────────────────────────────────────────────────────────

	/**
	 * Get a dynamic maintenance message via the configured AI provider.
	 *
	 * Uses WordPress transients to cache the response for one hour so the
	 * API is not called on every page load.
	 *
	 * The cache is intentionally NOT populated with the fallback message,
	 * so that entering an API key later will immediately trigger a real
	 * API call on the next page load instead of serving a stale fallback.
	 *
	 * @return string The AI-generated message, or a fallback if unavailable.
	 */
	public function get_dynamic_message() {
		// Return cached message if available.
		$cached = get_transient( $this->cache_key );
		if ( false !== $cached ) {
			error_log( 'Techanum Maintenance [Router] - Serving cached AI message.' );
			return $cached;
		}

		// Bail early (without caching) if no API key is configured.
		$api_key = $this->get_api_key();
		if ( ! $api_key ) {
			error_log( 'Techanum Maintenance [Router] - No API key configured; using fallback message.' );
			return $this->get_fallback_message();
		}

		// Resolve the provider to use.
		$provider = $this->resolve_provider( $api_key );
		error_log( 'Techanum Maintenance [Router] - Resolved provider: ' . $provider );

		// Dispatch to the correct provider using the default prompt.
		$message = $this->call_provider( $provider, $api_key, $this->get_prompt() );

		if ( $message ) {
			set_transient( $this->cache_key, $message, $this->cache_duration );
			error_log( 'Techanum Maintenance [Router] - AI message generated and cached successfully.' );
			return $message;
		}

		// API call failed — return fallback WITHOUT caching so the next
		// request retries the API rather than serving a stale fallback.
		error_log( 'Techanum Maintenance [Router] - AI call failed; using fallback message.' );
		return $this->get_fallback_message();
	}

	/**
	 * Call the AI provider with a custom prompt (used by techanum_call_ai_api()).
	 *
	 * Unlike get_dynamic_message(), this method does NOT use the transient
	 * cache and does NOT fall back to a hardcoded message — it returns false
	 * on failure so the caller can decide what to do.
	 *
	 * @param string $prompt The prompt to send to the AI.
	 * @return string|false The generated text, or false on failure.
	 */
	public function call_with_prompt( $prompt ) {
		$api_key = $this->get_api_key();
		if ( ! $api_key ) {
			error_log( 'Techanum Maintenance [Router::call_with_prompt] - No API key configured.' );
			return false;
		}

		$provider = $this->resolve_provider( $api_key );
		error_log( 'Techanum Maintenance [Router::call_with_prompt] - Provider: ' . $provider . ' | Prompt: ' . substr( $prompt, 0, 80 ) . '...' );

		return $this->call_provider( $provider, $api_key, $prompt );
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
		error_log( 'Techanum Maintenance [Router] - AI message cache cleared.' );
	}

	// ── Provider resolution ────────────────────────────────────────────────

	/**
	 * Resolve which provider to use.
	 *
	 * If the saved setting is anything other than "auto", that explicit
	 * choice is returned directly.  When set to "auto" (the default), the
	 * API key prefix is inspected:
	 *
	 *  - Starts with "sk-"  → OpenAI
	 *  - Starts with "AIza" → Google Gemini
	 *  - All other keys     → AI/ML API (OpenAI-compatible, broadest support)
	 *
	 * SharpAPI and Eden AI keys do not have a universally recognisable
	 * prefix, so users who hold those keys should select the provider
	 * explicitly from the dropdown.
	 *
	 * @param string $api_key The raw API key.
	 * @return string One of the PROVIDER_* constants.
	 */
	private function resolve_provider( $api_key ) {
		$saved = get_option( 'techanum_maintenance_ai_provider', self::PROVIDER_AUTO );

		// Explicit provider selected — honour it.
		if ( self::PROVIDER_AUTO !== $saved ) {
			$provider = sanitize_key( $saved );
			error_log( 'Techanum Maintenance [Router] - Explicit provider selected: ' . $provider );
			return $provider;
		}

		// Auto-detect from key prefix.
		if ( 0 === strpos( $api_key, 'sk-' ) ) {
			error_log( 'Techanum Maintenance [Router] - Auto-detected provider: openai (key starts with sk-).' );
			return self::PROVIDER_OPENAI;
		}

		if ( 0 === strpos( $api_key, 'AIza' ) ) {
			error_log( 'Techanum Maintenance [Router] - Auto-detected provider: gemini (key starts with AIza).' );
			return self::PROVIDER_GEMINI;
		}

		// Default fallback for unrecognised key formats.
		error_log( 'Techanum Maintenance [Router] - Auto-detect: unrecognised key prefix; defaulting to aimlapi.' );
		return self::PROVIDER_AIML;
	}

	// ── Dispatch ───────────────────────────────────────────────────────────

	/**
	 * Dispatch the request to the correct provider method.
	 *
	 * @param string $provider One of the PROVIDER_* constants.
	 * @param string $api_key  The API key.
	 * @param string $prompt   The prompt to send.
	 * @return string|false Generated text or false on failure.
	 */
	private function call_provider( $provider, $api_key, $prompt ) {
		if ( self::PROVIDER_GEMINI === $provider ) {
			return $this->call_gemini( $api_key, $prompt );
		}

		// All remaining providers share the OpenAI-compatible format.
		$base_url = isset( $this->base_urls[ $provider ] )
			? $this->base_urls[ $provider ]
			: $this->base_urls[ self::PROVIDER_AIML ];

		return $this->call_openai_compatible( $api_key, $base_url, $provider, $prompt );
	}

	// ── Google Gemini ──────────────────────────────────────────────────────

	/**
	 * Send a POST request to the Google Gemini API.
	 *
	 * Endpoint : https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={API_KEY}
	 * Auth     : API key as a query-string parameter.
	 * Response : $data['candidates'][0]['content']['parts'][0]['text']
	 *
	 * @param string $api_key The Gemini API key.
	 * @param string $prompt  The prompt to send.
	 * @return string|false Generated text or false on failure.
	 */
	private function call_gemini( $api_key, $prompt ) {
		$request_url = add_query_arg( 'key', $api_key, $this->gemini_url );

		error_log( 'Techanum Maintenance [Gemini] - Sending request to Gemini API.' );

		$body = wp_json_encode(
			array(
				'contents'         => array(
					array(
						'parts' => array(
							array( 'text' => $prompt ),
						),
					),
				),
				'generationConfig' => array(
					'temperature'     => 0.9,
					'maxOutputTokens' => 200,
				),
			)
		);

		if ( false === $body ) {
			error_log( 'Techanum Maintenance [Gemini] - Failed to JSON-encode request body.' );
			return false;
		}

		$response = wp_remote_post(
			$request_url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => $body,
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Techanum Maintenance [Gemini] - Network error: ' . $response->get_error_message() );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );

		if ( 200 !== (int) $code ) {
			error_log( 'Techanum Maintenance [Gemini] - HTTP ' . $code . ' | Response body: ' . $raw );
			return false;
		}

		$data = json_decode( $raw, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			error_log( 'Techanum Maintenance [Gemini] - JSON decode error: ' . json_last_error_msg() . ' | Raw: ' . $raw );
			return false;
		}

		if (
			isset( $data['candidates'][0]['content']['parts'][0]['text'] )
			&& '' !== trim( $data['candidates'][0]['content']['parts'][0]['text'] )
		) {
			$text = sanitize_text_field( $data['candidates'][0]['content']['parts'][0]['text'] );
			error_log( 'Techanum Maintenance [Gemini] - Success. Message length: ' . strlen( $text ) . ' chars.' );
			return $text;
		}

		error_log( 'Techanum Maintenance [Gemini] - Unexpected response structure. Full body: ' . $raw );
		return false;
	}

	// ── OpenAI-compatible providers ────────────────────────────────────────

	/**
	 * Send a POST request to an OpenAI-compatible /v1/chat/completions endpoint.
	 *
	 * Used for OpenAI, SharpAPI, Eden AI, and AI/ML API.
	 *
	 * Endpoint : {BASE_URL}/v1/chat/completions
	 * Auth     : Bearer token in the Authorization header.
	 * Response : $data['choices'][0]['message']['content']
	 *
	 * Model preference: gpt-4o-mini (primary), gpt-3.5-turbo (fallback).
	 * For non-OpenAI providers the model name is passed as-is; if the
	 * provider does not support gpt-4o-mini it will return an error and
	 * the fallback model is tried automatically.
	 *
	 * @param string $api_key  The provider API key.
	 * @param string $base_url The provider base URL (no trailing slash).
	 * @param string $provider Provider identifier used in log messages.
	 * @param string $prompt   The prompt to send.
	 * @return string|false Generated text or false on failure.
	 */
	private function call_openai_compatible( $api_key, $base_url, $provider, $prompt ) {
		// Build the full endpoint URL.
		// trailingslashit() ensures exactly one slash between base and path.
		$request_url = trailingslashit( $base_url ) . 'v1/chat/completions';

		error_log( 'Techanum Maintenance [' . $provider . '] - Sending request to: ' . $request_url );

		// Try gpt-4o-mini first; fall back to gpt-3.5-turbo if it fails.
		$models = array( 'gpt-4o-mini', 'gpt-3.5-turbo' );

		foreach ( $models as $model ) {
			$result = $this->do_openai_request( $api_key, $request_url, $provider, $model, $prompt );
			if ( false !== $result ) {
				return $result;
			}
			error_log( 'Techanum Maintenance [' . $provider . '] - Model "' . $model . '" failed; trying next model.' );
		}

		error_log( 'Techanum Maintenance [' . $provider . '] - All models exhausted. Returning false.' );
		return false;
	}

	/**
	 * Execute a single OpenAI-compatible chat completions request.
	 *
	 * @param string $api_key     The provider API key.
	 * @param string $request_url Full endpoint URL.
	 * @param string $provider    Provider identifier for log messages.
	 * @param string $model       Model name (e.g. gpt-4o-mini).
	 * @param string $prompt      The prompt to send.
	 * @return string|false Generated text or false on failure.
	 */
	private function do_openai_request( $api_key, $request_url, $provider, $model, $prompt ) {
		$body = wp_json_encode(
			array(
				'model'       => $model,
				'messages'    => array(
					array(
						'role'    => 'user',
						'content' => $prompt,
					),
				),
				'max_tokens'  => 200,
				'temperature' => 0.9,
			)
		);

		if ( false === $body ) {
			error_log( 'Techanum Maintenance [' . $provider . '] - Failed to JSON-encode request body for model ' . $model . '.' );
			return false;
		}

		$response = wp_remote_post(
			$request_url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => $body,
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Techanum Maintenance [' . $provider . '] - Network error (model: ' . $model . '): ' . $response->get_error_message() );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );

		if ( 200 !== (int) $code ) {
			error_log( 'Techanum Maintenance [' . $provider . '] - HTTP ' . $code . ' (model: ' . $model . ') | Response body: ' . $raw );
			return false;
		}

		$data = json_decode( $raw, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			error_log( 'Techanum Maintenance [' . $provider . '] - JSON decode error (model: ' . $model . '): ' . json_last_error_msg() . ' | Raw: ' . $raw );
			return false;
		}

		if (
			isset( $data['choices'][0]['message']['content'] )
			&& '' !== trim( $data['choices'][0]['message']['content'] )
		) {
			$text = sanitize_text_field( $data['choices'][0]['message']['content'] );
			error_log( 'Techanum Maintenance [' . $provider . '] - Success (model: ' . $model . '). Message length: ' . strlen( $text ) . ' chars.' );
			return $text;
		}

		error_log( 'Techanum Maintenance [' . $provider . '] - Unexpected response structure (model: ' . $model . '). Full body: ' . $raw );
		return false;
	}

	// ── Helpers ────────────────────────────────────────────────────────────

	/**
	 * Retrieve the API key from the WordPress option or wp-config.php constant.
	 *
	 * Checks (in order):
	 *  1. 'techanum_maintenance_api_key' WordPress option (set via the settings page).
	 *  2. 'techanum_ai_api_key' WordPress option (alternative option name).
	 *  3. TECHANUM_ANTIGRAVITY_API_KEY constant defined in wp-config.php (legacy).
	 *
	 * @return string|false The API key, or false if not configured.
	 */
	private function get_api_key() {
		// Primary option (saved by the settings page).
		$option = get_option( 'techanum_maintenance_api_key', '' );
		if ( ! empty( $option ) ) {
			return trim( $option );
		}

		// Alternative option name (mentioned in task requirements).
		$alt_option = get_option( 'techanum_ai_api_key', '' );
		if ( ! empty( $alt_option ) ) {
			error_log( 'Techanum Maintenance [Router] - Using API key from techanum_ai_api_key option.' );
			return trim( $alt_option );
		}

		// Legacy wp-config.php constant (backward compatibility).
		if ( defined( 'TECHANUM_ANTIGRAVITY_API_KEY' ) && TECHANUM_ANTIGRAVITY_API_KEY ) {
			error_log( 'Techanum Maintenance [Router] - Using API key from TECHANUM_ANTIGRAVITY_API_KEY constant.' );
			return TECHANUM_ANTIGRAVITY_API_KEY;
		}

		return false;
	}

	/**
	 * Return the default prompt sent to every AI provider.
	 *
	 * @return string
	 */
	private function get_prompt() {
		return 'Write a friendly maintenance message for a website, 2-3 sentences.';
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
}
