<?php
/**
 * Verify-Password
 *
 * @package    verify-password-ui
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * When a user attempts to register with an already registered email address, display the UI for logging in with miniOrange.
 */
function mooauth_client_verify_password_ui() { ?>
		<form name="f" method="post" action="">
			<?php wp_nonce_field( 'mo_oauth_verify_password_form', 'mo_oauth_verify_password_form_field' ); ?>
			<input type="hidden" name="option" value="mo_oauth_verify_customer" />
			<div class="mo_table_layout mo_oauth_outer_div" id="mo_oauth_register">				
			<div id="toggle1" class="mo_oauth_customization_header">
				<h3 class="mo_oauth_signing_heading mo_oauth_x_verifypassword_1"><?php esc_html_e( 'Login with miniOrange', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
				</div>
				<div class="mo_oauth_contact_heading" id="panel1">
				<p class="mo_oauth_paragraph_div mo_oauth_x_verifypassword_2"><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password.</b></p>
				<table class="mo_settings_table mo_oauth_configure_table">
						<tr>
							<td><div><strong class="mo_strong">Email <span class="mo_oauth_x_demo_3">*</span>: </strong></div></td>
							<td><input class="mo_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo esc_attr( get_option( 'mo_oauth_admin_email' ) ); ?>" /></td>
						</tr>
						<td><div><strong class="mo_strong">Password <span class="mo_oauth_x_demo_3">*</span>: </strong></div></td>
						<td><input class="mo_table_textbox" required type="password"
							name="password" placeholder="Choose your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" name="submit" value="<?php esc_attr_e( 'Login', 'miniorange-login-with-eve-online-google-facebook' ); ?>" class="button button-large mo_oauth_configure_btn mo_oauth_x_verifypassword_3" /></form>

								<input type="button" name="back-button" id="mo_oauth_back_button" onclick="document.getElementById('mo_oauth_change_email_form').submit();" value="<?php esc_attr_e( 'Sign up', 'miniorange-login-with-eve-online-google-facebook' ); ?>" class="button button-large mo_oauth_configure_btn mo_oauth_x_verifypassword_3" />

								<form id="mo_oauth_change_email_form" method="post" action="">
									<?php wp_nonce_field( 'mo_oauth_change_email_form', 'mo_oauth_change_email_form_field' ); ?>
									<input type="hidden" name="option" value="mo_oauth_change_email" />
								</form></td>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><h4><b><a href="#mo_oauth_forgot_password_link" class="mo_oauth_x_verifypassword_4">Click here if you forgot your password?</a></b></h4></td>
						</tr>
					</table>
				</div>
			</div>

		<form name="f" method="post" action="" id="mo_oauth_forgotpassword_form">
			<?php wp_nonce_field( 'mo_oauth_forgotpassword_form', 'mo_oauth_forgotpassword_form_field' ); ?>
			<input type="hidden" name="option" value="mo_oauth_forgot_password_form_option"/>
		</form>

		<?php
}
