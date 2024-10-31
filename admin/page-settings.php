<?php
// Create Plugin Admin Pages
function sl_create_menu_pages() {

    add_menu_page(
        'Secure Login',
        'Secure Login',
        'administrator',
        'secure-login',
        'sl_menu_page_display',
        ''
    );

    add_submenu_page(
        'secure-login',
        'Secure Login Settings',
        'Settings',
        'administrator',
        'secure-login-settings',
        'sl_options_display'
    );

}
add_action('admin_menu', 'sl_create_menu_pages');

// Secure Login Page Content
function sl_menu_page_display() {
?>

    <div class="wrap">
        <h2>Secure Login</h2>
        <?php include_once( SL_PATH . 'admin/page-main.php' ); ?>
    </div>
<?php
}

// Settings Page Content
function sl_options_display() {
?>

    <div class="wrap">
        <h2>Secure Login Settings</h2>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'sl_general_settings' ); ?>
            <?php do_settings_sections( 'sl_general_settings' ); ?>
            <?php submit_button(); ?>
        </form>

    </div>

<?php
}

// Initialize Settings
function sl_initialize_settings() {

    if( false == get_option( 'sl_general_settings' ) ) {
        add_option( 'sl_general_settings' );
    }

    add_settings_section(
        'general_settings_section',
        'General Settings',
        'sl_general_settings_callback',
        'sl_general_settings'
    );

    add_settings_field(
        'timeout',
        'OTP Timeout',
        'sl_field_timeout_callback',
        'sl_general_settings',
        'general_settings_section',
        array(
            'How many minutes before the One Time Pin expires.'
        )
    );

    add_settings_field(
        'from_email',
        'OTP Email From Address',
        'sl_field_from_email_callback',
        'sl_general_settings',
        'general_settings_section',
        array(
            'The From address for the OTP email.'
        )
    );

    register_setting(
        'sl_general_settings',
        'sl_general_settings',
        'sl_sanitize_general_settings'
    );

}
add_action('admin_init', 'sl_initialize_settings');

// Settings Section Callback
function sl_general_settings_callback() {
    echo '<p>General settings for the Secure Login plugin.</p>';
}

// Settings Field Callback
function sl_field_timeout_callback($args) {

    $options = get_option( 'sl_general_settings' );

    $timeout = 5;
    if( isset( $options['timeout'] ) && $options['timeout'] != '' ) {
        $timeout = $options['timeout'];
    }

    echo '<input type="text" id="timeout" name="sl_general_settings[timeout]" value="' . $timeout . '" /> mins';

}

// Settings Field Callback
function sl_field_from_email_callback($args) {

    $options = get_option( 'sl_general_settings' );

    $from_email = get_option('admin_email');
    if( isset( $options['from_email'] ) && $options['from_email'] != '' ) {
        $from_email = $options['from_email'];
    }

    echo '<input type="text" id="from_email" name="sl_general_settings[from_email]" value="' . $from_email . '" />';

}

// Sanitize General Settings Input Fields
function sl_sanitize_general_settings( $input ) {

    $output = array();

    // validate and sanitize timeout
    if ( isset( $input['timeout'] ) ) {
        if ( is_numeric( $input['timeout'] ) ) {
            $output['timeout'] = strip_tags( stripslashes( $input['timeout'] ) );
        } else {
            add_settings_error( 'sl_general_settings', 'timeout-data-type', "OTP Timeout must be numerical" );
        }
    }

    // validate and sanitize email
    if ( isset( $input['from_email'] ) ) {
        if ( is_email( $input['from_email'] ) ) {
            $output['from_email'] = sanitize_email( $input['from_email'] );
        } else {
            add_settings_error( 'sl_general_settings', 'email-error', "From email address not valid" );
        }
    }

    return apply_filters( 'sl_general_settings', $output, $input );
}