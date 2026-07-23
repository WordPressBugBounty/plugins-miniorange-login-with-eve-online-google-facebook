<?php
/**
 * Admin Menu
 *
 * @package    admin-menu
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

require 'class-mo-oauth-client-admin-utils.php';
require 'account' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-admin-account.php';
require 'apps' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-apps.php';
require 'support' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-support.php';
require 'guides' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-attribute-mapping.php';
require 'demo' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-demo.php';
require 'troubleshoot' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-troubleshoot.php';
require 'addons' . DIRECTORY_SEPARATOR . 'class-mo-oauth-client-addons.php';

/**
 * Initialize CSS files
 *
 * @param mixed $hook WordPress hook.
 */
function mooauth_client_plugin_settings_style( $hook ) {
	if ( 'toplevel_page_mo_oauth_settings' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'mo_oauth_admin_style', plugin_dir_url( __DIR__ ) . 'css/admin.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
	wp_enqueue_style( 'mo_oauth_admin_settings_style', plugin_dir_url( __DIR__ ) . 'css/style_settings.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
	wp_enqueue_style( 'mo_oauth_admin_settings_font_awesome', plugin_dir_url( __DIR__ ) . 'css/font-awesome.min.css', array(), '4.7.0' );
	wp_enqueue_style( 'mo_oauth_admin_settings_phone_style', plugin_dir_url( __DIR__ ) . 'css/phone.min.css', array(), '0.0.2' );
	wp_enqueue_style( 'mo_oauth_admin_settings_datatable_style', plugin_dir_url( __DIR__ ) . 'css/jquery.dataTables.min.css', array(), '3.6.0' );
	wp_enqueue_style( 'mo_oauth_admin_settings_inteltelinput_style', plugin_dir_url( __DIR__ ) . 'css/intlTelInput.min.css', array(), '17.0.19' );
	wp_enqueue_style( 'mo_oauth_admin_settings_jquery_ui_style', plugin_dir_url( __DIR__ ) . 'css/jquery-ui.min.css', array(), '1.12.1' );
	wp_enqueue_style( 'mo_oauth_admin_settings_overall_font_style', plugin_dir_url( __DIR__ ) . 'css/fontNunito.min.css', array(), '1.0.0' );
	wp_enqueue_style( 'mo_oauth_theme', plugin_dir_url( __DIR__ ) . 'css/mo-oauth-theme.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
}

/**
 * Initialize JS files
 *
 * @param mixed $hook WordPress hook.
 */
function mooauth_client_plugin_settings_script( $hook ) {
	if ( 'toplevel_page_mo_oauth_settings' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'mo_oauth_admin_script', plugin_dir_url( __DIR__ ) . 'js/admin.min.js', array(), $ver = MO_OAUTH_CSS_JS_VERSION, false );
	wp_enqueue_script( 'mo_oauth_admin_settings_script', plugin_dir_url( __DIR__ ) . 'js/settings.min.js', array(), $ver = MO_OAUTH_CSS_JS_VERSION, false );
	wp_enqueue_script( 'mo_oauth_admin_settings_phone_script', plugin_dir_url( __DIR__ ) . 'js/phone.min.js', array(), $ver = '0.8.3', false );
	wp_enqueue_script( 'mo_oauth_admin_settings_datatable_script', plugin_dir_url( __DIR__ ) . 'js/jquery.dataTables.min.js', array(), $ver = '1.10.20', false );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'mo_oauth_admin_settings_jquery-ui3', includes_url() . 'js/jquery/ui/datepicker.min.js', array(), $ver = false, false );
	wp_enqueue_script( 'mo_oauth_admin_settings_inteltelinput', plugin_dir_url( __DIR__ ) . 'js/intlTelInput.min.js', array(), $ver = '13.0.4', false );
	wp_enqueue_script( 'mo_oauth_theme_script', plugin_dir_url( __DIR__ ) . 'js/mo-oauth-theme.min.js', array( 'jquery' ), MO_OAUTH_CSS_JS_VERSION, true );
	wp_localize_script(
		'mo_oauth_theme_script',
		'moOauthExtractedVars',
		array(
			'siteUrl' => site_url(),
		)
	);
}

/**
 * Display Main Menu
 */
function mooauth_client_main_menu() {
	$today      = gmdate( 'Y-m-d H:i:s' );
	$currenttab = '';
	if ( isset( $_GET['tab'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		$currenttab = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
	}
	MO_OAuth_Client_Admin_Utils::curl_extension_check();
	MO_OAuth_Client_Admin_Menu::show_debug_log_notice( $currenttab );
	MO_OAuth_Client_Admin_Menu::show_menu( $currenttab );
	echo '<div id="mo_oauth_settings">';
	$admin_notice = new MO_OAuth_Admin_Notice();
	$admin_notice->show_notice_message();

		echo '
		<div class="miniorange_container">';

		echo '<div class="mo_oauth_layout_row">
			<div class="mo_oauth_layout_main mo_oauth_content">';

				MO_OAuth_Client_Admin_Menu::show_tab( $currenttab );
				echo '</div>';

				MO_OAuth_Client_Admin_Menu::show_support_sidebar( $currenttab );
				echo '</div>
				<div class="mo_tutorial_overlay" id="mo_tutorial_overlay" hidden></div>
		</div>';
}


/**
 * Migrate Customers
 *
 * @return true|false
 */
function mooauth_migrate_customers() {
	if ( get_option( 'mo_oauth_client_admin_customer_key' ) > 138200 ) {
		return true;
	} else {
		return false;
	}}

/**
 * [Display data based on different tabs]
 */
class MO_OAuth_Client_Admin_Menu {

	/**
	 * Delete log file
	 *
	 * @param string $log_file_path Path to the log file to be deleted.
	 */
	public static function logfile_delete( $log_file_path ) {
		if ( file_exists( $log_file_path ) ) {
			wp_delete_file( $log_file_path );
		}
	}

	/**
	 * Show Menu
	 *
	 * @param mixed $currenttab current tab the user has clicked.
	 */
	public static function show_menu( $currenttab ) {

		if ( get_option( 'mo_debug_check' ) ) {
			update_option( 'mo_debug_check', 0 );
		}

		$log_file_path = MOOAuth_Debug::get_log_file_path();

		$mo_log_enable = get_option( 'mo_debug_enable' );

		$mo_oauth_debug = get_option( 'mo_oauth_debug' );

		$log_dir = dirname( $log_file_path );

		$index_path = trailingslashit( $log_dir ) . 'index.php';
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$credentials = request_filesystem_credentials( site_url() );
		if ( 'on' === $mo_log_enable && ( ! $mo_oauth_debug || ! file_exists( $log_file_path ) ) ) {
			if ( ! $mo_oauth_debug ) {
				update_option( 'mo_oauth_debug', 'mo_oauth_debug' . uniqid() );
				$mo_oauth_debug = get_option( 'mo_oauth_debug' );
				$log_file_path  = MOOAuth_Debug::get_log_file_path();
			}
			if ( ! file_exists( $log_file_path ) ) {
				if ( WP_Filesystem( $credentials ) ) {
					global $wp_filesystem;
					$log_content = 'This is the miniOrange OAuth plugin Debug Log file';
					if ( ! $wp_filesystem->is_dir( $log_dir ) ) {
						$wp_filesystem->mkdir( $log_dir, FS_CHMOD_DIR );
					}
					if ( $wp_filesystem->put_contents( $log_file_path, $log_content, FS_CHMOD_FILE ) ) {
						$wp_filesystem->chmod( $log_file_path, 0644 );
					}
				}
			}
		}
		if ( 'on' === get_option( 'mo_debug_enable' ) && ! file_exists( $index_path ) ) {
			if ( WP_Filesystem( $credentials ) ) {
				global $wp_filesystem;
				$wp_filesystem->put_contents(
					$index_path,
					"<?php\n// Silence is golden.\n",
					0600
				);
			}
		}

		if ( 'licensing' !== $currenttab ) { ?>
		<div class="mo_oauth_plugin_body">
	<div class="container mo_oauth_free_plugin_container">
<?php } ?>
<div class="mo_oauth_header_bar">
		<?php if ( 'licensing' !== $currenttab ) { ?>
	<div class="mo_oauth_plugin_heading">
		<img src="<?php echo esc_attr( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/mini.png">
		<h1 class="mo_oauth_h1 mo_oauth_header_title"><span class="mo_oauth_brand_orange">miniOrange</span> <b>OAuth Single Sign On</b>
		</h1>
		</div>
		<div class="justify-content-end mo_oauth_wrapper">
			<h1 class="mo_oauth_h1">
			<a id="license_upgrade" class="mo-add-new-hover mo_premium-plans-btn mo_oauth_header_link"
			href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>"  target="_blank">
			<img class="mo_oauth_header_link_image mo_oauth_header_link_image_flush" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/prem.png" alt="miniOrange Premium Plans Logo">
			<?php esc_html_e( 'Premium Plans', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
			<a id="faq_button_id" class="mo_generic-btns-on-top mo_oauth_header_link"
			href="<?php echo ! empty( $_SERVER['REQUEST_URI'] ) ? esc_attr( add_query_arg( array( 'tab' => 'troubleshoot' ), sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : ''; ?>"><span>
			<img class="mo_oauth_header_link_image" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) . '/images/troubleshooting.png' ); ?>" alt="miniOrange Troubleshooting Logo"><?php esc_html_e( 'Troubleshooting', 'miniorange-login-with-eve-online-google-facebook' ); ?></span></a>
			<a id="form_button_id" class="mo_generic-btns-on-top mo_oauth_header_link" href="https://wordpress.org/support/plugin/miniorange-login-with-eve-online-google-facebook/"
			target="_blank"><span>
			<img class="mo_oauth_header_link_image" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/ask-questions.png"
				alt="miniOrange Ask Questions Logo">
			<?php esc_html_e( 'Ask questions on our forum', 'miniorange-login-with-eve-online-google-facebook' ); ?></span></a>
			<a id="features_button_id " class="mo_generic-btns-on-top mo_oauth_header_link"
			href="https://developers.miniorange.com/docs/oauth/wordpress/client" target="_blank"><span>
			<img class="mo_oauth_header_link_image" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) . '/images/feature-details.png' ); ?>"
				alt="miniOrange Feature Details Logo"><?php esc_html_e( 'Feature Details', 'miniorange-login-with-eve-online-google-facebook' ); ?></span></a>
	</h1>
	</div>
	<?php } ?>
</div>
</div>
		<?php if ( 'licensing' !== $currenttab ) { ?>
<div id="tab">
	<h2 class="nav-tab-wrapper mo_oauth_nav_tab_wrapper">
		<a id="tab-config" href="admin.php?page=mo_oauth_settings&tab=config"
			class="nav-tab mo_oauth_nav-tab anglebg 
			<?php
			if ( 'config' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Configure OAuth', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-attrmapping" href="admin.php?page=mo_oauth_settings&tab=attributemapping"
			class="nav-tab mo_oauth_nav-tab 
			<?php
			if ( 'attributemapping' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Attribute/Role Mapping', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-signinsettings" href="admin.php?page=mo_oauth_settings&tab=signinsettings"
			class="nav-tab mo_oauth_nav-tab 
			<?php
			if ( 'signinsettings' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Login Settings', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-customization" href="admin.php?page=mo_oauth_settings&tab=customization"
			class="nav-tab mo_oauth_nav-tab  
			<?php
			if ( 'customization' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Login Button Customization', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-user-analytics" href="admin.php?page=mo_oauth_settings&tab=user-analytics"
			class="nav-tab mo_oauth_nav-tab  
			<?php
			if ( 'user-analytics' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'User Analytics', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-requestdemo" href="admin.php?page=mo_oauth_settings&tab=requestfordemo"
			class="nav-tab mo_oauth_nav-tab 
			<?php
			if ( 'requestfordemo' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Trials Available', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-acc-setup" href="admin.php?page=mo_oauth_settings&tab=account"
			class="nav-tab mo_oauth_nav-tab 
			<?php
			if ( 'account' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Account Setup', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
		<a id="tab-addons" href="admin.php?page=mo_oauth_settings&tab=addons"
			class="nav-tab mo_oauth_nav-tab 
			<?php
			if ( 'addons' === $currenttab ) {
				echo 'nav-tab-active mo_oauth_nav-tab-active';}
			?>
			">
					<?php esc_html_e( 'Add-ons', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</a>
	</h2>
	<br>
</div>
			<?php
		}
	}

	/**
	 * Show IDP link
	 *
	 * @param mixed $currenttab current tab user is viewing.
	 */
	public static function show_idp_link( $currenttab ) {
		if ( ( ! get_option( 'mo_oauth_client_show_mo_server_message' ) ) ) {
			?>
<form name="f" method="post" action="" id="mo_oauth_client_mo_server_form">
			<?php wp_nonce_field( 'mo_oauth_mo_server_message_form', 'mo_oauth_mo_server_message_form_field' ); ?>
	<input type="hidden" name="option" value="mo_oauth_client_mo_server_message" />
	<div class="notice notice-info mo_oauth_padded_notice">
		<h4><?php esc_html_e( 'Looking for a User Storage/OAuth Server? We have a B2C Service(Cloud IDP) which can scale to hundreds of millions of consumer identities. You can', 'miniorange-login-with-eve-online-google-facebook' ); ?> <a href="https://idp.miniorange.com/b2c-pricing" target="_blank" rel="noopener"><?php esc_html_e( 'click here', 'miniorange-login-with-eve-online-google-facebook' ); ?></a> <?php esc_html_e( 'to find more about it.', 'miniorange-login-with-eve-online-google-facebook' ); ?></h4>
		<button type="button" class="notice-dismiss" id="mo_oauth_client_mo_server"><span
				class="screen-reader-text">Dismiss this notice.</span>
		</button>
	</div>
</form>
			<?php
		}
		self::mo_oauth_client_check_action_messages();
	}


	/**
	 * Handle admin notices
	 */
	public static function mo_oauth_client_check_action_messages() {
		$notices = get_option( 'mo_oauth_client_notice_messages' );

		if ( empty( $notices ) ) {
			return;
		}
		foreach ( $notices as $key => $notice ) {
			echo '<div class="notice notice-info mo_oauth_padded_notice"><h4>' . esc_attr( $notice ) . '</h4></div>';
		}
	}

	/**
	 * Handle views according to current tab.
	 *
	 * @param mixed $currenttab current tab user is viewing.
	 */
	public static function show_tab( $currenttab ) {
		if ( 'account' === $currenttab ) {
			echo '<div class="mo_oauth_card"><div class="mo_oauth_card_header"><h3>' . esc_html__( 'Account Setup', 'miniorange-login-with-eve-online-google-facebook' ) . '</h3></div><div class="mo_oauth_card_body">';
			if ( get_option( 'mo_oauth_client_verify_customer' ) === 'true' ) {
				MO_OAuth_Client_Admin_Account::verify_password();
			} elseif ( '' !== trim( get_option( 'mo_oauth_admin_email' ) ) && trim( get_option( 'mo_oauth_client_admin_api_key' ) ) === '' && 'true' !== get_option( 'mo_oauth_client_new_registration' ) ) {
				MO_OAuth_Client_Admin_Account::verify_password();
			} else {
				MO_OAuth_Client_Admin_Account::register();
			}
			echo '</div></div>';
		} elseif ( 'customization' === $currenttab ) {
				MO_OAuth_Client_Apps::customization();
		} elseif ( 'user-analytics' === $currenttab ) {
			MO_OAuth_Client_Apps::user_analytics();
		} elseif ( 'signinsettings' === $currenttab ) {
			MO_OAuth_Client_Apps::sign_in_settings();
		} elseif ( 'requestfordemo' === $currenttab ) {
			MO_OAuth_Client_Demo::requestfordemo();
		} elseif ( 'addons' === $currenttab ) {
			MO_OAuth_Client_Addons::addons();
		} elseif ( 'attributemapping' === $currenttab ) {
			MO_OAuth_Client_Apps::attribute_role_mapping();
		} elseif ( 'troubleshoot' === $currenttab ) {
			MO_OAuth_Client_Troubleshoot::troubleshooting();
		} elseif ( '' === $currenttab ) {
			?>
	<a id="goregister" class="mo_oauth_hidden" href="<?php /*WordPress.Security.NonceVerification.Recommended, Ignoring nonce verification because we are fetching data from URL and not on form submission.*/ echo ! empty( $_SERVER['REQUEST_URI'] ) ? esc_attr( add_query_arg( array( 'tab' => 'config' ), sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : ''; ?>">
			<?php
		} else {
			MO_OAuth_Client_Apps::applist();
		}
	}

	/**
	 * Display the debug-log file-size warning notice, just below #wpbody-content
	 * and above the plugin's own header bar, like the rest of the admin notices.
	 * Hidden by default; JS toggles it based on the "Enable Debug Log" checkbox
	 * in the support sidebar.
	 *
	 * @param mixed $currenttab current tab user is viewing.
	 */
	public static function show_debug_log_notice( $currenttab ) {
		if ( 'licensing' === $currenttab ) {
			return;
		}
		?>
		<div id="mo_oauth_debug_log_notice" class="mo_oauth_admin_notice_flex error mo_oauth_hidden">
			<div><p><span class="dashicons dashicons-warning mo_oauth_admin_notice_img" aria-hidden="true"></span> &nbsp;&nbsp;<?php echo wp_kses( __( '<b>Warning:</b> Debug logging is enabled. The log file will continue to grow as debug information is recorded. To avoid unnecessary disk usage, disable debugging when troubleshooting is complete.', 'miniorange-login-with-eve-online-google-facebook' ), array( 'b' => array() ) ); ?></p></div>
			<button type="button" id="mo_oauth_debug_log_notice_close" class="mo_oauth_notice_close" aria-label="<?php esc_attr_e( 'Dismiss', 'miniorange-login-with-eve-online-google-facebook' ); ?>">&times;</button>
		</div>
		<?php
	}

	/**
	 * Display Support Sidebar.
	 *
	 * @param mixed $currenttab current tab user is viewing.
	 */
	public static function show_support_sidebar( $currenttab ) {
		if ( 'licensing' !== $currenttab ) {
			echo '<div class="mo_oauth_layout_sidebar mo_oauth_sidebar">';
			self::show_upsell_card();
			self::show_addon_promo_card();
			if ( 'attributemapping' === $currenttab ) {
				echo esc_html( MO_OAuth_Client_Attribute_Mapping::emit_attribute_table() );
			}
			echo esc_html( MO_OAuth_Client_Support::support() );
			echo '</div>';
		}
	}

	/**
	 * Display the "Upgrade to Premium" gradient card in the sidebar.
	 */
	private static function show_upsell_card() {
		?>
		<div class="mo_oauth_upsell_card">
			<h4><?php esc_html_e( 'Unlock More SSO Features', 'miniorange-login-with-eve-online-google-facebook' ); ?></h4>
			<ul>
				<li><?php esc_html_e( 'Auto Register &amp; Advanced Role Mapping', 'miniorange-login-with-eve-online-google-facebook' ); ?></li>
				<li><?php esc_html_e( 'Multiple OAuth / OpenID Providers', 'miniorange-login-with-eve-online-google-facebook' ); ?></li>
				<li><?php esc_html_e( 'Force Authentication for the Whole Site', 'miniorange-login-with-eve-online-google-facebook' ); ?></li>
			</ul>
			<br>
			<a class="mo_oauth_upsell_button" href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Go Enterprise Now', 'miniorange-login-with-eve-online-google-facebook' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Display a promo card for one real add-on (SSO Session Management) in the sidebar.
	 */
	private static function show_addon_promo_card() {
		if ( ! class_exists( 'MO_OAuth_Client_Addons' ) ) {
			return;
		}
		$addon = null;
		foreach ( MO_OAuth_Client_Addons::$all_addons as $candidate ) {
			if ( 'session' === $candidate['tag'] ) {
				$addon = $candidate;
				break;
			}
		}
		if ( ! $addon ) {
			return;
		}
		?>
		<div class="mo_oauth_addon_promo_card">
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'addons/' . $addon['img'] ); ?>" alt="<?php echo esc_attr( $addon['title'] ); ?>">
			<h4><?php echo esc_html( $addon['title'] ); ?></h4>
			<p><?php echo esc_html( $addon['desc'] ); ?></p>
			<a class="mo_oauth_addon_promo_link" href="<?php echo esc_url( $addon['link'] ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Learn More', 'miniorange-login-with-eve-online-google-facebook' ); ?> &rarr;
			</a>
		</div>
		<?php
	}
}
?>
