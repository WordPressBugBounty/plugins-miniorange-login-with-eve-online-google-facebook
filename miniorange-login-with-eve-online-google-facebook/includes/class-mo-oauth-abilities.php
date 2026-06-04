<?php
/**
 * WordPress Abilities API integration for the miniOrange OAuth Client plugin.
 *
 * Registers 8 abilities under the `mo-oauth-client/` namespace so AI agents
 * (and any Abilities API consumer) can manage SSO, diagnose errors, and
 * submit support queries via wp-json/wp-abilities/v1/.
 *
 * Registration is gated by the `mo_oauth_enable_abilities_api` option, which
 * must be set to the string 'true' before any ability is exposed. The toggle
 * lives in the Troubleshooting tab of the plugin settings.
 *
 * @package miniOrange-oauth-client
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MO_OAuth_Abilities' ) ) {

	/**
	 * Registers OAuth Client abilities.
	 */
	class MO_OAuth_Abilities {

		/**
		 * Required capability for every ability registered by this class.
		 */
		const CAP = 'manage_options';

		/**
		 * Option key for the master enable toggle.
		 */
		const ENABLE_OPTION = 'mo_oauth_enable_abilities_api';

		/**
		 * Hook in only when the customer has opted into the Abilities API.
		 */
		public function __construct() {
			if ( 'true' !== get_option( self::ENABLE_OPTION ) ) {
				return;
			}
			add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
			add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
		}

		/**
		 * Register category groupings.
		 */
		public function register_categories() {
			if ( ! function_exists( 'wp_register_ability_category' ) ) {
				return;
			}
			wp_register_ability_category(
				'mo-oauth-configuration',
				array(
					'label'       => __( 'OAuth Configuration', 'miniorange-login-with-eve-online-google-facebook' ),
					'description' => __( 'Toggle and update miniOrange OAuth Client configuration.', 'miniorange-login-with-eve-online-google-facebook' ),
				)
			);
			wp_register_ability_category(
				'mo-oauth-troubleshooting',
				array(
					'label'       => __( 'OAuth Troubleshooting', 'miniorange-login-with-eve-online-google-facebook' ),
					'description' => __( 'Automated fixes for common miniOrange OAuth Client problems.', 'miniorange-login-with-eve-online-google-facebook' ),
				)
			);
			wp_register_ability_category(
				'mo-oauth-support',
				array(
					'label'       => __( 'OAuth Support', 'miniorange-login-with-eve-online-google-facebook' ),
					'description' => __( 'Reach the miniOrange team and find IdP setup guides.', 'miniorange-login-with-eve-online-google-facebook' ),
				)
			);
		}

		/**
		 * Register all abilities. Each ability has its own private registration
		 * method to keep the schema for one ability isolated and easy to change.
		 */
		public function register_abilities() {
			if ( ! function_exists( 'wp_register_ability' ) ) {
				return;
			}

			$this->register_send_support_query();
			$this->register_toggle_login_button();
			$this->register_get_idp_guide_links();
			$this->register_toggle_admin_sso();
			$this->register_enable_admin_sso_fix();
			$this->register_auto_map_attribute();
			$this->register_toggle_plugin_debug();
			$this->register_update_endpoints_via_discovery();
		}

		/**
		 * Register: send-support-query.
		 */
		private function register_send_support_query() {
			wp_register_ability(
				'mo-oauth-client/send-support-query',
				array(
					'label'               => __( 'Send a support query to miniOrange', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Submits a support request (and optional plugin config) to the miniOrange support team using the plugin\'s existing Contact Us channel.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-support',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'email'       => array(
								'type'        => 'string',
								'format'      => 'email',
								'description' => 'Reply-to email address.',
							),
							'phone'       => array(
								'type'        => 'string',
								'description' => 'Optional phone number.',
							),
							'query'       => array(
								'type'        => 'string',
								'description' => 'The support question or issue description.',
							),
							'send_config' => array(
								'type'        => 'boolean',
								'description' => 'Whether to attach plugin configuration to the support ticket.',
								'default'     => false,
							),
						),
						'required'             => array( 'email', 'query' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success' => array( 'type' => 'boolean' ),
							'message' => array( 'type' => 'string' ),
						),
						'required'   => array( 'success', 'message' ),
					),
					'execute_callback'    => array( $this, 'execute_send_support_query' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, true ),
				)
			);
		}

		/**
		 * Register: toggle-login-page-sso-button.
		 */
		private function register_toggle_login_button() {
			wp_register_ability(
				'mo-oauth-client/toggle-login-page-sso-button',
				array(
					'label'               => __( 'Toggle the SSO button on the WP login page', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Shows or hides the SSO login button on the wp-login.php page for a given OAuth app.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-configuration',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'app_name' => array(
								'type'        => 'string',
								'description' => 'Name of the OAuth app to update. Defaults to the first configured app.',
							),
							'enable'   => array(
								'type'        => 'boolean',
								'description' => 'True to show the button, false to hide it.',
							),
						),
						'required'             => array( 'enable' ),
						'additionalProperties' => false,
					),
					'output_schema'       => $this->toggle_output_schema(),
					'execute_callback'    => array( $this, 'execute_toggle_login_button' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, true ),
				)
			);
		}

		/**
		 * Register: get-idp-guide-links.
		 */
		private function register_get_idp_guide_links() {
			wp_register_ability(
				'mo-oauth-client/get-idp-guide-links',
				array(
					'label'               => __( 'Get IdP setup guide links', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Returns documentation URLs for common identity providers (Azure AD, Google, Okta, etc.).', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-support',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'idp_name' => array(
								'type'        => 'string',
								'description' => 'Optional filter (case-insensitive). If omitted, all guides are returned.',
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'guides' => array(
								'type'                 => 'object',
								'additionalProperties' => array( 'type' => 'string' ),
							),
						),
						'required'   => array( 'guides' ),
					),
					'execute_callback'    => array( $this, 'execute_get_idp_guide_links' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( true, false, true ),
				)
			);
		}

		/**
		 * Register: toggle-admin-sso-login.
		 */
		private function register_toggle_admin_sso() {
			wp_register_ability(
				'mo-oauth-client/toggle-admin-sso-login',
				array(
					'label'               => __( 'Toggle Admin SSO login', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Enables or disables SSO login for WordPress administrators on a given OAuth app.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-configuration',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'app_name' => array(
								'type'        => 'string',
								'description' => 'Name of the OAuth app to update. Defaults to the first configured app.',
							),
							'enable'   => array(
								'type'        => 'boolean',
								'description' => 'True to allow admin SSO, false to block it.',
							),
						),
						'required'             => array( 'enable' ),
						'additionalProperties' => false,
					),
					'output_schema'       => $this->toggle_output_schema(),
					'execute_callback'    => array( $this, 'execute_toggle_admin_sso' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, true ),
				)
			);
		}

		/**
		 * Register: enable-admin-sso-fix (WPO004).
		 */
		private function register_enable_admin_sso_fix() {
			wp_register_ability(
				'mo-oauth-client/enable-admin-sso-fix',
				array(
					'label'               => __( 'Fix WPO004 — enable Admin SSO', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Fixes error WPO004 ("Invalid Login attempt. Please login using email and password.") shown when an administrator tries to log in via SSO while admin SSO is disabled on the app. When an admin mentions WPO004, call this ability — it sets allow_admin_sso=1 on the target OAuth app so admin-role users can authenticate via the IdP. Safe to call repeatedly.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-troubleshooting',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'app_name' => array(
								'type'        => 'string',
								'description' => 'Name of the OAuth app. Defaults to the first configured app.',
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => $this->toggle_output_schema(),
					'execute_callback'    => array( $this, 'execute_enable_admin_sso_fix' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, true ),
				)
			);
		}

		/**
		 * Register: auto-map-attribute-from-test-config (WPO005 / attribute mapping).
		 */
		private function register_auto_map_attribute() {
			wp_register_ability(
				'mo-oauth-client/auto-map-attribute-from-test-config',
				array(
					'label'               => __( 'Fix WPO005 — auto-map email / username attribute from Test Configuration', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Fixes WPO005 and the related "Username not received. Check your Attribute Mapping configuration." error that appears when SSO login fails because the plugin cannot find a username or email field in the IdP response. Pass attribute_type to control which mapping to write: "email" when the admin mentions WPO005, "email not received", or "map email attribute"; "username" when the admin mentions "map username", "username attribute"; "both" when the admin mentions "username not received", "attribute mapping", "set up attribute mapping", "do attribute mapping", or "map all attributes" (this is the safest default for ambiguous reports because "Username not received" can be cleared by mapping either field). Reads the cached IdP response from the last Test Configuration run, picks the most likely field per attribute, and saves it on the target OAuth app. Requires the admin to have clicked Test Configuration at least once first; returns a fallback message if no Test Configuration data is cached or no matching field is present.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-troubleshooting',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'attribute_type' => array(
								'type'        => 'string',
								'enum'        => array( 'email', 'username', 'both' ),
								'default'     => 'both',
								'description' => 'Which attribute(s) to auto-map. Use "email" for email-only requests, "username" for username-only requests, and "both" (default) when the admin says "attribute mapping" generally or reports "Username not received" (since the plugin falls back to the email field for the username when only email is mapped).',
							),
							'app_name'       => array(
								'type'        => 'string',
								'description' => 'Name of the OAuth app. Defaults to the first configured app.',
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success'        => array( 'type' => 'boolean' ),
							'message'        => array( 'type' => 'string' ),
							'attribute_type' => array( 'type' => 'string' ),
							'mappings'       => array(
								'type'                 => 'object',
								'description'          => 'Per-attribute mapping result keyed by "email" and/or "username".',
								'additionalProperties' => array(
									'type'       => 'object',
									'properties' => array(
										'matched_key'  => array( 'type' => 'string' ),
										'sample_value' => array( 'type' => 'string' ),
									),
								),
							),
						),
						'required'   => array( 'success', 'message' ),
					),
					'execute_callback'    => array( $this, 'execute_auto_map_attribute' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, true ),
				)
			);
		}

		/**
		 * Register: toggle-plugin-debug.
		 */
		private function register_toggle_plugin_debug() {
			wp_register_ability(
				'mo-oauth-client/toggle-plugin-debug',
				array(
					'label'               => __( 'Toggle plugin debug logging', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Enables or disables miniOrange OAuth Client debug logging. Mirrors the in-admin debug toggle exactly.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-configuration',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'enable' => array(
								'type'        => 'boolean',
								'description' => 'True to enable debug logging, false to disable and remove the log.',
							),
						),
						'required'             => array( 'enable' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success'       => array( 'type' => 'boolean' ),
							'debug_enabled' => array( 'type' => 'boolean' ),
							'message'       => array( 'type' => 'string' ),
						),
						'required'   => array( 'success', 'debug_enabled', 'message' ),
					),
					'execute_callback'    => array( $this, 'execute_toggle_debug' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, true ),
				)
			);
		}

		/**
		 * Register: update-endpoints-via-discovery.
		 */
		private function register_update_endpoints_via_discovery() {
			wp_register_ability(
				'mo-oauth-client/update-endpoints-via-discovery',
				array(
					'label'               => __( 'Update OAuth endpoints from a discovery URL', 'miniorange-login-with-eve-online-google-facebook' ),
					'description'         => __( 'Fetches an OpenID Connect discovery document over HTTPS and updates authorization, token, and userinfo endpoints on an existing OAuth app.', 'miniorange-login-with-eve-online-google-facebook' ),
					'category'            => 'mo-oauth-configuration',
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'discovery_url' => array(
								'type'        => 'string',
								'format'      => 'uri',
								'description' => 'Full OpenID Connect discovery URL (the .well-known/openid-configuration endpoint). Must be HTTPS.',
							),
							'app_name'      => array(
								'type'        => 'string',
								'description' => 'Name of the OAuth app to update. Defaults to the first configured app.',
							),
						),
						'required'             => array( 'discovery_url' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success'                 => array( 'type' => 'boolean' ),
							'message'                 => array( 'type' => 'string' ),
							'app_name'                => array( 'type' => 'string' ),
							'authorizeurl'            => array( 'type' => 'string' ),
							'accesstokenurl'          => array( 'type' => 'string' ),
							'resourceownerdetailsurl' => array( 'type' => 'string' ),
							'scope'                   => array( 'type' => 'string' ),
						),
						'required'   => array( 'success', 'message' ),
					),
					'execute_callback'    => array( $this, 'execute_update_endpoints_via_discovery' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'meta'                => $this->build_meta( false, false ),
				)
			);
		}

		/**
		 * Shared output schema for the toggle-style abilities.
		 *
		 * @return array
		 */
		private function toggle_output_schema() {
			return array(
				'type'       => 'object',
				'properties' => array(
					'success'  => array( 'type' => 'boolean' ),
					'message'  => array( 'type' => 'string' ),
					'app_name' => array( 'type' => 'string' ),
					'enabled'  => array( 'type' => 'boolean' ),
				),
				'required'   => array( 'success', 'message' ),
			);
		}

		/**
		 * Build a meta block that always includes show_in_rest + accurate annotations,
		 * and includes the mcp.public flag for MCP Adapter discovery.
		 *
		 * @param bool $readonly   True if the ability does not mutate state.
		 * @param bool $idempotent True if calling the ability multiple times with the same input is safe.
		 * @return array
		 */
		private function build_meta( $readonly, $idempotent, $mcp_public = false ) {
			return array(
				'show_in_rest' => true,
				'mcp'          => array( 'public' => (bool) $mcp_public ),
				'annotations'  => array(
					'readonly'      => (bool) $readonly,
					'idempotent'    => (bool) $idempotent,
					'openWorldHint' => false,
				),
			);
		}

		/**
		 * Capability check applied to every ability.
		 *
		 * @return bool|WP_Error
		 */
		public function check_admin_permission() {
			if ( ! current_user_can( self::CAP ) ) {
				return new WP_Error(
					'mo_oauth_abilities_forbidden',
					__( 'You need the manage_options capability to use this ability.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 403 )
				);
			}
			return true;
		}

		/**
		 * Resolve the target app name: explicit input if provided, otherwise the first app.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		private function resolve_app( $input ) {
			$apps = get_option( 'mo_oauth_apps_list' );
			if ( ! is_array( $apps ) || empty( $apps ) ) {
				return new WP_Error(
					'mo_oauth_no_apps',
					__( 'No OAuth apps are configured. Add an app from the plugin settings first.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 404 )
				);
			}

			$requested = isset( $input['app_name'] ) ? (string) $input['app_name'] : '';
			if ( '' !== $requested ) {
				if ( ! array_key_exists( $requested, $apps ) ) {
					return new WP_Error(
						'mo_oauth_app_not_found',
						sprintf(
							/* translators: %s: requested app name */
							__( 'OAuth app "%s" was not found.', 'miniorange-login-with-eve-online-google-facebook' ),
							$requested
						),
						array( 'status' => 404 )
					);
				}
				return array(
					'app_name' => $requested,
					'apps'     => $apps,
				);
			}

			$keys = array_keys( $apps );
			return array(
				'app_name' => $keys[0],
				'apps'     => $apps,
			);
		}

		/**
		 * Flip a per-app integer flag inside mo_oauth_apps_list.
		 *
		 * @param array  $input  Ability input.
		 * @param string $field  Flag key inside the app entry.
		 * @param bool   $enable Desired state.
		 * @return array|WP_Error
		 */
		private function update_app_flag( $input, $field, $enable ) {
			$resolved = $this->resolve_app( $input );
			if ( is_wp_error( $resolved ) ) {
				return $resolved;
			}
			$apps                    = $resolved['apps'];
			$name                    = $resolved['app_name'];
			$apps[ $name ][ $field ] = $enable ? 1 : 0;
			update_option( 'mo_oauth_apps_list', $apps );

			return array(
				'success'  => true,
				'message'  => sprintf(
					/* translators: 1: field name 2: app name */
					__( 'Updated %1$s on app "%2$s".', 'miniorange-login-with-eve-online-google-facebook' ),
					$field,
					$name
				),
				'app_name' => $name,
				'enabled'  => (bool) $enable,
			);
		}

		/**
		 * Execute: send-support-query.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		public function execute_send_support_query( $input ) {
			$email       = isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '';
			$phone       = isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : '';
			$query       = isset( $input['query'] ) ? sanitize_textarea_field( $input['query'] ) : '';
			$send_config = isset( $input['send_config'] ) ? (bool) $input['send_config'] : false;

			if ( '' === $email || '' === $query ) {
				return new WP_Error(
					'mo_oauth_missing_fields',
					__( 'Both email and query are required.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 400 )
				);
			}

			if ( ! class_exists( 'MO_OAuth_Client_Customer' ) ) {
				return new WP_Error(
					'mo_oauth_customer_missing',
					__( 'The miniOrange customer client is not available.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 500 )
				);
			}

			$customer  = new MO_OAuth_Client_Customer();
			$submitted = $customer->submit_contact_us( $email, $phone, $query, $send_config );

			if ( false === $submitted ) {
				return array(
					'success' => false,
					'message' => __( 'Your query could not be submitted. Please verify the email and try again.', 'miniorange-login-with-eve-online-google-facebook' ),
				);
			}

			return array(
				'success' => true,
				'message' => __( 'Thanks for getting in touch! The miniOrange team will reply shortly.', 'miniorange-login-with-eve-online-google-facebook' ),
			);
		}

		/**
		 * Execute: toggle-login-page-sso-button.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		public function execute_toggle_login_button( $input ) {
			$enable = isset( $input['enable'] ) ? (bool) $input['enable'] : false;
			return $this->update_app_flag( $input, 'show_on_login_page', $enable );
		}

		/**
		 * Execute: get-idp-guide-links.
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public function execute_get_idp_guide_links( $input ) {
			$guides    = array();
			$json_path = plugin_dir_path( __DIR__ ) . 'admin' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'defaultapps.json';
			$apps      = function_exists( 'wp_json_file_decode' )
				? wp_json_file_decode( $json_path, array( 'associative' => true ) )
				: ( file_exists( $json_path ) ? json_decode( file_get_contents( $json_path ), true ) : null ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading a bundled plugin file, not a remote URL.

			if ( is_array( $apps ) ) {
				foreach ( $apps as $app_id => $app ) {
					if ( ! is_array( $app ) || empty( $app['guide'] ) ) {
						continue;
					}
					$key            = isset( $app['label'] ) ? (string) $app['label'] : (string) $app_id;
					$guides[ $key ] = (string) $app['guide'];
				}
			}

			$filter = isset( $input['idp_name'] ) ? strtolower( trim( (string) $input['idp_name'] ) ) : '';
			if ( '' !== $filter ) {
				$matches = array();
				foreach ( $guides as $name => $url ) {
					if ( false !== strpos( strtolower( $name ), $filter ) ) {
						$matches[ $name ] = $url;
					}
				}
				return array( 'guides' => $matches );
			}

			return array( 'guides' => $guides );
		}

		/**
		 * Execute: toggle-admin-sso-login.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		public function execute_toggle_admin_sso( $input ) {
			$enable = isset( $input['enable'] ) ? (bool) $input['enable'] : false;
			return $this->update_app_flag( $input, 'allow_admin_sso', $enable );
		}

		/**
		 * Execute: enable-admin-sso-fix.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		public function execute_enable_admin_sso_fix( $input ) {
			$result = $this->update_app_flag( $input, 'allow_admin_sso', true );
			if ( is_array( $result ) && ! empty( $result['success'] ) ) {
				$result['message'] .= ' ' . __( 'Please retry the SSO login and confirm if the issue is resolved. If it still occurs, contact oauthsupport@xecurify.com for further support.', 'miniorange-login-with-eve-online-google-facebook' );
			}
			return $result;
		}

		/**
		 * Execute: auto-map-attribute-from-test-config.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		public function execute_auto_map_attribute( $input ) {
			$attribute_type = isset( $input['attribute_type'] ) ? strtolower( (string) $input['attribute_type'] ) : 'both';
			if ( ! in_array( $attribute_type, array( 'email', 'username', 'both' ), true ) ) {
				return new WP_Error(
					'mo_oauth_bad_attribute_type',
					__( 'attribute_type must be "email", "username", or "both". The free version of this plugin does not support mapping other attributes.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 400 )
				);
			}

			$resolved = $this->resolve_app( $input );
			if ( is_wp_error( $resolved ) ) {
				return $resolved;
			}

			$attrs = get_option( 'mo_oauth_attr_name_list' );
			if ( empty( $attrs ) || ! ( is_array( $attrs ) || is_object( $attrs ) ) ) {
				return new WP_Error(
					'mo_oauth_no_test_response',
					__( 'No Test Configuration response is cached. Please run Test Configuration from the admin UI (Configure OAuth tab) and then retry. If the issue persists, contact oauthsupport@xecurify.com for further support.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 404 )
				);
			}

			$flat        = $this->flatten_attrs( $attrs );
			$targets     = 'both' === $attribute_type ? array( 'email', 'username' ) : array( $attribute_type );
			$mappings    = array();
			$apps        = $resolved['apps'];
			$name        = $resolved['app_name'];
			$app_entry   = is_array( $apps[ $name ] ) ? $apps[ $name ] : array();
			$missed      = array();
			$summary     = array();

			foreach ( $targets as $target ) {
				$candidates = 'username' === $target
					? array( 'preferred_username', 'userprincipalname', 'upn', 'username', 'login', 'uid', 'sub', 'nickname', 'email' )
					: array( 'email', 'emailaddress', 'mail', 'upn', 'preferred_username', 'userprincipalname' );

				$matched_key  = '';
				$sample_value = '';
				foreach ( $candidates as $needle ) {
					foreach ( $flat as $key => $value ) {
						if ( false !== strpos( strtolower( $key ), $needle ) ) {
							$matched_key  = $key;
							$sample_value = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
							break 2;
						}
					}
				}

				if ( '' === $matched_key ) {
					$missed[] = $target;
					continue;
				}

				if ( 'username' === $target ) {
					$app_entry['username_attr']          = $matched_key;
					$app_entry['mo_oauth_username_attr'] = $matched_key;
				} else {
					$app_entry['email_attr']          = $matched_key;
					$app_entry['mo_oauth_email_attr'] = $matched_key;
				}

				$mappings[ $target ] = array(
					'matched_key'  => $matched_key,
					'sample_value' => $sample_value,
				);
				$summary[]           = sprintf( '%s="%s"', $target, $matched_key );
			}

			if ( empty( $mappings ) ) {
				return new WP_Error(
					'mo_oauth_no_matching_attr',
					sprintf(
						/* translators: %s: comma-separated attribute types that could not be matched */
						__( 'Could not find a matching attribute in the cached Test Configuration response for: %s. The IdP may not be returning that field — please verify your IdP attribute release settings and contact oauthsupport@xecurify.com for further support.', 'miniorange-login-with-eve-online-google-facebook' ),
						implode( ', ', $missed )
					),
					array( 'status' => 404 )
				);
			}

			$apps[ $name ] = $app_entry;
			update_option( 'mo_oauth_apps_list', $apps );

			$message = sprintf(
				/* translators: 1: comma-separated list of "type=key" pairs 2: app name */
				__( 'Mapped %1$s for app "%2$s". Please retry the SSO login and confirm if the issue is resolved. If it still occurs, contact oauthsupport@xecurify.com for further support.', 'miniorange-login-with-eve-online-google-facebook' ),
				implode( ', ', $summary ),
				$name
			);
			if ( ! empty( $missed ) ) {
				$message .= ' ' . sprintf(
					/* translators: %s: comma-separated attribute types */
					__( 'Note: no matching field was found for: %s.', 'miniorange-login-with-eve-online-google-facebook' ),
					implode( ', ', $missed )
				);
			}

			return array(
				'success'        => true,
				'message'        => $message,
				'attribute_type' => $attribute_type,
				'mappings'       => $mappings,
			);
		}

		/**
		 * Flatten a nested attribute response into dot-separated keys for matching.
		 *
		 * @param mixed  $data   Source data (array or object).
		 * @param string $prefix Recursion prefix.
		 * @return array
		 */
		private function flatten_attrs( $data, $prefix = '' ) {
			$out = array();
			$it  = is_object( $data ) ? get_object_vars( $data ) : (array) $data;
			foreach ( $it as $key => $value ) {
				$full = '' === $prefix ? (string) $key : $prefix . '.' . $key;
				if ( is_array( $value ) || is_object( $value ) ) {
					$out = array_merge( $out, $this->flatten_attrs( $value, $full ) );
				} else {
					$out[ $full ] = $value;
				}
			}
			return $out;
		}

		/**
		 * Execute: toggle-plugin-debug.
		 *
		 * Mirrors class-mooauth.php::mo_oauth_reset_debug() at line 105-165.
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public function execute_toggle_debug( $input ) {
			$enable = isset( $input['enable'] ) ? (bool) $input['enable'] : false;

			if ( $enable ) {
				update_option( 'mo_debug_enable', 'on' );
				if ( ! get_option( 'mo_oauth_debug' ) ) {
					update_option( 'mo_oauth_debug', 'mo_oauth_debug' . uniqid() );
				}
				update_option( 'mo_debug_time', time() );
				if ( ! wp_next_scheduled( 'mo_oauth_auto_delete_debug_logs' ) ) {
					wp_schedule_single_event( time() + 604800, 'mo_oauth_auto_delete_debug_logs' );
				}
				return array(
					'success'       => true,
					'debug_enabled' => true,
					'message'       => __( 'Debug logging is now enabled.', 'miniorange-login-with-eve-online-google-facebook' ),
				);
			}

			update_option( 'mo_debug_enable', '' );
			if ( wp_next_scheduled( 'mo_oauth_auto_delete_debug_logs' ) ) {
				wp_clear_scheduled_hook( 'mo_oauth_auto_delete_debug_logs' );
			}
			if ( get_option( 'mo_oauth_debug' ) ) {
				if ( class_exists( 'MOOAuth_Debug' ) ) {
					$mo_file_path = MOOAuth_Debug::get_log_file_path();
					if ( $mo_file_path && file_exists( $mo_file_path ) ) {
						wp_delete_file( $mo_file_path );
					}
				}
				delete_option( 'mo_oauth_debug' );
				delete_option( 'mo_debug_time' );
			}

			return array(
				'success'       => true,
				'debug_enabled' => false,
				'message'       => __( 'Debug logging is now disabled.', 'miniorange-login-with-eve-online-google-facebook' ),
			);
		}

		/**
		 * Execute: update-endpoints-via-discovery.
		 *
		 * Rejects non-HTTPS URLs and private/loopback/link-local hosts to prevent
		 * SSRF and silent SSO hijack via attacker-controlled discovery documents.
		 *
		 * @param array $input Ability input.
		 * @return array|WP_Error
		 */
		public function execute_update_endpoints_via_discovery( $input ) {
			$discovery_url = isset( $input['discovery_url'] ) ? esc_url_raw( $input['discovery_url'] ) : '';
			if ( '' === $discovery_url || ! filter_var( $discovery_url, FILTER_VALIDATE_URL ) ) {
				return new WP_Error(
					'mo_oauth_bad_discovery_url',
					__( 'A valid discovery URL is required.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 400 )
				);
			}

			$parsed = wp_parse_url( $discovery_url );
			if ( empty( $parsed['scheme'] ) || 'https' !== strtolower( $parsed['scheme'] ) ) {
				return new WP_Error(
					'mo_oauth_discovery_not_https',
					__( 'The discovery URL must use HTTPS.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 400 )
				);
			}

			if ( empty( $parsed['host'] ) || $this->is_private_host( $parsed['host'] ) ) {
				return new WP_Error(
					'mo_oauth_discovery_private_host',
					__( 'The discovery URL host is not allowed.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 400 )
				);
			}

			$resolved = $this->resolve_app( $input );
			if ( is_wp_error( $resolved ) ) {
				return $resolved;
			}

			$ssl_verify = class_exists( 'MO_OAuth_Utils' )
				? MO_OAuth_Utils::get_ssl_verify_setting( $discovery_url )
				: true;

			$response = wp_remote_get( $discovery_url, array( 'sslverify' => $ssl_verify ) );
			if ( is_wp_error( $response ) ) {
				return new WP_Error(
					'mo_oauth_discovery_http_error',
					$response->get_error_message(),
					array( 'status' => 502 )
				);
			}
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return new WP_Error(
					'mo_oauth_discovery_bad_status',
					sprintf(
						/* translators: %d: HTTP status */
						__( 'Discovery endpoint returned HTTP %d.', 'miniorange-login-with-eve-online-google-facebook' ),
						(int) wp_remote_retrieve_response_code( $response )
					),
					array( 'status' => 502 )
				);
			}

			$body    = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body );
			if ( ! is_object( $decoded ) ) {
				return new WP_Error(
					'mo_oauth_discovery_bad_json',
					__( 'Discovery endpoint did not return valid JSON.', 'miniorange-login-with-eve-online-google-facebook' ),
					array( 'status' => 502 )
				);
			}

			$apps                             = $resolved['apps'];
			$name                             = $resolved['app_name'];
			$entry                            = is_array( $apps[ $name ] ) ? $apps[ $name ] : array();
			$entry['authorizeurl']            = isset( $decoded->authorization_endpoint ) ? (string) $decoded->authorization_endpoint : '';
			$entry['accesstokenurl']          = isset( $decoded->token_endpoint ) ? (string) $decoded->token_endpoint : '';
			$entry['resourceownerdetailsurl'] = isset( $decoded->userinfo_endpoint ) ? (string) $decoded->userinfo_endpoint : '';
			$entry['discovery']               = $discovery_url;

			if ( isset( $decoded->scopes_supported ) && is_array( $decoded->scopes_supported ) ) {
				$scope1 = isset( $decoded->scopes_supported[0] ) ? (string) $decoded->scopes_supported[0] : '';
				$scope2 = isset( $decoded->scopes_supported[1] ) ? (string) $decoded->scopes_supported[1] : '';
				$openid = '';
				if ( 'openid' !== $scope1 && 'openid' !== $scope2 && in_array( 'openid', $decoded->scopes_supported, true ) ) {
					$openid = 'openid';
				}
				$entry['scope'] = '' !== $openid
					? trim( $openid . ' ' . $scope1 . ' ' . $scope2 )
					: trim( $scope1 . ' ' . $scope2 );
			}

			$endpoints_to_validate = array(
				'authorizeurl'            => $entry['authorizeurl'],
				'accesstokenurl'          => $entry['accesstokenurl'],
				'resourceownerdetailsurl' => $entry['resourceownerdetailsurl'],
			);
			foreach ( $endpoints_to_validate as $ep_url ) {
				if ( '' !== $ep_url && ! $this->validate_https_public_endpoint( $ep_url ) ) {
					return new WP_Error(
						'mo_oauth_bad_endpoint',
						__( 'Discovery document contains an invalid endpoint URL.', 'miniorange-login-with-eve-online-google-facebook' ),
						array( 'status' => 400 )
					);
				}
			}

			$apps[ $name ] = $entry;
			update_option( 'mo_oauth_apps_list', $apps );
			update_option( 'mo_oc_valid_discovery_ep', true );

			return array(
				'success'                 => true,
				'message'                 => sprintf(
					/* translators: %s: app name */
					__( 'Endpoints updated for app "%s".', 'miniorange-login-with-eve-online-google-facebook' ),
					$name
				),
				'app_name'                => $name,
				'authorizeurl'            => $entry['authorizeurl'],
				'accesstokenurl'          => $entry['accesstokenurl'],
				'resourceownerdetailsurl' => $entry['resourceownerdetailsurl'],
				'scope'                   => isset( $entry['scope'] ) ? $entry['scope'] : '',
			);
		}

		/**
		 * Validate that a URL uses HTTPS and does not point to a private/loopback host.
		 *
		 * @param string $url URL to validate.
		 * @return bool True if the URL is acceptable, false otherwise.
		 */
		private function validate_https_public_endpoint( $url ) {
			if ( '' === $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
				return false;
			}
			$parsed = wp_parse_url( $url );
			if ( empty( $parsed['scheme'] ) || 'https' !== strtolower( $parsed['scheme'] ) ) {
				return false;
			}
			return ! empty( $parsed['host'] ) && ! $this->is_private_host( $parsed['host'] );
		}

		/**
		 * Determine whether a host string resolves to a private, loopback, or link-local address.
		 *
		 * @param string $host Hostname or IP literal.
		 * @return bool
		 */
		private function is_private_host( $host ) {
			$host = strtolower( trim( $host, '[]' ) );
			if ( in_array( $host, array( 'localhost', 'localhost.localdomain', 'ip6-localhost', 'ip6-loopback' ), true ) ) {
				return true;
			}
			$ip = filter_var( $host, FILTER_VALIDATE_IP );
			if ( false === $ip ) {
				$resolved = gethostbyname( $host );
				if ( $resolved === $host ) {
					return true;
				}
				$ip = $resolved;
			}
			return false === filter_var(
				$ip,
				FILTER_VALIDATE_IP,
				FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
			);
		}
	}

	add_action( 'init', static function () {
		new MO_OAuth_Abilities();
	}, 5 );
}
