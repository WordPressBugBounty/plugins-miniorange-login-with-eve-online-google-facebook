<?php
/**
 * Widget
 *
 * @package    widget
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Adding required files.
 */
require 'class-mooauth-debug.php';

/**
 * Terminate an SSO attempt with a single, identity-independent response.
 *
 * The SSO callback is reachable by an unauthenticated visitor and used to
 * answer with a different message depending on what the asserted identity
 * matched in the WordPress user table: one code when the login belonged to an
 * administrator, another when the login existed but the asserted email did not
 * match, another when the IdP had not verified the email. Those distinctions
 * turned the callback into an oracle for username existence and for which
 * usernames hold the administrator role, so every one of them now produces the
 * exact same output. The specific reason is only ever recorded in the plugin
 * debug log, which is server-side and admin-only.
 *
 * @param string $log_message Internal reason, written to the debug log only.
 * @return void
 */
function mooauth_deny_sso_login( $log_message ) {
	MOOAuth_Debug::mo_oauth_log( $log_message );
	wp_die( esc_html__( 'Invalid login attempt. Please contact your administrator.', 'miniorange-login-with-eve-online-google-facebook' ) );
}

/**
 * [Add Widget Functionality]
 */
class MOOAuth_Widget extends WP_Widget {

	/**
	 * Initialzie widget parameters.
	 */
	public function __construct() {
		// Only write when the value actually needs to change — this constructor runs
		// on every front-end/admin request (widgets_init instantiates the widget
		// every time), so an unconditional update_option() here re-ran the write on
		// every single page load.
		if ( MO_OAUTH_HOSTNAME !== get_option( 'host_name' ) ) {
			update_option( 'host_name', MO_OAUTH_HOSTNAME );
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'mo_oauth_register_plugin_styles' ) );
		add_action( 'init', array( $this, 'mo_oauth_start_session' ) );
		add_action( 'init', array( $this, 'mo_oauth_add_email_verification_option' ) );
		add_action( 'wp_logout', array( $this, 'mo_oauth_end_session' ) );
		add_action( 'login_form', array( $this, 'mo_oauth_wplogin_form_button' ) );
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'woocommerce_login_form_end', array( $this, 'mo_oauth_wplogin_form_button' ) );
		}
		add_action(
			'wp_enqueue_scripts',
			function() {
				if ( apply_filters( 'miniorange_oauth_force_load_login_script', false ) ) {
					$this->mo_oauth_load_login_script();
				}
			}
		);
		parent::__construct( 'mooauth_widget', MO_OAUTH_ADMIN_MENU, array( 'description' => __( 'Login to Apps with OAuth', 'miniorange-login-with-eve-online-google-facebook' ) ) );

	}

	/**
	 * Handle migration for Email verification.
	 */
	public function mo_oauth_add_email_verification_option() {
		$is_first_setup = get_option( 'mo_oauth_email_verification_option_initialized' );
		if ( false === $is_first_setup ) {
			$app_config = array();

			$app_config['mo_oauth_email_verify_check']       = 'true';
			$app_config['mo_oauth_idp_email_verified_key']   = 'email_verified';
			$app_config['mo_oauth_idp_email_verified_value'] = '1';

			update_option( 'mo_oauth_login_settings_option', $app_config );
			update_option( 'mo_oauth_email_verification_option_initialized', true );
		}
	}

	/**
	 * Enqueue CSS for widget
	 */
	public function mo_oauth_wplogin_form_style() {

		wp_enqueue_style( 'mo_oauth_fontawesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ), array(), '4.7.0' );
		wp_enqueue_style( 'mo_oauth_wploginform', plugins_url( 'css/login-page.min.css', __FILE__ ), array(), MO_OAUTH_CSS_JS_VERSION );
	}

	/**
	 * Display Login widget
	 */
	public function mo_oauth_wplogin_form_button() {
		$appslist = get_option( 'mo_oauth_apps_list' );
		if ( is_array( $appslist ) && count( $appslist ) > 0 ) {
			$scripts_loaded = false;
			$show_button    = false;

			foreach ( $appslist as $key => $app ) {
				$show_button = false;

				// WordPress Login Form.
				if ( 'login_form' === current_filter() ) {
					$show_on_login_page = isset( $app['show_on_login_page'] ) && 1 === (int) $app['show_on_login_page'];
					if ( $show_on_login_page ) {
						if ( ! $scripts_loaded ) {
							$this->mo_oauth_load_login_script();
							$this->mo_oauth_wplogin_form_style();
							$scripts_loaded = true;
							echo '<h4 class="mo_oauth_connect_heading">' . esc_html__( 'Connect with :', 'miniorange-login-with-eve-online-google-facebook' ) . '</h4>';
						}
						$show_button = true;
					}
				}

				// WooCommerce Login Form.
				if ( 'woocommerce_login_form_end' === current_filter() ) {
					$show_on_woocommerce = isset( $app['mo_oauth_show_on_woocommerce_login_form'] ) && 'true' === $app['mo_oauth_show_on_woocommerce_login_form'];
					if ( $show_on_woocommerce ) {
						if ( ! $scripts_loaded ) {
							$this->mo_oauth_load_login_script();
							$this->mo_oauth_wplogin_form_style();
							$scripts_loaded = true;
						}
						$show_button = true;
					}
				}

				// Render button.
				if ( $show_button ) {
					echo '<br>';
					echo '<div class="row">';
					$logo_class = $this->mo_oauth_client_login_button_logo( $app['appId'] );
					echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\'' . esc_attr( $key ) . '\');"><div class="mo_oauth_login_button mo_oauth_login_button_text"><i class="' . esc_attr( $logo_class ) . ' mo_oauth_login_button_icon"></i>Login with ' . esc_attr( ucwords( $key ) ) . '</div></a>';
					echo '</div><br><br>';
				}
			}
		}
	}

	/**
	 * Get logo class for the configured app.
	 *
	 * @param mixed $current_app_id current app for which the logo needs to be displayed.
	 */
	public function mo_oauth_client_login_button_logo( $current_app_id ) {
		$currentapp = mooauth_client_get_app( $current_app_id );
		$logo_class = $currentapp->logo_class;
		return $logo_class;
	}

	/**
	 * Redirect to SSO after clicking on button
	 */
	public function mo_oauth_start_session() {
		// Only start a PHP session on requests that are actually part of the SSO
		// flow (redirect-out, IdP callback, or test-configuration). Starting one on
		// every page view sends a PHPSESSID cookie plus no-cache headers (defeating
		// full-page/proxy caching) and holds PHP's session-file lock, serializing
		// concurrent requests from the same visitor.
		$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$sso_option  = isset( $_REQUEST['option'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used to decide whether this request is part of the SSO flow; no state is changed here.
		$is_sso_flow = in_array( $sso_option, array( 'oauthredirect', 'testattrmappingconfig' ), true )
			|| isset( $_REQUEST['code'] ) //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- IdP callback detection only; the value is not read here.
			|| false !== strpos( $request_uri, '/oauthcallback' )
			|| false !== strpos( $request_uri, 'openid.ns' );

		if ( $is_sso_flow && session_status() === PHP_SESSION_NONE && ! mooauth_client_is_ajax_request() && ! mooauth_client_is_rest_api_call() ) {
			$session_path = session_save_path();
			if ( empty( $session_path ) ) {
				$session_path = sys_get_temp_dir();
			}
			if ( is_writable( $session_path ) ) { //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- This checks the PHP session save path, not a WordPress file; bootstrapping WP_Filesystem here would needlessly load the whole filesystem API on every single request.
				session_start();
			}
		}

		if ( isset( $_REQUEST['option'] ) && sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ) === 'testattrmappingconfig' ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			$mo_oauth_app_name = ! empty( $_REQUEST['app'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['app'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			wp_safe_redirect( site_url() . '?option=oauthredirect&app_name=' . rawurlencode( $mo_oauth_app_name ) . '&test=true' );
			exit();
		}

	}

	/**
	 * Destroy user session.
	 */
	public function mo_oauth_end_session() {

		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}

		if ( session_status() === PHP_SESSION_ACTIVE ) {
			session_destroy();
		}
	}

	/**
	 * Echoes the widget content.
	 *
	 * @param mixed $args Display arguments including 'before_title', 'after_title',
	 *                         'before_widget', and 'after_widget'..
	 * @param mixed $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$wid_title = '';
		if ( ! empty( $instance['wid_title'] ) ) {
			$wid_title = $instance['wid_title'];
		}
		$wid_title = apply_filters( 'widget_title', $wid_title );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_widget'] is html that needs to render on dom escaping will not render html.
		if ( ! empty( $wid_title ) ) {
			echo esc_attr( $args['before_title'] ) . esc_html( $wid_title ) . esc_attr( $args['after_title'] );
		}
		$this->mo_oauth_login_form();
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['after_widget'] is html that needs to render on dom escaping will not render html.
	}

	/**
	 * MiniOrange method to override parent method to update a particular instance of a widget.
	 *
	 * @param mixed $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param mixed $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		if ( isset( $new_instance['wid_title'] ) ) {
			$instance['wid_title'] = wp_strip_all_tags( $new_instance['wid_title'] );
		}

		return $instance;
	}

	/**
	 * Display login widget content.
	 *
	 * @param bool $check_if_shortcode Render the compact login-page button style
	 *                                 (.mo_oauth_login_button, the same markup the
	 *                                 wp-login.php form uses) instead of the taller
	 *                                 widget button. Defaults to false so the widget
	 *                                 keeps its existing appearance.
	 */
	public function mo_oauth_login_form( $check_if_shortcode = false ) {
		global $post;
		$appslist = get_option( 'mo_oauth_apps_list' );
		if ( $appslist && count( $appslist ) > 0 ) {
			$apps_configured = true;
		}

		if ( ! is_user_logged_in() ) {

			if ( isset( $apps_configured ) && $apps_configured ) {

				$this->mo_oauth_wplogin_form_style();
				$this->mo_oauth_load_login_script();

				$style      = get_option( 'mo_oauth_icon_width' ) ? 'width:' . get_option( 'mo_oauth_icon_width' ) . ';' : '';
				$style     .= get_option( 'mo_oauth_icon_height' ) ? 'height:' . get_option( 'mo_oauth_icon_height' ) . ';' : '';
				$style     .= get_option( 'mo_oauth_icon_margin' ) ? 'margin:' . get_option( 'mo_oauth_icon_margin' ) . ';' : '';
				$custom_css = get_option( 'mo_oauth_icon_configure_css' );
				if ( empty( $custom_css ) ) {
					echo '<style>.oauthloginbutton{background: #7272dc;height:40px;padding:8px;text-align:center;color:#fff;}</style>';
				} else {
					echo '<style>' . esc_html( $custom_css ) . '</style>';
				}

				if ( is_array( $appslist ) ) {
					// .mo_oauth_login_button is width:100% — inside the narrow
					// wp-login.php form that reads correctly, but post/page content is
					// full-bleed, so the shortcode needs a wrapper to cap the width and
					// space multiple app buttons apart.
					if ( $check_if_shortcode ) {
						echo '<div class="mo_oauth_shortcode_login">';
					}
					foreach ( $appslist as $key => $app ) {
						$logo_class = $this->mo_oauth_client_login_button_logo( $app['appId'] );

						if ( $check_if_shortcode ) {
							echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\'' . esc_attr( $key ) . '\');"><div class="mo_oauth_login_button mo_oauth_login_button_text"><i class="' . esc_attr( $logo_class ) . ' mo_oauth_login_button_icon"></i>Login with ' . esc_attr( ucwords( $key ) ) . '</div></a>';
						} else {
							echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\'' . esc_attr( $key ) . '\');"><div class="mo_oauth_login_button_widget"><i class="' . esc_attr( $logo_class ) . ' mo_oauth_login_button_icon_widget"></i><h3 class="mo_oauth_login_button_text_widget">Login with ' . esc_attr( ucwords( $key ) ) . '</h3></div></a>';
						}
					}
					if ( $check_if_shortcode ) {
						echo '</div>';
					}
				}
			} else {
				echo '<div>No apps configured.</div>';
			}
		} else {
			$current_user       = wp_get_current_user();
			$link_with_username = __( 'Howdy, ', 'miniorange-login-with-eve-online-google-facebook' ) . $current_user->display_name;
			echo '<div id="logged_in_user" class="login_wid">
			<li>' . esc_attr( $link_with_username ) . ' | <a href="' . esc_url( wp_logout_url( site_url() ) ) . '" >Logout</a></li>
		</div>';

		}
	}

	/**
	 * Load login script
	 */
	private function mo_oauth_load_login_script() {
		?>
	<script type="text/javascript">

		function HandlePopupResult(result) {
			window.location.href = result;
		}

		function moOAuthLoginNew(app_name) {
			var redirectTo = new URLSearchParams(window.location.search).get('redirect_to');
			if ( ! redirectTo && window.location.href.indexOf('wp-login.php') === -1 ) {
				redirectTo = window.location.href;
			}
			var url = '<?php echo esc_url( site_url() ); ?>' + '/?option=oauthredirect&app_name=' + encodeURIComponent(app_name) + '&time=' + Date.now();
			if ( redirectTo ) {
				url += '&redirect_to=' + encodeURIComponent(redirectTo);
			}
			window.location.href = url;
		}
	</script>
		<?php
	}



	/**
	 * Register Plugin styles.
	 *
	 * style_login_widget.min.css only styles the logged-in/"Howdy" state
	 * (.login_wid) — the login button itself is styled by
	 * login-page.min.css, enqueued separately at the point it actually renders
	 * (mo_oauth_wplogin_form_style()). So this stylesheet is only ever needed on
	 * a page that actually renders the widget or the [mo_oauth_login] shortcode,
	 * not on every front-end page view.
	 */
	public function mo_oauth_register_plugin_styles() {
		if ( ! $this->mo_oauth_login_widget_or_shortcode_in_use() ) {
			return;
		}
		wp_enqueue_style( 'style_login_widget', plugins_url( 'css/style_login_widget.min.css', __FILE__ ), array(), MO_OAUTH_CSS_JS_VERSION );
	}

	/**
	 * Whether the current page is expected to render the login widget or the
	 * [mo_oauth_login] shortcode.
	 *
	 * @return bool
	 */
	private function mo_oauth_login_widget_or_shortcode_in_use() {
		if ( is_active_widget( false, false, 'mooauth_widget' ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();
			if ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'mo_oauth_login' ) ) {
				return true;
			}
		}

		// Escape hatch for shortcodes placed outside post_content (page builders,
		// footer text widgets, etc.) that the checks above can't see.
		return (bool) apply_filters( 'mo_oauth_force_load_widget_style', false );
	}


}

/**
 * Update email as username attribute.
 *
 * @param mixed $currentappname Current SSO app name.
 */
function mooauth_update_email_to_username_attr( $currentappname ) {
	$appslist = get_option( 'mo_oauth_apps_list' );

	// Only ever update an app that exists and actually has an email attribute to fall back on.
	// Without this the function happily creates an entry for an unknown (or empty) app name and
	// persists that junk into the saved app list.
	if ( ! is_array( $appslist ) || ! isset( $appslist[ $currentappname ]['email_attr'] ) ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : Cannot use email as username attribute - app not configured.' );
		return;
	}

	$appslist[ $currentappname ]['username_attr'] = $appslist[ $currentappname ]['email_attr'];
	update_option( 'mo_oauth_apps_list', $appslist );
}

/**
 * Main SSO flow.
 */
function mooauth_login_validate() {

	/* Handle Authorize request */
	if ( isset( $_REQUEST['option'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ), 'oauthredirect' ) !== false ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		$appname  = ! empty( $_REQUEST['app_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['app_name'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		$appslist = get_option( 'mo_oauth_apps_list' );

		if ( isset( $_REQUEST['test'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			setcookie( 'mo_oauth_test', true, time() + 3600, '/', '', true, true );
		} else {
			setcookie( 'mo_oauth_test', false, time() + 3600, '/', '', true, true );
		}

		$mo_oauth_redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only a landing URL; validated below via wp_validate_redirect().
		$mo_oauth_redirect_to = $mo_oauth_redirect_to ? wp_validate_redirect( $mo_oauth_redirect_to, '' ) : '';
		if ( ! empty( $mo_oauth_redirect_to ) ) {
			setcookie(
				'mo_oauth_redirect_to',
				$mo_oauth_redirect_to,
				array(
					'expires'  => time() + 300,
					'httponly' => true,
					'secure'   => is_ssl(),
					'samesite' => 'Lax',
					'path'     => COOKIEPATH,
					'domain'   => COOKIE_DOMAIN,
				)
			);
		}

		if ( false === $appslist ) {
			MOOAuth_Debug::mo_oauth_log( 'ERROR : Looks like you have not configured OAuth provider, please try to configure OAuth provider first' );
			exit( 'Looks like you have not configured OAuth provider, please try to configure OAuth provider first' );
		}

		foreach ( $appslist as $key => $app ) {

			// Only the app that was actually requested may drive this authorization request.
			// Every other configured app is skipped outright: falling through to one of them
			// would redirect the user into an OAuth flow they never asked for, built from
			// state that was never generated for it.
			if ( $appname !== $key ) {
				continue;
			}

			// OIDC nonce: generated per-request for OpenID Connect apps only, to bind the ID token
			// back to this specific authorization request and block replay of a captured token.
			$is_oidc_app = isset( $app['apptype'] ) && 'openidconnect' === $app['apptype'];
			$oidc_nonce  = $is_oidc_app ? bin2hex( \openssl_random_pseudo_bytes( 32 ) ) : '';

			if ( isset( $app['send_state'] ) !== true || $app['send_state'] | 'oauth1' === $app['appId'] || 'twitter' === $app['appId'] ) {

				if ( 'twitter' === $app['appId'] || 'oauth1' === $app['appId'] ) {
					include 'class-mo-oauth-custom-oauth1.php';
					setcookie( 'tappname', $appname, time() + 3600, '/', '', true, true );
					$setcookie = ! empty( $_COOKIE['tappname'] ) ? MOOAuth_Custom_OAuth1::mo_oauth1_auth_request( sanitize_text_field( wp_unslash( $_COOKIE['tappname'] ) ) ) : '';
					exit();
				}

				$timestamp           = time();
				$client_ip           = mooauth_get_client_ip();
				$hmac_secret         = wp_salt( 'auth' );
				$timestamp_hmac      = hash_hmac( 'sha256', $timestamp, $hmac_secret );
				$state_nonce         = bin2hex( \openssl_random_pseudo_bytes( 32 ) );
				$state_nonce_hmac    = hash_hmac( 'sha256', $state_nonce, $timestamp_hmac );
				$ip_hmac             = hash_hmac( 'sha256', $client_ip, $timestamp_hmac );
				$state_string        = $appname . '|' . $timestamp . '|' . $ip_hmac . '|' . $state_nonce_hmac;
				$state_string_cookie = $appname . '|' . $timestamp . '|' . $ip_hmac . '|' . $state_nonce;
				$state_cookie        = base64_encode( $state_string_cookie );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encode will be required for fetching appname from state.
				$state               = base64_encode( $state_string ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encode will be required for fetching appname from state.
				$authorization_url   = $app['authorizeurl'];

				if ( strpos( $authorization_url, '?' ) !== false ) {
					$authorization_url = $authorization_url . '&client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code&state=' . $state;
				} else {
					$authorization_url = $authorization_url . '?client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code&state=' . $state;
				}

				if ( ! empty( $oidc_nonce ) ) {
					$authorization_url .= '&nonce=' . $oidc_nonce;
				}

				// Server-side record that this site minted this state. Validation requires it
				// to be present and then deletes it, making every state single-use and blocking
				// replay of a captured callback URL inside the 5 minute window.
				set_transient( 'mo_oauth_state_' . hash( 'sha256', $state_nonce_hmac ), $appname, 300 );

				// Apple is driven with response_mode=form_post, so the IdP returns the callback
				// as a cross-site POST. Browsers withhold SameSite=Lax cookies on cross-site
				// POST, which would leave the state unbindable to the originating browser, so
				// that flow needs SameSite=None - only honoured together with Secure.
				$is_form_post = ( false !== strpos( $authorization_url, 'apple' ) );

				setcookie(
					'mo_oauth_sso_state',
					$state_cookie,
					array(
						'expires'  => time() + 300,   // 5 minutes
						'httponly' => true,
						'secure'   => $is_form_post ? true : is_ssl(),
						'samesite' => $is_form_post ? 'None' : 'Lax',
						'path'     => COOKIEPATH,
						'domain'   => COOKIE_DOMAIN,
					)
				);

				if ( ! empty( $oidc_nonce ) ) {
					setcookie(
						'mo_oauth_sso_nonce',
						$oidc_nonce,
						array(
							'expires'  => time() + 300,   // 5 minutes
							'httponly' => true,
							'secure'   => $is_form_post ? true : is_ssl(),
							'samesite' => $is_form_post ? 'None' : 'Lax',
							'path'     => COOKIEPATH,
							'domain'   => COOKIE_DOMAIN,
						)
					);
				}

				if ( strpos( $authorization_url, 'apple' ) !== false ) {
					$authorization_url = str_replace( 'response_type=code', 'response_type=code+id_token', $authorization_url );
					$authorization_url = $authorization_url . '&response_mode=form_post';
				}

				if ( 'steam' === $app['appId'] ) {
					$return    = null;
					$alt_realm = null;

					$authorization_url = $app['authorizeurl'];

					$sub_param1 = null;
					$sub_param2 = null;

					// Compare the forwarded protocol against 'https' itself. This previously read
					// $sub_param2 before it was assigned, which both emitted a warning and made
					// the proxy check compare against null.
					$use_https = ! empty( $_SERVER['HTTPS'] ) || ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) ) );

					if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['SCRIPT_NAME'] ) ) {
						$sub_param1 .= sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
						$sub_param2 .= sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) );
					}

					$return = ( $use_https ? 'https' : 'http' ) . '://' . $sub_param1 . $sub_param2;

					$params = array(
						'openid.ns'         => 'http://specs.openid.net/auth/2.0',
						'openid.mode'       => 'checkid_setup',
						'openid.return_to'  => $return,
						'openid.realm'      => null !== $alt_realm ? $alt_realm : ( ( $use_https ? 'https' : 'http' ) . '://' . $sub_param1 ),
						'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
						'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
					);

					$authorization_url = $authorization_url . '?' . http_build_query( $params );
				}

				if ( session_status() === PHP_SESSION_NONE ) {
					session_start();
				}
				$_SESSION['oauth2state'] = $state_cookie;
				$_SESSION['appname']     = $appname;

				MOOAuth_Debug::mo_oauth_log( 'Authorization Request Sent => ' . $authorization_url );
				header( 'Location: ' . $authorization_url );
				exit;
			} else {
				$state             = null;
				$authorization_url = $app['authorizeurl'];
				if ( strpos( $authorization_url, '?' ) !== false ) {
					$authorization_url = $authorization_url . '&client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code';
				} else {
					$authorization_url = $authorization_url . '?client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code';
				}

				if ( ! empty( $oidc_nonce ) ) {
					$authorization_url .= '&nonce=' . $oidc_nonce;
				}

				// State is disabled for this app, so no state was generated for this request.
				// Expire any state left behind by an earlier flow rather than planting one, so a
				// stale cookie can never be mistaken for this request's state on the callback.
				setcookie(
					'mo_oauth_sso_state',
					'',
					array(
						'expires'  => time() + 300,
						'httponly' => true,
						'secure'   => is_ssl(),
						'samesite' => 'Lax',
						'path'     => COOKIEPATH,
						'domain'   => COOKIE_DOMAIN,
					)
				);
				unset( $_COOKIE['mo_oauth_sso_state'] );

				if ( ! empty( $oidc_nonce ) ) {
					setcookie(
						'mo_oauth_sso_nonce',
						$oidc_nonce,
						array(
							'expires'  => time() + 300,   // 5 minutes
							'httponly' => true,
							'secure'   => is_ssl(),
							'samesite' => 'Lax',
							'path'     => COOKIEPATH,
							'domain'   => COOKIE_DOMAIN,
						)
					);
				}
				if ( session_status() === PHP_SESSION_NONE ) {
					session_start();
				}
				unset( $_SESSION['oauth2state'] );
				$_SESSION['appname'] = $appname;

				MOOAuth_Debug::mo_oauth_log( 'Authorization Request Sent => ' . $authorization_url );
				header( 'Location: ' . $authorization_url );
				exit;
			}
		}

		// Nothing in the configured app list matched the requested app name. Stop here instead
		// of letting the request fall through, and keep the requested name out of the response
		// so it is never reflected back to the caller.
		MOOAuth_Debug::mo_oauth_log( 'ERROR : No OAuth application is configured for the requested app name.' );
		wp_die( 'Authentication failed. The requested application is not configured.' );
	} elseif ( ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'openidcallback' ) !== false ) || ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'oauth_token' ) !== false ) && ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'oauth_verifier' ) ) ) {
		$appslist        = get_option( 'mo_oauth_apps_list' );
		$username_attr   = '';
		$email_attr      = '';
		$currentapp      = false;
		$allow_admin_sso = '';

		// This callback is meaningless without the app name this plugin set when it started the
		// OAuth1 flow. Resolve it once, up front, so a callback URL replayed without the cookie
		// fails closed instead of reading an absent key on every use below.
		$tappname = ! empty( $_COOKIE['tappname'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['tappname'] ) ) : '';
		if ( '' === $tappname || ! is_array( $appslist ) ) {
			MOOAuth_Debug::mo_oauth_log( 'ERROR : OAuth1 callback received without a pending authorization request.' );
			wp_die( 'Authentication failed. Please try again.' );
		}

		foreach ( $appslist as $key => $app ) {
			if ( $key === $tappname ) {
						include 'class-mo-oauth-custom-oauth1.php';
						$currentapp = $app;
				if ( isset( $app['username_attr'] ) ) {
					$username_attr = $app['username_attr'];
				}
				if ( isset( $app['email_attr'] ) ) {
					if ( ! isset( $app['username_attr'] ) ) {
						mooauth_update_email_to_username_attr( $tappname );
						$username_attr = $app['email_attr'];

					}

					$email_attr = $app['email_attr'];
				}
				if ( isset( $app['allow_admin_sso'] ) ) {
					$allow_admin_sso = $app['allow_admin_sso'];
				}
			}
		}

		$resource_owner = MOOAuth_Custom_OAuth1::mo_oidc1_get_access_token( $tappname );
		$username       = '';
		$email          = '';
		update_option( 'mo_oauth_attr_name_list', $resource_owner );
		// Test Configuration.
		if ( isset( $_COOKIE['mo_oauth_test'] ) && sanitize_text_field( wp_unslash( $_COOKIE['mo_oauth_test'] ) ) ) {
			setcookie( 'mo_oauth_test', false, time() + 3600, '/', '', true, true );
			echo '<div style="font-family:Calibri;padding:0 3%;color:012970;">';
			echo '<style>table{border-collapse:collapse;color:#012970;}th{background-color: #c6d8f6bd; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#012970;}tr:nth-child(odd) {background-color: #e4eeff;}td{padding:8px;border-width:1px; border-style:solid; border-color:#012970;word-break: break-all;}</style>';
			echo '<h2>Test Configuration</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>';
			mooauth_client_testattrmappingconfig( '', $resource_owner );
			echo '</table>';
			echo '<div style="padding: 10px;"></div><input style="padding:7px 12px;width:100px;background: #012970 none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA; inset;color: #FFF;"type="button" value="Done" onClick="self.close();">&emsp;';
			echo '</div>';
			exit();
		}

		if ( ! empty( $username_attr ) ) {
			$username = mooauth_client_getnestedattribute( $resource_owner, $username_attr );
			MOOAuth_Debug::mo_oauth_log( 'Username received.=>' . $username );
		}

		if ( empty( $username ) || '' === $username ) {
					MOOAuth_Debug::mo_oauth_log( 'Username not received. Check your Attribute Mapping configuration.' );
					exit( 'Username not received. Check your <b>Attribute Mapping</b> configuration.' );
		}

		if ( ! is_string( $username ) ) {
			MOOAuth_Debug::mo_oauth_log( 'Username is not a string. It is ' . mooauth_client_get_proper_prefix( gettype( $username ) ) );
			wp_die( 'Username is not a string. It is ' . esc_html( mooauth_client_get_proper_prefix( gettype( $username ) ) ) );
		}
		if ( ! empty( $email_attr ) ) {
			$email = mooauth_client_getnestedattribute( $resource_owner, $email_attr );
			MOOAuth_Debug::mo_oauth_log( 'email received.=>' . $email );
		}

		$user = get_user_by( 'login', $username );
		if ( ! $user && ! empty( $email_attr ) ) {
			$user = get_user_by( 'email', $email );
		}

		if ( $user ) {
			$user_id = $user->ID;

			if ( in_array( 'administrator', $user->roles, true ) && ! $allow_admin_sso ) {
				mooauth_deny_sso_login( 'WPO004: Invalid Login attempt. Please login using email and password.' );
			}

			// Whenever the IdP asserts an email for this login (email_attr is mapped),
			// verify it against the matched account for EVERY user, not only
			// administrators: matching solely on a claim value (username or email)
			// with no cross-check would let a forged/mismatched claim log in as any
			// existing account. This used to run only for administrators. App
			// configs that map only a username attribute (no email_attr) have no
			// email signal to check here and are unaffected, exactly as before.
			if ( ! empty( $email_attr ) ) {
				$current_user_email           = $user->user_email;
				$mo_oauth_email_verify_config = get_option( 'mo_oauth_login_settings_option' );
				$mo_oauth_email_verify_check  = isset( $mo_oauth_email_verify_config['mo_oauth_email_verify_check'] ) ? $mo_oauth_email_verify_config['mo_oauth_email_verify_check'] : false;

				if ( strtolower( $current_user_email ) !== strtolower( $email ) ) {
					mooauth_deny_sso_login( 'Error : WPO01 Invalid login attempt. Asserted email does not match the matched account.' );
				}

				if ( $mo_oauth_email_verify_check ) {

					$idp_email_verified_key = isset( $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_key'] ) && '' !== $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_key']
					? $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_key']
					: 'email_verified';

					$idp_email_verified_value = isset( $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_value'] ) && '' !== $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_value']
					? $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_value']
					: '1';

					if ( isset( $resource_owner[ $idp_email_verified_key ] ) ) {
						$email_verified = $resource_owner[ $idp_email_verified_key ];
						if ( (string) $email_verified !== (string) $idp_email_verified_value ) {
							mooauth_deny_sso_login( 'Error: wpoauth:002 - Email verification failed. The IdP did not report the asserted email as verified.' );
						}
					}
				}

				wp_update_user(
					array(
						'ID'         => $user_id,
						'user_email' => $email,
					)
				);
			}
		} else {
			if ( mooauth_migrate_customers() ) {
				$user = mooauth_looped_user( $username );
			} else {
				$user = mooauth_handle_user_registration( $username, $email );
			}
		}

		if ( $user ) {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			$user = get_user_by( 'ID', $user->ID );
			do_action( 'wp_login', $user->user_login, $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			MOOAuth_Debug::mo_oauth_log( 'User logged-in.' );

			$redirect_to = mooauth_get_sso_redirect_to();

			wp_safe_redirect( $redirect_to );
			exit;
		}
	} elseif ( ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-json/moserver/token' ) === false && ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/oauthcallback' ) !== false || isset( $_REQUEST['code'] ) ) ) || ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'openid.ns' ) !== false ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
		MOOAuth_Debug::mo_oauth_log( 'OAuth plugin catched the flow, $_REQUEST array=>' );
		MOOAuth_Debug::mo_oauth_log( $_REQUEST ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL.

		// checking addiional condition for steam application.
		if ( isset( $_REQUEST['code'] ) || isset( $_REQUEST['openid_ns'] ) ) {  //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			// exit from our control when user is already logged in. This it to prevent the issue with Ecwid Ecommerce plugin.
			if ( is_user_logged_in() && ! isset( $_COOKIE['mo_oauth_test'] ) ) {
				return;
			}

			try {

				$currentappname = '';

				if ( isset( $_SESSION['appname'] ) && ! empty( $_SESSION['appname'] ) ) {
					$currentappname = sanitize_text_field( $_SESSION['appname'] );
				}
				if ( isset( $_REQUEST['state'] ) && ! empty( $_REQUEST['state'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
					$state_encoded  = sanitize_text_field( wp_unslash( $_REQUEST['state'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
					$state_data     = mooauth_validate_state( $state_encoded );
					$currentappname = $state_data['appname'];
				} else {
					$appslist       = get_option( 'mo_oauth_apps_list' );
					$state_required = false;
					foreach ( $appslist as $key => $app ) {
						MOOAuth_Debug::mo_oauth_log( 'Send State Value: ' );
						MOOAuth_Debug::mo_oauth_log( $app['send_state'] );
						if ( isset( $app['send_state'] ) && true == $app['send_state'] ) {
							$state_required = true;
							break;
						}
					}
					if ( $state_required ) {
						MOOAuth_Debug::mo_oauth_log( 'ERROR : State parameter is required but not found in request.' );
						wp_die( 'Authentication failed. State parameter is required.' );
					}
				}

				if ( empty( $currentappname ) ) {
					MOOAuth_Debug::mo_oauth_log( 'ERROR : No request found for this application.' );
					return;
				}
				$appslist        = get_option( 'mo_oauth_apps_list' );
				$username_attr   = '';
				$email_attr      = '';
				$currentapp      = false;
				$allow_admin_sso = '';
				foreach ( $appslist as $key => $app ) {
					if ( $key === $currentappname ) {
						$currentapp = $app;
						if ( isset( $app['username_attr'] ) ) {
							$username_attr = $app['username_attr'];
						}
						if ( isset( $app['email_attr'] ) ) {
							if ( ! isset( $app['username_attr'] ) ) {
								// The app to update is the one that just matched. This used to read the
								// OAuth1 'tappname' cookie, which is absent on the OAuth2 callback, so it
								// stored the fallback under an empty app name instead of this app.
								mooauth_update_email_to_username_attr( $currentappname );
								$username_attr = $app['email_attr'];

							}
							$email_attr = $app['email_attr'];
						}
						if ( isset( $app['allow_admin_sso'] ) ) {
							$allow_admin_sso = $app['allow_admin_sso'];
						}
					}
				}

				if ( ! $currentapp ) {
					MOOAuth_Debug::mo_oauth_log( 'Authorization Response Recieved => ERROR : Application not configured.' );
					exit( 'Application not configured.' );
				}
				$resource_owner_details_url = $currentapp['resourceownerdetailsurl'];
				$mo_oauth_handler           = new MO_OAuth_Handler();
				MOOAuth_Debug::mo_oauth_log( 'Authorization Response Received' );
				if ( isset( $currentapp['apptype'] ) && 'openidconnect' === $currentapp['apptype'] ) {
					// OpenId connect.
					MOOAuth_Debug::mo_oauth_log( 'OpenId Flow' );

					// If configured Steam application.
					if ( isset( $_REQUEST['openid_op_endpoint'] ) && isset( $_REQUEST['openid_claimed_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
						MOOAuth_Debug::mo_oauth_log( 'Applciation selecetd: Steam' );

						// The response's own openid.claimed_id is unauthenticated client input until
						// Steam itself confirms the assertion via the OpenID 2.0 check_authentication
						// round-trip (spec section 11.4.2). Without this, an attacker can forge the
						// callback with any SteamID and log in as whichever WP account maps to it.
						if ( ! mooauth_verify_openid_assertion( $currentapp ) ) {
							MOOAuth_Debug::mo_oauth_log( 'ERROR : OpenID assertion could not be verified with the provider.' );
							wp_die( 'Authentication failed. Unable to verify the OpenID response with the provider.' );
						}

						$str     = sanitize_text_field( wp_unslash( $_REQUEST['openid_claimed_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
						$extract = ( explode( '/', $str ) );

						if ( ! isset( $extract[5] ) || ! ctype_digit( $extract[5] ) ) {
							MOOAuth_Debug::mo_oauth_log( 'ERROR : Invalid SteamID format in claimed_id.' );
							wp_die( 'Authentication failed. Invalid SteamID received.' );
						}

						$mo_steam_id = $extract[5];

						$access_token_url = $currentapp['accesstokenurl'];
						$client_id        = $currentapp['clientid'];

						$profile_url = $access_token_url . $client_id . '&steamids=' . $mo_steam_id;

						$resource_owner = $mo_oauth_handler->get_resource_owner( $profile_url, '' );
					} else { // Openid flow.
						$code = ! empty( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
						if ( ! isset( $currentapp['send_headers'] ) ) {
							$currentapp['send_headers'] = false;
						}
						if ( ! isset( $currentapp['send_body'] ) ) {
							$currentapp['send_body'] = false;
						}

						// Read back the nonce bound to this authorization request (set in the redirect step
						// above) and clear it immediately so it can't be reused against a second callback.
						$expected_nonce = '';
						if ( isset( $_COOKIE['mo_oauth_sso_nonce'] ) ) {
							$expected_nonce = sanitize_text_field( wp_unslash( $_COOKIE['mo_oauth_sso_nonce'] ) );
							setcookie( 'mo_oauth_sso_nonce', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
						}

						$token_response = $mo_oauth_handler->get_id_token(
							$currentapp['accesstokenurl'],
							'authorization_code',
							$currentapp['clientid'],
							$currentapp['clientsecret'],
							$code,
							$currentapp['redirecturi'],
							$currentapp['send_headers'],
							$currentapp['send_body']
						);

						$id_token = isset( $token_response['id_token'] ) ? $token_response['id_token'] : $token_response['access_token'];
						MOOAuth_Debug::mo_oauth_log( 'ID Token => ' );
						MOOAuth_Debug::mo_oauth_log( $id_token );
						$resource_owner = $mo_oauth_handler->get_resource_owner_from_id_token(
							$id_token,
							isset( $currentapp['discovery'] ) ? $currentapp['discovery'] : '',
							isset( $currentapp['clientid'] ) ? $currentapp['clientid'] : '',
							$expected_nonce
						);
						MOOAuth_Debug::mo_oauth_log( 'Resource Owner Response => ' . wp_json_encode( $resource_owner ) );
					}
				} else {
					MOOAuth_Debug::mo_oauth_log( 'OAuth Flow' );
					$access_token_url = $currentapp['accesstokenurl'];
					if ( ! isset( $currentapp['send_headers'] ) ) {
						$currentapp['send_headers'] = false;
					}
					if ( ! isset( $currentapp['send_body'] ) ) {
						$currentapp['send_body'] = false;
					}

					$access_token = $mo_oauth_handler->get_access_token( $access_token_url, 'authorization_code', $currentapp['clientid'], $currentapp['clientsecret'], sanitize_text_field( wp_unslash( $_GET['code'] ) ), $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.

					if ( ! $access_token ) {
						MOOAuth_Debug::mo_oauth_log( 'Access Token Response => ERROR : Invalid token received.' );
						exit( 'Invalid token received.' );
					}

					if ( substr( $resource_owner_details_url, -1 ) === '=' ) {
						$resource_owner_details_url .= $access_token;
					}
					MOOAuth_Debug::mo_oauth_log( 'Token Response Recieved => ' . $access_token );
					$resource_owner = $mo_oauth_handler->get_resource_owner( $resource_owner_details_url, $access_token );
					MOOAuth_Debug::mo_oauth_log( 'Resource Owner Response => ' );
					MOOAuth_Debug::mo_oauth_log( $resource_owner );
				}

				$username = '';
				$email    = '';
				update_option( 'mo_oauth_attr_name_list', $resource_owner );
				// Test Configuration.
				if ( isset( $_COOKIE['mo_oauth_test'] ) && sanitize_text_field( wp_unslash( $_COOKIE['mo_oauth_test'] ) ) ) {
					setcookie( 'mo_oauth_test', false, time() + 3600, '/', '', true, true );
					echo '<div style="font-family:Calibri;padding:0 3%;color:012970;">';
					echo '<style>table{border-collapse:collapse;color:#012970;}th{background-color: #c6d8f6bd; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#012970;}tr:nth-child(odd) {background-color: #e4eeff;}td{padding:8px;border-width:1px; border-style:solid; border-color:#012970;word-break: break-all;}</style>';
					echo '<h2>' . esc_html__( 'Test Configuration', 'miniorange-login-with-eve-online-google-facebook' ) . '</h2><table><tr><th>' . esc_attr__( 'Attribute Name', 'miniorange-login-with-eve-online-google-facebook' ) . '</th><th>' . esc_attr__( 'Attribute Value', 'miniorange-login-with-eve-online-google-facebook' ) . '</th></tr>';
					mooauth_client_testattrmappingconfig( '', $resource_owner );
					$app = array_values( get_option( 'mo_oauth_apps_list' ) )[0];
					if ( isset( $app['username_attr'] ) ) {
						$username_attr_mapping = $app['username_attr'];
					} else {
						$username_attr_mapping = false;
					}
					echo '</table>';
					echo '<div style="padding: 10px;"></div><input style="padding:7px 12px;width:100px;background: #012970 none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA; inset;color: #FFF;"type="button" value="Done" onClick="self.close();">&emsp;';
					echo '</div>';

					exit();
				}

				if ( ! empty( $username_attr ) ) {
					$username = mooauth_client_getnestedattribute( $resource_owner, $username_attr );
					MOOAuth_Debug::mo_oauth_log( 'Username received.=>' . $username );
				}

				if ( empty( $username ) || '' === $username ) {
					MOOAuth_Debug::mo_oauth_log( 'Username not received. Check your Attribute Mapping configuration.' );
					exit( 'Username not received. Check your <b>Attribute Mapping</b> configuration.' );
				}

				if ( ! empty( $email_attr ) ) {
					$email = mooauth_client_getnestedattribute( $resource_owner, $email_attr );
					MOOAuth_Debug::mo_oauth_log( 'Email received.=>' . $email );
				}
				$user = get_user_by( 'login', $username );
				if ( ! $user && ! empty( $email_attr ) ) {
					$user = get_user_by( 'email', $email );
				}

				if ( $user ) {
					$user_id = $user->ID;

					if ( in_array( 'administrator', $user->roles, true ) && ! $allow_admin_sso ) {
						mooauth_deny_sso_login( 'WPO005: Invalid Login attempt. Please login using email and password.' );
					}

					// Whenever the IdP asserts an email for this login (email_attr is
					// mapped), verify it against the matched account for EVERY user,
					// not only administrators: matching solely on a claim value
					// (username or email) with no cross-check would let a
					// forged/mismatched claim log in as any existing account. This
					// used to run only for administrators. App configs that map only
					// a username attribute (no email_attr) have no email signal to
					// check here and are unaffected, exactly as before.
					if ( ! empty( $email_attr ) ) {
						$current_user_email           = $user->user_email;
						$mo_oauth_email_verify_config = get_option( 'mo_oauth_login_settings_option' );
						$mo_oauth_email_verify_check  = isset( $mo_oauth_email_verify_config['mo_oauth_email_verify_check'] ) ? $mo_oauth_email_verify_config['mo_oauth_email_verify_check'] : false;

						if ( strtolower( $current_user_email ) !== strtolower( $email ) ) {
							mooauth_deny_sso_login( 'Error : WPO01 Invalid login attempt. Asserted email does not match the matched account.' );
						}

						if ( $mo_oauth_email_verify_check ) {

							$idp_email_verified_key = isset( $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_key'] )
							? $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_key']
							: 'email_verified';

							$idp_email_verified_value = isset( $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_value'] )
							? $mo_oauth_email_verify_config['mo_oauth_idp_email_verified_value']
							: '1';

							if ( isset( $resource_owner[ $idp_email_verified_key ] ) ) {
								$email_verified = $resource_owner[ $idp_email_verified_key ];
								if ( (string) $email_verified !== (string) $idp_email_verified_value ) {
									mooauth_deny_sso_login( 'Error: wpoauth:002 - Email verification failed. The IdP did not report the asserted email as verified.' );
								}
							}
						}

						wp_update_user(
							array(
								'ID'         => $user_id,
								'user_email' => $email,
							)
						);
					}
				} else {
					if ( mooauth_migrate_customers() ) {
						$user = mooauth_looped_user( $username );
					} else {
						$user = mooauth_handle_user_registration( $username, $email );
					}
				}
				if ( $user ) {
					wp_set_current_user( $user->ID );
					wp_set_auth_cookie( $user->ID );

					$redirect_to = mooauth_get_sso_redirect_to();
					if ( has_action( 'mo_hack_login_session_redirect' ) ) {
						$token    = mooauth_gen_rand_str();
						$password = mooauth_gen_rand_str();
						$config   = array(
							'user_id'       => $user->ID,
							'user_password' => $password,
						);
						set_transient( $token, $config );
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						do_action( 'mo_hack_login_session_redirect', $user, $password, $token, $redirect_to );
					}
					$user = get_user_by( 'ID', $user->ID );
					do_action( 'wp_login', $user->user_login, $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					MOOAuth_Debug::mo_oauth_log( 'User logged in, login cookie setted.' );

					wp_safe_redirect( $redirect_to );
					exit;
				}
			} catch ( Exception $e ) {

				// Failed to get the access token or user details.

				MOOAuth_Debug::mo_oauth_log( $e->getMessage() );
				exit( esc_attr( $e->getMessage() ) );

			}
		} else { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			if ( isset( $_REQUEST['error_description'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
				MOOAuth_Debug::mo_oauth_log( 'Authorization Response Recieved => ERROR : ' . sanitize_text_field( wp_unslash( $_REQUEST['error_description'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
				exit( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['error_description'] ) ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			} elseif ( isset( $_REQUEST['error'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
				MOOAuth_Debug::mo_oauth_log( 'Authorization Response Recieved => ERROR : ' . sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
				exit( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			}
			MOOAuth_Debug::mo_oauth_log( 'Authorization Response Recieved => ERROR : Invalid response' );
			exit( 'Invalid response' );
		}
	}
}

/**
 * Handle user registration.
 *
 * @param mixed $username username for the current user.
 * @param mixed $email email for the current user.
 */
function mooauth_handle_user_registration( $username, $email = null ) {
	$random_password = wp_generate_password( 10, false );

	if ( strlen( $username ) > 60 ) {
		mooauth_deny_sso_login( 'ERROR : The username received has a length greater than 60 characters.' );
	}

	if ( preg_match( '/[+,\/~!#$%^&*():={}|;">?\/\\\\\/\\\\\']/', $username ) ) {
		mooauth_deny_sso_login( 'ERROR : The username received has a special character' );
	}

	$user_create_response = wp_create_user( $username, $random_password, $email );
	if ( is_wp_error( $user_create_response ) ) {
		// The raw WP_Error text distinguishes "that username already exists"
		// from "that email address is already used", which is the same
		// existence oracle the denial messages above used to leak, so only the
		// generic response is emitted and the detail goes to the debug log.
		mooauth_deny_sso_login( 'ERROR : User registration failed => ' . $user_create_response->get_error_message() );
	}

	$user = get_user_by( 'login', $username );
	wp_update_user( array( 'ID' => $user_create_response ) );
	return $user;
}

/**
 * Handler User registration.
 *
 * @param mixed $temp_var temp var.
 */
function mooauth_looped_user( $temp_var ) {
	return mooauth_looped_redirect( $temp_var );
}

/**
 * Display attribute mapping in Test Configuration.
 *
 * @param mixed  $nestedprefix nested prefix.
 * @param mixed  $resource_owner_details resource owner details of the current user.
 * @param string $tr_class_prefix prefix for tr class.
 */
function mooauth_client_testattrmappingconfig( $nestedprefix, $resource_owner_details, $tr_class_prefix = '' ) {

	$username_value = '';
	foreach ( $resource_owner_details as $key => $resource ) {
		if ( is_array( $resource ) || is_object( $resource ) ) {
			if ( ! empty( $nestedprefix ) ) {
				$nestedprefix .= '.';
			}
			mooauth_client_testattrmappingconfig( $nestedprefix . $key, $resource, $tr_class_prefix );
			$nestedprefix = rtrim( $nestedprefix, '.' );
		} else {
			echo '<tr class="' . esc_attr( $tr_class_prefix ) . 'tr"><td class="' . esc_attr( $tr_class_prefix ) . 'td">';
			if ( ! empty( $nestedprefix ) ) {
				$key = $nestedprefix . '.' . $key;
			}
			echo esc_html( $key ) . '</td><td class="' . esc_attr( $tr_class_prefix ) . 'td">' . esc_html( $resource ) . '</td></tr>';

			$appslist       = get_option( 'mo_oauth_apps_list' );
			$currentapp     = null;
			$currentappname = null;
			if ( is_array( $appslist ) ) {
				foreach ( $appslist as $currentappname => $currentapp ) {
					break;
				}
			}
			if ( strpos( $username_value, 'username' ) === false ) {
				if ( strpos( $key, 'username' ) !== false ) {
					$username_value = $key;
				} elseif ( strpos( $key, 'email' ) !== false && filter_var( $resource, FILTER_VALIDATE_EMAIL ) ) {
					$username_value = $key;
				}
			}
		}
	}

	if ( ! isset( $currentapp['username_attr'] ) && $username_value ) {
		$currentapp['username_attr'] = $username_value;
		$appslist[ $currentappname ] = $currentapp;
		update_option( 'mo_oauth_apps_list', $appslist );
	}
}

/**
 * Get nested attribute.
 *
 * @param mixed $resource resource owner info.
 * @param mixed $key attriubte key.
 */
function mooauth_client_getnestedattribute( $resource, $key ) {
	if ( '' === $key ) {
		return '';
	}

	// Check if the key exists directly in the resource.
	if ( isset( $resource[ $key ] ) ) {
		return $resource[ $key ];
	}

	// Handle nested keys.
	if ( strpos( $key, '.' ) !== false ) {
		$keys        = explode( '.', $key );
		$current_key = array_shift( $keys );

		if ( count( $keys ) > 0 ) {
			if ( isset( $resource[ $current_key ] ) ) {
				return mooauth_client_getnestedattribute( $resource[ $current_key ], implode( '.', $keys ) );
			}
		} else {
			if ( isset( $resource[ $current_key ] ) ) {
				return $resource[ $current_key ];
			}
		}
	}
	return null;
}

/**
 * Handle user registration.
 *
 * @param mixed $ejhi temp var.
 */
function mooauth_looped_redirect( $ejhi ) {
	$user = mooauth_handle_user_registration( $ejhi );
	return $user;
}

/**
 * Get prefix.
 *
 * @param mixed $type type of variable.
 * @return array
 */
function mooauth_client_get_proper_prefix( $type ) {
	$letter = substr( $type, 0, 1 );
	$vowels = array( 'a', 'e', 'i', 'o', 'u' );
	return ( in_array( $letter, $vowels, true ) ) ? ' an ' . $type : ' a ' . $type;
}

/**
 * Register widget.
 */
function mooauth_register_widget() {
	register_widget( 'mooauth_widget' );
}

/**
 * Check if DOING_AJAX is defined.
 */
function mooauth_client_is_ajax_request() {
	return defined( 'DOING_AJAX' ) && DOING_AJAX;
}

/**
 * Valid html
 *
 * Helper function for escaping.
 *
 * @param array $args HTML to add to valid args.
 *
 * @return array valid html.
 **/
function mooauth_get_valid_html( $args = array() ) {
	$retval = array(
		'strong' => array(),
		'em'     => array(),
		'b'      => array(),
		'i'      => array(),
		'a'      => array(
			'href'   => array(),
			'target' => array(),
		),
	);
	if ( ! empty( $args ) ) {
		return array_merge( $args, $retval );
	}
	return $retval;
}

/**
 * Check for REST API call.
 *
 * @return [type]
 */
function mooauth_client_is_rest_api_call() {
	return ! empty( $_SERVER['REQUEST_URI'] ) ? strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-json' ) !== false : false;
}

/**
 * Generate random string.
 *
 * @param int $length length of the string to be generated.
 * @return string
 */
function mooauth_gen_rand_str( $length = 10 ) {
	$characters        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characters_length = strlen( $characters );
	$random_string     = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
	}
	return $random_string;
}

	add_action( 'widgets_init', 'mooauth_register_widget' );
	add_action( 'init', 'mooauth_login_validate' );

/**
 * Get client IP address.
 *
 * Only the real connection IP ($_SERVER['REMOTE_ADDR']) is used. Proxy headers
 * such as X-Forwarded-For, Client-IP and Forwarded are deliberately NOT read:
 * they are supplied by the client, are trivially spoofable, and trusting them
 * would let an attacker bind an OAuth state parameter to a victim's IP and
 * defeat the CSRF/IP-binding check in mooauth_validate_state().
 *
 * @return string Validated client IP address, or 'UNKNOWN' if unavailable.
 */
function mooauth_get_client_ip() {
	$ipaddress = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) ) : '';

	if ( ! filter_var( $ipaddress, FILTER_VALIDATE_IP ) ) {
		$ipaddress = 'UNKNOWN';
	}

	return $ipaddress;
}

/**
 * Resolve where the user should be sent after a successful SSO login.
 *
 * @return string Safe redirect URL.
 */
function mooauth_get_sso_redirect_to() {
	$redirect_to = home_url();

	if ( ! empty( $_COOKIE['mo_oauth_redirect_to'] ) ) {
		$stored = wp_validate_redirect( esc_url_raw( wp_unslash( $_COOKIE['mo_oauth_redirect_to'] ) ), '' );
		if ( ! empty( $stored ) ) {
			$redirect_to = $stored;
		}
		// One-time use: clear the stored URL now that we've consumed it.
		setcookie( 'mo_oauth_redirect_to', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	}

	return $redirect_to;
}

/**
 * Validate OAuth state parameter.
 * Expected format: appname|timestamp|ip_hmac|state_nonce_hmac
 *
 * Three independent checks must all pass:
 *   1. Freshness  - the embedded timestamp is under 5 minutes old.
 *   2. Issuance   - a server-side transient proves this site minted the state,
 *                   and consuming it makes the state single-use (anti-replay).
 *   3. Session    - the nonce HMAC matches the one in the HttpOnly state cookie,
 *                   binding the callback to the browser that began the login
 *                   (RFC 6749 s10.12, RFC 9700 s4.7).
 *
 * The ip_hmac field is retained in the wire format for compatibility but is not
 * used to authenticate the callback.
 *
 * @param string $state_encoded Base64 encoded state parameter.
 * @return array Decoded state data, or wp_die() if invalid.
 */
function mooauth_validate_state( $state_encoded ) {
	$state_string = base64_decode( $state_encoded ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Base64 decode will be required for fetching appname from state.

	if ( ! $state_string ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : Invalid state parameter format.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	$state_parts = explode( '|', $state_string );

	if ( count( $state_parts ) !== 4 ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : Invalid state parameter structure.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	$appname   = $state_parts[0];
	$timestamp = $state_parts[1];
	// $state_parts[2] is the legacy IP HMAC. It is deliberately NOT used as an
	// authenticator: client IPs are shared behind reverse proxies and NAT, so they
	// cannot identify a browser session. The field is still written by the request
	// builder so the state/cookie wire format stays unchanged.
	$state_nonce_hmac_request = $state_parts[3];

	$hmac_secret = wp_salt( 'auth' );

	$current_time = time();
	$state_time   = intval( $timestamp );
	$time_diff    = $current_time - $state_time;

	if ( $time_diff > 300 ) { // 5 minutes = 300 seconds
		MOOAuth_Debug::mo_oauth_log( 'ERROR : State parameter expired. Time difference: ' . $time_diff . ' seconds.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	$timestamp_hmac = hash_hmac( 'sha256', $timestamp, $hmac_secret );

	// 1. Server-side proof of issuance. A missing record means the state was never
	// minted by this site, has expired, or has already been consumed.
	$state_transient_key = 'mo_oauth_state_' . hash( 'sha256', $state_nonce_hmac_request );
	$issued_appname      = get_transient( $state_transient_key );

	if ( false === $issued_appname ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : State parameter is unknown, expired, or has already been used.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	// Consume it immediately so a captured callback URL cannot be replayed.
	delete_transient( $state_transient_key );

	if ( ! hash_equals( (string) $issued_appname, (string) $appname ) ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : State appname does not match the value recorded at issuance.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	// 2. Bind the callback to the browser that started the login (RFC 6749 s10.12,
	// RFC 9700 s4.7). The raw nonce is held only in an HttpOnly cookie, so a callback
	// URL opened in any other browser has no matching cookie and is rejected. There is
	// deliberately no IP-based fallback: an attacker could simply omit the cookie to
	// reach it, and client IPs are shared behind proxies and NAT anyway.
	$cookie_name  = 'mo_oauth_sso_state';
	$cookie_state = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ?? '' ) );

	if ( empty( $cookie_state ) ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : State cookie missing - cannot bind the callback to the originating browser session.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	$cookie_parts = explode( '|', base64_decode( $cookie_state ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Base64 decode is required to read the nonce back out of the state cookie.

	if ( count( $cookie_parts ) !== 4 ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : Invalid state cookie structure.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	$state_nonce_hmac_cookie = hash_hmac( 'sha256', $cookie_parts[3], $timestamp_hmac );

	// Timing-safe: the cookie value is a secret, so never leak it to the debug log.
	if ( ! hash_equals( $state_nonce_hmac_cookie, $state_nonce_hmac_request ) ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : State parameter does not match the state cookie.' );
		wp_die( 'Authentication failed. Please try again.' );
	}

	// One-time use: clear the cookie now that it has been consumed.
	setcookie( $cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

	return array(
		'appname'   => $appname,
		'timestamp' => $state_time,
		'ip'        => mooauth_get_client_ip(),
	);
}

/**
 * Verifies an OpenID 2.0 authentication response via the stateless
 * check_authentication round-trip (OpenID Authentication 2.0, section
 * 11.4.2), so the response's claims (e.g. openid.claimed_id) can only be
 * trusted once the provider itself confirms it signed them. Always posts
 * to the app's own configured authorizeurl rather than the endpoint
 * asserted in the response, since the latter is attacker-controlled.
 *
 * @param array $currentapp Configured app data.
 * @return bool True only if the provider confirms the assertion is valid.
 */
function mooauth_verify_openid_assertion( $currentapp ) {
	$params = array();
	foreach ( $_REQUEST as $key => $value ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verifying the OpenID provider's assertion signature, not a form submission.
		if ( 0 === strpos( $key, 'openid_' ) && is_string( $value ) ) {
			$openid_key            = 'openid.' . substr( $key, 7 );
			$params[ $openid_key ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}

	if ( empty( $params['openid.assoc_handle'] ) || empty( $params['openid.sig'] ) || empty( $params['openid.signed'] ) ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : OpenID response missing assoc_handle/sig/signed - cannot verify assertion.' );
		return false;
	}

	$params['openid.mode'] = 'check_authentication';

	$response = wp_remote_post(
		$currentapp['authorizeurl'],
		array(
			'timeout'   => 20,
			'body'      => $params,
			'sslverify' => MO_OAuth_Utils::get_ssl_verify_setting( $currentapp['authorizeurl'] ),
		)
	);

	if ( is_wp_error( $response ) ) {
		MOOAuth_Debug::mo_oauth_log( 'ERROR : OpenID check_authentication request failed - ' . $response->get_error_message() );
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	MOOAuth_Debug::mo_oauth_log( 'OpenID check_authentication response => ' . $body );

	return (bool) preg_match( '/is_valid\s*:\s*true/i', $body );
}
?>
