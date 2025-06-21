<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_submit_gtb_newsletter', 'handle_gtb_block_newsletter_submission');
add_action('wp_ajax_nopriv_submit_gtb_newsletter', 'handle_gtb_block_newsletter_submission');

function handle_gtb_block_newsletter_submission() {
    check_ajax_referer('submit_newsletter_nonce');
    $name  = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    if (empty($name) || empty($email)) {
        wp_send_json_error(['message' => 'Name and email are required.']);
    }
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }
    global $wpdb;
    $table = $wpdb->prefix . 'sam_newsletter';
    $exists = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email)
    );
    if ($exists) {
        wp_send_json_error(['message' => 'You are already subscribed.']);
    }
    $inserted = $wpdb->insert($table, [
        'name'  => $name,
        'email' => $email,
        'status'     => 'publish',
        'created_at' => current_time('mysql'),
    ], ['%s', '%s', '%s']);
    if ($inserted) {
        $subject = "🎉 You're Subscribed to Our Newsletter!";
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Newsletter Team <no-reply@yourdomain.com>'
        ];
        $body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                <h2 style="color: #0073aa;">Thank You for Subscribing, ' . esc_html($name) . '!</h2>
                <p style="font-size: 16px; line-height: 1.6;">
                    You’ve successfully subscribed to our newsletter.<br>
                    We’ll keep you updated with the latest news, updates, and exclusive offers.
                </p>
                <p style="font-size: 15px; color: #555;">
                    If you did not sign up, feel free to ignore this email.
                </p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="font-size: 13px; color: #999;">&copy; ' . date('Y') . ' Your Company Name. All rights reserved.</p>
            </div>
        ';

        wp_mail($email, $subject, $body, $headers);
        wp_send_json_success(['message' => 'Thank you for subscribing!']);
    } else {
        wp_send_json_error(['message' => 'Could not save your information. Please try again.']);
    }
}