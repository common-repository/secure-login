<?php
defined( 'ABSPATH' ) or die(); // Protect from alien invasion

global $wpdb;
$auth_token = sanitize_key($wp->query_vars['sl_auth']);
$table_name = $wpdb->prefix . "securelogins";
$sl_settings = get_option( 'sl_general_settings' );
if ( !isset( $sl_settings['timeout'] ) || '' == $sl_settings['timeout'] ) {
    $sl_settings['timeout'] = 5;
}
if ( !isset( $sl_settings['from_email'] ) || '' == $sl_settings['from_email'] ) {
    $sl_settings['from_email'] = get_option('admin_email');
}

if ( $auth_token != "" ) {
    global $login_attempt;
    $login_attempt = $wpdb->get_row( $wpdb->prepare(
        "
            SELECT *
            FROM $table_name
            WHERE auth_token = %s
        ",
        $auth_token
    ) );

    // check for login status, check for otp already sent, check for timeout
    $the_time = current_time( 'timestamp' );

    if ( ( $login_attempt != NULL ) && ( ( $the_time- strtotime( $login_attempt->login_time ) ) <= $sl_settings['timeout'] * MINUTE_IN_SECONDS ) && ( 0 == $login_attempt->login_status ) ) {

        if ( isset( $_POST['otp'] ) ) {
            $user_otp = sanitize_key( $_POST['otp'] );
            if ( $login_attempt->otp == $user_otp ) {
                $user_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
                if ( $user_ip == $login_attempt->user_ip ) {
                    $user = unserialize( $login_attempt->user_obj );

                    remove_filter( 'authenticate', 'sl_auth_login', 30 );

                    $creds = array();
                    $creds['user_login'] = 'example';
                    $creds['user_password'] = 'plaintextpw';
                    $creds['remember'] = false;

                    $logged_user = wp_signon( $creds, false );
                    if ( $logged_user ) {
                        $wpdb->update(
                            $table_name,
                            array(
                                'login_status' => 1
                            ),
                            array( 'auth_token' => $auth_token ),
                            array(
                                '%d'
                            ),
                            array( '%s' )
                        );

                        wp_redirect( get_bloginfo( "wpurl" ) . "/wp-admin/" );
                        exit;
                    }
                } else {
                    $wpdb->update(
                        $table_name,
                        array(
                            'login_status' => 4
                        ),
                        array( 'auth_token' => $auth_token ),
                        array(
                            '%d'
                        ),
                        array( '%s' )
                    );

                    $login_url = wp_login_url();
                    $redirect_to = add_query_arg( array('sl_error' => '402'), $login_url );
                    wp_redirect( $redirect_to );
                    exit;
                }
            } else {
                $sl_error = '<strong>ERROR</strong>: Incorrect OTP entered!';
            }
        }

        // Generate OTP and send
        if ( NULL == $login_attempt->otp ) {
            $user = unserialize( $login_attempt->user_obj );
            $otp = mt_rand(100000, 999999);

            $wpdb->update(
                $table_name,
                array(
                    'otp' => $otp
                ),
                array( 'auth_token' => $auth_token ),
                array(
                    '%d'
                ),
                array( '%s' )
            );

            // Edit the OTP email message here
            $message = "{$user->user_nicename}, \r\n\r\n";
            $message .= "Your One Time Pin is: {$otp}\r\n\r\n";
            $message .= "This pin is only valid for the next {$sl_settings['timeout']} minutes. \r\n\r\n";
            $message .= get_bloginfo('name');
            $headers = 'From: ' . get_bloginfo('name') . ' <' . $sl_settings['from_email'] . '>';

            function sl_otp_email( $user, $otp, $message, $headers ) {
                $mail_sent = wp_mail( $user->user_email, get_bloginfo('name') . ": One Time Pin", apply_filters( "sl_otp_message", $message ), apply_filters( "sl_otp_headers", $headers ) );
            }
            if ( ! has_action( "sl_otp_send" ) ) {
                add_action( "sl_otp_send", "sl_otp_email", 10, 4 );
            }

            do_action( "sl_otp_send", $user, $otp, $message, $headers );

        }
    } else {
        if ( 0 == $login_attempt->login_status ) {
            $wpdb->update(
                $table_name,
                array(
                    'login_status' => 3
                ),
                array( 'auth_token' => $auth_token ),
                array(
                    '%d'
                ),
                array( '%s' )
            );
        }

        $login_url = wp_login_url();
        $redirect_to = add_query_arg( array('sl_error' => '401'), $login_url );
        wp_redirect( $redirect_to );
        exit;
    }
}

 ?>
<!DOCTYPE html>
<!--[if IE 8]>
    <html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
    <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width; initial-scale=1.0">
<title><?php bloginfo('name'); ?> &rsaquo; Enter OTP Code</title>
<?php

wp_admin_css( 'login', true );

do_action( 'login_enqueue_scripts' );
?>
</head>
<body class="login login-action-login wp-core-ui  locale-en-us">
    <div id="login">
        <?php
        if ( isset( $sl_error ) ) { ?>
        <div id='login_error'><p><?php echo $sl_error; ?></p></div>
        <?php } else { ?>
        <div><p class='message'>Your One Time Pin has been sent to you.</p></div>
        <?php } ?>

        <form name="otpform" id="otpform" action="<?php echo get_bloginfo( 'wpurl' ) . '/verify-login/' . $auth_token . '/'; ?>" method="post">
            <p>
                <label for="user_otp">Enter One Time Pin<br />
                <input type="number" name="otp" id="user_otp" class="input" value="" size="20" /></label>
            </p>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Secure Log In" />
            </p>
        </form>

        <p id="nav">
            Haven't received your OTP? <a href="#">Resend OTP</a>
        </p>

        <script type="text/javascript">
        function wp_attempt_focus(){
        setTimeout( function(){ try{
        d = document.getElementById('user_otp');
        d.focus();
        d.select();
        } catch(e){}
        }, 200);
        }

        wp_attempt_focus();
        if(typeof wpOnload=='function')wpOnload();
        </script>

    </div>
    <div class="clear"></div>
</body>
</html>