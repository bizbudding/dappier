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
		add_filter( 'pre_update_option_dappier',               [ $this, 'update' ], 10, 3 );
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

		wp_enqueue_style( 'dappier-settings', dappier_get_file_url( 'dappier-settings', 'css' ), [], DAPPIER_PLUGIN_VERSION );
		wp_enqueue_script( 'dappier-settings', dappier_get_file_url( 'dappier-settings', 'js' ), [], DAPPIER_PLUGIN_VERSION, true );
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
		 * Fields   *
		 ************/

		// API Key.
		add_settings_field(
			'api_key', // id
			__( 'API Key', 'dappier' ), // title
			[ $this, 'api_key_callback' ], // callback
			'dappier', // page
			'dappier_one' // section
		);

		// Agent Name.
		add_settings_field(
			'aimodel_id', // id
			// __( 'Name', 'dappier' ), // title
			'', // title
			[ $this, 'aimodel_id_callback' ], // callback
			'dappier', // page
			'dappier_two' // section
		);

		// Agent Name.
		add_settings_field(
			'agent_name', // id
			// __( 'Name', 'dappier' ), // title
			'', // title
			[ $this, 'agent_name_callback' ], // callback
			'dappier', // page
			'dappier_two' // section
		);

		// Agent Description.
		add_settings_field(
			'agent_desc', // id
			// __( 'Description', 'dappier' ), // title
			'', // title
			[ $this, 'agent_desc_callback' ], // callback
			'dappier', // page
			'dappier_two' // section
		);

		// Agent Description.
		add_settings_field(
			'agent_persona', // id
			// __( 'Persona', 'dappier' ), // title
			'', // title
			[ $this, 'agent_persona_callback' ], // callback
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
			'api_key'       => 'sanitize_text_field',
			'aimodel_id'    => 'sanitize_text_field',
			'datamodel_id'  => 'sanitize_text_field',
			'agent_name'    => 'sanitize_text_field',
			'agent_desc'    => 'sanitize_textarea_field',
			'agent_persona' => 'sanitize_textarea_field',
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
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function api_key_callback() {
		echo '<div class="dappier-step__field">';
			printf( '<input class="dappier-step__input" type="password" name="dappier[api_key]" id="api_key" value="%s">', dappier_get_option( 'api_key' ) );
		echo '</div>';
	}

	/**
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function aimodel_id_callback() {
		$aimodel_id = dappier_get_option( 'aimodel_id' );
		$agents     = $this->get_agents();

		echo '<div class="dappier-step__field">';
			printf( '<label class="dappier-step__label" for="dappier[aimodel_id]">%s</label>', __( 'Agent', 'dappier' ) );
			printf( '<p class="dappier-step__desc">%s</p>', __( 'Select an existing agent or create a new one.', 'dappier' ) );

			echo '<select class="dappier-step__input" name="dappier[aimodel_id]" id="aimodel_id">';
				// If agents.
				if ( $agents ) {
					// Add default option.
					echo '<option value="">Select an Agent</option>';

					// Add existing agents.
					foreach ( $agents as $agent ) {
						// SKip if id and name are not set.
						if ( ! isset( $agent['id'], $agent['name'] ) ) {
							continue;
						}

						$selected = $aimodel_id === $agent['id'] ? ' selected' : '';
						printf( '<option value="%s"%s>%s</option>', $agent['id'], $selected, $agent['name'] );
					}
				}

				// Add option to create a new agent.
				echo '<option value="_create_agent">Create a new Agent</option>';
			echo '</select>';
		echo '</div>';
	}

	/**
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function agent_name_callback() {
		$value = dappier_get_option( 'agent_name' );
		$value = ! $this->get_agents() ? get_bloginfo( 'name' ) : $value;

		echo '<div style="display:none;" class="dappier-step__field agent_name">';
			printf( '<label class="dappier-step__label" for="dappier[agent_name]">%s</label>', __( 'Name', 'dappier' ) );
			printf( '<input class="dappier-step__input" type="text" name="dappier[agent_name]" id="agent_name" value="%s">', $value );
		echo '</div>';
	}

	/**
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function agent_desc_callback() {
		$value = dappier_get_option( 'agent_desc' );
		$value = ! $this->get_agents() ? get_bloginfo( 'description' ) : $value;

		echo '<div style="display:none;" class="dappier-step__field agent_desc">';
			printf( '<label class="dappier-step__label" for="dappier[agent_desc]">%s</label>', __( 'Description', 'dappier' ) );
			printf( '<p class="dappier-step__desc">%s</p>', __( 'Short description of the site', 'dappier' ) );
			printf( '<textarea id="agent_desc" class="dappier-step__input" name="dappier[agent_desc]" rows="3">%s</textarea>', $value );
		echo '</div>';
	}

	/**
	 * Setting callback.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function agent_persona_callback() {
		$value = dappier_get_option( 'agent_persona' );

		echo '<div style="display:none;" class="dappier-step__field agent_persona">';
			printf( '<label class="dappier-step__label" for="dappier[agent_persona]">%s</label>', __( 'Persona', 'dappier' ) );
			printf( '<p class="dappier-step__desc">%s</p>', __( 'How should this AI Agent answer questions? What does it do? What should it not do?', 'dappier' ) );
			printf( '<textarea class="dappier-step__input" name="dappier[agent_persona]" id="agent_persona" rows="3">%s</textarea>', $value );
		echo '</div>';
	}

	/**
	 * Adds setting page content.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_content() {
		// Start the wrap.
		echo '<div class="wrap">';

			// Header.
			echo '<div class="dappier-header">';
				printf( '<p class="dappier-logo"><img class="dappier-logo__img" src="%s" alt="%s" /></p>', DAPPIER_PLUGIN_URL . 'src/img/dappier-logo.webp', __( 'Dappier Logo', 'dappier' ) );
				echo '<div class="dappier-header__content">';
					printf( '<p>%s: %s</p>', __( 'Version', 'dappier' ), DAPPIER_PLUGIN_VERSION );
				echo '</div>';
			echo '</div>';

			// Description.
			printf( '<h2>%s</h2>', __( 'Dappier for WordPress', 'dappier' ) );
			printf( '<p>%s</p>', __( 'Settings and configuration for Dappier for WordPress plugin.', 'dappier' ) );

			// Inner.
			echo '<div class="dappier-inner">';
				// Content.
				echo '<div class="dappier-inner__content">';
					echo '<form class="dappier-form" method="post" action="options.php">';
						// Get details.
						$details = $this->get_details();

						// If details.
						if ( $details ) {
							// Account info.
							echo '<div class="dappier-step dappier-step__info">';
								echo '<div class="dappier-step__inner">';
									printf( '<h3 class="dappier-heading">%s</h3>', __( 'Account', 'dappier' ) );
									echo '<div class="dappier-step__content">';
										printf( '<p>%s</p>', __( 'This is the first section.', 'dappier' ) );
										printf( '<p>%s</p>', __( 'Some text about making an account', 'dappier' ) );

										echo '<table>';

										foreach ( $details as $key => $value ) {
											printf( '<tr><td>%s: </td><td>%s</td></tr>', $key, $value );
										}

										echo '</table>';

									echo '</div>';
								echo '</div>';
							echo '</div>';
						}

						// Get the saved API key and heading.
						$api_key = dappier_get_option( 'api_key' );
						$heading = $api_key ? __( 'API key', 'dappier' ) : __( 'Connect your account', 'dappier' );

						// Step 1.
						echo '<div class="dappier-step dappier-step__one">';
							if ( ! $api_key ) {
								printf( '<p class="dappier-preheading">%s</p>', __( 'Step 1', 'dappier' ) );
							}
							echo '<div class="dappier-step__inner">';
								printf( '<h3 class="dappier-heading">%s</h3>', $heading );
								echo '<div class="dappier-step__content">';
									printf( '<p>%s</p>', __( 'Enter your Dappier API key below to connect your site.', 'dappier' ) );
									do_settings_fields( 'dappier', 'dappier_one');
								echo '</div>';
							echo '</div>';
						echo '</div>';

						ray( get_option( 'dappier' ) );

						// If API key.
						if ( $api_key ) {
							// Get the agent and heading.
							$agent   = dappier_get_option( 'aimodel_id' );
							$heading = $agent ? __( 'Agent', 'dappier' ) : __( 'Create or choose your agent', 'dappier' );

							// Step 2.
							echo '<div class="dappier-step dappier-step__three">';
								if ( ! $agent ) {
									printf( '<p class="dappier-preheading">%s</p>', __( 'Step 2', 'dappier' ) );
								}
								echo '<div class="dappier-step__inner">';
									printf( '<h3 class="dappier-heading">%s</h3>', $heading );
									echo '<div class="dappier-step__content">';
										printf( '<p>%s</p>', __( 'Instructions for this section.', 'dappier' ) );
										do_settings_fields( 'dappier', 'dappier_two');
									echo '</div>';
								echo '</div>';
							echo '</div>';
						}

						// Hidden fields and submit button.
						settings_fields( 'dappier' );
						submit_button( __( 'Save Settings', 'dappier' ) );

					echo '</form>';
				echo '</div>';

				// Sidebar.
				echo '<div class="dappier-inner__sidebar">';
					echo '<div class="dappier-step dappier-step_sidebar">';
						echo '<div class="dappier-step__inner">';
							printf( '<h3 class="dappier-heading">%s</h3>', __( 'Sidebar', 'dappier' ) );
							echo '<div class="dappier-step__content">';
								printf( '<p>%s</p>', __( 'This is the sidebar section.', 'dappier' ) );
								printf( '<p>%s</p>', __( 'Some text about making an account', 'dappier' ) );
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</div>';

			echo '</div>';
		echo '</div>';
	}

	/**
	 * Get the account details.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_details() {
		// Get the API key.
		$api_key = dappier_get_option( 'api_key' );

		// Bail if no API key.
		if ( ! $api_key ) {
			return;
		}

		// Set up the transient name.
		$transient = 'dappier_details';

		// Check for transient.
		if ( false === ( $body = get_transient( $transient ) ) ) {
			// Set up the API url and body.
			$aimodel_id = '66fd67f552c021d02d45432f';
			$url        = "https://api.dappier.com/v1/integrations/account?aimodelid={$aimodel_id}";
			$args       = [
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
				],
			];

			// Make the request.
			$response = wp_remote_get( $url, $args );
			$code     = wp_remote_retrieve_response_code( $response );
			$body     = wp_remote_retrieve_body( $response );

			// Check for errors.
			if ( 200 !== $code ) {
				$body = [];
			}
			// No errors.
			else {
				// Decode the body.
				$body = json_decode( $body, true );

				// Set the transient.
				set_transient( $transient, $body, MINUTE_IN_SECONDS * 5 );
			}
		}

		// Map the details.
		$map = [
			'account_id'           => [ 'label' => __( 'Account ID', 'dappier' ), 'sanitize' => 'sanitize_key' ],
			'email'                => [ 'label' => __( 'Email', 'dappier' ), 'sanitize' => 'sanitize_email' ],
			'name'                 => [ 'label' => __( 'Name', 'dappier' ), 'sanitize' => 'sanitize_text_field' ],
			'subscription_level'   => [ 'label' => __( 'Subscription Level', 'dappier' ), 'sanitize' => 'sanitize_text_field' ],
			'created_at'           => [ 'label' => __( 'Created At', 'dappier' ), 'sanitize' => 'sanitize_text_field' ],
			'ai_agents_used'       => [ 'label' => __( 'AI Agents Used', 'dappier' ), 'sanitize' => 'sanitize_text_field' ],
			'queries_used_month'   => [ 'label' => __( 'Queries Used This Month', 'dappier' ), 'sanitize' => 'sanitize_text_field' ],
			'rev_share_in_percent' => [ 'label' => __( 'Revenue Share in Percent', 'dappier' ), 'sanitize' => 'sanitize_text_field' ],
		];

		// Loop and sanitize.
		foreach ( $map as $key => $data ) {
			// Skip if not set.
			if ( ! isset( $body[ $key ] ) ) {
				continue;
			}

			// Sanitize.
			$body[ $data['label'] ] = $data['sanitize']( $body[ $key ] );

			// Format.
			switch ( $key ) {
				case 'created_at':
					$timestamp    = strtotime( $body[ $key ] );
					$date         = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
					$body[ $data['label'] ] = $date;
				break;
				case 'rev_share_in_percent':
					$body[ $data['label'] ] .= '%';
				break;
			}

			// Unset the old key.
			unset( $body[ $key ] );
		}

		return $body;
	}

	/**
	 * Get the agents.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_agents() {
		// Get the API key.
		$api_key = dappier_get_option( 'api_key' );

		// Bail if no API key.
		if ( ! $api_key ) {
			return [];
		}

		// Set up the transient name.
		$transient = 'dappier_agents';

		// Check for transient.
		if ( false === ( $body = get_transient( $transient ) ) ) {
			// Set up the API url and body.
			$url  = 'https://api.dappier.com/v1/integrations/agent?type=rss';
			$args = [
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
				],
			];

			// Make the request.
			$response = wp_remote_get( $url, $args );
			$code     = wp_remote_retrieve_response_code( $response );
			$body     = wp_remote_retrieve_body( $response );
			$body     = json_decode( $body, true );

			// Check for errors.
			if ( 200 !== $code ) {
				// Get message.
				$message = isset( $response['response']['message'] ) ? $response['response']['message'] : __( 'An error occurred while processing the request.', 'dappier' );

				// Add a settings error with a unique error code.
				add_settings_error(
					'dappier',
					'get_agent_error_' . $code,
					sprintf( __( 'Error Getting Agents (%d): %s', 'dappier' ), $code, $message ),
					'error'
				);
			}

			// Set the transient.
			set_transient( $transient, $body, MINUTE_IN_SECONDS * 5 );
		}

		return $body;
	}

	function get_agent() {

	}

	/**
	 * Updated the settings.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param mixed  $old_value The old option value.
	 * @param string $option    Option name.
	 *
	 * @return void
	 */
	function update( $value, $old_value, $option ) {
		// Bail if not our option.
		if ( 'dappier' !== $option ) {
			return $value;
		}

		// Bail if no value.
		if ( ! ( is_array( $value ) && $value ) ) {
			return $value;
		}

		// Get all transients to delete.
		$transients = [
			'dappier_details',
			'dappier_agents',
		];

		// Loop and delete.
		foreach ( $transients as $transient ) {
			delete_transient( $transient );
		}

		// Get the API key.
		$api_key = isset( $value['api_key'] ) ? $value['api_key'] : '';

		// Bail if no API key.
		if ( ! $api_key ) {
			return $value;
		}

		// Get the data.
		$aimodel_id = isset( $value['aimodel_id'] ) ? $value['aimodel_id'] : '';
		$name       = isset( $value['agent_name'] ) ? $value['agent_name'] : '';
		$desc       = isset( $value['agent_desc'] ) ? $value['agent_desc'] : '';
		$pers       = isset( $value['agent_persona'] ) ? $value['agent_persona'] : '';

		// Unset the agent data.
		unset( $value['agent_name'], $value['agent_desc'], $value['agent_persona'] );

		// Bail if not creating.
		if ( '_create_agent' !== $aimodel_id ) {
			return $value;
		}

		// Unset the agent.
		$value['aimodel_id'] = '';

		// Bail if no agent data.
		if ( ! ( $name && $desc && $pers ) ) {
			return $value;
		}

		// Check for agent submission.
		$agent = $this->create_agent(
			[
				'api_key' => $api_key,
				'name'    => $name,
				'desc'    => $desc,
				'persona' => $pers,
			]
		);

		// Update the agent.
		if ( $agent && is_array( $agent ) ) {
			if ( isset( $agent['id'] ) ) {
				$value['aimodel_id'] = $agent['id'];
			}

			if ( isset( $agent['datamodel_id'] ) ) {
				$value['datamodel_id'] = $agent['datamodel_id'];
			}
		}

		return $value;
	}

	/**
	 * Create an agent.
	 *
	 * @since 0.1.0
	 *
	 * @param array $data The agent data.
	 *
	 * @return void
	 */
	function create_agent( $data ) {
		// Set up the API url and body.
		$url  = 'https://api.dappier.com/v1/integrations/agent';
		$body = [
			'name'           => $data['name'],
			'description'    => $data['desc'],
			'persona'        => $data['persona'],
			'type'           => 'rss', // TODO: What should this be for our REST API endpoint?
			'feed_url'       => home_url( '/wp-json/dappier/v1/posts' ),
			'prompt_samples' => [],
		];

		// Set up the request arguments.
		$args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $data['api_key'],
			],
			'body' => wp_json_encode( $body ),
		];

		// Make the request.
		$response = wp_remote_post( $url, $args );
		$code     = wp_remote_retrieve_response_code( $response );
		$body     = wp_remote_retrieve_body( $response );
		$body     = json_decode( $body, true );

		// Check for errors.
		if ( 200 !== $code ) {
			// Get message.
			$message = isset( $response['response']['message'] ) ? $response['response']['message'] : __( 'An error occurred while processing the request.', 'dappier' );

			// Add a settings error with a unique error code.
			add_settings_error(
				'dappier',
				'create_agent_error_' . $code,
				sprintf( __( 'Error Creating Agent (%d): %s', 'dappier' ), $code, $message ),
				'error'
			);
		}

		return $body;
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
		$actions['settings'] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=mai_ad&page=settings' ) ), __( 'Settings', 'dappier' ) );

		return $actions;
	}
}