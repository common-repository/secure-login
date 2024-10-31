<?php
defined( 'ABSPATH' ) or die(); // Protect from alien invasion

// OTP page rewrite rule
add_action( 'init', 'sl_init_internal' );
function sl_init_internal()
{
    add_rewrite_tag('%sl_auth%', '([^&]+)');
    add_rewrite_rule( '^verify-login/([^/]*)/?', 'index.php?sl_api=1&sl_auth=$matches[1]', 'top' );
}

add_filter( 'query_vars', 'sl_query_vars' );
function sl_query_vars( $query_vars )
{
    $query_vars[] = 'sl_api';
    $query_vars[] = 'sl_error';
    return $query_vars;
}

add_action( 'parse_request', 'sl_parse_request' );
function sl_parse_request( &$wp )
{
    if ( array_key_exists( 'sl_api', $wp->query_vars ) ) {
        require_once( SL_PATH . 'templates/verify-login.php');
        exit();
    }
    return;
}