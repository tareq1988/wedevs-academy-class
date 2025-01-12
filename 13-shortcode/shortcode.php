<?php

/**
 * Plugin Name: Shortcode
 * Description: A plugin to demonstrate WordPress shortcode and nested shortcode.
 * Version: 1.0
 * Author: weDevs Academy
 * Author URI: https://wedevs.academy
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Shortcode_Demo {

    function __construct() {
        add_shortcode('contact_form', array($this, 'shortcode_demo'));

        // columns
        add_shortcode('columns', [$this, 'columns']);
        add_shortcode('column', [$this, 'column']);
    }

    function shortcode_demo($atts, $content = '') {
        $args = shortcode_atts([
            'title'        => 'Default Title',
            'id'           => 0,
            'style'        => 'light',
            'border_color' => '#ddd'
        ], $atts);

        if (!$args['id']) {
            return 'Contact form ID is required';
        }

        ob_start();

        include __DIR__ . '/shortcode-view.php';

        return ob_get_clean();
    }

    public function columns($atts, $content = '') {
        ob_start();
        echo '<div class="columns-wrapper">';
        echo do_shortcode($content); // handle nested shortcode
        echo '</div>';

        return ob_get_clean();
    }

    public function column($atts, $content = '') {
        ob_start();

        echo '<div class="column">';
        echo $content;
        echo '</div>';

        return ob_get_clean();
    }
}

function dump($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

new Shortcode_Demo();
