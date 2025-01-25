<?php
/**
 * Plugin Name: Academy i18n Demo
 * Description: A simple plugin to demonstrate i18n in WordPress.
 * Version: 1.0
 * Author: weDevs Academy
 * Author URI: https://wedevs.academy
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wd-i18n
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Academy_i18n_Demo {

    /**
     * Plugin constructor
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
    }

    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'wd-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Academy Demo', 'wd-i18n' ),
            __( 'Academy Demo', 'wd-i18n' ),
            'manage_options',
            'academy-i18n-demo',
            [ $this, 'admin_page_content' ],
            'dashicons-translation',
            20
        );
    }

    /**
     * Admin page content
     */
    public function admin_page_content() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Welcome to the Academy i18n Demo!', 'wd-i18n' ); ?></h1>
            <p><?php esc_html_e( 'This is a demonstration of internationalization in WordPress plugins.', 'wd-i18n' ); ?></p>
            <p><?php esc_html_e( 'Translate this plugin to your own language!', 'wd-i18n' ); ?></p>
        </div>
        <?php
    }
}

new Academy_i18n_Demo();
