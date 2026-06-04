<?php
/**
 * FAQ
 *
 * @package    faq
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling FAQ
 */
class MO_OAuth_Client_Troubleshoot {

	/**
	 * Display Troubleshooting page
	 */
	public static function troubleshooting() {
		$appslist    = get_option( 'mo_oauth_apps_list' );
		$errorjson   = wp_json_file_decode( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'mo_oauth_errorcode.json' );
		$faqjson     = wp_json_file_decode( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'mo_oauth_faq.json' );
		$esc_allowed = array(
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
			'style'  => array(
				'table',
				'tr',
				'td',
				'th',
			),
			'br'     => array(),
			'th'     => array( 'style' ),
			'strong' => array(),
			'b'      => array(),
			'table'  => array(),
			'h2'     => array(),
			'h3'     => array(),
			'h4'     => array(),
			'tr'     => array(),
			'h6'     => array(),
			'tbody'  => array(),
			'div'    => array(),
			'td'     => array(),
		);
		$abilities_enabled = 'true' === get_option( 'mo_oauth_enable_abilities_api' );
		$abilities_supported = version_compare( get_bloginfo( 'version' ), '6.9', '>=' );
		?>
		<div class="mo_table_layout mo_oauth_outer_div">
		<div>
		<h3 class='mo_app_heading' style='font-size:23px'>
		<?php esc_html_e( 'Troubleshooting', 'miniorange-login-with-eve-online-google-facebook' ); ?>
		</h3>
		<hr class='mo-divider'><br>
		</div>
		<style>
			/* 3-tab layout overrides (default plugin CSS assumes 2 boxes at 50% width inside a 60%-wide container). */
			.mo_oauth_error_faq_option.mo_oauth_has_three_tabs { margin: 0 10%; }
			.mo_oauth_error_faq_option.mo_oauth_has_three_tabs > div { width: 33.3333%; min-width: 0; }
			.mo_oauth_error_faq_option.mo_oauth_has_three_tabs .mo_app_heading { white-space: nowrap; font-size: 16px; }
			.mo_oauth_ai_options { padding: 5px 10px; border-radius: 7px; cursor: pointer; }

			.mo_oauth_ai_section { padding: 20px 24px; }
			.mo_oauth_ai_section .mo_oauth_ai_header { display:flex; align-items:flex-start; gap:14px; margin-bottom:14px; }
			.mo_oauth_ai_section .mo_oauth_ai_icon { font-size:26px; color:#012970; flex:0 0 auto; line-height:1; padding-top:2px; }
			.mo_oauth_ai_section h3.mo_app_heading { margin:0 0 6px 0; }
			.mo_oauth_ai_section p.mo_oauth_ai_intro { margin:0; color:#4a5568; }
			.mo_oauth_ai_row { display:flex; align-items:center; gap:14px; padding:14px 16px; background:#f7f9fc; border:1px solid #e1e8f0; border-radius:6px; }
			.mo_oauth_ai_row_label { flex:1 1 auto; min-width:0; }
			.mo_oauth_ai_row_label strong { display:block; color:#012970; margin-bottom:2px; }
			.mo_oauth_ai_row_label small { color:#5a6778; }
			.mo_oauth_ai_control { display:flex; align-items:center; gap:10px; flex:0 0 auto; }
			.mo_oauth_toggle_switch { position:relative; display:inline-block; width:48px; height:26px; flex:0 0 auto; }
			.mo_oauth_toggle_switch input { opacity:0; width:0; height:0; }
			.mo_oauth_toggle_slider { position:absolute; cursor:pointer; inset:0; background:#c5cdd9; border-radius:26px; transition:.2s; }
			.mo_oauth_toggle_slider:before { content:""; position:absolute; height:20px; width:20px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,0.15); }
			.mo_oauth_toggle_switch input:checked + .mo_oauth_toggle_slider { background:#012970; }
			.mo_oauth_toggle_switch input:checked + .mo_oauth_toggle_slider:before { transform:translateX(22px); }
			.mo_oauth_toggle_switch input:disabled + .mo_oauth_toggle_slider { opacity:0.5; cursor:not-allowed; }
			.mo_oauth_ai_status { display:inline-block; padding:3px 10px; border-radius:11px; font-size:12px; font-weight:600; letter-spacing:0.3px; }
			.mo_oauth_ai_status.is_on { background:#d4edda; color:#155724; }
			.mo_oauth_ai_status.is_off { background:#e9ecef; color:#6c757d; }
			.mo_oauth_ai_feedback { margin-top:12px; font-size:13px; color:#155724; min-height:18px; }
			.mo_oauth_ai_feedback.is_error { color:#a94442; }
			.mo_oauth_ai_unsupported { padding:14px 16px; background:#fdecea; border:1px solid #f5c6cb; color:#a94442; border-radius:6px; }
			.mo_oauth_mcp_note { margin-top:14px; padding:10px 14px; background:#eef4ff; border-left:3px solid #4a7fe5; border-radius:4px; font-size:13px; color:#2d3748; }
			.mo_oauth_mcp_note strong { color:#c0392b; }
			.mo_oauth_mcp_clients { display:flex; gap:14px; margin-top:14px; flex-wrap:wrap; }
			.mo_oauth_mcp_client_card { flex:1 1 0; min-width:160px; background:#f7f9fc; border:1px solid #e1e8f0; border-radius:8px; padding:14px 16px; }
			.mo_oauth_mcp_client_card .mo_oauth_mcp_client_name { display:flex; align-items:center; gap:8px; font-weight:600; color:#012970; margin-bottom:4px; font-size:14px; }
			.mo_oauth_mcp_client_card .mo_oauth_mcp_client_name img { width:22px; height:22px; object-fit:contain; }
			.mo_oauth_mcp_client_card p { margin:0 0 10px 0; font-size:12px; color:#5a6778; line-height:1.5; }
			.mo_oauth_mcp_client_card a { font-size:12px; color:#4a7fe5; text-decoration:none; font-weight:500; }
			.mo_oauth_mcp_client_card a:hover { text-decoration:underline; }
			.mo_oauth_mcp_icon { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:5px; color:#fff; font-size:11px; font-weight:700; line-height:1; flex:0 0 auto; }
		</style>
		<div class="mo_oauth_error_faq_option mo_oauth_has_three_tabs">
			<div class="mo_oauth__errorcodes_options">
				<h3 class='mo_app_heading'><?php esc_html_e( 'Error Codes', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
			</div>
			<div class="mo_oauth_faq_options">
				<h3 class='mo_app_heading'><?php esc_html_e( 'FAQs', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
			</div>
			<div class="mo_oauth_ai_options">
				<h3 class='mo_app_heading'><?php esc_html_e( 'AI Setup', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
			</div>
		</div>
		<br><br>
		<div class="mo_oauth_errorcodes">

		<?php
		if ( empty( $appslist ) || ! isset( $appslist ) ) {
			?>
			<blockquote class="mo_oauth_blackquote mo_oauth_paragraph_div" style="  margin-bottom: 0px;">No Applications is configured. Please configure the application in the <b><a style="cursor: pointer" href="<?php echo ! empty( $_SERVER['REQUEST_URI'] ) ? esc_attr( add_query_arg( array( 'tab' => 'config' ), sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : ''; ?>"><?php esc_html_e( 'Configure OAuth', 'miniorange-login-with-eve-online-google-facebook' ); ?></a></b> tab. </blockquote>
			<?php
		} else {
			$configuredapp = get_option( 'mo_oauth_apps_list' ) ? array_key_first( get_option( 'mo_oauth_apps_list' ) ) : '';
			$app_name      = $appslist[ $configuredapp ]['appId'];
			if ( isset( $errorjson->$app_name ) ) {
				?>
				<table class="mo_oauth_troubleshoot_table">
				<tr class='mo_troubleshoot_heading'>
					<td style='width:30%'>Error</td>
					<td>Description</td>
				</tr>
				<?php
				foreach ( $errorjson->$app_name as  $error ) {
						echo '<tr>';
							echo ' <td>' . esc_attr( $error->error ) . '</td>';
							echo '<td>' . wp_kses( $error->desc, $esc_allowed ) . '</td>';
						echo '</tr>';
				}
				?>
				</table>
				<?php
			} else {
				?>
				<blockquote class="mo_oauth_blackquote mo_oauth_paragraph_div" style="  margin-bottom: 0px;">We will address error codes for your identity provider in the future. Please contact <a href="mailto:oauthsupport@xecurify.com">oauthsupport@xecurify.com</a> for a quick resolution of the error.</blockquote>
				<?php
			}
		}
		?>
			</div>
			<div class="mo_oauth_faq">
			<table class="mo_oauth_troubleshoot_table">
				<tr class='mo_troubleshoot_heading'>
					<td style='width:40%'>Error</td>
					<td>Description</td>
				</tr>
				<?php
				foreach ( $faqjson as  $faq => $desc ) {

						echo '<tr>';
							echo ' <td>' . esc_attr( $faq ) . '</td>';
							echo '<td>' . wp_kses( $desc, $esc_allowed ) . '</td>';
						echo '</tr>';
				}
				?>
				</table>

				Please refer to this for more <b><a href = 'https://faq.miniorange.com/kb/oauth-openid-connect/' target = '_blank' rel="noopener noreferrer">FAQs</a></b>.
			</div>
			<div class="mo_oauth_ai_panel">
				<div class="mo_oauth_ai_section">
					<div class="mo_oauth_ai_header">
						<i class="fa fa-magic mo_oauth_ai_icon"></i>
						<div>
							<h3 class='mo_app_heading'><?php esc_html_e( 'AI / MCP Abilities API', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
							<p class="mo_oauth_ai_intro">
								<?php esc_html_e( 'Expose 8 abilities (configure SSO, fix common errors, submit support queries) to AI agents such as Claude, ChatGPT and any MCP-compatible client. All abilities require the manage_options capability. Disabled by default.', 'miniorange-login-with-eve-online-google-facebook' ); ?>
							</p>
						</div>
					</div>

					<?php if ( ! $abilities_supported ) : ?>
						<div class="mo_oauth_ai_unsupported">
							<i class="fa fa-exclamation-triangle"></i>
							<?php esc_html_e( 'Requires WordPress 6.9 or newer. Please upgrade WordPress to use this feature.', 'miniorange-login-with-eve-online-google-facebook' ); ?>
						</div>
					<?php else : ?>
						<div class="mo_oauth_ai_row">
							<div class="mo_oauth_ai_row_label">
								<strong><?php esc_html_e( 'Enable Abilities API + MCP integration', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>
								<small><?php esc_html_e( 'Flipping the switch saves immediately. AI agents will then discover and call the mo-oauth-client/* abilities.', 'miniorange-login-with-eve-online-google-facebook' ); ?></small>
							</div>
							<div class="mo_oauth_ai_control">
								<span id="mo_oauth_ai_status_badge" class="mo_oauth_ai_status <?php echo $abilities_enabled ? 'is_on' : 'is_off'; ?>">
									<?php echo $abilities_enabled ? esc_html__( 'ACTIVE', 'miniorange-login-with-eve-online-google-facebook' ) : esc_html__( 'OFF', 'miniorange-login-with-eve-online-google-facebook' ); ?>
								</span>
								<label class="mo_oauth_toggle_switch" title="<?php esc_attr_e( 'Toggle Abilities API', 'miniorange-login-with-eve-online-google-facebook' ); ?>">
									<input type="checkbox" id="mo_oauth_abilities_toggle_input" <?php checked( $abilities_enabled ); ?> />
									<span class="mo_oauth_toggle_slider"></span>
								</label>
							</div>
						</div>
						<div id="mo_oauth_ai_feedback" class="mo_oauth_ai_feedback"></div>

						<div class="mo_oauth_mcp_note">
							<strong><?php esc_html_e( 'Note:', 'miniorange-login-with-eve-online-google-facebook' ); ?></strong>
							<?php esc_html_e( ' Enabling this option will make SSO abilities publicly accessible when connected with MCP.', 'miniorange-login-with-eve-online-google-facebook' ); ?>
						</div>

						<div class="mo_oauth_mcp_clients">
							<div class="mo_oauth_mcp_client_card">
								<div class="mo_oauth_mcp_client_name">
									<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex:0 0 auto;border-radius:5px"><rect width="22" height="22" rx="5" fill="#CC785C"/><text x="11" y="15.5" text-anchor="middle" font-size="12" font-weight="700" font-family="Arial,sans-serif" fill="#fff">C</text></svg>
									<?php esc_html_e( 'Claude Desktop', 'miniorange-login-with-eve-online-google-facebook' ); ?>
								</div>
								<p><?php esc_html_e( 'Connect Claude Desktop to your WordPress site via MCP.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
								<a href="https://plugins.miniorange.com/connect-wordpress-with-claude-mcp-guide" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View setup guide →', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
							</div>
							<div class="mo_oauth_mcp_client_card">
								<div class="mo_oauth_mcp_client_name">
									<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex:0 0 auto;border-radius:5px"><rect width="22" height="22" rx="5" fill="#1a1a1a"/><polygon points="8,6 17,11 8,16" fill="#fff"/></svg>
									<?php esc_html_e( 'Cursor', 'miniorange-login-with-eve-online-google-facebook' ); ?>
								</div>
								<p><?php esc_html_e( 'Connect Cursor IDE to your WordPress site via MCP.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
								<a href="https://plugins.miniorange.com/connect-wordpress-with-cursor-mcp-guide" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View setup guide →', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
							</div>
							<div class="mo_oauth_mcp_client_card">
								<div class="mo_oauth_mcp_client_name">
									<svg width="22" height="22" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex:0 0 auto;border-radius:5px;background:#10a37f;padding:3px"><path d="M37.532 16.87a9.963 9.963 0 0 0-.856-8.184 10.078 10.078 0 0 0-10.855-4.835 9.965 9.965 0 0 0-7.505-3.348 10.079 10.079 0 0 0-9.612 6.977 9.967 9.967 0 0 0-6.664 4.834 10.08 10.08 0 0 0 1.24 11.817 9.965 9.965 0 0 0 .856 8.185 10.079 10.079 0 0 0 10.855 4.835 9.965 9.965 0 0 0 7.504 3.347 10.08 10.08 0 0 0 9.617-6.981 9.967 9.967 0 0 0 6.663-4.834 10.079 10.079 0 0 0-1.243-11.813zM22.498 37.886a7.474 7.474 0 0 1-4.799-1.735c.061-.033.168-.091.237-.134l7.964-4.6a1.294 1.294 0 0 0 .655-1.134V19.054l3.366 1.944a.12.12 0 0 1 .066.092v9.299a7.505 7.505 0 0 1-7.49 7.496zM6.392 31.006a7.471 7.471 0 0 1-.894-5.023c.06.036.162.099.237.141l7.964 4.6a1.297 1.297 0 0 0 1.308 0l9.724-5.614v3.888a.12.12 0 0 1-.048.103l-8.051 4.649a7.504 7.504 0 0 1-10.24-2.744zM4.297 13.62A7.469 7.469 0 0 1 8.2 10.333c0 .068-.004.19-.004.274v9.201a1.294 1.294 0 0 0 .654 1.132l9.723 5.614-3.366 1.944a.12.12 0 0 1-.114.012L7.044 23.86a7.504 7.504 0 0 1-2.747-10.24zm27.658 6.437-9.724-5.615 3.367-1.943a.121.121 0 0 1 .114-.012l8.048 4.648a7.498 7.498 0 0 1-1.158 13.528v-9.476a1.293 1.293 0 0 0-.647-1.13zm3.35-5.043c-.059-.037-.162-.099-.236-.141l-7.965-4.6a1.298 1.298 0 0 0-1.308 0l-9.723 5.614v-3.888a.12.12 0 0 1 .048-.103l8.05-4.645a7.497 7.497 0 0 1 11.135 7.763zm-21.063 6.929-3.367-1.944a.12.12 0 0 1-.065-.092v-9.299a7.497 7.497 0 0 1 12.293-5.756 6.94 6.94 0 0 0-.236.134l-7.965 4.6a1.294 1.294 0 0 0-.654 1.132l-.006 11.225zm1.829-3.943 4.33-2.501 4.332 2.5v4.999l-4.331 2.5-4.331-2.5V18z" fill="#fff"/></svg>
									<?php esc_html_e( 'ChatGPT', 'miniorange-login-with-eve-online-google-facebook' ); ?>
								</div>
								<p><?php esc_html_e( 'Connect ChatGPT to your WordPress site using guide.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
								<a href="https://plugins.miniorange.com/setup-chatgpt-to-wordpress-abilities-api-using-mcp" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View setup guide →', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php if ( $abilities_supported ) : ?>
		<script>
			var mo_oauth_ai_ajax = {
				url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
				nonce: <?php echo wp_json_encode( wp_create_nonce( 'mo_oauth_abilities_toggle_nonce' ) ); ?>
			};
		</script>
		<?php endif; ?>
		<script>
			jQuery(document).ready(function () {
				var ACTIVE_BG = "rgb(237 243 255 / 61%)";
				function moOAuthShowSection(which) {
					jQuery(".mo_oauth_errorcodes, .mo_oauth_faq, .mo_oauth_ai_panel").hide();
					jQuery(".mo_oauth__errorcodes_options, .mo_oauth_faq_options, .mo_oauth_ai_options").css({"background-color":"white","border":"none"});
					if (which === "errors") {
						jQuery(".mo_oauth_errorcodes").show();
						jQuery(".mo_oauth__errorcodes_options").css("background-color", ACTIVE_BG);
					} else if (which === "faq") {
						jQuery(".mo_oauth_faq").show();
						jQuery(".mo_oauth_faq_options").css("background-color", ACTIVE_BG);
					} else if (which === "ai") {
						jQuery(".mo_oauth_ai_panel").show();
						jQuery(".mo_oauth_ai_options").css("background-color", ACTIVE_BG);
					}
				}
				moOAuthShowSection("errors");
				jQuery(".mo_oauth__errorcodes_options").click(function () { moOAuthShowSection("errors"); });
				jQuery(".mo_oauth_faq_options").click(function () { moOAuthShowSection("faq"); });
				jQuery(".mo_oauth_ai_options").click(function () { moOAuthShowSection("ai"); });

				jQuery("#mo_oauth_abilities_toggle_input").on("change", function () {
					if (typeof mo_oauth_ai_ajax === "undefined") { return; }
					var $input = jQuery(this);
					var $badge = jQuery("#mo_oauth_ai_status_badge");
					var $msg = jQuery("#mo_oauth_ai_feedback").removeClass("is_error").text("Saving...");
					var desired = $input.is(":checked");
					$input.prop("disabled", true);
					jQuery.post(mo_oauth_ai_ajax.url, {
						action: "mo_oauth_abilities_toggle_ajax",
						mo_oauth_nonce: mo_oauth_ai_ajax.nonce,
						enable: desired ? "true" : "false"
					}).done(function (resp) {
						if (resp && resp.success) {
							$badge.removeClass("is_on is_off").addClass(resp.enabled ? "is_on" : "is_off").text(resp.enabled ? "ACTIVE" : "OFF");
							$msg.removeClass("is_error").text(resp.message || "Saved.");
						} else {
							$input.prop("checked", !desired);
							$msg.addClass("is_error").text((resp && resp.message) ? resp.message : "Could not save.");
						}
					}).fail(function () {
						$input.prop("checked", !desired);
						$msg.addClass("is_error").text("Network error. Please try again.");
					}).always(function () {
						$input.prop("disabled", false);
					});
				});
			});
		</script>
		<?php
	}
}
