<?php
/**
 * OAuth Handler
 *
 * @package    oauth-handler
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

/**
 * [Description Handle OAuth token, and resource_owner_info API]
 */
class MO_OAuth_Handler {

	/**
	 * Get Access Token
	 *
	 * @param mixed $tokenendpoint token endpoint of OAuth/OpendID provider.
	 * @param mixed $grant_type grant type of OAuth/OpendID provider.
	 * @param mixed $clientid client ID of OAuth/OpendID provider.
	 * @param mixed $clientsecret client secret of OAuth/OpendID provider.
	 * @param mixed $code authorization code obtained from OAuth/OpendID provider.
	 * @param mixed $redirect_url redirect URL configured in the plugin.
	 * @param mixed $send_headers sending information in Header of API request.
	 * @param mixed $send_body sending information in Body of API request.
	 *
	 * @return [string]
	 */
	public function get_access_token( $tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body ) {

		$response = $this->get_token( $tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body );
		$content  = json_decode( $response, true );

		if ( isset( $content['access_token'] ) ) {
			return $content['access_token'];
		} else {
			MOOAuth_Debug::mo_oauth_log( 'Token Response Received => ERROR : Invalid response received from OAuth Provider. Contact your administrator for more details. ' . esc_html( $response ) );
			echo 'Invalid response received from OAuth Provider. Contact your administrator for more details.<br><br><b>Response : </b><br>' . esc_html( $response );
			exit;
		}
	}

	/**
	 * Fetch Access Token
	 *
	 * @param mixed $tokenendpoint token endpoint of OAuth/OpendID provider.
	 * @param mixed $grant_type grant type of OAuth/OpendID provider.
	 * @param mixed $clientid client ID of OAuth/OpendID provider.
	 * @param mixed $clientsecret client secret of OAuth/OpendID provider.
	 * @param mixed $code authorization code obtained from OAuth/OpendID provider.
	 * @param mixed $redirect_url redirect URL configured in the plugin.
	 * @param mixed $send_headers sending information in Header of API request.
	 * @param mixed $send_body sending information in Body of API request.
	 * @return [string]
	 */
	public function get_token( $tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body ) {

		MOOAuth_Debug::mo_oauth_log( 'Token request content => ' );

		$clientsecret = html_entity_decode( $clientsecret );
		$body         = array(
			'grant_type'    => $grant_type,
			'code'          => $code,
			'client_id'     => $clientid,
			'client_secret' => $clientsecret,
			'redirect_uri'  => $redirect_url,
		);
		$headers      = array(
			'Accept'        => 'application/json',
			'charset'       => 'UTF - 8',
			'Authorization' => 'Basic ' . base64_encode( $clientid . ':' . $clientsecret ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encoding of client id and secret will be required for sending Authentication header in Token request.
			'Content-Type'  => 'application/x-www-form-urlencoded',
		);
		if ( $send_headers && ! $send_body ) {
				unset( $body['client_id'] );
				unset( $body['client_secret'] );
		} elseif ( ! $send_headers && $send_body ) {
				unset( $headers['Authorization'] );
		}
		MOOAuth_Debug::mo_oauth_log( 'Token Request Sent => ' . $tokenendpoint );
		MOOAuth_Debug::mo_oauth_log( 'body =>' );
		MOOAuth_Debug::mo_oauth_log( $body );
		MOOAuth_Debug::mo_oauth_log( 'headers =>' );
		MOOAuth_Debug::mo_oauth_log( $headers );

		$response = wp_remote_post(
			$tokenendpoint,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
				'body'        => $body,
				'cookies'     => array(),
				'sslverify'   => MO_OAuth_Utils::get_ssl_verify_setting( $tokenendpoint ),
			)
		);
		if ( is_wp_error( $response ) ) {
			MOOAuth_Debug::mo_oauth_log( 'Token Response Received => ERROR : Invalid response recieved while fetching token' );
			MOOAuth_Debug::mo_oauth_log( 'Invalid response recieved while fetching token' );
			MOOAuth_Debug::mo_oauth_log( $response );
			wp_die( $response ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $response is escaped before being passed in.
		}
		$response = $response['body'];
		MOOAuth_Debug::mo_oauth_log( 'Token Response Received => ' . $response );
		if ( ! is_array( json_decode( $response, true ) ) ) {
			echo '<b>Response : </b><br>' . esc_html( $response );
			echo '<br><br>';
			MOOAuth_Debug::mo_oauth_log( 'Invalid response received.' );
			if ( isset( $response['body'] ) ) {
				MOOAuth_Debug::mo_oauth_log( $response['body'] );
			}
			exit( 'Invalid response received.' );
		}

		$content = json_decode( $response, true );
		if ( isset( $content['error_description'] ) ) {
			MOOAuth_Debug::mo_oauth_log( 'Token Response Received => ERROR : ' . $content['error_description'] );
			exit( esc_html( $content['error_description'] ) );
		} elseif ( isset( $content['error'] ) ) {
			MOOAuth_Debug::mo_oauth_log( 'Token Response Received => ERROR : ' . $content['error'] );
			exit( esc_html( $content['error'] ) );
		}

		return $response;
	}

	/**
	 * Fetch ID token of OpenID provider.
	 *
	 * @param mixed $tokenendpoint token endpoint of OAuth/OpendID provider.
	 * @param mixed $grant_type grant type of OAuth/OpendID provider.
	 * @param mixed $clientid client ID of OAuth/OpendID provider.
	 * @param mixed $clientsecret client secret of OAuth/OpendID provider.
	 * @param mixed $code authorization code obtained from OAuth/OpendID provider.
	 * @param mixed $redirect_url redirect URL configured in the plugin.
	 * @param mixed $send_headers sending information in Header of API request.
	 * @param mixed $send_body sending information in Body of API request.
	 * @return [string]
	 */
	public function get_id_token( $tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body ) {
		MOOAuth_Debug::mo_oauth_log( 'Token Request Sent' );
		$response = $this->get_token( $tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body );
		$content  = json_decode( $response, true );
		if ( isset( $content['id_token'] ) || isset( $content['access_token'] ) ) {
			return $content;
		} else {
			MOOAuth_Debug::mo_oauth_log( 'Token Response Received => ERROR : Invalid response received from OpenId Provider. Contact your administrator for more details. Response : ' . esc_html( $response ) );
			echo 'Invalid response received from OpenId Provider. Contact your administrator for more details.<br><br><b>Response : </b><br>' . esc_html( $response );
			exit;
		}
	}

	/**
	 * Get user information from id_token obtained from OpenID provider.
	 *
	 * @param mixed  $id_token id_token obtained from OpenID provider.
	 * @param string $discovery_url OIDC discovery document URL configured for this app, if any. Enables RS256 signature verification against the provider's JWKS when present.
	 * @param string $client_id OAuth client ID configured for this app, used to validate the 'aud' claim when present in the token.
	 * @param string $expected_nonce Nonce value generated and stored when the authorization request was sent, if any. Validated against the token's 'nonce' claim to block replay of a captured ID token.
	 * @return [array]
	 */
	public function get_resource_owner_from_id_token( $id_token, $discovery_url = '', $client_id = '', $expected_nonce = '' ) {
		$id_array = explode( '.', $id_token );
		if ( isset( $id_array[1] ) ) {
			$id_body = base64_decode( str_pad( strtr( $id_array[1], '-_', '+/' ), strlen( $id_array[1] ) % 4, '=', STR_PAD_RIGHT ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- base64 will be required for getting contents from JWT token.
			$claims  = json_decode( $id_body, true );
			if ( is_array( $claims ) ) {
				$this->validate_id_token_claims( $claims, $client_id, $discovery_url, $expected_nonce );
				$this->verify_id_token_signature( $id_token, $discovery_url );
				return $claims;
			}
		}
		MOOAuth_Debug::mo_oauth_log( 'Invalid response received while fetching Id token from the Resource Owner. Id_token : ' . esc_html( $id_token ) );
		echo 'Invalid response received.<br><b>Id_token : </b>' . esc_html( $id_token );
		exit;
	}

	/**
	 * Validate the exp/aud/iss claims on a decoded ID token payload.
	 *
	 * exp/aud: only rejects the login (wp_die) when the claim is present and
	 * demonstrably invalid; a missing claim is not treated as a failure since many
	 * non-standard OAuth providers supported by this plugin omit them.
	 *
	 * iss: only checked when a discovery URL is configured for this app (i.e. this
	 * is a genuine OIDC provider we can fetch an expected issuer for). Per the OIDC
	 * spec 'iss' is a REQUIRED claim, so in that case a token missing 'iss'
	 * entirely, or asserting a different issuer than the configured provider's
	 * discovery document, is rejected — this catches an ID token signed by a
	 * different (but key-compatible) issuer than the one this app is configured
	 * to trust. Apps without a discovery URL configured (custom/non-OIDC
	 * providers) have no expected issuer to compare against and are unaffected.
	 *
	 * @param array  $claims Decoded ID token claims.
	 * @param string $client_id Configured OAuth client ID for this app.
	 * @param string $discovery_url OIDC discovery document URL configured for this app, if any.
	 * @param string $expected_nonce Nonce value generated when the authorization request was sent, if any.
	 *
	 * nonce: only checked when $expected_nonce is non-empty, i.e. this login attempt actually
	 * generated and sent a nonce (OpenID Connect apps only — see mooauth_login_validate()). When
	 * present, the token's 'nonce' claim MUST exist and match exactly; a missing or mismatched
	 * claim means the ID token was not issued in response to this specific authorization request
	 * (e.g. a captured/replayed token), and the login is rejected.
	 */
	private function validate_id_token_claims( $claims, $client_id, $discovery_url = '', $expected_nonce = '' ) {
		if ( isset( $claims['exp'] ) && is_numeric( $claims['exp'] ) && time() > (int) $claims['exp'] ) {
			MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token has expired (exp claim is in the past).' );
			wp_die( esc_html__( 'Authentication failed: the ID token has expired. Please try logging in again.', 'miniorange-login-with-eve-online-google-facebook' ) );
		}

		if ( ! empty( $client_id ) && isset( $claims['aud'] ) ) {
			$audiences = is_array( $claims['aud'] ) ? $claims['aud'] : array( $claims['aud'] );
			if ( ! in_array( $client_id, $audiences, true ) ) {
				MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token aud claim does not match the configured client ID.' );
				wp_die( esc_html__( 'Authentication failed: the ID token was not issued for this application.', 'miniorange-login-with-eve-online-google-facebook' ) );
			}
		}

		if ( ! empty( $expected_nonce ) ) {
			if ( empty( $claims['nonce'] ) ) {
				MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token is missing the required nonce claim.' );
				wp_die( esc_html__( 'Authentication failed: the ID token is missing the nonce claim.', 'miniorange-login-with-eve-online-google-facebook' ) );
			}
			if ( ! hash_equals( $expected_nonce, (string) $claims['nonce'] ) ) {
				MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token nonce claim does not match the value sent in the authorization request.' );
				wp_die( esc_html__( 'Authentication failed: the ID token nonce does not match this login attempt.', 'miniorange-login-with-eve-online-google-facebook' ) );
			}
		}

		if ( ! empty( $discovery_url ) ) {
			$discovery_doc     = $this->get_discovery_document( $discovery_url );
			$expected_issuer   = isset( $discovery_doc['issuer'] ) ? $discovery_doc['issuer'] : '';
			if ( ! empty( $expected_issuer ) ) {
				if ( empty( $claims['iss'] ) ) {
					MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token is missing the required iss claim.' );
					wp_die( esc_html__( 'Authentication failed: the ID token is missing the issuer claim.', 'miniorange-login-with-eve-online-google-facebook' ) );
				}
				if ( untrailingslashit( $claims['iss'] ) !== untrailingslashit( $expected_issuer ) ) {
					MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token iss claim (' . $claims['iss'] . ') does not match the configured provider (' . $expected_issuer . ').' );
					wp_die( esc_html__( 'Authentication failed: the ID token was not issued by the configured provider.', 'miniorange-login-with-eve-online-google-facebook' ) );
				}
			}
		}
	}

	/**
	 * Best-effort RS256 signature verification of an ID token against the
	 * provider's JWKS, when an OIDC discovery URL is configured for the app.
	 *
	 * If verification cannot be performed for any reason (no discovery URL
	 * configured, unsupported/missing alg, JWKS unreachable, no matching key)
	 * this silently no-ops so existing configurations keep working exactly as
	 * before. If verification IS performed and the signature does not match,
	 * the login is rejected.
	 *
	 * @param string $id_token Raw JWT id_token.
	 * @param string $discovery_url OIDC discovery document URL configured for this app.
	 */
	private function verify_id_token_signature( $id_token, $discovery_url ) {
		if ( empty( $discovery_url ) ) {
			return;
		}

		$parts = explode( '.', $id_token );
		if ( count( $parts ) !== 3 ) {
			return;
		}

		$header = json_decode( $this->base64url_decode( $parts[0] ), true );
		if ( ! is_array( $header ) || empty( $header['alg'] ) || 'RS256' !== $header['alg'] ) {
			MOOAuth_Debug::mo_oauth_log( 'ID token signature verification skipped: unsupported or missing alg.' );
			return;
		}

		$jwks = $this->get_jwks( $discovery_url );
		if ( empty( $jwks['keys'] ) ) {
			MOOAuth_Debug::mo_oauth_log( 'ID token signature verification skipped: JWKS unavailable.' );
			return;
		}

		$kid         = isset( $header['kid'] ) ? $header['kid'] : null;
		$matched_key = null;
		foreach ( $jwks['keys'] as $key ) {
			if ( null === $kid || ( isset( $key['kid'] ) && $key['kid'] === $kid ) ) {
				$matched_key = $key;
				break;
			}
		}

		if ( null === $matched_key || ! isset( $matched_key['n'], $matched_key['e'] ) ) {
			MOOAuth_Debug::mo_oauth_log( 'ID token signature verification skipped: no matching JWKS key found for kid.' );
			return;
		}

		$pem           = $this->jwk_rsa_to_pem( $matched_key['n'], $matched_key['e'] );
		$signing_input = $parts[0] . '.' . $parts[1];
		$signature     = $this->base64url_decode( $parts[2] );

		if ( 1 !== openssl_verify( $signing_input, $signature, $pem, OPENSSL_ALGO_SHA256 ) ) {
			MOOAuth_Debug::mo_oauth_log( 'ERROR : ID token signature verification FAILED against provider JWKS.' );
			wp_die( esc_html__( 'Authentication failed: the ID token signature could not be verified.', 'miniorange-login-with-eve-online-google-facebook' ) );
		}

		MOOAuth_Debug::mo_oauth_log( 'ID token signature verified successfully against provider JWKS.' );
	}

	/**
	 * Fetch (and cache for an hour) an OIDC discovery document.
	 *
	 * Shared by signature verification (jwks_uri) and iss validation (issuer), so
	 * both draw from the same cached fetch instead of hitting the discovery URL
	 * twice.
	 *
	 * @param string $discovery_url OIDC discovery document URL.
	 * @return array|null
	 */
	private function get_discovery_document( $discovery_url ) {
		$cache_key = 'mo_oauth_discovery_' . md5( $discovery_url );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$discovery_response = wp_remote_get(
			$discovery_url,
			array(
				'timeout'   => 15,
				'sslverify' => MO_OAuth_Utils::get_ssl_verify_setting( $discovery_url ),
			)
		);
		if ( is_wp_error( $discovery_response ) ) {
			return null;
		}
		$discovery_doc = json_decode( wp_remote_retrieve_body( $discovery_response ), true );
		if ( ! is_array( $discovery_doc ) || empty( $discovery_doc ) ) {
			return null;
		}

		set_transient( $cache_key, $discovery_doc, HOUR_IN_SECONDS );
		return $discovery_doc;
	}

	/**
	 * Fetch (and cache for an hour) the JSON Web Key Set advertised by an OIDC discovery document.
	 *
	 * @param string $discovery_url OIDC discovery document URL.
	 * @return array|null
	 */
	private function get_jwks( $discovery_url ) {
		$cache_key = 'mo_oauth_jwks_' . md5( $discovery_url );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$discovery_doc = $this->get_discovery_document( $discovery_url );
		if ( empty( $discovery_doc['jwks_uri'] ) ) {
			return null;
		}

		$jwks_response = wp_remote_get(
			$discovery_doc['jwks_uri'],
			array(
				'timeout'   => 15,
				'sslverify' => MO_OAuth_Utils::get_ssl_verify_setting( $discovery_doc['jwks_uri'] ),
			)
		);
		if ( is_wp_error( $jwks_response ) ) {
			return null;
		}
		$jwks = json_decode( wp_remote_retrieve_body( $jwks_response ), true );
		if ( empty( $jwks['keys'] ) ) {
			return null;
		}

		set_transient( $cache_key, $jwks, HOUR_IN_SECONDS );
		return $jwks;
	}

	/**
	 * Decode a base64url-encoded string (JWT segments use this variant, not plain base64).
	 *
	 * @param string $data base64url-encoded input.
	 * @return string
	 */
	private function base64url_decode( $data ) {
		return base64_decode( strtr( $data, '-_', '+/' ) . str_repeat( '=', ( 4 - strlen( $data ) % 4 ) % 4 ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- base64url decoding is required for JWT/JWK handling.
	}

	/**
	 * Build a PEM-encoded RSA public key (SubjectPublicKeyInfo/X.509) from a JWK's modulus (n) and exponent (e).
	 *
	 * @param string $n_b64url base64url-encoded RSA modulus.
	 * @param string $e_b64url base64url-encoded RSA public exponent.
	 * @return string PEM-encoded public key.
	 */
	private function jwk_rsa_to_pem( $n_b64url, $e_b64url ) {
		$modulus  = $this->base64url_decode( $n_b64url );
		$exponent = $this->base64url_decode( $e_b64url );

		$modulus_der  = $this->der_integer( $modulus );
		$exponent_der = $this->der_integer( $exponent );
		$rsa_pubkey   = "\x30" . $this->der_length( strlen( $modulus_der ) + strlen( $exponent_der ) ) . $modulus_der . $exponent_der;

		$rsa_pubkey_bitstring = "\x00" . $rsa_pubkey;
		$bitstring_der        = "\x03" . $this->der_length( strlen( $rsa_pubkey_bitstring ) ) . $rsa_pubkey_bitstring;

		// AlgorithmIdentifier for rsaEncryption (OID 1.2.840.113549.1.1.1) with NULL params.
		$alg_id = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
		$spki   = "\x30" . $this->der_length( strlen( $alg_id ) + strlen( $bitstring_der ) ) . $alg_id . $bitstring_der;

		return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----\n"; //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- PEM encoding requires base64.
	}

	/**
	 * DER-encode a length value (definite form, short or long).
	 *
	 * @param int $len Length to encode.
	 * @return string
	 */
	private function der_length( $len ) {
		if ( $len <= 0x7f ) {
			return chr( $len );
		}
		$bytes = ltrim( pack( 'N', $len ), "\x00" );
		return chr( 0x80 | strlen( $bytes ) ) . $bytes;
	}

	/**
	 * DER-encode a big-endian unsigned integer as an ASN.1 INTEGER.
	 *
	 * @param string $bin Raw big-endian unsigned integer bytes.
	 * @return string
	 */
	private function der_integer( $bin ) {
		$bin = ltrim( $bin, "\x00" );
		if ( '' === $bin ) {
			$bin = "\x00";
		}
		if ( ord( $bin[0] ) > 0x7f ) {
			$bin = "\x00" . $bin;
		}
		return "\x02" . $this->der_length( strlen( $bin ) ) . $bin;
	}

	/**
	 * Get user information from id_token obtained from OAuth/OpenID provider
	 *
	 * @param mixed $resourceownerdetailsurl endpoint to fetch user information from OAuth/OpenID provider.
	 * @param mixed $access_token access token obtained from OAuth/OpenID provider.
	 * @return [array|string]
	 */
	public function get_resource_owner( $resourceownerdetailsurl, $access_token ) {
		$headers                  = array();
		$headers['Authorization'] = 'Bearer ' . $access_token;

		MOOAuth_Debug::mo_oauth_log( 'Resource Owner request content => ' );
		MOOAuth_Debug::mo_oauth_log( 'headers =>' );
		MOOAuth_Debug::mo_oauth_log( $headers );
		MOOAuth_Debug::mo_oauth_log( 'Resource Owner Endpoint: ' . $resourceownerdetailsurl );

		$response = wp_remote_get(
			$resourceownerdetailsurl,
			array(
				'method'      => 'GET',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
				'cookies'     => array(),
				'sslverify'   => MO_OAuth_Utils::get_ssl_verify_setting( $resourceownerdetailsurl ),
			)
		);

		if ( is_wp_error( $response ) ) {
			MOOAuth_Debug::mo_oauth_log( 'Invalid response recieved while fetching resource owner details' );
			MOOAuth_Debug::mo_oauth_log( $response->get_error_message() );
			wp_die( esc_html( $response->get_error_message() ) );
		}

		$response = $response['body'];

		if ( ! is_array( json_decode( $response, true ) ) ) {
			$response = addcslashes( $response, '\\' );
			if ( ! is_array( json_decode( $response, true ) ) ) {
				echo '<b>Response : </b><br>' . esc_html( $response );
				echo '<br><br>';
				MOOAuth_Debug::mo_oauth_log( 'Invalid response received.' );
				exit( 'Invalid response received.' );
			}
		}

		$content = json_decode( $response, true );
		if ( isset( $content['error_description'] ) ) {
			MOOAuth_Debug::mo_oauth_log( $content['error_description'] );
			exit( esc_html( $content['error_description'] ) );
		} elseif ( isset( $content['error'] ) ) {
			MOOAuth_Debug::mo_oauth_log( $content['error'] );
			exit( esc_html( $content['error'] ) );
		}
		return $content;
	}
}
