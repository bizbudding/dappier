<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * Adds settings page.
 */
class Dappier_Settings {
	/**
	 * Construct the class.
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'admin_menu',                              [ $this, 'add_menu_item' ], 12 );
		add_action( 'admin_enqueue_scripts',                   [ $this, 'enqueue_script' ] );
		add_action( 'admin_init',                              [ $this, 'init' ] );
		add_filter( 'plugin_action_links_dappier/dappier.php', [ $this, 'add_plugin_links' ], 10, 4 );
	}

	/**
	 * Adds menu item for settings page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_menu_item() {
		// Add menu item under Settings for Dappier.
		add_submenu_page(
			'options-general.php', // parent_slug
			__( 'Dappier', 'dappier' ), // page_title
			__( 'Dappier', 'dappier' ), // menu_title
			'manage_options', // capability
			'dappier', // menu_slug
			[ $this, 'add_content' ] // function
		);
	}

	/**
	 * Enqueue script for select2.
	 *
	 * @since 0.7.0
	 *
	 * @return void
	 */
	function enqueue_script() {
		$screen = get_current_screen();

		if ( 'settings_page_dappier' !== $screen->id ) {
			return;
		}

		wp_enqueue_style( 'dappier-settings', dapper_get_file_url( 'dappier-settings', 'css' ), [], DAPPIER_PLUGIN_VERSION );
	}

	/**
	 * Adds setting page content.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_content() {
		echo '<div class="wrap">';
			echo '<div class="dappier-header">';
				printf( '<p class="dappier-logo"><img class="dappier-logo__img" src="%s" alt="%s" /></p>', DAPPIER_PLUGIN_URL . 'src/img/dappier-logo.webp', __( 'Dappier Logo', 'dappier' ) );
				echo '<div class="dappier-header__content">';
					printf( '<h2>%s</h2>', __( 'Dappier for WordPress', 'dappier' ) );
					printf( '<p>%s: %s</p>', __( 'Version', 'dappier' ), DAPPIER_PLUGIN_VERSION );
				echo '</div>';
			echo '</div>';
			printf( '<p>%s</p>', __( 'Settings and configuration for Dappier for WordPress plugin.', 'dappier' ) );
			echo '<form method="post" action="options.php">';
				settings_fields( 'dappier' );
				do_settings_sections( 'dappier' );
				submit_button();
			echo '</form>';
		echo '</div>';
	}

	/**
	 * Initialize the settings.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function init() {
		register_setting(
			'dappier', // option_group
			'dappier', // option_name
			[ $this, 'sanitize' ] // sanitize_callback
		);

		/************
		 * Sections *
		 ************/

		add_settings_section(
			'dappier_one', // id
			'', // title
			[ $this, 'dappier_section_one' ], // callback
			'dappier' // page
		);

		add_settings_section(
			'dappier_two', // id
			'', // title
			[ $this, 'dappier_section_two' ], // callback
			'dappier' // page
		);

		/************
		 * Fields   *
		 ************/

		// API Key.
		add_settings_field(
			'api_key', // id
			__( 'API Key', 'dappier' ) . ' ' . $this->get_api_status(), // title
			[ $this, 'api_key_callback' ], // callback
			'dappier', // page
			'dappier_two' // section
		);
	}

	/**
	 * Sanitized saved values.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	function sanitize( $input ) {
		$allowed = [
			'api_key' => 'sanitize_text_field',
		];

		// Get an array of matching keys from $input.
		$input = array_intersect_key( $input, $allowed );

		// Sanitize.
		foreach ( $input as $key => $value ) {
			$input[ $key ] = $allowed[ $key ]( $value );
		}

		return $input;
	}

	/**
	 * Displays HTML before settings.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function dappier_section_one() {
		echo '<div class="dappier-step dappier-step__one">';
			printf( '<h2 class="dappier-heading"><span class="dappier-heading__step">%s</span><span class="dappier-heading__text">%s</span></h2>', __( 'Step 1', 'dappier' ), __( 'Section One', 'dappier' ) );
			echo '<div class="dappier-step__content">';
				printf( '<p>%s</p>', __( 'This is the first section.', 'dappier' ) );
				printf( '<p>%s</p>', __( 'Some text about making an account', 'dappier' ) );
			echo '</div>';
		echo '</div>';
	}

	function dappier_section_two() {
		echo '<div class="dappier-step dappier-step__two">';
			printf( '<h2 class="dappier-heading"><span class="dappier-heading__step">%s</span><span class="dappier-heading__text">%s</span></h2>', __( 'Step 2', 'dappier' ), __( 'Section Two', 'dappier' ) );
			echo '<div class="dappier-step__content">';
				printf( '<p>%s</p>', __( 'Enter your Dappier API key below to connect your site.', 'dappier' ) );
	}

	function get_api_status() {
		return '';
		// return $this->connection_info( sprintf( 'What is the weather in Austin today?' ) );
	}

	/**
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function api_key_callback() {
		printf( '<input class="regular-text" type="password" name="dappier[api_key]" id="api_key" value="%s">', dappier_get_option( 'api_key' ) );

		// Display the connection info.
		echo $this->connection_info();

		// Close the section.
			echo '</div>';
		echo '</div>';
	}


	/**
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	// function another_callback() {
	// 	$selected = dappier_get_option( 'api_key' );

	// 	echo '<select name="mai_publisher[ad_mode]">';
	// 		printf( '<option value="active">%s</option>', __( 'Active', 'dappier' ) );
	// 		printf( '<option value="disabled"%s>%s</option>', selected( $selected, 'disabled' ), __( 'Disabled', 'dappier' ) );
	// 		printf( '<option value="demo"%s>%s</option>', selected( $selected, 'demo' ), __( 'Demo', 'dappier' ) );
	// 	echo '</select>';
	// }

	/**
	 * Checks if connection is valid.
	 *
	 * @link https://matomo.org/faq/how-to/faq_20278/
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function connection_info() {
		// Get the API key.
		$api_key = dappier_get_option( 'api_key' );

		// Bail if no API key.
		if ( ! $api_key ) {
			return;
		}

		// Get the city.
		$city  = get_option( 'timezone_string' );
		$city  = explode( '/', $city );
		$city  = end( $city );
		$city  = $city ?: 'Austin';
		$city  = str_replace( '_', ' ', $city );
		$query = sprintf( 'What is the weather in %s today?', $city );

		// Set up the API URL.
		$url = 'https://api.dappier.com/app/datamodel/dm_01hpsxyfm2fwdt2zet9cg6fdxt';

		// Set up the request arguments.
		$args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			],
			'body' => json_encode( [ 'query' => $query ] ),
		];

		// Make the request.
		$response = wp_remote_post( $url, $args );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			return $this->get_notice( false, 'Error: ' . $response->get_error_message() );
		}

		// Get response code, decoded body, message, and status.
		$code    = wp_remote_retrieve_response_code( $response );
		$body    = wp_remote_retrieve_body( $response );
		$body    = json_decode( $body, true );
		$success = 200 === $code;
		$status  = $success ? __( 'Success', 'dappier' ) : __( 'Error', 'dappier' );
		$message = '';

		// If message.
		if ( isset( $body['message'] ) && $body['message'] ) {
			$message = $body['message'];
		}
		// If array response with results, get the first one.
		elseif ( isset( $body[0]['response']['results'] ) && $body[0]['response']['results'] ) {
			$message = $body[0]['response']['results'];
		}
		// If response message.
		elseif ( isset( $response['response']['message'] ) && $response['response']['message'] ) {
			$message = $response['response']['message'];
		}

		// Build return.
		if ( $success ) {
			$string = sprintf(
				__( 'The temperature in %s is %s degrees.', 'dappier' ),
				esc_html( $city ),
				esc_html( $message )
			);
			$return = sprintf( '%s: %s', $status, esc_html( $string ) );
		} else {
			$return = sprintf( '%s: (%s) %s', $status, $code, esc_html( $message ) );
		}

		// Return the status.
		return $this->get_notice( $success, trim( $return ) );
	}

	/**
	 * Get a notice HTML.
	 *
	 * @since 0.1.0
	 *
	 * @param bool   $success Whether the status is a success or error.
	 * @param string $message The message to display.
	 *
	 * @return string
	 */
	function get_notice( $success, $message ) {
		$html = sprintf( '<div class="dappier-notice%s">', $success ? ' dappier-notice--success' : ' dappier-notice--error' );
			$html .= '<div class="dappier-notice__inner">';
				$html .= sprintf( '<span class="dappier-notice__icon dashicons dashicons-%s"></span>', $success ? 'yes' : 'no' );
				$html .= sprintf( '<span class="dappier-notice__message">%s</span>', $message );
			$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Return the plugin action links.  This will only be called if the plugin is active.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $actions     Associative array of action names to anchor tags.
	 * @param string $plugin_file Plugin file name, ie my-plugin/my-plugin.php.
	 * @param array  $plugin_data Associative array of plugin data from the plugin file headers.
	 * @param string $context     Plugin status context, ie 'all', 'active', 'inactive', 'recently_active'.
	 *
	 * @return array associative array of plugin action links.
	 */
	function add_plugin_links( $actions, $plugin_file, $plugin_data, $context ) {
		// $actions['ads']      = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=mai_ad' ) ), __( 'Ads', 'dappier' ) );
		$actions['settings'] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=mai_ad&page=settings' ) ), __( 'Settings', 'dappier' ) );

		return $actions;
	}
}