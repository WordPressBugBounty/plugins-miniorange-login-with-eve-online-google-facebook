<?php
/**
 * Customization
 *
 * @package    apps
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Display Customizations options for login button
 */
function mooauth_client_customization_ui() {
	wp_enqueue_script( 'mo_oauth_customize_icon_tab', esc_url( plugins_url( 'customization.min.js', __FILE__ ) ), array(), MO_OAUTH_CSS_JS_VERSION, false );
	?>
<div class="mo_oauth_card">
	<div class="mo_oauth_card_header"><h3><?php esc_html_e( 'Login Button Customization', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3></div>
	<div class="mo_oauth_card_body">
		<div id="mo_oauth_customiztion" class="mo_table_layout mo_oauth_app_customization">
		<div class="mo_oauth_customization_header"><div class="mo_oauth_attribute_map_heading mo_oauth_x_customization_1"><b class="mo_oauth_position"><?php esc_html_e( 'Customize Icons ', 'miniorange-login-with-eve-online-google-facebook' ); ?></b> <small><div class="mo_oauth_tooltip" ><span class="mo_oauth_tooltiptext" >STANDARD</span><a href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>"  target="_blank" rel="noopener noreferrer"><span class="mo_oauth_x_customization_2"><img class="mo_oauth_premium-label" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) . '/images/mo_oauth_premium-label.png' ); ?>" alt="miniOrange Standard Plans Logo"></span></a></div></small></div><div class="mo_oauth_tooltip"><span class="mo_tooltiptext">Know how this is useful</span><a class="mo_oauth_x_customization_3" target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/client/login-button-customization" rel="noopener noreferrer">
		<img class="mo_oauth_guide_img" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) . '/images/mo_oauth_info-icon.png' ); ?>" alt="miniOrange Premium Plans Logo" aria-hidden="true"></a></div></div>
	<form id="form-common" name="form-common" class="mo_oauth_customization_font" method="" action="admin.php?page=mo_oauth_settings&tab=customization">
		<div class="mo_oauth_x_customization_4"><h2 id="mo_oauth_customize_icon" class="mo_oauth_switching_tab mo_active_div_css"><?php esc_html_e( 'Customize SSO button', 'miniorange-login-with-eve-online-google-facebook' ); ?></h2><h2 id="mo_oauth_write_custom_code" class="mo_oauth_switching_tab mo_oauth_x_customization_5"><?php esc_html_e( 'Write your custom code', 'miniorange-login-with-eve-online-google-facebook' ); ?></h2></div>
		<div class="mo_oauth_custom_tab mo_oauth_customize_SSO_buttons">
		<div class="mo_oauth_custom_tab_item mo_oauth_custom_tab_flex_grow">
				<div class="mo_oauth_x_customization_6">
					<div class="mo_oauth_custom_tab_item_2">
						<h3 class="mo_oauth_h3_heading"> THEME </h3>
						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_default" name="mo_oauth_icon_theme" value="default" onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())"><span class="mo_oauth_x_customization_7">Default</span></label>
						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_custom" name="mo_oauth_icon_theme" value="custom"  onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())"><span class="mo_oauth_x_customization_7">Custom</span><br>
							<input type="color" class="mo_oauth_custom_tab_margin_2 mo_oauth_x_customization_8" id="mo_oauth_icon_color" name="mo_oauth_icon_color"  onclick="moOauthIconsPreview(getArg())">
						</label>

						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_white" name="mo_oauth_icon_theme" value="white"  onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())"><span class="mo_oauth_x_customization_7">White</span></label>
						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_hover" name="mo_oauth_icon_theme" value="hover"  onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())"><span class="mo_oauth_x_customization_7">Hover</span></label>
						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_custom_hover" name="mo_oauth_icon_theme" value="customhover"  onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())"><span class="mo_oauth_x_customization_7">Custom Hover</span><br>
							<input type="color" class="mo_oauth_custom_tab_margin_2 mo_oauth_x_customization_8" id="mo_oauth_icon_custom_color" name="mo_oauth_icon_custom_color" value="#008ec2" onclick="moOauthIconsPreview(getArg())">
						</label>
						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_smart" name="mo_oauth_icon_theme" value="smart"  onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())" checked="checked"><span class="mo_oauth_x_customization_7">Smart</span><br>
							<input type="color" class="mo_oauth_custom_tab_margin_2 mo_oauth_x_customization_9" id="mo_oauth_icon_smart_color_1" name="mo_oauth_icon_smart_color_1" value="#ff1f4b"  onclick="moOauthIconsPreview(getArg())">
							<input type="color" class="mo_oauth_custom_tab_margin_2 mo_oauth_x_customization_10" id="mo_oauth_icon_smart_color_2" name="mo_oauth_icon_smart_color_2" value="#2008ff" onclick="moOauthIconsPreview(getArg())">
						</label>
						<label>
							<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_theme_previous" name="mo_oauth_icon_theme" value="previous"  onclick="moOauthIconsPreview(getArg()),moOauthThemeSelector(selectLoginTheme())"><span class="mo_oauth_x_customization_7">Previous</span></label>
						</label>
					</div>

					<div class="mo_oauth_custom_tab_item_2">
				<h3 class="mo_oauth_h3_heading"> SHAPE </h3>
				<label>
					<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_shape_round" name="mo_oauth_icon_shape" value="circle"  onclick="moOauthIconsPreview(getArg()),moOauthShapeHandler()"><span class="mo_oauth_x_customization_7">Round</span></label>
				<label>
					<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_shape_oval" name="mo_oauth_icon_shape" value="oval"  onclick="moOauthIconsPreview(getArg()),moOauthShapeHandler()"><span class="mo_oauth_x_customization_7">Round Edge</span></label>
				<label>
					<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_shape_square" name="mo_oauth_icon_shape" value="square"  onclick="moOauthIconsPreview(getArg()),moOauthShapeHandler()"><span class="mo_oauth_x_customization_7">Square</span></label>
				<label>
					<input type="radio" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_shape_longbutton" name="mo_oauth_icon_shape" value="longbutton"  onclick="moOauthIconsPreview(getArg()),moOauthShapeHandler()" checked="checked"><span class="mo_oauth_x_customization_7">Long Button</span></label>
				<hr>
				<h3 class="mo_oauth_h3_heading"> Effect </h3>
				<label>
					<input type="checkbox" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_effect_scale" name="mo_oauth_icon_effect_scale" value="scale"  onclick="moOauthIconsPreview(getArg())" checked="checked"><span class="mo_oauth_x_customization_7">Transform</span></label>
				<label>
					<input type="checkbox" class="mo_oauth_custom_tab_margin_2 " id="mo_oauth_icon_effect_shadow" name="mo_oauth_icon_effect_shadow" value="shadow"  onclick="moOauthIconsPreview(getArg())" checked="checked"><span class="mo_oauth_x_customization_7">Shadow</span></label>

					</div>

				</div>
				<div class="mo_oauth_x_customization_6">
					<div id="mo_oauth_longbutton_parameter" class="mo_oauth_custom_tab_item_3">
						<h3 class="mo_oauth_h3_heading"> Size of the Icons </h3>
						<label class="mo_oauth_custom_tab_margin_3"><span class="mo_oauth_x_customization_11">Height&nbsp;: </span>
						<input type="text" id="mo_oauth_icon_height" name="mo_oauth_icon_height" value="35" class="mo_oauth_x_customization_12">
							<input type="button" id="mo_oauth_height_plus" class="mo_oauth_icon_dimension" value="+" onclick=" moOauthIconsPreview(getArg())">&nbsp;<input type="button" id="mo_oauth_height_minus" class="mo_oauth_icon_dimension" value="-" onclick="moOauthIconsPreview(getArg())">
						</label>
						<label class="mo_oauth_custom_tab_margin_3"><span class="mo_oauth_x_customization_11">Width&nbsp;&nbsp;: </span>
						<input type="text" id="mo_oauth_icon_width" name="mo_oauth_icon_width" value="260" class="mo_oauth_x_customization_12">
							<input type="button" id="mo_oauth_width_plus" class="mo_oauth_icon_dimension" value="+" onclick="moOauthIconsPreview(getArg())">&nbsp;<input type="button" id="mo_oauth_width_minus" class="mo_oauth_icon_dimension" value="-" onclick="moOauthIconsPreview(getArg())">
						</label>
						<label class="mo_oauth_custom_tab_margin_3"><span class="mo_oauth_x_customization_11">Curve &nbsp;&nbsp;: </span>
						<input type="text" id="mo_oauth_icon_curve" name="mo_oauth_icon_curve" value="6" class="mo_oauth_x_customization_12">
							<input type="button" id="mo_oauth_curve_plus" class="mo_oauth_icon_dimension" value="+" onclick=" moOauthIconsPreview(getArg())">&nbsp;<input type="button" id="mo_oauth_curve_minus" class="mo_oauth_icon_dimension" value="-" onclick="moOauthIconsPreview(getArg())">
						</label>
						</div>
					<div id="mo_oauth_button_parameter" class="mo_oauth_custom_tab_item_3 mo_oauth_x_customization_14">
						<h3 class="mo_oauth_h3_heading"> Size of the Icons </h3>
						<label><span class="mo_oauth_x_customization_11">Icon Size : </span>
							<input type="text" id="mo_oauth_icon_size" name=" mo_oauth_icon_size" value="40" class="mo_oauth_x_customization_15">
							<input type="button" id="mo_oauth_icon_plus" class="mo_oauth_icon_dimension" value="+" onclick="moOauthIconsPreview(getArg())">&nbsp;<input type="button" id="mo_oauth_icon_minus" class="mo_oauth_icon_dimension" value="-" onclick="moOauthIconsPreview(getArg())">

						</label>
					</div>
							<div class="mo_oauth_custom_tab_item_3">
						<h3 class="mo_oauth_h3_heading"> Space Between the Icons </h3>
								<label class="mo_oauth_custom_tab_margin_2">
									<input type="text" id="mo_oauth_icon_margin" name=" mo_oauth_icon_margin" value="10" class="mo_oauth_x_customization_12">
									<input type="button" id="mo_oauth_space_icon_plus" class="mo_oauth_icon_dimension" value="+" onclick="moOauthIconsPreview(getArg())">&nbsp;<input type="button" id="mo_oauth_space_icon_minus" class="mo_oauth_icon_dimension" value="-" onclick="moOauthIconsPreview(getArg())">
								</label>
							</div>
				</div>
			</div>
			<div class="mo_oauth_custom_tab_item mo_oauth_custom_tab_item_color">
				<?php
				$active_app = get_option( 'mo_oauth_apps_list' );
				if ( ! get_option( 'mo_oauth_apps_list' ) ) {
					?>
					<p>Please setup a SSO application.</p>
					<?php
				} else {
					?>
					<p class="mo_oauth_customization_tab_notice"><strong>Note:-</strong>This feature is available in Standard and above plans.</p>
					<?php
					$app_details = get_option( 'mo_oauth_apps_list' );
					$app_id      = array_key_first( $app_details );
					$displayname = 'Login with ' . $app_id;
					$appname     = $app_id;
					$icons       = $appname;
					if ( 'vkontakte' === $appname ) {
						$icons = 'vk';
					}if ( 'oauth1' === $appname || 'other' === $appname || 'openidconnect' === $appname || 'miniorange' === $appname ) {
						$icons = 'lock';
					}if ( 'gapps' === $appname ) {
						$appname = 'google';
						$icons   = 'google';
					}if ( 'fbapps' === $appname ) {
						$appname = 'facebook';
						$icons   = 'facebook-square';
					}if ( 'Freja eID' === $appname ) {
						$appname = 'frejaeid';
					}if ( 'swiss rx login' === $appname ) {
						$appname = 'swissrx';
						$icons   = 'lock';
					}if ( 'azureb2c' === $appname ) {
						$appname = 'azure';
					}
					if ( 'slack' === $appname || 'google' === $appname || 'facebook' === $appname || 'apple' === $appname || 'github' === $appname || 'gitlab' === $appname || 'reddit' === $appname || 'paypal' === $appname || 'yahoo' === $appname || 'spotify' === $appname || 'vimeo' === $appname || 'vkontakte' === $appname || 'pinterest' === $appname || 'deviantart' === $appname || 'twitch' === $appname || 'linkedin' === $appname || 'WordPress' === $appname ) {
						?>
				<i id="mo_oauth_default_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_default_icon_preview mo_oauth_def_btn_<?php echo esc_attr( $appname ); ?>"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_custom_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_custom_icon_preview"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_white_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_white_icon_preview mo_oauth_white_btn_<?php echo esc_attr( $appname ); ?>"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_hover_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_hover_icon_preview mo_oauth_hov_btn_<?php echo esc_attr( $appname ); ?>"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_custom_hover_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_custom_hover_icon_preview"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_smart_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_smart_icon_preview"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_previous_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo 'lock'; ?> mo_oauth_previous_icon_preview"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
						<?php
					} elseif ( 'oauth1' === $appname || 'other' === $appname || 'openidconnect' === $appname || 'miniorange' === $appname || 'swissrx' === $appname ) {
						?>
					<i id="mo_oauth_default_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_default_icon_preview mo_oauth_def_btn_<?php echo esc_attr( $appname ); ?> mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
					<i id="mo_oauth_custom_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_custom_icon_preview mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
					<i id="mo_oauth_white_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_white_icon_preview mo_oauth_white_btn_<?php echo esc_attr( $appname ); ?> mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
					<i id="mo_oauth_hover_icon_preview_<?php echo esc_attr( $appname ); ?> mo_oauth_lock_pad" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_hover_icon_preview mo_oauth_hov_btn_<?php echo esc_attr( $appname ); ?> mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
					<i id="mo_oauth_custom_hover_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_custom_hover_icon_preview mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
					<i id="mo_oauth_smart_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo esc_attr( $icons ); ?> mo_oauth_smart_icon_preview mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
					<i id="mo_oauth_previous_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo 'lock'; ?> mo_oauth_previous_icon_preview mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
						<?php
					} else {
						?>
					<i id="mo_oauth_default_icon_preview_<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?>" class=" fa mo_oauth_default_icon_preview mo_oauth_def_btn_<?php echo esc_attr( $appname ); ?>" ><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?> class="mo_oauth_login_but_img"><span  class="mo_oauth_login_button_font mo_oauth_login_but_img_span"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_custom_icon_preview_<?php echo esc_attr( $appname ); ?>" class=" fa mo_oauth_custom_icon_preview " ><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?> class="mo_oauth_login_but_img"><span  class="mo_oauth_login_button_font mo_oauth_login_but_img_span"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_white_icon_preview_<?php echo esc_attr( $appname ); ?>" class=" fa mo_oauth_white_icon_preview mo_oauth_white_btn_<?php echo esc_attr( $appname ); ?>" > <img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $icons . '.png' ); ?> class="mo_oauth_login_but_img"><span  class="mo_oauth_login_button_font mo_oauth_login_but_img_span"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_hover_icon_preview_<?php echo esc_attr( $appname ); ?>" class=" fa mo_oauth_hover_icon_preview mo_oauth_hov_btn_<?php echo esc_attr( $appname ); ?>"><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . '.png' ); ?> class="mo_oauth_login_but_img without_hover"><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?> class="mo_oauth_login_but_img with_hover mo_oauth_x_customization_14"><span  class="mo_oauth_login_button_font mo_oauth_login_but_img_span"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_custom_hover_icon_preview_<?php echo esc_attr( $appname ); ?>" class=" fa mo_oauth_custom_hover_icon_preview "><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?> class="mo_oauth_login_but_custom_img without_hover"><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?> class="mo_oauth_login_but_custom_img with_hover mo_oauth_x_customization_14"><span  class="mo_oauth_login_button_font mo_oauth_login_but_img_span"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_smart_icon_preview_<?php echo esc_attr( $appname ); ?>" class=" fa mo_oauth_smart_icon_preview " ><img src=<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/' . $appname . 's.png' ); ?> class="mo_oauth_login_but_img"><span  class="mo_oauth_login_button_font mo_oauth_login_but_img_span"><?php echo esc_attr( $displayname ); ?></span></i>
				<i id="mo_oauth_previous_icon_preview_<?php echo esc_attr( $appname ); ?>" class="fa fa-<?php echo 'lock'; ?> mo_oauth_previous_icon_preview mo_oauth_lock_pad"><span  class="mo_oauth_login_button_font"><?php echo esc_attr( $displayname ); ?></span></i>
						<?php
					}
				}
				?>
			</div>
		</div>
<div class="mo_oauth_write_custom_code_tab mo_oauth_customize_SSO_buttons"><p class="mo_oauth_x_customization_16"><?php esc_html_e( 'Save the settings to see the preview of the Custom CSS', 'miniorange-login-with-eve-online-google-facebook' ); ?> </p>
		<table  class="mo_settings_table" >

			<tr>
				<td><strong><?php esc_html_e( 'Custom CSS:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></td>
				<td>
	</br>
					<textarea disabled type="text" class="mo_oauth_input_disabled mo_oauth_x_customization_17" id="mo_oauth_icon_configure_css" rows="6" name="mo_oauth_icon_configure_css"></textarea>
					<br/>
					<strong><?php esc_html_e( 'Example CSS:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>
<pre >
		background: #7272dc;
		height:40px;
		width:300px;
		padding:8px;
		text-align:center;
		color:#fff;
</pre>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php esc_html_e( 'Logout Button Text:', 'miniorange-login-with-eve-online-google-facebook' ); ?> </strong>
				</td>
				<td>
					<input disabled type="text" class="mo_oauth_input_disabled mo_oauth_x_customization_18" id="mo_oauth_custom_logout_text" name="mo_oauth_custom_logout_text" placeholder="Howdy, ##user##  ##Logout##">
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<br><strong><?php esc_html_e( 'Example:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>
<pre>
		Text you enter: Howdy ##user## ##Sign Out##
		Text displayed: Howdy (username)  <u>Sign Out</u>
</pre>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td></td>
			</tr>
		</table></div>
	<div class="mo_oauth_outer_padding mo_oauth_x_customization_19">

	<table class="mo_oauth_custom_settings_table">
		<div class="mo_oauth_notice_label">
				<h4 class="mo_oauth_x_customization_20"><?php esc_html_e( 'Apply above customized settings to the wp-admin SSO buttons:', 'miniorange-login-with-eve-online-google-facebook' ); ?>
				<label class="mo_oauth_switch mo_oauth_x_customization_21">
							<input value="1" type="checkbox" class="mo_oauth_x_customization_22"
								name="mo_apply_customized_setting_on_wp_admin" />
							<span class="mo_oauth_slider round "></span>
				</label></h4>
		</div>
	</table>
	<table class="mo_oauth_custom_settings_table">
			<tr>
				<h4 class="mo_oauth_x_customization_20"><?php esc_html_e( 'Customize display name (special charactors are allowed.)', 'miniorange-login-with-eve-online-google-facebook' ); ?></h4>
	</tr>
	<?php
	$appslist    = is_array( get_option( 'mo_oauth_apps_list' ) ) ? get_option( 'mo_oauth_apps_list' ) : array();
	$displayname = '';
	foreach ( $appslist as $key => $val ) {
		$displayname = $key;
	}
	?>
	<tr>
		<td>
				<strong><label class="mo_oauth_fix_fontsize"><?php esc_html_e( 'Enter text to display on your login buttons:', 'miniorange-login-with-eve-online-google-facebook' ); ?></label>&nbsp;&nbsp;<?php echo esc_attr( $displayname ); ?></strong></td>
				<td><input class="mo_oauth_textfield_css mo_oauth_input_disabled mo_oauth_x_customization_23" type="text" placeholder="SSO with : "/></td>
			   
	</tr>
</table>
<hr>
<table class="mo_oauth_custom_settings_table" id="mo_custom_icon_table">
<tr>
			<h4 class="mo_oauth_x_customization_20">Upload Custom Icons :</h4>
</tr>
	<?php
	$displayname = 'No App Configured';
	foreach ( $appslist as $key => $val ) {
		$displayname = $key;
	}
	?>
<tr>
	<td><strong> Application </strong></td>
	<td><strong> Custom Image for Icon</strong></td>
</tr>
<tr id="mo_custom_icon" class="rows">
	<td>
	<select class="mo_oauth_x_customization_24" name="<?php echo 'mo_custom_icon_file'; ?>" id="wp_icon_list" ><option value="">Select App from List</option><option value=""><?php echo esc_attr( $displayname ); ?></option></select></td>
	<td><input  type="file" id="mo_custom_icon" name="custom_icon[]" class="mo_oauth_input_disabled"></td>
	</tr>
	<tr><td><h4><a class="mo_oauth_input_disabled mo_oauth_x_customization_25" id="add_icon">Add More Icons</a></h4></td><td>&nbsp;</td></tr>
</table>
<hr>
	<table class="mo_oauth_custom_settings_table">
			<tr>
				<h4 class="mo_oauth_x_customization_20"><?php esc_html_e( 'Customize Connect with text on WP Login page', 'miniorange-login-with-eve-online-google-facebook' ); ?></h4>
	</tr><tr>
						<div class="mo_oauth_x_customization_26"><td>
							<strong><label class="mo_oauth_fix_fontsize"><?php esc_html_e( 'Enter text to show above login widget:', 'miniorange-login-with-eve-online-google-facebook' ); ?></label><strong></td>
							<td><input class="mo_oauth_textfield_css mo_oauth_input_disabled mo_oauth_x_customization_23"  type="text" name="mo_oauth_widget_customize_text" Placeholder="Connect with :" /></td>
						</div>
					</div>
	</tr>
	</table><hr>
	<table class="mo_oauth_custom_settings_table"> 
	<tr>
					<h4 class="mo_oauth_x_customization_20"><?php esc_html_e( 'Customize Text to show user after Login', 'miniorange-login-with-eve-online-google-facebook' ); ?></h4>
				<td>
					<strong><?php esc_html_e( 'Customize Logout Text: (Anchor tags are allowed)', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>
				</td>
				<td>
					<input class="mo_oauth_input_disabled mo_oauth_x_customization_27" type="text" id="mo_oauth_custom_logout_text" name="mo_oauth_custom_logout_text" placeholder="Howdy, ##user##  ##Logout##" value="">
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<strong><?php esc_html_e( 'Example:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>
<pre>
		Text you enter: Howdy ##user## ##Sign Out##
		Text displayed: Howdy (username)  <u>Sign Out</u>
</pre>
				</td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'With Logout Link: (If unchecked, remove logout link)', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></td>
				<td><input type="checkbox" name="mo_custom_html_with_logout_link" value="1"></td>
			</tr>
				</table>

				<table class="mo_oauth_custom_settings_table">
			<tr>
			<td></td>
		<td><input type="submit" id="button_submit"name="submit" value="<?php esc_html_e( 'Save Settings', 'miniorange-login-with-eve-online-google-facebook' ); ?>"
		class="button button-primary button-large mo_disabled_btn mo_oauth_x_customization_28" /></td>
	</tr></table>
	</div>
	</form>
	</div>
	</div>
	</div>

	<?php
}
