<?php
/**
 * Feedback Form
 *
 * @package    feedback-form
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Display Feedback form.
 */
function mooauth_client_display_feedback_form() {
	if ( ! empty( $_SERVER['PHP_SELF'] ) && 'plugins.php' !== basename( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ) ) ) {
		return;
	}
	$deactivate_reasons = array(
		' Issues with SSO Setup',
		' Upgrading to Paid version',
		' My OAuth Server is not listed',
		' Would like to go on a call with expert',
		' Would like to test a premium plugin',
		' Other Reasons',
	);
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );
	wp_enqueue_script( 'utils' );
	wp_enqueue_style( 'mo_oauth_admin_settings_style', plugin_dir_url( dirname( __FILE__ ) ) . '/admin/css/style_settings.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
	wp_enqueue_style( 'mo_oauth_admin_settings_font_awesome', plugin_dir_url( dirname( __FILE__ ) ) . 'css/font-awesome.min.css', array(), '4.6.2' );
	wp_enqueue_style( 'mo_oauth_theme', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/mo-oauth-theme.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
	wp_enqueue_script( 'mo_oauth_theme_script', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/mo-oauth-theme.min.js', array( 'jquery' ), MO_OAUTH_CSS_JS_VERSION, true );
	$keep_settings_intact = true;
	?>

	<div id="oauth_client_feedback_modal" class="mo_oauth_modal mo_oauth_x_feedback_1">
		<div class="mo_oauth_modal-content">
			<h3 class="mo_oauth_x_feedback_2"><b class="mo_oauth_x_feedback_3"> <span class="mo_oauth_x_feedback_4">midd</span>Feedback Form
			</b><span class="mo_close" id="mo_oauth_client_close">&times;</span></h3>
			<img class="mo_oauth_feedback_img" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/Feedback_img.png' ); ?>" />
			<div class="mo_oauth_div_inside_model">
			<form name="f" method="post" action="" id="mo_oauth_client_feedback">
				<?php wp_nonce_field( 'mo_oauth_feedback_form', 'mo_oauth_feedback_form_field' ); ?>
				<input type="hidden" name="mo_oauth_client_feedback" value="true"/>
				<div class="mo-oauth-idp-keep-conf-intact" id="mo_idp_keep_configuration_intact">
						<b class="mo_oauth_x_feedback_5">Keep Configuration Intact</b>
						<label class="mo-oauth-switch">
							<input type="checkbox" class="mo_input_checkbox" name="mo_oauth_keep_settings_intact" id="keepSettingsIntact" <?php echo esc_attr( $keep_settings_intact ) ? 'checked' : ''; ?>>
							<span class="mo-oauth-slider mo-oauth-round"></span>
						</label>
						<p class="mo_idp_keep_configuration_intact_descr mo_oauth_x_feedback_6">Enabling this would keep your settings intact when plugin is uninstalled. Please enable this option when you are updating to a Premium version.</p>
					</div>
				<div>
					<h4 class="mo_oauth_x_feedback_7">How satisfied are you with our product/service?</h4>
					<div align="center">
					<div id="mo_oauth_smi_rate" class="mo_oauth_x_feedback_8">
					<input class="mo_oauth_rating_face mo_oauth_radio_type" type="radio" name="rate" id="mo_oauth_angry" value="1"/>
						<label for="mo_oauth_angry"><img class="mo_oauth_feedback_face" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/angry.png' ); ?>" />
						</label>
					<input class="mo_oauth_rating_face mo_oauth_radio_type" type="radio" name="rate" id="mo_oauth_sad" value="2"/>
						<label for="mo_oauth_sad"><img class="mo_oauth_feedback_face" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/sad.png' ); ?>" />
						</label>
					<input class="mo_oauth_rating_face mo_oauth_radio_type" type="radio" name="rate" id="mo_oauth_neutral" value="3"/>
						<label for="mo_oauth_neutral"><img class="mo_oauth_feedback_face" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/normal.png' ); ?>" />
						</label>
					<input class="mo_oauth_rating_face mo_oauth_radio_type" type="radio" name="rate" id="mo_oauth_smile" value="4"/>
						<label for="mo_oauth_smile">
						<img class="mo_oauth_feedback_face" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/smile.png' ); ?>" />
						</label>
					<input class="mo_oauth_rating_face mo_oauth_radio_type" type="radio" name="rate" id="mo_oauth_happy" value="5" checked/>
						<label for="mo_oauth_happy"><img class="mo_oauth_feedback_face" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/happy.png' ); ?>" />
						</label>
					</div>

					<div class="mo_oauth_x_feedback_9">
					<h4 class="mo_oauth_x_feedback_10">Tell us what happened?<br></h4>

					<fieldset>
					<table class="mo_oauth_x_feedback_11">
				<?php
					$count = 0;
				foreach ( $deactivate_reasons as $deactivate_reason ) {
					if ( 0 === $count ) {
						echo '<tr>';
						echo '<td class="mo_reason"><input type="radio" class="mo_oauth_radio_type mo_oauth_x_feedback_12" name="deactivate_reason_select" id = "' . esc_attr( $deactivate_reason ) . '" value="' . esc_attr( $deactivate_reason ) . '"><label for="' . esc_attr( $deactivate_reason ) . '">' . esc_attr( $deactivate_reason ) . '</label></td>';
						++$count;
					} elseif ( 1 === $count ) {
						echo '<td class="mo_reason"><input type="radio" class="mo_oauth_radio_type mo_oauth_x_feedback_12" name="deactivate_reason_select" id = "' . esc_attr( $deactivate_reason ) . '" value="' . esc_attr( $deactivate_reason ) . '"';
						echo checked( esc_attr( $deactivate_reason ) === ' Other Reasons' ) . ' ';
						echo ' ><label for="' . esc_attr( $deactivate_reason ) . '">' . esc_attr( $deactivate_reason ) . '</label></td>';
						echo '</tr>';
						$count = 0;
					}
				}
				?>
					</table>
					</fieldset>
					<textarea id="mo_oauth_query_feedback" name="query_feedback" rows="3" class="mo_oauth_x_feedback_13" placeholder="Write your query here.."></textarea>
					<?php
					$email = get_option( 'mo_oauth_admin_email' );
					if ( empty( $email ) ) {
						$user  = wp_get_current_user();
						$email = $user->user_email;
					}
					?>
					<div>
						<input type="email" id="mo_oauth_query_mail" name="query_mail" class="mo_oauth_x_feedback_14" placeholder="your email address" required value="<?php echo esc_attr( $email ); ?>" readonly="readonly"/>
						<i class="fa fa-pencil mo_oauth_x_feedback_15" onclick="mooauth_editName()"></i>
						</div>
						<div class="mo_oauth_x_feedback_16">
						<input type="checkbox" class="mo_input_checkbox" name="get_reply" value="reply" checked>miniOrange representative will reach out to you at the email-address entered above.
						</div>
					</div></div>
					<div class="mo_modal-footer">
						<div class="mo_oauth_x_feedback_17">
						<input id="mo_skip_oauth_client" type="submit" onclick="remove_skip_required()" name="miniorange_feedback_skip"
							class="button mo_oauth_x_feedback_18" value="Skip"/>
						<input type="submit" name="miniorange_feedback_submit"
							class="button button-primary button-large mo_oauth_feedback_btn" value="Submit"/></div>
					</div>
				</div>
			</form>
			</div>
			<form name="f" method="post" action="" id="mo_oauth_client_feedback_form_close">
				<?php wp_nonce_field( 'mo_oauth_skip_feedback_form', 'mo_oauth_skip_feedback_form_field' ); ?>
				<input type="hidden" name="option" value="mo_oauth_client_skip_feedback"/>
			</form>
		</div>
	</div>
	<?php
}

?>
