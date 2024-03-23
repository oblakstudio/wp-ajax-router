# Thoughts and musings

While working on the library - most common question we got is:

![Why](https://files.oblak.bot/but-why.gif)

So we decided to write a bit about it. Let's counter some fundamental criticisms of the library.

## WordPress already has an AJAX API

Indeed it does. Let's see some limitations of the default AJAX API:

### Boilerplating and repetition

Simple action which allows getting some data from the server:

```php
function my_action_callback() {
  if (!wp_verify_nonce($_POST['nonce'], 'my_action')) {
    wp_send_json_error('Invalid nonce');
  }

  $post_status = ['publish'];

  if (current_user_can('edit_others_posts')) {
    $post_status[] = 'private';
  }

  $whatever  = sanitize_text_field(wp_unslash($_POST['whatever']));
  $something = sanitize_text_field(wp_unslash($_POST['something']));

  $posts = get_posts([
    'post_status' => $post_status,
    's'           => $whatever,
    'category'    => $something,
    'fields'      => 'ids',
  ]);

  wp_send_json_success($posts);
}

add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );
add_action( 'wp_ajax_my_action', 'my_action_callback' );
```

So, for every action, we have a (optional) nonce check, sanitization and validation of the data, and response handling. If you have multiple actions - you'll have to repeat this process for each of them. And if you want to add some additional checks - you'll have to add them to each action.

### POST request handling

`admin-ajax.php` expects an `action` parameter to be present in the posted data, or as query parameter. So sending a POST request with proper body is impossible.

## Why not use REST API?

Because:

### Registering routes looks like this:

```php
class WP_REST_Posts_Controller extends WP_REST_Controller {
    public function register_routes() {


        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_item' ),
                    'permission_callback' => array( $this, 'create_item_permissions_check' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                ),
                'allow_batch' => $this->allow_batch,
                'schema'      => array( $this, 'get_public_item_schema' ),
            )
        );


        $schema        = $this->get_item_schema();
        $get_item_args = array(
            'context' => $this->get_context_param( array( 'default' => 'view' ) ),
        );
        if ( isset( $schema['properties']['password'] ) ) {
            $get_item_args['password'] = array(
                'description' => __( 'The password for the post if it is password protected.' ),
                'type'        => 'string',
            );
        }
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                'args'        => array(
                    'id' => array(
                        'description' => __( 'Unique identifier for the post.' ),
                        'type'        => 'integer',
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $get_item_args,
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item' ),
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_item' ),
                    'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    'args'                => array(
                        'force' => array(
                            'type'        => 'boolean',
                            'default'     => false,
                            'description' => __( 'Whether to bypass Trash and force deletion.' ),
                        ),
                    ),
                ),
                'allow_batch' => $this->allow_batch,
                'schema'      => array( $this, 'get_public_item_schema' ),
            )
        );
    }
}
```
### And handling requests looks like this:

```php
class WP_REST_Posts_Controller extends WP_REST_Controller {
  public function get_items( $request ) {

		// Ensure a search string is set in case the orderby is set to 'relevance'.
		if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
			return new WP_Error(
				'rest_no_search_term_defined',
				__( 'You need to define a search term to order by relevance.' ),
				array( 'status' => 400 )
			);
		}

		// Ensure an include parameter is set in case the orderby is set to 'include'.
		if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
			return new WP_Error(
				'rest_orderby_include_missing_include',
				__( 'You need to define an include parameter to order by include.' ),
				array( 'status' => 400 )
			);
		}

		// SNIPPED FOR BREVITY

		$response = rest_ensure_response( $posts );

		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$collection_url = rest_url( rest_get_route_for_post_type_items( $this->post_type ) );
		$base           = add_query_arg( urlencode_deep( $request_params ), $collection_url );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}
}
```
### Non-JSON responses

If your WP-REST API endpoint needs to return anything other than a string or a JSON object - you're out of luck. You'll have to use the `rest_pre_serve_request` filter to intercept the request and do all the heavy lifting yourself.

### Compared to REST API

WP REST API is a great feature - but it has its limitations and shortcomings. It's not a silver bullet. Neither is this library. But with a declarative approach to defining routes and controllers - it's easier to manage and maintain your codebase.

## Conclusion

This library is not a replacement for the default AJAX API or the REST API. It's a tool that can be used to simplify the process of creating AJAX endpoints in WordPress. It's not perfect, but it's a step in the right direction. We aim to improve it and develop a wp-centric PSR compliant router that will be able to handle both REST and AJAX requests - without too much overhead and performance loss.
