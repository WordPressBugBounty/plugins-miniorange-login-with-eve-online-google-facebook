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
			<blockquote class="mo_oauth_blackquote mo_oauth_paragraph_div mo_oauth_x_troubleshoot_1">No Applications is configured. Please configure the application in the <b><a class="mo_oauth_x_troubleshoot_2" href="<?php echo ! empty( $_SERVER['REQUEST_URI'] ) ? esc_attr( add_query_arg( array( 'tab' => 'config' ), sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : ''; ?>"><?php esc_html_e( 'Configure OAuth', 'miniorange-login-with-eve-online-google-facebook' ); ?></a></b> tab. </blockquote>
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
				<blockquote class="mo_oauth_blackquote mo_oauth_paragraph_div mo_oauth_x_troubleshoot_1">We will address error codes for your identity provider in the future. Please contact <a href="mailto:oauthsupport@xecurify.com">oauthsupport@xecurify.com</a> for a quick resolution of the error.</blockquote>
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
									<input type="checkbox" id="mo_oauth_abilities_toggle_input" data-mo-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-mo-nonce="<?php echo esc_attr( wp_create_nonce( 'mo_oauth_abilities_toggle_nonce' ) ); ?>" <?php checked( $abilities_enabled ); ?> />
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
									<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" class="mo_oauth_x_troubleshoot_3"><rect width="22" height="22" rx="5" fill="#CC785C"/><text x="11" y="15.5" text-anchor="middle" font-size="12" font-weight="700" font-family="Arial,sans-serif" fill="#fff">C</text></svg>
									<?php esc_html_e( 'Claude Desktop', 'miniorange-login-with-eve-online-google-facebook' ); ?>
								</div>
								<p><?php esc_html_e( 'Connect Claude Desktop to your WordPress site via MCP.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
								<a href="https://plugins.miniorange.com/connect-wordpress-with-claude-mcp-guide" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View setup guide →', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
							</div>
							<div class="mo_oauth_mcp_client_card">
								<div class="mo_oauth_mcp_client_name">
									<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" class="mo_oauth_x_troubleshoot_3"><rect width="22" height="22" rx="5" fill="#1a1a1a"/><polygon points="8,6 17,11 8,16" fill="#fff"/></svg>
									<?php esc_html_e( 'Cursor', 'miniorange-login-with-eve-online-google-facebook' ); ?>
								</div>
								<p><?php esc_html_e( 'Connect Cursor IDE to your WordPress site via MCP.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
								<a href="https://plugins.miniorange.com/connect-wordpress-with-cursor-mcp-guide" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View setup guide →', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
							</div>
							<div class="mo_oauth_mcp_client_card">
								<div class="mo_oauth_mcp_client_name">
									<svg width="22" height="22" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg" class="mo_oauth_x_troubleshoot_4"><path d="M37.532 16.87a9.963 9.963 0 0 0-.856-8.184 10.078 10.078 0 0 0-10.855-4.835 9.965 9.965 0 0 0-7.505-3.348 10.079 10.079 0 0 0-9.612 6.977 9.967 9.967 0 0 0-6.664 4.834 10.08 10.08 0 0 0 1.24 11.817 9.965 9.965 0 0 0 .856 8.185 10.079 10.079 0 0 0 10.855 4.835 9.965 9.965 0 0 0 7.504 3.347 10.08 10.08 0 0 0 9.617-6.981 9.967 9.967 0 0 0 6.663-4.834 10.079 10.079 0 0 0-1.243-11.813zM22.498 37.886a7.474 7.474 0 0 1-4.799-1.735c.061-.033.168-.091.237-.134l7.964-4.6a1.294 1.294 0 0 0 .655-1.134V19.054l3.366 1.944a.12.12 0 0 1 .066.092v9.299a7.505 7.505 0 0 1-7.49 7.496zM6.392 31.006a7.471 7.471 0 0 1-.894-5.023c.06.036.162.099.237.141l7.964 4.6a1.297 1.297 0 0 0 1.308 0l9.724-5.614v3.888a.12.12 0 0 1-.048.103l-8.051 4.649a7.504 7.504 0 0 1-10.24-2.744zM4.297 13.62A7.469 7.469 0 0 1 8.2 10.333c0 .068-.004.19-.004.274v9.201a1.294 1.294 0 0 0 .654 1.132l9.723 5.614-3.366 1.944a.12.12 0 0 1-.114.012L7.044 23.86a7.504 7.504 0 0 1-2.747-10.24zm27.658 6.437-9.724-5.615 3.367-1.943a.121.121 0 0 1 .114-.012l8.048 4.648a7.498 7.498 0 0 1-1.158 13.528v-9.476a1.293 1.293 0 0 0-.647-1.13zm3.35-5.043c-.059-.037-.162-.099-.236-.141l-7.965-4.6a1.298 1.298 0 0 0-1.308 0l-9.723 5.614v-3.888a.12.12 0 0 1 .048-.103l8.05-4.645a7.497 7.497 0 0 1 11.135 7.763zm-21.063 6.929-3.367-1.944a.12.12 0 0 1-.065-.092v-9.299a7.497 7.497 0 0 1 12.293-5.756 6.94 6.94 0 0 0-.236.134l-7.965 4.6a1.294 1.294 0 0 0-.654 1.132l-.006 11.225zm1.829-3.943 4.33-2.501 4.332 2.5v4.999l-4.331 2.5-4.331-2.5V18z" fill="#fff"/></svg>
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
		<?php
	}
}
