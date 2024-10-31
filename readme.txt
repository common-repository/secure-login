=== Secure Login ===
Contributors: d4v1d
Donate link: http://rockingthemes.com/donate
Tags: secure, login, 2-step, verification, safety, otp
Author URI: http://davidleonard.co.za
Plugin URI: http://rockingthemes.com/wordpress-plugins/secure-login
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Secure, 2 step Verification for WordPress login, via One Time Pin (OTP).

== Description ==

Secure your WordPress site with WordPress Secure Login.

WordPress Secure Login provides 2-step verification on login. Once a user submits their login credentials, a One Time Pin (OTP) is emailed to them. They need to enter this OTP in order to continue to login.

Stop Brute force hacking attempts, and keep your data safe!

    * Easy to install!
    * Easy to replace the Email system with an SMS Gateway
    * WordPress 4.0 Ready!

== Installation ==

Copy the "secure-login" folder to your WordPress Plugins directory via FTP. The WordPress Plugins directory is situated in "/wp-content/plugins/"

Once the "secure-login" folder has been copied to the WordPress Plugins directory, log in to your WordPress admin area (http://www.yourdomain.com/wp-admin).

Click on the "Plugins" menu item. You will be redirected to the Plugins page. Locate the "Secure Login" plugin, and click activate.

**Note:** In order to use this plugin, you will need a valid WordPress install. This plugin will not work on a wordpress.com hosted site, and has not been tested on a Multi Site install.

In order for this plugin to function correctly, you will need to use a permalink structure that uses rewrite rules. The "Post name" structure is recommended.

For accurate login time tracking, make sure your correct Timezone is selected under "Settings" > "General".

== Changelog ==

= 1.0.4 =
* Fixed: Rewrite Rules Flush

= 1.0.3 =
* Added: Action hook on OTP Send
* Added: Filter hooks on OTP message and email headers

= 1.0.2 =
* Added: Rewrite Rules Flush on activation.

= 1.0.1 =
* Added: Responsive Web Design Support.
