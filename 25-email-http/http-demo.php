<?php

/**
 * HTTP API Demo class
 */
class HTTP_API_Demo {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [$this, 'add_menu_pages'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_action( 'wp_ajax_fetch_posts', [$this, 'ajax_fetch_posts'] );
        add_action( 'wp_ajax_create_post', [$this, 'ajax_create_post'] );
        add_action( 'wp_ajax_delete_post', [$this, 'ajax_delete_post'] );
    }

    /**
     * Add menu pages
     */
    public function add_menu_pages() {
        add_submenu_page(
            'email-http-demo',
            'HTTP API Demo',
            'HTTP API Demo',
            'manage_options',
            'http-api-demo',
            [$this, 'render_http_api_page']
        );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts( $hook ) {
        if ( 'email-http-demo_page_http-api-demo' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'http-api-demo', plugin_dir_url( __FILE__ ) . 'js/http-api-demo.js', ['jquery'], '1.0', true );
        wp_localize_script( 'http-api-demo', 'httpApiDemo', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'http_api_demo_nonce' ),
        ] );
    }

    /**
     * Render HTTP API demo page
     */
    public function render_http_api_page() {
        ?>
<div class="wrap">
    <h1>HTTP API Demo</h1>
    <button id="fetch-posts" class="button">Fetch Posts</button>
    <div id="posts-list"></div>
    <h2>Create New Post</h2>
    <form id="create-post-form">
        <p>
            <label for="post-title">Title:</label>
            <input type="text" id="post-title" name="post-title" required>
        </p>
        <p>
            <label for="post-body">Body:</label>
            <textarea id="post-body" name="post-body" required></textarea>
        </p>
        <p>
            <input type="submit" class="button button-primary" value="Create Post">
        </p>
    </form>
</div>
<?php
    }

    /**
     * AJAX handler for fetching posts
     */
    public function ajax_fetch_posts() {
        check_ajax_referer( 'http_api_demo_nonce', 'nonce' );

        $response = wp_remote_get( 'https://jsonplaceholder.typicode.com/posts' );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        wp_send_json_success( $data );
    }

    /**
     * AJAX handler for creating a post
     */
    public function ajax_create_post() {
        check_ajax_referer( 'http_api_demo_nonce', 'nonce' );

        $title = sanitize_text_field( $_POST['title'] );
        $body  = sanitize_textarea_field( $_POST['body'] );

        $response = wp_remote_post( 'https://jsonplaceholder.typicode.com/posts', [
            'body' => wp_json_encode( [
                'title'   => $title,
                'body'    => $body,
                'userId'  => 1,
                'api_key' => 'API_KEY_HERE',
            ] ),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        wp_send_json_success( $data );
    }

    /**
     * AJAX handler for deleting a post
     */
    public function ajax_delete_post() {
        check_ajax_referer( 'http_api_demo_nonce', 'nonce' );

        $post_id = intval( $_POST['post_id'] );

        $response = wp_remote_request( "https://jsonplaceholder.typicode.com/posts/{$post_id}", [
            'method'  => 'DELETE',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key'    => 'API_KEY_HERE',
            ],
        ] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( ['message' => 'Post deleted successfully'] );
    }
}
?>