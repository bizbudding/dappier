<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * Get a file version based on last modified date.
 *
 * @since 0.1.0
 *
 * @param string $filename The file name. Example: `dapper`.
 * @param string $type     The file type. Example: `css`.
 * @param bool   $debug    Whether to use the debug version.
 *
 * @return string
 */
function dappier_get_file_version( $filename, $type, $debug = null ) {
	$version   = DAPPIER_PLUGIN_VERSION;
	$debug     = is_null( $debug ) ? defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG : false;
	$path      = $debug ? 'src' : 'build';
	$suffix    = $debug ? '' : '.min';
	$filepath  = MAI_ASKNEWS_DIR . "{$path}/{$type}/{$filename}{$suffix}.{$type}";
	$version  .= '.' . date( 'njYHi', filemtime( $filepath ) );

	return $version;
}

/**
 * Get the URL of a file in the plugin.
 * Checks if script debug is enabled.
 *
 * @since 0.4.0
 *
 * @param string $filename The file name. Example: `dapper`.
 * @param string $type     The file type. Example: `css`.
 * @param bool   $debug    Whether to use the debug version.
 *
 * @return string
 */
function dappier_get_file_url( $filename, $type, $debug = null ) {
	$debug  = is_null( $debug ) ? defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG : false;
	$path   = $debug ? 'src' : 'build';
	$suffix = $debug ? '' : '.min';

	return DAPPIER_PLUGIN_URL . "{$path}/{$type}/{$filename}{$suffix}.{$type}";
}

/**
 * Get an option from the dappier options.
 *
 * @since 0.1.0
 *
 * @param string $key The option key.
 *
 * @return string
 */
function dappier_get_option( $key ) {
	$options = get_option( 'dappier', [] );

	return isset( $options[ $key ] ) ? $options[ $key ] : null;
}

/**
 * Update an option in the dappier options.
 *
 * @since 0.1.0
 *
 * @param string $key   The option key.
 * @param string $value The option value.
 *
 * @return bool
 */
function dappier_update_option( $key, $value ) {
	$options = get_option( 'dappier', [] );

	$options[ $key ] = $value;

	return update_option( 'dappier', $options );
}

/**
 * Delete an option from the dappier options.
 *
 * @since 0.1.0
 *
 * @param string $key The option key.
 *
 * @return bool
 */
function dappier_delete_option( $key ) {
	$options = get_option( 'dappier', [] );

	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );

		return update_option( 'dappier', $options );
	}

	return false;
}