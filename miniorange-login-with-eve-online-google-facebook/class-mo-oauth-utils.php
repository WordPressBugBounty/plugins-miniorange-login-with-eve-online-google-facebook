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
	 * Check if SSL certificate is valid for a domain
	 *
	 * @param string $domain The domain to validate SSL for.
	 * @return bool True if SSL is valid, false otherwise.
	 */
	public static function check_ssl_validity( $domain ) {
		$domain       = preg_replace( '#^https?://#', '', $domain );
		$domain       = explode( '/', $domain )[0];
		$domain_parts = explode( ':', $domain );
		$host         = $domain_parts[0];
		$port         = isset( $domain_parts[1] ) ? $domain_parts[1] : '443';

		if ( 'localhost' === $host || '127.0.0.1' === $host || '::1' === $host ) {
			if ( class_exists( 'MOOAuth_Debug' ) ) {
				MOOAuth_Debug::mo_oauth_log( 'SSL Certificate Check: SKIPPED for localhost domain: ' . $host . ' - SSL verification disabled' );
			}
			return false;
		}

		$context_options = array(
			'ssl' => array(
				'capture_peer_cert' => true,
				'verify_peer'       => true,
				'verify_peer_name'  => true,
				'allow_self_signed' => false,
			),
		);

		$context = stream_context_create( $context_options );
		$client  = @stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno,
			$errstr,
			10,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( false === $client ) {
			return false;
		}

		$params = stream_context_get_params( $client );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			fclose( $client ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			return false;
		}

		$cert       = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );
		$valid_to   = $cert['validTo_time_t'];
		$valid_from = $cert['validFrom_time_t'];
		$is_valid   = time() >= $valid_from && time() < $valid_to;

		fclose( $client ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		if ( class_exists( 'MOOAuth_Debug' ) ) {
			$status = $is_valid ? 'VALID' : 'INVALID';
			$expiry = gmdate( 'Y-m-d H:i:s', $valid_to );
			MOOAuth_Debug::mo_oauth_log( 'SSL Certificate Check: ' . $status . ' for WordPress domain: ' . $domain . ' (Expires: ' . $expiry . ')' );
		}

		return $is_valid;
	}

	/**
	 * Get SSL verification setting for wp_remote requests
	 *
	 * @param string $url The URL to check SSL for.
	 * @return bool Whether SSL verification should be enabled.
	 */
	public static function get_ssl_verify_setting( $url ) {
		$site_url        = site_url();
		$parsed_site_url = wp_parse_url( $site_url );

		if ( ! $parsed_site_url || ! isset( $parsed_site_url['host'] ) ) {
			if ( class_exists( 'MOOAuth_Debug' ) ) {
				MOOAuth_Debug::mo_oauth_log( 'SSL Verify Setting: TRUE (default) - Unable to parse WordPress site URL: ' . $site_url );
			}
			return true;
		}

		$ssl_valid = self::check_ssl_validity( $parsed_site_url['host'] );

		if ( class_exists( 'MOOAuth_Debug' ) ) {
			$setting = $ssl_valid ? 'TRUE' : 'FALSE';
			MOOAuth_Debug::mo_oauth_log( 'SSL Verify Setting: ' . $setting . ' for WordPress domain: ' . $parsed_site_url['host'] . ' (Request URL: ' . $url . ')' );
		}

		return $ssl_valid;
	}
}
