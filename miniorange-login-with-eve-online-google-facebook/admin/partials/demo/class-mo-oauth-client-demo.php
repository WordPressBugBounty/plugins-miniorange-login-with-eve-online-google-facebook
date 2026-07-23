<?php
/**
 * Demo
 *
 * @package    demo
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

/**
 * Handle demo requests
 */
class MO_OAuth_Client_Demo {

	/**
	 * Request for demo
	 */
	public static function requestfordemo() {
		self::demo_request();
	}

	/**
	 * Display UI to make demo request
	 */
	public static function demo_request() {

		// Get WordPress version.
		global $wp_version;

		$wp_version_trim = substr( $wp_version, 0, 3 );
		?>
<div class="mo_oauth_card">
	<div class="mo_oauth_card_header"><h3><?php esc_html_e( 'Trials Available', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3></div>
	<div class="mo_oauth_card_body">
			<div class="mo_demo_layout mo_oauth_contact_heading mo_oauth_outer_div">
			<div class="mo_oauth_request_demo_header">
				<div class="mo_oauth_attribute_map_heading"> <?php esc_html_e( 'Request for Demo/ Trial', 'miniorange-login-with-eve-online-google-facebook' ); ?></div>
			</div>
					<br/><blockquote class="mo_oauth_blackquote mo_oauth_paragraph_div mo_oauth_x_demo_1"><?php esc_html_e( 'Want to try out the paid features before purchasing the license? Simply complete the form provided below, specify your preferred add-ons, get access to the trial of All-Inclusive plan, giving you unrestricted access to test all our top-tier features.', 'miniorange-login-with-eve-online-google-facebook' ); ?></blockquote>
					<form method="post" action="">
					<input type="hidden" name="option" value="mo_oauth_client_demo_request_form" />
			<?php wp_nonce_field( 'mo_oauth_client_demo_request_form', 'mo_oauth_client_demo_request_field' ); ?>
					<div class="mo_oauth_x_demo_2"><div>
					<table class="mo_demo_table_layout">
						<tr><td>
							<div><strong class="mo_strong">Email id <span class="mo_oauth_x_demo_3">*</span>: </strong></div>
							<div><input class="mo_oauth_request_demo_inputs mo_oauth_x_demo_4" required type="email" name="mo_auto_create_demosite_email" placeholder="We will use this email to setup the demo for you" value="<?php echo esc_attr( get_option( 'mo_oauth_admin_email' ) ); ?>" /></div></td>
						</tr>
						<tr><td>
							<div><strong class="mo_strong"><?php esc_html_e( 'Request a demo for', 'miniorange-login-with-eve-online-google-facebook' ); ?> <span class="mo_oauth_x_demo_3">*</span>: </strong></div>
							<div>
							<select class="mo_oauth_request_demo_inputs mo_oauth_x_demo_4" required name="mo_auto_create_demosite_demo_plan" id="mo_oauth_client_demo_plan_id">
								<option disabled selected>------------------ Select ------------------</option>
								<option value="miniorange-oauth-client-standard-common">WP <?php echo esc_html( MO_OAUTH_PLUGIN_NAME ); ?> Standard Plugin</option>
								<option value="mo-oauth-client-premium">WP <?php echo esc_html( MO_OAUTH_PLUGIN_NAME ); ?> Premium Plugin</option>
								<option value="miniorange-oauth-client-enterprise">WP <?php echo esc_html( MO_OAUTH_PLUGIN_NAME ); ?> Enterprise Plugin</option>
								<option value="miniorange-oauth-client-allinclusive">WP <?php echo esc_html( MO_OAUTH_PLUGIN_NAME ); ?> All Inclusive Plugin</option>
								<option value="Not Sure"><?php esc_html_e( 'Not Sure', 'miniorange-login-with-eve-online-google-facebook' ); ?></option>
							</select>
							</div></td>
						</tr>
						<tr><td>
							<div><strong class="mo_strong"><?php esc_html_e( 'Usecase', 'miniorange-login-with-eve-online-google-facebook' ); ?><span class="mo_oauth_x_demo_3">*</span> : </strong></div>
							<div>
							<textarea class="mo_oauth_request_demo_inputs mo_oauth_x_demo_5" minlength="15" name="mo_auto_create_demosite_usecase" rows="4" placeholder="<?php esc_attr_e( 'Example. Login into WordPress using Cognito, SSO into WordPress with my company credentials, Restrict gmail.com accounts to my WordPress site etc.', 'miniorange-login-with-eve-online-google-facebook' ); ?>" required></textarea>
							</div></td>
						</tr>
						</table></div><div>
						<table class="mo_demo_table_layout">
						<tr id="add-on-list">
							<td colspan="2">
							<p><strong class="mo_strong"><?php esc_html_e( 'Select the Add-ons you are interested in (Optional)', 'miniorange-login-with-eve-online-google-facebook' ); ?> :</strong></p>
							<blockquote class="mo_oauth_blackquote"><i><strong class="mo_strong">(<?php esc_html_e( 'Note', 'miniorange-login-with-eve-online-google-facebook' ); ?>: </strong> <?php esc_html_e( 'All-Inclusive plan entitles all the addons in the license cost itself.', 'miniorange-login-with-eve-online-google-facebook' ); ?> )</i></blockquote>
							<table>
					<?php
					$count = 0;
					foreach ( MO_OAuth_Client_Addons::$all_addons as $key => $value ) {
						if ( 0 !== $key && 0 !== $value && true === $value['in_allinclusive'] ) {
							if ( 0 === $count ) {
								?>
											<tr>
												<td>
													<input type="checkbox" class="mo_input_checkbox mo_oauth_demo_form_checkbox mo_oauth_x_demo_6" name="<?php echo esc_attr( $value['tag'] ); ?>" value="true"> <?php echo esc_html( $value['title'] ); ?><br/>
												</td>
									<?php
									++$count;
							} elseif ( 1 === $count ) {
								?>
											<td>
												<input type="checkbox" class="mo_input_checkbox mo_oauth_demo_form_checkbox mo_oauth_x_demo_6" name="<?php echo esc_attr( $value['tag'] ); ?>" value="true"> <?php echo esc_html( $value['title'] ); ?><br/>
											</td>
											</tr>
									<?php
									$count = 0;
							}
						}
					}
					?>
								</table>
							</td>
						</tr>
							</table></div></div><table class="mo_oauth_x_demo_7">
						<!-- New WordPress sandbox demo trail -->
						<tr>
							<td>
								<button id="mo_oauth_sandbox_btn" name="mo_oauth_sandbox_btn" class="button button-large mo_oauth_demo_request_btn" data-wp-version="<?php echo esc_attr( $wp_version_trim ); ?>" data-referer="<?php echo esc_attr( get_site_url() ); ?>">Submit Demo Request</button>
							</td>
						</tr>

					</table>
			</form>
			</div>
	</div>
	</div>
<div class="mo_oauth_card">
	<div class="mo_oauth_card_header"><h3><?php esc_html_e( 'Request for Video Demo', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3></div>
	<div class="mo_oauth_card_body">
						<!-- VIDEO DEMO DOWN -->
			<div class="mo_demo_layout mo_oauth_contact_heading mo_oauth_outer_div">
					<form method="post" action="" id="request_video_demo">
					<input type="hidden" name="option" value="mo_oauth_client_video_demo_request_form" />
					<?php wp_nonce_field( 'mo_oauth_client_video_demo_request_form', 'mo_oauth_client_video_demo_request_field' ); ?>
					<div class="mo_oauth_x_demo_2">
						<div class="mo_oauth_video_demo_container_form">
								<table class="mo_demo_table_layout">
								<tr><td>
										<div><strong class="mo_strong">Email id <span class="mo_oauth_x_demo_3">*</span>: </strong></div>
										<div><input type="email" class="mo_oauth_video_demo_email mo_oauth_x_demo_4" placeholder="We will use this email to setup the demo for you" name="mo_oauth_video_demo_email" ></div>
							</td></tr>
								<tr>
									<td><div><strong class="mo_strong">Date<span class="mo_oauth_x_demo_3">*</span>: </strong></div>
									<div><input type="date" class="mo_oauth_video_demo_date mo_oauth_x_demo_4" name="mo_oauth_video_demo_request_date" placeholder="Enter the date for demo"></div>
								</td>
								</tr>
								<tr>
									<td>
									<div><strong class="mo_strong">Local Time<span class="mo_oauth_x_demo_3">*</span>: </strong></div>
									<div><input type="time" class="mo_oauth_video_demo_time mo_oauth_x_demo_4" placeholder="Enter your time" name="mo_oauth_video_demo_request_time">
										<input type="hidden" name="mo_oauth_video_demo_time_diff" id="mo_oauth_video_demo_time_diff"></div>
									</td>
								</tr>
								<tr>
									<td class="mo_oauth_x_demo_8">Eg:- 12:56, 18:30, etc.</td>
								</tr>
									<tr><td><div>
										<strong class="mo_strong">Usecase/ Any comments:<span class="mo_oauth_x_demo_3">*</span>: </strong></div>
										<div><textarea name="mo_oauth_video_demo_request_usecase_text" class="mo_oauth_video_demo_form_usecase mo_oauth_x_demo_9" minlength="15" placeholder="Example. Login into WordPress using Cognito, SSO into WordPress with my company credentials, Restrict gmail.com accounts to my WordPress site etc."></textarea>
									</div></td></tr>
									</table>
								</div>
						<div class="mo_oauth_demo_container_gif_section mo_demo_table_layout">
							<div class="mo_oauth_video_demo_message">
								Your overview <a class="mo_oauth_x_demo_10"><strong class="mo_strong">Video Demo</strong></a> will include
							</div>
							<div class="mo_oauth_video_demo_bottom_message">
								<img class="mo_oauth_video_demo_gif" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/setup-gif.jpg' ); ?>" alt="mo-demo-jpg">
							</div>
							<div class="mo_oauth_video_demo_bottom_message" >
									<strong class="mo_strong">You can set up a screen share meeting with our developers to walk you through our plugin features.</strong>
								<div class="mo_oauth_video_demo_bottom_message">
									<img class="mo_oauth_video_demo_icon" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/check.png' ); ?>" alt="">
									Overview of all Premium Plugin features.
								</div>
								<div class="mo_oauth_x_demo_11">
									<img class="mo_oauth_video_demo_icon" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/support.png' ); ?>" alt="">
									Get a guided demo from a Developer via screen share meeting.
								</div>
							</div>
						</div>
					</div>
					<table class="mo_oauth_x_demo_7">
						<tr>
							<td>
								<input type="submit" name="submit" value="<?php esc_attr_e( 'Submit Demo Request', 'miniorange-login-with-eve-online-google-facebook' ); ?>" class="button button-large mo_oauth_demo_request_btn" />
							</td>
						</tr>
					</table>
					</form>
				</div>
	</div>
	</div>
			<?php
	}
}
