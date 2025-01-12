<?php

/**
 * Plugin Name: Essential Security
 * Description: Sanitize and validate data
 * Version: 1.0
 * Author: weDevs Academy
 * Author URI: https://wedevs.academy
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Essential_Security_Demo {
    private $option_name = 'esd_settings';
    private $errors = [];

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));

        add_action('admin_post_save_esd_settings', array($this, 'handle_form_submission'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Security Demo',
            'Security Demo',
            'manage_options',
            'security-demo',
            array($this, 'display_admin_page'),
            'dashicons-shield'
        );
    }

    public function handle_form_submission() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access'));
        }

        if (!isset($_POST['esd_nonce']) || !wp_verify_nonce($_POST['esd_nonce'], 'esd_save_settings')) {
            wp_die(__('Invalid nonce'));
        }

        $input = $this->validate_input($_POST);

        if (!empty($this->errors)) {
            set_transient('esd_errors', $this->errors, 45);
            wp_redirect(add_query_arg('settings-updated', 'error', wp_get_referer()));
            exit;
        }

        update_option($this->option_name, $input);
        wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }

    private function validate_input($input) {
        $sanitized = [];

        // Validate Message
        if (empty($input['user_message'])) {
            $this->errors['user_message'] = 'Message is required';
        } else {
            // $sanitized['user_message'] = sanitize_textarea_field($input['user_message']);
            $sanitized['user_message'] = sanitize_text_field($input['user_message']);
        }

        // Validate Email
        if (empty($input['email'])) {
            $this->errors['email'] = 'Email is required';
        } elseif (!is_email($input['email'])) {
            $this->errors['email'] = 'Invalid email format';
        } else {
            $sanitized['email'] = sanitize_email($input['email']);
        }

        // Validate Website
        if (empty($input['website'])) {
            $this->errors['website'] = 'Website is required';
        } elseif (!filter_var($input['website'], FILTER_VALIDATE_URL)) {
            $this->errors['website'] = 'Invalid URL format';
        } else {
            $sanitized['website'] = sanitize_url($input['website']);
        }

        return $sanitized;
    }

    public function display_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access'));
        }

        $options = get_option($this->option_name, []);
        $errors = get_transient('esd_errors');
        delete_transient('esd_errors'); ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($errors): ?>
                <div class="notice notice-error is-dismissible">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo esc_html($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true'): ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php esc_html_e('Settings saved successfully!', 'essential-security'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form method="post"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="save_esd_settings">

                <?php wp_nonce_field('esd_save_settings', 'esd_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="user_message">Message</label>
                        </th>
                        <td>
                            <textarea id="user_message" name="user_message" class="regular-text"
                                rows="10"><?php echo esc_textarea($options['user_message'] ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email">Email</label>
                        </th>
                        <td>
                            <input type="email" id="email" name="email"
                                value="<?php echo esc_attr($options['email'] ?? ''); ?>"
                                class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="website">Website</label>
                        </th>
                        <td>
                            <input type="url" id="website" name="website"
                                value="<?php echo esc_attr($options['website'] ?? ''); ?>"
                                class="regular-text">
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <?php if ($options): ?>
                <h2>Saved Data</h2>
                <ul>
                    <li>Message:
                        <?php echo esc_html($options['user_message'] ?? ''); ?>
                    </li>
                    <li>Email:
                        <?php echo esc_html($options['email'] ?? ''); ?>
                    </li>
                    <li>Website:
                        <?php echo esc_url($options['website'] ?? ''); ?>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }
}

new Essential_Security_Demo();
