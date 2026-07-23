<?php
/**
 * OAuth Utilities
 *
 * @package    oauth-utils
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

/**
 * Utility class for OAuth operations including SSL certificate validation
 */
class MO_OAuth_Utils {

	/**
	 * Get the decoded default-apps catalogue (defaultapps.json, ~49 KB).
	 *
	 * The file used to be re-read and re-JSON-decoded at every call site — several
	 * times per admin render. The decoded object is cached in a static for the
	 * current request and in the object cache (persistent when a drop-in such as
	 * Redis/Memcached is installed). The cache key embeds the plugin asset version
	 * so plugin updates naturally invalidate it.
	 *
	 * @return object|null Catalogue keyed by app ID, or null if the file is unreadable.
	 */
	public static function get_default_apps() {
		static $apps = null;

		if ( null !== $apps ) {
			return $apps;
		}

		$version   = defined( 'MO_OAUTH_CSS_JS_VERSION' ) ? MO_OAUTH_CSS_JS_VERSION : '0';
		$cache_key = 'mo_oauth_default_apps_' . $version;
		$cached    = wp_cache_get( $cache_key, 'mo_oauth' );

		if ( false !== $cached ) {
			$apps = $cached;
			return $apps;
		}

		$apps = wp_json_file_decode( plugin_dir_path( __FILE__ ) . 'admin' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'defaultapps.json' );
		wp_cache_set( $cache_key, $apps, 'mo_oauth', HOUR_IN_SECONDS );

		return $apps;
	}

	/**
	 * Get SSL verification setting for wp_remote requests.
	 *
	 * Always verifies SSL for real external hosts (letting wp_remote_get's own
	 * certificate validation run as normal) — the only exception is a narrow
	 * carve-out for localhost/loopback targets, a common local-development
	 * convenience where self-signed certificates are the norm.
	 *
	 * This used to determine the verdict by checking the *local WordPress site's*
	 * own certificate rather than the target $url's host, and silently disabled
	 * verification for every outbound call whenever that unrelated check failed —
	 * a TLS-downgrade risk. Now it looks at $url itself and defaults to requiring
	 * verification, matching how sslverify should behave for a remote IdP endpoint.
	 *
	 * @param string $url The URL about to be requested via wp_remote_get/post.
	 * @return bool Whether SSL verification should be enabled for this request.
	 */
	public static function get_ssl_verify_setting( $url ) {
		$parsed_url = wp_parse_url( $url );
		$host       = isset( $parsed_url['host'] ) ? trim( $parsed_url['host'], '[]' ) : ''; // parse_url() keeps the brackets around an IPv6 literal host, e.g. "[::1]".

		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			if ( class_exists( 'MOOAuth_Debug' ) ) {
				MOOAuth_Debug::mo_oauth_log( 'SSL Verify Setting: FALSE - localhost/loopback target: ' . $url );
			}
			return false;
		}

		return true;
	}
}
