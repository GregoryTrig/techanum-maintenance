/**
 * Techanum Maintenance – Admin JS
 *
 * Handles the "Generate with AI" button on the settings page.
 * Sends an AJAX request to the server, shows a spinner while waiting,
 * populates the textarea on success, and displays an inline error notice
 * on failure.
 *
 * Depends on: jQuery (bundled with WordPress)
 * Localised object: techanumAdmin  { ajaxurl, nonce }
 */
( function ( $ ) {
	'use strict';

	$( document ).ready( function () {

		var $btn     = $( '#techanum-generate-ai-btn' );
		var $spinner = $( '#techanum-ai-spinner' );
		var $textarea = $( '#techanum-maintenance-custom-message' );
		var $notice  = $( '#techanum-ai-notice' );

		if ( ! $btn.length ) {
			return;
		}

		$btn.on( 'click', function ( e ) {
			e.preventDefault();

			// Remove any previous notice.
			$notice.hide().removeClass( 'notice-error notice-success' ).text( '' );

			// Show spinner, disable button.
			$spinner.addClass( 'is-active' );
			$btn.prop( 'disabled', true );

			$.ajax( {
				url:    techanumAdmin.ajaxurl,
				method: 'POST',
				data:   {
					action:   'techanum_generate_ai_message',
					nonce:    techanumAdmin.nonce,
				},
				timeout: 30000,
			} )
			.done( function ( response ) {
				if ( response && response.success && response.data && response.data.message ) {
					$textarea.val( response.data.message );
					$notice
						.addClass( 'notice notice-success' )
						.text( 'AI message generated successfully. Save Settings to keep it.' )
						.show();
				} else {
					var errMsg = ( response && response.data && response.data.error )
						? response.data.error
						: 'Unknown error. Please check the error log.';
					$notice
						.addClass( 'notice notice-error' )
						.text( 'Error: ' + errMsg )
						.show();
				}
			} )
			.fail( function ( jqXHR, textStatus ) {
				var errMsg = ( 'timeout' === textStatus )
					? 'The request timed out. The AI API may be slow — please try again.'
					: 'AJAX request failed (' + textStatus + '). Please try again.';
				$notice
					.addClass( 'notice notice-error' )
					.text( errMsg )
					.show();
			} )
			.always( function () {
				$spinner.removeClass( 'is-active' );
				$btn.prop( 'disabled', false );
			} );
		} );
	} );

}( jQuery ) );
