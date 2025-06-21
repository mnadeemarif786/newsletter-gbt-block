<?php
/**
 * Plugin Name: Newsletter Subscription
 * Description: Custom Newsletter Subscription Gutenberg Block Plugin.
 * Version: 1.0
 * Author: Muhammad Nadeem Arif
 * Author URI: https://mnadeemarif786.github.io
 * Requires PHP: 7.4
 * Text Domain: newsletter-subscription
 */
// Prevent direct access
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', 'newsletter_enqueue_script');
function newsletter_enqueue_script() {
    wp_enqueue_script('newsletter-js', plugin_dir_url(__FILE__) . 'assets/js/newsletter.js', ['jquery'], '6.8.1', true);
    wp_localize_script('newsletter-js', 'newsletter_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        '_ajax_nonce' => wp_create_nonce('submit_newsletter_nonce')
    ]);
}

function plugin_log($message) {
    $plugin_dir = plugin_dir_path(__FILE__) . 'plugin_logs/';
    $log_file = $plugin_dir . 'plugin_log.log';
    if (!is_dir($plugin_dir)) {
        wp_mkdir_p($plugin_dir);
    }
    if (!file_exists($log_file)) {
        file_put_contents($log_file, "");
    }
    $timestamp = date("Y-m-d H:i:s");
    $log_message = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
define( 'NEWSLETTER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
register_activation_hook( __FILE__, 'check_plugin_dependencies' );
function check_plugin_dependencies() {
	$min_php      = '7.4';
    $min_wp       = '5.8';
    $current_php  = PHP_VERSION;
    $current_wp   = get_bloginfo( 'version' );
    $plugin_name  = __( 'Newsletter Subscription', 'newsletter-subscription' );
    if ( version_compare( $current_php, $min_php, '<' ) ) {
    	deactivate_plugins( NEWSLETTER_PLUGIN_BASENAME );
        wp_die(
            sprintf(
                __( '%1$s requires PHP version %2$s or higher. You are running version %3$s.', 'newsletter-subscription' ),
                $plugin_name, $min_php, $current_php
            ),
            __( 'Plugin dependency check failed', 'newsletter-subscription' ),
            [ 'back_link' => true ]
        );
    }
    if ( version_compare( $current_wp, $min_wp, '<' ) ) {
    	deactivate_plugins( NEWSLETTER_PLUGIN_BASENAME );
        wp_die(
            sprintf(
                __( '%1$s requires WordPress version %2$s or higher. You are running version %3$s.', 'newsletter-subscription' ),
                $plugin_name, $min_wp, $current_wp
            ),
            __( 'Plugin dependency check failed', 'newsletter-subscription' ),
            [ 'back_link' => true ]
        );
    }
	if ( ! function_exists( 'register_block_type' ) ) {
		deactivate_plugins( NEWSLETTER_PLUGIN_BASENAME );;
		wp_die(
            __( 'This plugin requires the Gutenberg editor or WordPress 5.8+ to be active.', 'newsletter-subscription' ),
            __( 'Plugin dependency check failed', 'newsletter-subscription' ),
            ['back_link' => true]
        );
	}
}

add_action( 'enqueue_block_editor_assets', 'enqueue_newsletter_block_editor_assets' );
function enqueue_newsletter_block_editor_assets() {
	$script_path = plugin_dir_path( __FILE__ ) . 'build/index.js';
    $style_path  = plugin_dir_path( __FILE__ ) . 'build/editor.css';
    if ( file_exists( $script_path ) ) {
    	wp_enqueue_script( 'newsletter-block-editor-script', plugins_url( 'build/index.js', __FILE__ ), [ 'wp-blocks', 'wp-element', 'wp-editor' ], filemtime( $script_path ));
    }
    if ( file_exists( $style_path ) ) {
    	wp_enqueue_style( 'newsletter-block-editor-style', plugins_url( 'build/editor.css', __FILE__ ), [], filemtime( $style_path ));
    }
}

// add_action( 'admin_enqueue_scripts', 'enqueue_newsletter_admin_assets' );
function enqueue_newsletter_admin_assets( $hook ) {
	plugin_log( $hook );
    if ( $hook !== 'toplevel_page_my-newsletter' ) {
        return;
    }
    wp_enqueue_style( 'newsletter-admin-style', plugins_url( 'assets/admin/admin.css', __FILE__ ) );
    wp_enqueue_script( 'newsletter-admin-script', plugins_url( 'assets/admin/admin.js', __FILE__ ), [ 'jquery' ], null, true );
}

add_action( 'admin_notices', 'newsletter_missing_gutenberg_notice' );
function newsletter_missing_gutenberg_notice() {
    if ( current_user_can( 'activate_plugins' ) && ! function_exists( 'register_block_type' ) ) {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Newsletter Subscription Plugin requires the Gutenberg editor or WordPress 5.8+. Please update your WordPress installation.', 'newsletter-subscription' );
        echo '</p></div>';
    }
}

if ( ! function_exists( 'load_block_file' ) ) {
	function load_block_file() {
	    require_once plugin_dir_path(__FILE__) . 'includes/gutenberg_block.php';
        require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
        require_once plugin_dir_path(__FILE__) . 'includes/admin-dashboard.php';
	}
	add_action('plugins_loaded', 'load_block_file');
}

require_once plugin_dir_path(__FILE__) . 'includes/db_newsletter_db.php';
register_activation_hook(__FILE__, 'activate_newsletter_plugin');
function activate_newsletter_plugin() {
    check_plugin_dependencies();
    sam_create_newsletter_table();
}
?>