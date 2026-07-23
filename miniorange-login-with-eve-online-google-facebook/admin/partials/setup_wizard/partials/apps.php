<?php
/**
 * Setup Wizard Apps
 *
 * @package    setup-wizard
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Setup wizard step 1 - show app list
 */
function mooauth_client_setup_apps() {
	$defaultappsjson = MO_OAuth_Utils::get_default_apps();
	$custom_apps     = array();

	echo '<center><h4>OAuth / OpenID Connect Providers</h4></center>
		<form id="mo_setup_wizard_form">
		<div class="_3HxD">
			<div class="q8lC">
				<span class="iconSearch _2Ysz fa fa-search"></span>
				<input type="text" name="mo_oauth_search" autofocus=true autocomplete=false value id="mo_oauth_client_default_apps_search" onkeyup="mooauth_client_default_apps_input_filter()" placeholder="' . esc_html__( 'Search Your Provider', 'miniorange-login-with-eve-online-google-facebook' ) . '">
			</div>
		</div>
	<!-- app list -->
	<div id="mo_oauth_client_search_res"></div>
	<div id="mo_oauth_client_searchable_apps">
		<ui id="mo_oauth_client_default_apps" class="mo-flex-container mo-wrap">';
	foreach ( $defaultappsjson as $app_id => $application ) {
		if ( 'other' === $app_id || 'openidconnect' === $app_id || 'oauth1' === $app_id || 'oauth2.1' === $app_id ) {
			$custom_apps[ $app_id ] = $application;
			// continue.
		}

		if ( 'oauth2.1' === $app_id || 'neoncrm' === $app_id || 'mindbody' === $app_id || 'imis' === $app_id || 'classlink' === $app_id || 'vendesta' === $app_id || 'clever' === $app_id || 'orcid' === $app_id ) {
			$image_name      = $application->image;
			$tooltip_message = 'oauth2.1' === $app_id ? 'OAuth 2.1 protocol is supported in our paid plugin versions. You can reach out to us to unlock this functionality.' : esc_html( $application->label ) . ' application is available in the Paid Version of the plugin. <a class ="skip-this mo-w-trynow" href="' . esc_url( 'https://sandbox.miniorange.com/?mo_plugin=oauth_client' ) . '" target="_blank">Try Now</a> the paid version.';
			$image_url       = plugins_url( '/partials/apps/images/' . $image_name, dirname( dirname( dirname( __FILE__ ) ) ) );
			echo '<li data-appid="' . esc_attr( $app_id ) . '" class="mo-flex-item mo_oauth_tooltip "><span class="mo_oauth_tooltiptext">' . wp_kses_post( $tooltip_message ) . '</span><a class = "mo_oauth_client_search_idp" href="#"><img class="mo_oauth_two_point_one_app_icon" src="' . esc_url( $image_url ) . '"><img class="mo_oauth_pro_icon" src="' . esc_url( plugins_url( '/partials/apps/images/pro.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '">';
		} else {
			echo '<li data-appid="' . esc_attr( $app_id ) . '" class="mo-flex-item"><a class = "mo_oauth_client_search_idp" ' . ( 'cognito' === $app_id ? 'id=vip-default-app' : '' ) . ' href="#" ><img class="mo_oauth_client_default_app_icon" src=" ' . esc_url( plugins_url( '/partials/apps/images/' . $application->image, dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '">';
		}

		echo ' <br><p>' . esc_attr( $application->label ) . '</p></a><input type="hidden" value="' . esc_html( wp_json_encode( $application ) ) . '"></li>';
	}

	echo '<li class="mo-flex-item hidden-flex-item"></li><li class="mo-flex-item hidden-flex-item"></li><li class="mo-flex-item hidden-flex-item"></li><li class="mo-flex-item hidden-flex-item"></li></ui></div></form>';
}


