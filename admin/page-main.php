<?php
global $wpdb;
$table_name = $wpdb->prefix . "securelogins";
$logins = $wpdb->get_results(
    "
    SELECT *
    FROM $table_name
    "
);

?>

<form id="logins-filter" action="" method="get">
    <table class="wp-list-table widefat fixed pages">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-text" style=""><span>User</span></th>
                <th scope="col" class="manage-column column-date" style="">Login Time</th>
                <th scope="col" class="manage-column column-date" style=""><span>IP Address</span></th>
                <th scope="col" class="manage-column column-text" style=""><span>Login Status</span></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-text" style=""><span>User</span></th>
                <th scope="col" class="manage-column column-date" style="">Login Time</th>
                <th scope="col" class="manage-column column-text" style=""><span>IP Address</span></th>
                <th scope="col" class="manage-column column-text" style=""><span>Login Status</span></th>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php
            foreach ( $logins as $login ) {
                $user_info = get_userdata( $login->user_id );
                switch ($login->login_status) {
                    case 0:
                        $login_status = "<span class='sl-status-default'>Not logged in</span>";
                        break;
                    case 1:
                        $login_status = "<span class='sl-status-success'>Logged in</span>";
                        break;
                    case 2:
                        $login_status = "<span class='sl-status-failed'>OTP Failed</span>";
                        break;
                    case 3:
                        $login_status = "<span class='sl-status-warning'>Timed out</span>";
                        break;
                    case 4:
                        $login_status = "<span class='sl-status-failed'>IP match failed</span>";
                        break;
                }
            ?>
            <tr class="alternate">
                <td><strong><?php echo $user_info->user_login; ?></strong> (<?php echo $user_info->user_email; ?>)</td>
                <td><?php echo $login->login_time; ?></td>
                <td><?php echo $login->user_ip; ?></td>
                <td><?php echo $login_status; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</form>