<?php
defined( 'ABSPATH' ) or die(); // Protect from alien invasion

global $sl_db_version;
$sl_db_version = '1.0';

// Setup Database Table
function sl_install () {
    global $wpdb;
    global $sl_db_version;

    $table_name = $wpdb->prefix . "securelogins";

    $charset_collate = '';

    if ( ! empty( $wpdb->charset ) ) {
      $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }

    if ( ! empty( $wpdb->collate ) ) {
        $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) UNSIGNED NOT NULL,
        user_obj blob NOT NULL,
        login_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        auth_token varchar(32) NOT NULL,
        otp int(6) UNSIGNED,
        login_status int(1) UNSIGNED DEFAULT 0 NOT NULL,
        otp_destination varchar(55),
        user_ip varchar(45),
        UNIQUE KEY id (id)
        ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'sl_db_version', $sl_db_version );

    sl_init_internal();
    flush_rewrite_rules();

}