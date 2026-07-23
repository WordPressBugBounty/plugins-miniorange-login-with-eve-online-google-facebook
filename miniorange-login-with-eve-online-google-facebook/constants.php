<?php
/**
 * Constants
 *
 * @package    constants
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'MO_OAUTH_PLUGIN_NAME' ) ) {
	define( 'MO_OAUTH_PLUGIN_NAME', 'OAuth Single Sign On' );
}
if ( ! defined( 'MO_OAUTH_README_PLUGIN_NAME' ) ) {
	define( 'MO_OAUTH_README_PLUGIN_NAME', 'OAuth Single Sign On - SSO (OAuth Client)' );
}
if ( ! defined( 'MO_OAUTH_README_PLUGIN_URI' ) ) {
	define( 'MO_OAUTH_README_PLUGIN_URI', 'miniorange-login-with-eve-online-google-facebook' );
}
if ( ! defined( 'MO_OAUTH_AREA_OF_INTEREST' ) ) {
	define( 'MO_OAUTH_AREA_OF_INTEREST', 'WP OAuth Client' );
}
if ( ! defined( 'MO_OAUTH_ADMIN_MENU' ) ) {
	define( 'MO_OAUTH_ADMIN_MENU', 'miniOrange OAuth' );
}
if ( ! defined( 'MO_OAUTH_PLUGIN_SLUG' ) ) {
	define( 'MO_OAUTH_PLUGIN_SLUG', 'miniorange-login-with-eve-online-google-facebook' );
}
if ( ! defined( 'MO_OAUTH_CLIENT_DEAL_DATE' ) ) {
	define( 'MO_OAUTH_CLIENT_DEAL_DATE', '2021-12-31 23:59:59' );
}
if ( ! defined( 'MO_OAUTH_CLIENT_PRICING_PLAN' ) ) {
	define( 'MO_OAUTH_CLIENT_PRICING_PLAN', 'https://plugins.miniorange.com/wordpress-sso#pricing' );
}
if ( ! defined( 'MO_OAUTH_HOSTNAME' ) ) {
	define( 'MO_OAUTH_HOSTNAME', 'https://login.xecurify.com' );
}
if ( ! defined( 'MO_OAUTH_CLIENT_DISCOUNT_URL' ) ) {
	if ( gmdate( 'Y-m-d H:i:s' ) <= MO_OAUTH_CLIENT_DEAL_DATE ) {
		define( 'MO_OAUTH_CLIENT_DISCOUNT_URL', '<p><font style="color:red; font-size:20px;"><a href="https://plugins.miniorange.com/wordpress-oauth-sso-end-of-the-year-deals" target="_blank"><u>CLICK HERE</u> </a> to know end of year deal</font></p>' );
	} else {
		define( 'MO_OAUTH_CLIENT_DISCOUNT_URL', '' );
	}
}

if ( ! function_exists( 'mo_oauth_get_log_dir' ) ) {
	/**
	 * Get the debug-log directory path.
	 *
	 * wp_upload_dir() runs filters and option reads, so it used to be called
	 * unconditionally at file-load time (i.e. on every single request) just to
	 * define a constant that only the debug logger ever reads, and only when a
	 * log line is actually being written or the weekly cleanup runs. Computing it
	 * lazily — memoized in a static so repeat calls within a request are free —
	 * means the cost is now paid only when logging is actually happening.
	 *
	 * @return string
	 */
	function mo_oauth_get_log_dir() {
		static $log_dir = null;

		if ( null === $log_dir ) {
			$mooauth_upload_dir = wp_upload_dir();
			$log_dir            = $mooauth_upload_dir['basedir'] . '/miniorange-login-with-eve-online-google-facebook';
		}

		return $log_dir;
	}
}
