<?php
/**
 * Plugin Name: Email and HTTP API Demo
 * Description: Demonstrates sending emails and using HTTP API in WordPress
 * Version: 1.1
 * Author: Tareq Hasan
 * Author URI: https://tareq.co
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/http-demo.php';

/**
 * Main plugin class
 */
class Email_HTTP_Demo {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
        add_action( 'admin_post_send_email', [ $this, 'handle_email_form_submission' ] );

        add_filter( 'wp_mail_from', [ $this, 'custom_wp_mail_from' ] );
        add_filter( 'wp_mail_from_name', [ $this, 'custom_wp_mail_from_name' ] );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'admin_footer', [ $this, 'add_inline_scripts' ] );

        // Initialize HTTP API Demo
        new HTTP_API_Demo();
    }

    /**
     * Add menu pages
     */
    public function add_menu_pages() {
        add_menu_page(
            'Email & HTTP Demo',
            'Email & HTTP Demo',
            'manage_options',
            'email-http-demo',
            [ $this, 'render_email_page' ],
            'dashicons-email-alt'
        );

        add_submenu_page(
            'email-http-demo',
            'Email Demo',
            'Email Demo',
            'manage_options',
            'email-http-demo',
            [ $this, 'render_email_page' ]
        );
    }

    /**
     * Enqueue styles
     */
    public function enqueue_styles( $hook ) {
        if ( strpos( $hook, 'email-http-demo' ) !== false ) {
            wp_enqueue_style( 'email-http-demo-style', plugin_dir_url( __FILE__ ) . 'css/form.css', [], '1.0' );
        }
    }

    /**
     * Add inline scripts
     */
    public function add_inline_scripts() {
        ?>
<script>
    jQuery(document).ready(function($) {
        $('input[name="email_format"]').change(function() {
            if ($(this).val() === 'html') {
                $('#message-plain').hide();
                $('#message-html').show();
                $('#message').removeAttr('required');
            } else {
                $('#message-plain').show();
                $('#message-html').hide();
                $('#message').attr('required', 'required');
            }
        });

        $('#email-form').submit(function(e) {
            if ($('input[name="email_format"]:checked').val() === 'html') {
                $('#message').remove();
            }
        });
    });
</script>
<?php
    }

    /**
     * Render email demo page
     */
    public function render_email_page() {
        ?>
<div class="wrap">
    <h1>Email Demo</h1>

    <?php $this->show_admin_notices(); ?>

    <form id="email-form" method="post"
        action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="send_email">
        <?php wp_nonce_field( 'send_email', 'email_nonce' ); ?>
        <p>
            <label for="to_email">To Email Address:</label>
            <input type="email" id="to_email" name="to_email" required>
        </p>
        <p>
            <label for="from_name">From Name:</label>
            <input type="text" id="from_name" name="from_name" required>
        </p>
        <p>
            <label for="from_email">From Email:</label>
            <input type="email" id="from_email" name="from_email" required>
        </p>
        <p>
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
        </p>
        <div class="radio-group">
            <label>Email Format:</label>
            <label><input type="radio" name="email_format" value="plain" checked> Plain Text</label>
            <label><input type="radio" name="email_format" value="html"> HTML</label>
        </div>
        <div id="message-plain">
            <p>
                <label for="message">Message (Plain Text):</label>
                <textarea id="message" name="message" rows="10" cols="50" required></textarea>
            </p>
        </div>
        <div id="message-html" style="display:none;">
            <p>
                <label for="message_html">Message (HTML):</label>
                <?php
                        wp_editor( '', 'message_html', [
                            'textarea_name' => 'message_html',
                            'media_buttons' => false,
                            'textarea_rows' => 10,
                            'teeny'         => true,
                        ] ); ?>
            </p>
        </div>
        <p>
            <input type="submit" class="button button-primary" value="Send Email">
        </p>
    </form>

    <div style="margin-top: 20px">
        <button class="button button-secondary" onClick="fillForm()">Fill Form</button>
    </div>

    <script>
        function fillForm() {
            document.getElementById('to_email').value = 'receipent@gmail.com';
            document.getElementById('from_name').value = 'Sender Name';
            document.getElementById('from_email').value = 'from@wedevs.academy';
            document.getElementById('subject').value = 'Test Email';
            document.getElementById('message').value = 'This is a test email message.';
        }
    </script>

</div>
<?php
    }

    /**
     * Handle email form submission
     */
    public function handle_email_form_submission() {
        if ( ! isset( $_POST['email_nonce'] ) || ! wp_verify_nonce( $_POST['email_nonce'], 'send_email' ) ) {
            wp_die( 'Invalid nonce.' );
        }

        $to         = sanitize_email( $_POST['to_email'] );
        $subject    = sanitize_text_field( $_POST['subject'] );
        $from_name  = sanitize_text_field( $_POST['from_name'] );
        $from_email = sanitize_email( $_POST['from_email'] );
        $format     = $_POST['email_format'] === 'html' ? 'html' : 'plain';

        if ( $format === 'html' ) {
            $message = wpautop( wp_kses_post( $_POST['message_html'] ) );
        } else {
            $message = sanitize_textarea_field( $_POST['message'] );
        }

        $headers = [
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Content-Type: text/' . $format . '; charset=UTF-8',
        ];

        $result = wp_mail( $to, $subject, $message, $headers );

        wp_safe_redirect( add_query_arg(
            [
                'page'    => 'email-http-demo',
                'message' => $result ? 'success' : 'error',
            ],
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        if ( isset( $_GET['message'] ) ) {
            if ( $_GET['message'] === 'success' ) {
                echo '<div class="notice notice-success is-dismissible"><p>Email sent successfully!</p></div>';
            } elseif ( $_GET['message'] === 'error' ) {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to send email. Please try again.</p></div>';
            }
        }
    }

    /**
     * Customize wp_mail_from
     *
     * @param string $email default email address
     *
     * @return string modified email address
     */
    public function custom_wp_mail_from( $email ) {
        return 'custom@example.com';
    }

    /**
     * Customize wp_mail_from_name
     *
     * @param string $name default sender name
     *
     * @return string modified sender name
     */
    public function custom_wp_mail_from_name( $name ) {
        return 'Custom Sender Name';
    }
}

// Initialize the plugin
new Email_HTTP_Demo();
