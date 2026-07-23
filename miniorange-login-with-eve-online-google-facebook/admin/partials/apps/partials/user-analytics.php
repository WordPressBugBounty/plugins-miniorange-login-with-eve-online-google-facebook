<?php
/**
 * User Analytics
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
 * Display User Analytics
 */
function mooauth_client_user_analytics_ui() { ?>
<div class="mo_oauth_card">
	<div class="mo_oauth_card_header"><h3><?php esc_html_e( 'User Analytics', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3></div>
	<div class="mo_oauth_card_body">
	<div class="mo_table_layout" id="mo_oauth_user_analytics">
			<div class="mo_wpns_small_layout">
					<table>
						<tr>
							<td class="mo_oauth_td_full_width"><div class="mo_oauth_attribute_map_heading mo_oauth_display_inline"><b class="mo_oauth_position"><?php esc_html_e( 'User Transactions Report ', 'miniorange-login-with-eve-online-google-facebook' ); ?></b> <small><div class="mo_oauth_tooltip" ><span class="mo_oauth_tooltiptext" >ENTERPRISE</span><a href="<?php echo esc_url( MO_OAUTH_CLIENT_PRICING_PLAN ); ?>" target="_blank" rel="noopener noreferrer"><span class="mo_oauth_no_border"><img class="mo_oauth_premium-label" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) . '/images/mo_oauth_premium-label.png' ); ?>" alt="miniOrange Standard Plans Logo"></span></a></div></small></div></td><td></td><td class="mo_oauth_td_right"><div class="mo_oauth_tooltip"><span class="mo_tooltiptext">Know how this is useful</span><a class="mo_oauth_no_underline" target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/client/user-analytics" rel="noopener noreferrer">
		<img class="mo_oauth_guide_img" src="<?php echo esc_url( dirname( plugin_dir_url( __FILE__ ) ) . '/images/mo_oauth_info-icon.png' ); ?>" alt="miniOrange Premium Plans Logo" aria-hidden="true"></a><br><br></div></td></tr><tr><td></td>
							<td>
								<input disabled type="submit" value="<?php esc_html_e( 'Refresh', 'miniorange-login-with-eve-online-google-facebook' ); ?>" class="button button-primary button-large mo_disabled_btn" />
							</td>
							<td>
								<input disabled type="submit" value="<?php esc_html_e( 'Clear Reports', 'miniorange-login-with-eve-online-google-facebook' ); ?>" class="button button-primary button-large mo_disabled_btn" />
							</td>
						</tr>
					</table><br>
				<table id="reports_table" class="mo_oauth_bootstrap_table mo_oauth_client_user_analytics">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Username', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
							<th><?php esc_html_e( 'Status', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
							<th><?php esc_html_e( 'Application', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
							<th><?php esc_html_e( 'Created Date', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
							<th><?php esc_html_e( 'Email', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
							<th><?php esc_html_e( 'Client IP', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
							<th><?php esc_html_e( 'Navigation URL', 'miniorange-login-with-eve-online-google-facebook' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>testuser1</td>
							<td class="mo_oauth_status_failed"><strong>FAILED. Invalid Email Received</strong></td>
							<td>-</td>
							<td>Mar 20,2024 1:53:10 pm</td>
							<td>-</td>
							<td>124.0.1</td>
							<td>-</td>
						</tr>
						<tr>
							<td>-</td>
							<td class="mo_oauth_status_failed"><strong>FAILED. Invalid Username Received.</strong></td>
							<td>-</td>
							<td>Mar 20,2024 1:58:31 pm</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
						</tr>
						<tr>
							<td>testuser3</td>
							<td class="mo_oauth_status_success"><strong>SUCCESS</strong></td>
							<td>localserver</td>
							<td>Mar 20,2024 2:01:10 pm</td>
							<td>testuser3@test.com</td>
							<td>124.0.1</td>
							<td><?php echo esc_url( home_url( '/' ) ); ?></td>
						</tr>
						<tr>
							<td>testuser4</td>
							<td class="mo_oauth_status_success"><strong>SUCCESS</strong></td>
							<td>localserver</td>
							<td>Mar 20,2024 2:07:15 pm</td>
							<td>testuser4@test.com</td>
							<td>124.0.1</td>
							<td><?php echo esc_url( home_url( '/' ) ); ?></td>
						</tr>
						<tr>
							<td>-</td>
							<td class="mo_oauth_status_failed"><strong>FAILED. Invalid Username Received.</strong></td>
							<td>-</td>
							<td>Mar 20,2024 2:25:18 pm</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	</div>
	<?php
}
