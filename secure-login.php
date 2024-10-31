<?php
/**
 * Plugin Name: Secure Login
 * Plugin URI: http://rockingthemes.com/wordpress-plugins/secure-login
 * Description: Secure, 2 step Verification for WordPress login
 * Version: 1.0.4
 * Author: David Leonard
 * Author URI: http://davidleonard.co.za
 * License: GPL2
 */

defined( 'ABSPATH' ) or die(); // Protect from alien invasion

define( 'SL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SL_URL', plugin_dir_url( __FILE__ ) );

// Include required core files
require_once( SL_PATH . 'includes/rewrite-rules.php' );
require_once( SL_PATH . 'includes/init.php' );
require_once( SL_PATH . 'includes/authenticate.php' );
require_once( SL_PATH . 'admin/page-settings.php' );

// Create database table
register_activation_hook( __FILE__, 'sl_install' );

// Validate login with OTP
add_filter( 'authenticate', 'sl_auth_login', 30, 3 );
function sl_auth_login ( $user, $username, $password ) {
    if ( is_wp_error( $user ) ) {
        return $user;
    } else {
        global $wpdb;
        $table_name = $wpdb->prefix . "securelogins";
        $sl_settings = get_option( 'sl_general_settings' );
        if ( !isset( $sl_settings['timeout'] ) || '' == $sl_settings['timeout'] ) {
            $sl_settings['timeout'] = 5;
        }

        $user_id = sanitize_key( $user->ID );

        $login_attempt = $wpdb->get_row( $wpdb->prepare(
            "
                SELECT *
                FROM $table_name
                WHERE user_id = %d AND login_status = 0
            ",
            $user_id
        ) );

        if ( NULL === $login_attempt ) {
            $user_hash = md5( $user->ID . time() );
            $user_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'user_obj' => serialize($user),
                    'auth_token' => $user_hash,
                    'login_time' => current_time( 'mysql' ),
                    'user_ip' => $user_ip,
                )
            );

            wp_redirect( home_url() . "/verify-login/" . $user_hash . "/");
        } elseif ( ( current_time( 'timestamp' ) - strtotime( $login_attempt->login_time ) ) > $sl_settings['timeout'] * MINUTE_IN_SECONDS ) {
            $wpdb->update(
                $table_name,
                array(
                    'login_status' => 3
                ),
                array( 'auth_token' => $login_attempt->auth_token ),
                array(
                    '%d'
                ),
                array( '%s' )
            );

            $user_hash = md5( $user->ID . time() );
            $user_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'user_obj' => serialize($user),
                    'auth_token' => $user_hash,
                    'login_time' => current_time( 'mysql' ),
                    'user_ip' => $user_ip,
                )
            );

            wp_redirect( home_url() . "/verify-login/" . $user_hash . "/");
        } else {
            wp_redirect( home_url() . "/verify-login/" . $login_attempt->auth_token . "/");
        }

        exit;
    }
}

// Display error message on login page
function wptp_modify_html() {
    $sl_error = $_GET['sl_error'];
    if ( $sl_error != '' ) {
        $login_error = get_query_var( 'sl_error' );
        switch ( $sl_error ) {
            case 401:
                $message = '<strong>ERROR</strong>: Session timed out!';
                break;
            case 402:
                $message = '<strong>ERROR</strong>: IP does not match!';
                break;
            default:
                $message = '<strong>ERROR</strong>: Session timed out!';
        }
        add_filter( 'login_message', create_function( '', "return '<div id=\"login_error\">$message</div>';" ) );
    }
}
add_action( 'login_head', 'wptp_modify_html');

// Load Plugin Admin CSS
function load_custom_wp_admin_style() {
        wp_register_style( 'secure-login', SL_URL . '/admin/css/style.css', false, '1.0.0' );
        wp_enqueue_style( 'secure-login' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );