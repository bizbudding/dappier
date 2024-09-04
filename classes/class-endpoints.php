<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * The endpoints class.
 *
 * @since 0.1.0
 */
class Dappier_Endpoints {
	protected $user;
	protected $request;
	protected $body;

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
		add_filter( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register the endpoints.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_endpoints() {
		/**
		 * /dappier/v1/posts/
		 */
		$routes = [
			'app'   => 'handle_app_request',
			'posts' => 'handle_posts_request',
		];

		// Loop through routes and register them.
		foreach ( $routes as $path => $callback ) {
			register_rest_route( 'dappier/v1', $path, [
				'methods'             => 'GET',
				'callback'            => [ $this, $callback ],
				'permission_callback' => [ $this, 'authenticate_request' ],
			] );
		}
	}

	/**
	 * Handle the site request.
	 *
	 * This method returns the site title and description.
	 *
	 * @example GET /wp-json/dappier/v1/app
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	function handle_app_request( $request ) {
		// Get the site title and description.
		$name = get_bloginfo( 'name' );
		$desc = get_bloginfo( 'description' );

		// Prepare the response.
		$response = [
			'feed'        => home_url( '/wp-json/dappier/v1/posts' ),
			'name'        => $name,
			'description' => $desc,
		];

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Handle the posts request.
	 *
	 * This method returns a paginated list of posts with specific custom data.
	 * You can control the pagination by passing `per_page` and `page` parameters in the request.
	 *
	 * @example GET /wp-json/dappier/v1/posts?per_page=10&page=1
	 * This will return the first 10 posts.
	 *
	 * @example GET /wp-json/dappier/v1/posts?per_page=5&page=2
	 * This will return the next 5 posts, starting from the 6th post.
	 *
	 * @example GET /wp-json/dappier/v1/posts
	 * If `per_page` and `page` are not specified, it will default to 20 posts per page and start from the first page.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	function handle_posts_request( $request ) {
		// Get pagination parameters from the request.
		$post_type = $request->get_param( 'post_type' ) ? sanitize_text_field( $request->get_param( 'post_type' ) ) : 'post';
		$per_page  = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 20;
		$page      = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;

		// Prepare the query arguments.
		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'paged'          => $page,
		];

		// Query the posts.
		$query = new WP_Query( $args );

		// Prepare the response.
		$data = [];

		// Loop through the posts and add custom data.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				// Get the post ID.
				$post_id = get_the_ID();

				// Get the post content, the same way `the_content()` does.
				$content = get_the_content();
				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );

				// Add custom data to each post.
				$data[] = [
					'id'             => $post_id,
					'title'          => get_the_title(),
					'date'           => get_the_date( 'c' ),
					'featured_image' => get_the_post_thumbnail_url( $post_id, 'full' ),
					'excerpt'        => get_the_excerpt(),
					'content'        => $content,
					'categories'     => $this->get_terms( $post_id, 'category' ),
					'tags'           => $this->get_terms( $post_id, 'post_tag' ),
					// 'custom_field'   => get_post_meta( get_the_ID(), 'custom_field_key', true ),
				];
			}
			wp_reset_postdata();
		}

		// Prepare the paginated response.
		$response = [
			'posts'        => $data,
			'total'        => (int) $query->found_posts,
			'per_page'     => (int) $per_page,
			'current_page' => (int) $page,
			'total_pages'  => (int) $query->max_num_pages,
		];

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Get the terms associated with a post.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return array
	 */
	function get_terms( $post_id, $taxonomy ) {
		// Get the terms associated with the post.
		$terms = get_the_terms( $post_id, $taxonomy );

		// Check if terms were returned.
		if ( ! $terms || is_wp_error( $terms ) ) {
			return [];
		}

		// Extract the term names.
		$names = wp_list_pluck( $terms, 'name' );

		return $names;
	}

	/**
	 * Authenticate and validate the request.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function authenticate_request( $request ) {
		return true;

		// Get the headers
		$headers = $request->get_headers();

		// Bail if no headers.
		if ( ! isset( $headers['authorization'] ) ) {
			// If authorization header is missing
			return new WP_Error( 'rest_forbidden', 'Authorization header missing.', [ 'status' => 403 ] );
		}

		// Extract the application password from the Authorization header.
		$auth_header                = $headers['authorization'];
		list( $type, $credentials ) = explode( ' ', reset( $auth_header ), 2 );

		// Basic Authentication should start with 'Basic'
		if ( 'Basic' !== $type ) {
			return new WP_Error( 'rest_forbidden', 'Invalid authentication method.', [ 'status' => 403 ] );
		}

		// Decode the credentials
		$decoded_credentials         = base64_decode( $credentials );
		list( $username, $password ) = explode( ':', $decoded_credentials, 2 );

		// Validate the application password
		if ( ! $username || ! $password ) {
			return new WP_Error( 'rest_forbidden', 'Invalid credentials.', [ 'status' => 403 ] );
		}

		// Authenticate the user
		$this->user = wp_authenticate_application_password( $username, $password );

		// If the authentication failed.
		if ( is_wp_error( $this->user ) ) {
			return new WP_Error( 'rest_forbidden', 'Invalid application password.', [ 'status' => 403 ] );
		}

		return true;
	}
}