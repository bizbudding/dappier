<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'the_content', 'dappier_add_module' );
/**
 * Add Dappier Ask AI module to the content.
 *
 * @since  0.1.0
 *
 * @param  string $content The content.
 *
 * @return string
 */
function dappier_add_module( $content ) {
	// Bail if in the Dashboard or not the main query.
	if ( is_admin() || ! is_main_query() ) {
		return $content;
	}

	// Get plugin options.
	$api_key    = dappier_get_option( 'api_key' );
	$aimodel_id = dappier_get_option( 'aimodel_id' );
	// $aimodel_id = '66fd67f552c021d02d45432f'; // Testing.

	// Bail missing data.
	if ( ! ( $api_key && $aimodel_id ) ) {
		return $content;
	}

	// Add the module.
	$html  = '';
	$html .= '<link href="https://assets.dappier.com/dappier-ask-ai.css" rel="stylesheet"/>';
	$html .= '<script src="https://assets.dappier.com/dappier-ask-ai.js"></script>';
	$html .= sprintf( '<dappier-ask-ai-widget title="%s"/>', __( 'Ask AI', 'dappier' ) );

	return $content . $html;
}
