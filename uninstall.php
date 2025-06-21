<?php
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'sam_newsletter';
$wpdb->query("DROP TABLE IF EXISTS $table_name");