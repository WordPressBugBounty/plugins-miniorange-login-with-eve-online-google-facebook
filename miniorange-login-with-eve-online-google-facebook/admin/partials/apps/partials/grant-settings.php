<?php
/**
 * Grant Settings
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
 * Display Grant Settings
 */
function mooauth_client_grant_type_settings() {
	?>
	</div>
	<div class="mo_table_layout mo_oauth_contact_heading mo_oauth_outer_div mo_oauth_x_grantsettings_1" id="mo_grant_settings">
		<div class="mo_oauth_customization_header"><div class="mo_oauth_attribute_map_heading mo_oauth_x_grantsettings_2"><b class="mo_oauth_position"><?php esc_html_e( 'JWT Validation & PKCE', 'miniorange-login-with-eve-online-google-facebook' ); ?></b> <small><div class="mo_oauth_tooltip mo_oauth_tooltip_float_right"><span class="mo_oauth_tooltiptext"  >ENTERPRISE</span><a href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>" target="_blank" rel="noopener noreferrer"><span class="mo_oauth_x_grantsettings_3"><img class="mo_oauth_premium-label" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/mo_oauth_premium-label.png" alt="miniOrange Premium Plans Logo"></span></a></div></small></div>
	<div class="mo_oauth_tooltip mo_oauth_tooltip_float_right"><span class="mo_tooltiptext"  >Know how this is useful</span><a class="mo_oauth_x_grantsettings_4" target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/client/multiple-grant-support#configure-pkce-flow" rel="noopener noreferrer">
		<img class="mo_oauth_guide_img" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) ); ?>/images/mo_oauth_info-icon.png" alt="miniOrange Premium Plans Logo" aria-hidden="true"></a></div></div>
				<div>
					<table class="mo_settings_table mo_oauth_grant_setting">
						<tr>
							<td><strong class="mo_strong"><?php esc_html_e( 'JWKS URL:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></td>
							<td><input class="mo_table_textbox mo_oauth_input_disabled" type="text" value="" disabled /></td>
						</tr>
						<tr>
							<td><strong class="mo_strong"><?php esc_html_e( 'Enable JWT Verification:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></td>
							<td><input type="checkbox" class="mo_input_checkbox" value="" disabled/></td>
						</tr>
						<tr>
							<td><strong class="mo_strong"><?php esc_html_e( 'JWT Signing Algorithm:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></td>
							<td><select disabled>
									<option>HSA</option>
									<option>RSA</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td><strong class="mo_strong"><?php esc_html_e( 'PKCE (Proof Key for Code Exchange):', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong></td>
							<td><input id="pkce_flow" type="checkbox" class="mo_input_checkbox" name="pkce_flow" value="0" disabled/></td>
						</tr>
					</table>
					<p class="mo_oauth_upgrade_warning mo_oauth_x_grantsettings_6"><strong class="mo_strong">*NOTE: </strong><?php esc_html_e( 'PKCE can be used with Authorization Code Grant and users aren\'t required to provide a client_secret.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
				</div>
			<br><br>
		<div class="notes">
			<hr />
			<?php esc_html_e( 'Grant Type Settings and JWT Validation & PKCE are configurable in ', 'miniorange-login-with-eve-online-google-facebook' ); ?><a href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>" target="_blank" rel="noopener noreferrer">premium and enterprise</a><?php esc_html_e( ' versions of the plugin.', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</div>
	</div>
	<div>
	<?php
}
