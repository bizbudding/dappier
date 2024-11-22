<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'wp_enqueue_scripts', 'dappier_enqueue_scripts' );
/**
 * Enqueue scripts and styles.
 *
 * @since  0.1.0
 */
function dappier_enqueue_scripts() {
	// Bail if not a single post.
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	// Get plugin options.
	$api_key    = dappier_get_option( 'api_key' );
	$aimodel_id = dappier_get_option( 'aimodel_id' );
	$widget_id  = dappier_get_option( 'widget_id' );

	// Bail missing data.
	if ( ! ( $api_key && $aimodel_id && $widget_id ) ) {
		return;
	}

	// printf( '<script src="https://assets.dappier.com/widget/dappier-loader.min.js" widget-id="%s" ></script>', $widget_id );
	// wp_enqueue_script( 'dappier-loader', 'https://assets.dappier.com/widget/dappier-loader.min.js', [], DAPPIER_PLUGIN_VERSION, [] );
	wp_enqueue_script( 'dappier-loader', 'https://assets.dappier.com/widget/dappier-loader.min.js', [], null, [] );
}

add_filter( 'script_loader_tag', 'dappier_add_script_attributes', 10, 2 );
/**
 * Add attributes to script tag.
 *
 * @since  0.1.0
 *
 * @param  string $tag    The script tag.
 * @param  string $handle The script handle.
 *
 * @return string
 */
function dappier_add_script_attributes( $tag, $handle ) {
	if ( 'dappier-loader' !== $handle ) {
		return $tag;
	}

	// Get widget ID.
	$widget_id = dappier_get_option( 'widget_id' );

	// Bail if no widget ID.
	if ( ! $widget_id ) {
		return $tag;
	}

	// Set up tag processor.
	$tags = new WP_HTML_Tag_Processor( $tag );

	// Loop through tags.
	while ( $tags->next_tag( [ 'tag_name' => 'script' ] ) ) {
		$tags->set_attribute( 'widget-id', esc_attr( $widget_id ) );
		// $tags->set_attribute( 'env', 'dev' );
	}

	// Get updated tag.
	$tag = $tags->get_updated_html();

	return $tag;
}

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

	// Bail if not a single post.
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}

	// Get plugin options.
	$location   = dappier_get_option( 'module_location' );
	$api_key    = dappier_get_option( 'api_key' );
	$aimodel_id = dappier_get_option( 'aimodel_id' );
	$widget_id  = dappier_get_option( 'widget_id' );

	// Bail if not a valid location.
	if ( ! in_array( $location, [ 'before', 'after' ], true ) ) {
		return $content;
	}

	// Bail missing data.
	if ( ! ( $api_key && $aimodel_id && $widget_id ) ) {
		return $content;
	}

	// Get settings.
	$title       = __( 'Ask AI', 'dappier' );
	$widget_id   = $widget_id;
	$bg_color    = dappier_get_option( 'module_bg_color' );
	$bg_color    = is_null( $bg_color ) ? '#f8f9fa' : $bg_color;
	$bg_color    = $bg_color ?: 'inherit';
	$fg_color    = dappier_get_option( 'module_fg_color' );
	$fg_color    = $fg_color ?: 'inherit';
	$theme_color = dappier_get_option( 'module_theme_color' );
	$theme_color = is_null( $theme_color ) ? '#674ad9' : $theme_color;
	$theme_color = $theme_color ?: '#674ad9';

	// Add the module.
	$html = sprintf( '<dappier-ask-ai-widget
	  widgetId="%s"
	  title="%s"
	  mainBackgroundColor="%s"
	  mainTextColor="%s"
	  themeColor="%s"
	  mainLogoUrl="https://assets.dappier.com/dappier_logo.png"
	  mainLogoWidth="90"
	  chatIconUrl="https://assets.dappier.com/dappier_logo_small.png"
	  chatIconWidth="31"
	  enablePromptSuggestions="true"
	  enableContentRecommendations="true"
	  showAttributionLinks="true"
	  enableSiteName="false"
	  enableTitle="false" />',
		esc_attr( $widget_id ),
		esc_attr( $title ),
		esc_attr( $bg_color ),
		esc_attr( $fg_color ),
		esc_attr( $theme_color )
	);

	// If before.
	if ( 'before' === $location ) {
		return $html . $content;
	} else {
		return $content . $html;
	}
}
