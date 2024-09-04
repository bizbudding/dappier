<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * Get the URL of a file in the plugin.
 * Checks if script debug is enabled.
 *
 * @since 0.1.0
 *
 * @param string $filename The file name. Example: `dapper`.
 * @param string $type     The file type. Example: `css`.
 *
 * @return string
 */
function dapper_get_file_url( $filename, $type ) {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	return DAPPIER_PLUGIN_URL . "build/{$type}/{$filename}{$suffix}.{$type}";
}

function dappier_get_option( $key ) {
	$options = get_option( 'dappier', [] );

	return isset( $options[ $key ] ) ? $options[ $key ] : '';
}