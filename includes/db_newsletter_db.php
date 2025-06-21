<?php
if (!defined('ABSPATH')) exit;

function sam_create_newsletter_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sam_newsletter';
    $charset_collate = $wpdb->get_charset_collate();
    $create_sql_table = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        status ENUM('publish','trash') NOT NULL DEFAULT 'publish',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($create_sql_table);
}

function sam_drop_newsletter_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sam_newsletter';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}