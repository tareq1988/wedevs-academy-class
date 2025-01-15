<?php
/**
 * Plugin Name: WP Ajax
 * Description: A test plugin for WP Ajax.
 * Version: 1.0
 * Author: weDevs Academy
 * Author URI: https://wedevs.academy
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: test-plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Simple_Auth {

    public function __construct() {
        add_shortcode( 'simple-auth', [ $this, 'render_shortocde' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts'] );

        // Login and Profile Update
        add_action( 'wp_ajax_simple-auth-profile-form', [$this, 'update_profile'] );
        add_action( 'wp_ajax_nopriv_simple-auth-login-form', [$this, 'handle_login'] );

        // Fetch posts
        add_shortcode( 'simple-fetch-posts', [$this, 'render_fetch_posts'] );
        add_action( 'wp_ajax_simple-fetch-posts', [$this, 'fetch_posts'] );
        add_action( 'wp_ajax_nopriv_simple-fetch-posts', [$this, 'fetch_posts'] );
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'simple-auth-style', plugin_dir_url( __FILE__ ) . 'assets/css/auth.css' );

        wp_enqueue_script( 'simple-auth-js', plugin_dir_url( __FILE__ ) . 'assets/js/auth.js', ['jquery', 'wp-util'] );
        wp_localize_script( 'simple-auth-js', 'simpleAuthAjax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'simple-auth-profile' ),
        ] );
    }

    public function render_shortocde() {
        if ( is_user_logged_in() ) {
            return $this->render_profile_page();
        } else {
            return $this->render_auth_page();
        }
    }

    public function update_profile() {
        check_ajax_referer( 'simple-auth-profile' );

        /*
        // manual verification
        if ( ! isset( $_POST['_wpnonce'] ) ) {
            return wp_send_json_error( [
                'message' => 'Nonce not available',
            ] );
        }

        if ( wp_verify_nonce( $_POST['_wp_nonce'], 'simple-auth-profile' ) ) {
            return wp_send_json_error( [
                'message' => 'Nonce verification failed',
            ] );
        }
        */

        $display_name = sanitize_text_field( $_POST['display_name'] );
        $email        = sanitize_email( $_POST['email'] );

        $user_data = [
            'ID'           => get_current_user_id(),
            'display_name' => $display_name,
            'user_email'   => $email,
        ];

        $user_id = wp_update_user( $user_data );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( [
                'message' => $user_id->get_error_message(),
            ] );
        }

        wp_send_json_success( [
            'message' => 'Profile updated',
        ] );
    }

    public function handle_login() {
        check_ajax_referer( 'simple-auth-login' );

        $username = sanitize_text_field( $_POST['username'] );
        $password = sanitize_text_field( $_POST['password'] );

        $user = wp_signon( [
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        ] );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( [
                'message' => $user->get_error_message(),
            ] );
        }

        wp_send_json_success( [
            'message' => 'Login success, redirecting...',
        ] );
    }

    public function render_profile_page() {
        $user = wp_get_current_user();

        ob_start(); ?>
        <div id="simple-auth-profile">
            <h2>Update Profile</h2>

            <div id="profile-update-message" class="success-message hidden"></div>

            <form method="post" id="profile-form">
                <label>
                    Display Name
                    <input type="text" name="display_name" required value="<?php echo esc_attr( $user->display_name ); ?>" />
                </label>

                <label>
                    Email
                    <input type="email" name="email" required value="<?php echo esc_attr( $user->user_email ); ?>" />
                </label>

                <input type="hidden" name="action" value="simple-auth-profile-form" />

                <?php wp_nonce_field( 'simple-auth-profile' ); ?>

                <button type="submit">Update Profile</button>
            </form>
        </div>
        <?php

        return ob_get_clean();
    }

    public function render_auth_page() {
        $user = wp_get_current_user();

        ob_start(); ?>
        <div id="simple-auth-profile">
            <h2>Login</h2>

            <div id="login-message" class="hidden"></div>

            <form method="post" id="simple-auth-login-form">
                <label>
                    Username
                    <input type="text" name="username" required value="" placeholder="Username" />
                </label>

                <label>
                    Password
                    <input type="password" name="password" required value="" placeholder="Password" />
                </label>

                <input type="hidden" name="action" value="simple-auth-login-form" />

                <?php wp_nonce_field( 'simple-auth-login' ); ?>

                <button type="submit">Login</button>
            </form>
        </div>
        <?php

        return ob_get_clean();
    }

    public function render_fetch_posts() {
        ob_start(); ?>
        <div id="simple-fetch-posts">
            <ul id="ajax-posts-list"></div>

            <button id="fetch-posts">Fetch Posts</button>
        </div>
        <?php

        return ob_get_clean();
    }

    public function fetch_posts() {
        check_ajax_referer( 'simple-auth-profile' );

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

        $posts = get_posts( [
            'post_type'      => 'post',
            'posts_per_page' => 5,
            'paged'          => $page,
        ] );

        $data = [];

        foreach ( $posts as $post ) {
            $data[] = [
                'title'   => $post->post_title,
                'link'    => get_permalink( $post->ID ),
            ];
        }

        wp_send_json_success( $data );
    }
}

new Simple_Auth();
