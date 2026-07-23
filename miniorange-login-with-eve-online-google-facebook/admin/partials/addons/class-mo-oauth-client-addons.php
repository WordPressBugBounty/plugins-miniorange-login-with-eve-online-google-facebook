<?php
/**
 * Add-ons
 *
 * @package    add-ons
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Display Add-ons
 */
class MO_OAuth_Client_Addons {
	/**
	 * Array for all the add-ons
	 *
	 * @var all_addons
	 */
	public static $all_addons = array(
		array(
			'tag'             => 'page-restriction',
			'title'           => 'Page & Post Restriction',
			'desc'            => 'Allows to restrict access to WordPress pages/posts based on user roles and their login status, thereby preventing them from unauthorized access.',
			'img'             => 'images/page-restriction.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-page-restriction',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'scim',
			'title'           => 'SCIM User Provisioning',
			'desc'            => 'Allows user provisioning with SCIM standard. System for Cross-domain Identity Management is a standard for automating the exchange of user identity information between identity domains, or IT systems.',
			'img'             => 'images/scim.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-user-provisioning',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'learndash',
			'title'           => 'LearnDash Integration',
			'desc'            => 'Integrates LearnDash with your IDP by mapping the users to LearnDash groups based on the attributes sent by your IDP.',
			'img'             => 'images/learndash.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-learndash-integrator',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'buddypress',
			'title'           => 'BuddyPress Integrator',
			'desc'            => 'Allows to integrate user information received from OAuth/OpenID Provider with the BuddyPress profile.',
			'img'             => 'images/buddypress.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-buddypress-integrator',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'media',
			'title'           => 'Media Restriction',
			'desc'            => 'miniOrange Media Restriction add-on restrict unauthorized users from accessing the media files on your WordPress site.',
			'img'             => 'images/media.jpg',
			'link'            => 'https://plugins.miniorange.com/wordpress-media-restriction',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'jwt-single-sign-on-sso',
			'title'           => 'JWT Single Sign-On (SSO)',
			'desc'            => 'Users can easily log in to your WordPress site from other applications or mobile web views, making for a seamless login experience across all of your platforms.',
			'img'             => 'images/json.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-login-using-jwt-single-sign-on-sso',
			'in_allinclusive' => false,
		),

		array(
			'tag'             => 'paid-memberships-pro',
			'title'           => 'Paid Memberships Pro Integrator',
			'desc'            => 'Allows you to assign membership levels to your users based on their groups in your Identity Provider.',
			'img'             => 'images/member-login.png',
			'link'            => 'https://plugins.miniorange.com/paid-membership-pro-integrator',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'memberpress',
			'title'           => 'MemberPress Integration',
			'desc'            => 'Map the SSO users to MemberPress Membership levels as per the attributes sent by your Identity Provider.',
			'img'             => 'images/member-login.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-memberpress-integrator',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'woocommerce',
			'title'           => 'WooCommerce Integrator',
			'desc'            => 'Allows you to map the attributes sent by your IdP (Identity Provider) to the appropriate checkout fields in WooCommerce. This automatically pre-populates the attributes in the WooCommerce checkout page.',
			'img'             => 'images/woocommerce.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-woocommerce-integrator',
			'in_allinclusive' => false,
		),

		array(
			'tag'             => 'Cognito',
			'title'           => 'Cognito Integration',
			'desc'            => 'WordPress Cognito Integrator provides integration of Cognito SDKs in WordPress for handling seamless Login, Registration, Profile Update, Password Reset of AWS Cognito users from WordPress.',
			'img'             => 'images/Cognito.png',
			'link'            => 'https://plugins.miniorange.com/aws-cognito-wordpress-single-sign-on-integration',
			'in_allinclusive' => false,
		),

		array(
			'tag'             => 'discord',
			'title'           => 'Discord Integrator',
			'desc'            => 'Allows you to perform User Restriction and Role Mapping for the users who are performing Single Sign-On using a Discord user profile on your WordPress site.',
			'img'             => 'images/discord.png',
			'link'            => 'https://plugins.miniorange.com/discord-wordpress-single-sign-on-integration',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'guest-user-login',
			'title'           => 'Guest User Login',
			'desc'            => 'Allows you to login the users to the WordPress site using the IdP credentials without creating the users in the site',
			'img'             => 'images/guest-user.png',
			'link'            => 'https://plugins.miniorange.com/guest-user-login',
			'in_allinclusive' => false,
		),

		array(
			'tag'             => 'profile_pic',
			'title'           => 'Profile Picture Add-on',
			'desc'            => 'Maps raw image data or URL received from your Identity Provider into Gravatar for the user.',
			'img'             => 'images/profile_pic.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-profile-picture-map',
			'in_allinclusive' => false,
		),

		array(
			'tag'             => 'attribute',
			'title'           => 'Attribute Based Redirection',
			'desc'            => 'ABR add-on helps you to redirect your users to different pages after they log into your site, based on the attributes sent by your Identity Provider.',
			'img'             => 'images/attribute-icon.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-attribute-based-redirection-restriction',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'Azure',
			'title'           => 'Azure Integration',
			'desc'            => 'WordPress Azure integration provides integration of Azure AD Graph APIs in WordPress for handling seamless Login, Registration, Profile Update, Password Reset of Azure AD and Azure B2C users from WordPress.',
			'img'             => 'images/azure.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-azure-integration',
			'in_allinclusive' => true,
		),

		array(
			'tag'             => 'jwetoken',
			'title'           => 'SSO Into Multiple Apps',
			'desc'            => 'Set up SSO using JWE BASED COOKIE TECHNIQUE, which allow users to perform SSO into multiple applications without entering the credentials again if they are hosted on the same domain/subdomain.',
			'img'             => 'images/jwe_token.jpg',
			'link'            => 'https://plugins.miniorange.com/sso-in-wordpress-and-applications-using-jwe-token-in-cookie',
			'in_allinclusive' => false,
		),

		array(
			'tag'             => 'login-audit',
			'title'           => 'SSO Login Audit',
			'desc'            => 'SSO Login Audit captures all the SSO users and will generate the reports.',
			'img'             => 'images/report.png',
			'link'            => 'https://plugins.miniorange.com/wordpress-sso-login-audit',
			'in_allinclusive' => true,
		),
	);
	/**
	 * Array the add-ons path
	 *
	 * @var recommended_addons_path
	 */
	public static $recommended_addons_path = array(

		'learndash'            => 'sfwd-lms/sfwd_lms.php',
		'buddypress'           => 'buddypress/bp-loader.php',
		'paid-memberships-pro' => 'paid-memberships-pro/paid-memberships-pro.php',
		'memberpress'          => 'memberpress/memberpress.php',
		'woocommerce'          => 'woocommerce/woocommerce.php',
	);


	/**
	 * Display Add-ons Page
	 */
	public static function addons() {
		self::addons_page();
	}

	/**
	 * Display recommended add-ons
	 */
	public static function addons_page() {

		$addons_recommended = array();

		?>

<input type="hidden" value="<?php echo esc_attr( mooauth_is_customer_registered() ); ?>" id="mo_customer_registered_addon">

<a  id="mobacktoaccountsetup_addon" class="mo_oauth_hidden" href="<?php echo ! empty( $_SERVER['REQUEST_URI'] ) ? esc_attr( add_query_arg( array( 'tab' => 'account' ), sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : ''; ?>">Back</a>

<form class="mo_oauth_hidden" id="loginform_addon" action="<?php echo esc_attr( get_option( 'host_name' ) ) . '/moas/login'; ?>" target="_blank" method="post">
			<?php wp_nonce_field( 'mo_oauth_loginform_addon_nonce', 'mo_oauth_loginform_addon_field' ); ?>
			<input type="email" name="username" value="<?php echo esc_attr( get_option( 'mo_oauth_admin_email' ) ); ?>"/>
			<input type="text" name="redirectUrl"
	value="<?php echo 'https://plugins.miniorange.com/go/oauth-2fa-buy-now-payment'; ?>"/>
			<input type="text" name="requestOrigin" id="requestOrigin"/>
</form>
		<?php
		foreach ( self::$recommended_addons_path as $key => $value ) {
			if ( is_plugin_active( $value ) ) {
				$addon                        = $key;
				$addons_recommended[ $addon ] = $addon;
			}
		}

		?>
<div class="mo_oauth_card">
	<div class="mo_oauth_card_header"><h3><?php esc_html_e( 'Add-ons', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3></div>
	<div class="mo_oauth_card_body">
		<?php
		if ( count( $addons_recommended ) > 0 ) {
			?>
	<div class="mo_table_layout">
	<b><p class="mo_oauth_x_addons_1"><?php esc_attr_e( 'Recommended Add-ons for you:', 'miniorange-login-with-eve-online-google-facebook' ); ?></p></b>
	<div class="mo_oauth_outermost-div">
	<div class="row-view">
			<?php
			foreach ( $addons_recommended as $key => $value ) {
				self::get_single_addon_cardt( $value );
			}
			?>
	</div>
</div>
</div>
			<?php
		}
		?>

<div class="mo_table_layout mo_oauth_outer_div" id="mo_oauth_register">
<div class="mo_oauth_customization_header">
<div class="mo_oauth_signing_heading">Check out our Add-ons :</div></div>
<div class="mo_oauth_outermost-div">

		<?php

		$available_addons = array();
		foreach ( self::$all_addons as $key => $value ) {
			// code...
			if ( ! array_search( $value['tag'], $addons_recommended, true ) ) {
				array_push( $available_addons, $value['tag'] );
			}
		}

		$all_addons   = self::$all_addons;
		$total_addons = count( $available_addons );

		for ( $i = 0; $i < $total_addons; $i++ ) {
			?>
			<div class="row-view">
			<?php
			self::get_single_addon_cardt( $available_addons[ $i ] );
			if ( $i + 1 < $total_addons ) {
				self::get_single_addon_cardt( $available_addons[ $i + 1 ] );
				$i++;
			}
			if ( $i + 1 < $total_addons ) {
				self::get_single_addon_cardt( $available_addons[ $i + 1 ] );
				$i++;
			}
			?>
			</div>
			<?php
		}
		?>
</div></div>
	</div>
	</div>

		<?php
	}

	/**
	 * Get the card for an add-on
	 *
	 * @param mixed $tag check for a particular add-on.
	 */
	public static function get_single_addon_cardt( $tag ) {
		foreach ( self::$all_addons as $key => $value ) {
			if ( strpos( $value['tag'], $tag ) !== false ) {
				$addon = $value;
				break;
			}
		}
		if ( isset( $addon ) ) {
			?>
		<div class="grid_view mo_oauth_column_container mo_oauth_x_addons_2">
			<div class="mo_oauth_column_inner mo_oauth_x_addons_3">
			<div class="row mo_oauth_benefits-outer-block">
					<div> 
					<img src="<?php echo esc_url( plugins_url( $addon['img'], __FILE__ ) ); ?>" class="mo_oauth_addon_img">					</div>
				<div class="mo_oauth_addon_headline"><strong class="mo_strong"><p><a  href= "<?php echo isset( $addon['link'] ) ? esc_attr( $addon['link'] ) : ''; ?>" target="_blank" rel="noopener"><?php echo esc_html( $addon['title'] ); ?></a></p></strong></div>
			<p class="mo_oauth_addon_description"><?php echo esc_html( $addon['desc'] ); ?></p>
			<a class="mo_oauth_btn mo_oauth_know_more_button" href= "<?php echo esc_url( $addon['link'] ); ?>" target="_blank" >Know More</a> 
			</div>
			</div>
		</div>
			<?php
		}
	}
}
?>
