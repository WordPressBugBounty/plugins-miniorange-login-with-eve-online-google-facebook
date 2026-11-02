<?php
/**
 * Halloween Sale Notice
 *
 * @package    Notice
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the dismissible Halloween Sale admin notice and its "Claim Now" request pop-up
 * for the miniOrange OAuth plugin.
 */
class MO_OAuth_Halloween_Sale {

	/**
	 * Option holding the timestamp until which the notice is snoozed.
	 *
	 * @var string
	 */
	const SNOOZE_OPTION = 'mo_oauth_halloween_sale_notice_snoozed_until';

	/**
	 * Nonce action and AJAX action used to dismiss the notice.
	 *
	 * @var string
	 */
	const DISMISS_ACTION = 'mo_oauth_dismiss_halloween_sale_notice';

	/**
	 * Nonce action and AJAX action used to submit the discount request.
	 *
	 * @var string
	 */
	const REQUEST_ACTION = 'mo_oauth_halloween_sale_request';

	/**
	 * How long the notice stays hidden after it is dismissed.
	 *
	 * @var int
	 */
	const SNOOZE_DURATION = 3 * DAY_IN_SECONDS;

	/**
	 * Initializing required hooks
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display_notice' ) );
		add_action( 'admin_footer', array( $this, 'display_modal' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_' . self::DISMISS_ACTION, array( $this, 'dismiss_notice' ) );
		add_action( 'wp_ajax_' . self::REQUEST_ACTION, array( $this, 'request_discount' ) );
	}

	/**
	 * Sale end as a timestamp. The end date is read in the site's timezone so the
	 * notice disappears at midnight local time, not midnight UTC.
	 *
	 * @return int
	 */
	private function get_sale_end() {
		$sale_end = new DateTimeImmutable( MO_OAUTH_CLIENT_HALLOWEEN_SALE_END_DATE, wp_timezone() );
		return $sale_end->getTimestamp();
	}

	/**
	 * Checks whether the notice should be shown to the current user right now.
	 *
	 * @return bool
	 */
	private function should_display() {
		if ( ! current_user_can( 'manage_options' ) || time() > $this->get_sale_end() ) {
			return false;
		}
		return time() >= (int) get_option( self::SNOOZE_OPTION, 0 );
	}

	/**
	 * Enqueue the notice styles and script only when the notice is shown.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}
		$admin_url = plugin_dir_url( dirname( __DIR__ ) );
		wp_enqueue_style( 'mo_oauth_halloween_sale_style', $admin_url . 'css/mo-oauth-halloween-sale.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
		wp_enqueue_script( 'mo_oauth_halloween_sale_script', $admin_url . 'js/mo-oauth-halloween-sale.min.js', array(), MO_OAUTH_CSS_JS_VERSION, true );
		wp_localize_script(
			'mo_oauth_halloween_sale_script',
			'moOauthHalloweenSale',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'dismissAction' => self::DISMISS_ACTION,
				'dismissNonce'  => wp_create_nonce( self::DISMISS_ACTION ),
				'requestAction' => self::REQUEST_ACTION,
				'requestNonce'  => wp_create_nonce( self::REQUEST_ACTION ),
				'i18n'          => array(
					'invalidEmail' => __( 'Please enter a valid email address.', 'miniorange-login-with-eve-online-google-facebook' ),
					'error'        => __( 'We could not send your request right now. Please try again, or email us at oauthsupport@xecurify.com.', 'miniorange-login-with-eve-online-google-facebook' ),
				),
			)
		);
	}

	/**
	 * Pricing page link with campaign tracking.
	 *
	 * @param string $medium Where on the page the link sits.
	 * @return string
	 */
	private function get_pricing_url( $medium ) {
		return add_query_arg(
			array(
				'utm_source'   => 'plugin',
				'utm_medium'   => $medium,
				'utm_campaign' => 'oauth-halloween',
			),
			MO_OAUTH_CLIENT_PRICING_PLAN
		);
	}

	/**
	 * Print the "Halloween SALE" lettering used in the banner and the pop-up.
	 */
	private function display_sale_lettering() {
		?>
		<span class="mo_oauth_halloween_sale_title"><?php esc_html_e( 'Halloween', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
		<span class="mo_oauth_halloween_sale_tag"><?php esc_html_e( 'SALE', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
		<?php
	}

	/**
	 * Show the sale banner.
	 */
	public function display_notice() {
		if ( ! $this->should_display() ) {
			return;
		}
		?>
		<div class="notice mo_oauth_halloween_sale_notice" role="region" aria-label="<?php esc_attr_e( 'miniOrange Halloween Sale', 'miniorange-login-with-eve-online-google-facebook' ); ?>">
			<div class="mo_oauth_halloween_sale_content">
				<div class="mo_oauth_halloween_sale_brand">
					<svg class="mo_oauth_halloween_sale_icon" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true" focusable="false">
						<path d="M19 9.5c0-2.6.6-4.6 2.2-6l1.6 1.2c-1.2 1.2-1.8 2.8-1.8 4.8z" fill="#4d7c2f"/>
						<path d="M22 6.5c1.2-3.6 4.2-5.2 8.4-4.6-.9 3.8-3.8 5.6-8.4 4.6z" fill="#6cbf4b"/>
						<ellipse cx="13" cy="23.5" rx="9" ry="13" fill="#d9480f"/>
						<ellipse cx="27" cy="23.5" rx="9" ry="13" fill="#d9480f"/>
						<ellipse cx="20" cy="23.5" rx="9.5" ry="14" fill="#f76707"/>
						<circle cx="20" cy="21" r="4" fill="#ffd43b"/>
						<path d="M18 23.5h4l1.2 8h-6.4z" fill="#ffd43b"/>
					</svg>
					<?php $this->display_sale_lettering(); ?>
				</div>
				<p class="mo_oauth_halloween_sale_text">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: discount, e.g. "20% OFF". */
							esc_html__( 'Get Flat %s on OAuth Single Sign-On Paid Plans', 'miniorange-login-with-eve-online-google-facebook' ),
							'<span class="mo_oauth_halloween_sale_highlight">20% OFF</span>'
						)
					);
					?>
					<span class="mo_oauth_halloween_sale_sparkle" aria-hidden="true"></span>
				</p>
				<a class="mo_oauth_halloween_sale_button" href="<?php echo esc_url( $this->get_pricing_url( 'ribbon' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog"><?php esc_html_e( 'Claim Now', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
			</div>
			<button type="button" class="mo_oauth_halloween_sale_dismiss">
				<span aria-hidden="true">&times;</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
			</button>
		</div>
		<?php
	}

	/**
	 * Print the "Claim Now" pop-up at the end of the page so it is never clipped by
	 * wherever core moves the notice to.
	 */
	public function display_modal() {
		if ( ! $this->should_display() ) {
			return;
		}
		$email = wp_get_current_user()->user_email;
		?>
		<div class="mo_oauth_halloween_sale_modal" id="mo_oauth_halloween_sale_modal" hidden>
			<div class="mo_oauth_halloween_sale_modal_backdrop" data-mo-oauth-halloween-sale-close></div>
			<div class="mo_oauth_halloween_sale_modal_dialog" role="dialog" aria-modal="true" aria-labelledby="mo_oauth_halloween_sale_modal_title">
				<button type="button" class="mo_oauth_halloween_sale_modal_close" data-mo-oauth-halloween-sale-close>
					<span aria-hidden="true">&times;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
				</button>

				<div class="mo_oauth_halloween_sale_hero">
					<div class="mo_oauth_halloween_sale_hero_info">
						<span class="mo_oauth_halloween_sale_wordmark">mini<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M19.52 10.26A8 8 0 1 1 13.39 5.12" fill="none" stroke="#f7931e" stroke-width="3" stroke-linecap="round"/><circle cx="12" cy="12.2" r="2.2" fill="#f7931e"/><path d="M11 13.6h2l.6 3.4h-3.2z" fill="#f7931e"/><path d="M15.5 6.5c.5-2.6 2.5-4 5.5-4-.4 2.8-2.4 4.2-5.5 4z" fill="#6cbf4b"/></svg>range<span class="screen-reader-text">miniOrange</span></span>
						<h2 class="mo_oauth_halloween_sale_hero_title" id="mo_oauth_halloween_sale_modal_title">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: discount, e.g. "20% Off". */
									esc_html__( 'Get Flat %s on WordPress OAuth SSO', 'miniorange-login-with-eve-online-google-facebook' ),
									'<span class="mo_oauth_halloween_sale_highlight">20% Off</span>'
								)
							);
							?>
						</h2>
						<span class="mo_oauth_halloween_sale_pill"><?php esc_html_e( 'Spookiest Savings of the Season — Hurry!', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
						<p class="mo_oauth_halloween_sale_deadline">
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							<?php
							/* translators: %s: sale end date. */
							echo esc_html( sprintf( __( 'Now Live Till %s', 'miniorange-login-with-eve-online-google-facebook' ), wp_date( 'M j, Y', $this->get_sale_end() ) ) );
							?>
						</p>
					</div>
					<div class="mo_oauth_halloween_sale_hero_art" aria-hidden="true">
						<?php $this->display_sale_lettering(); ?>
						<span class="mo_oauth_halloween_sale_badge"><?php esc_html_e( 'FLAT', 'miniorange-login-with-eve-online-google-facebook' ); ?><strong>20%</strong><?php esc_html_e( 'OFF', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
					</div>
				</div>

				<form class="mo_oauth_halloween_sale_form" novalidate>
					<label class="mo_oauth_halloween_sale_label" for="mo_oauth_halloween_sale_email">
						<?php esc_html_e( 'Where should we send your discount?', 'miniorange-login-with-eve-online-google-facebook' ); ?>
						<span class="mo_oauth_halloween_sale_required" aria-hidden="true">*</span>
					</label>
					<div class="mo_oauth_halloween_sale_field">
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 7l8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<input type="email" id="mo_oauth_halloween_sale_email" name="email" value="<?php echo esc_attr( $email ); ?>" placeholder="you@company.com" autocomplete="email" required aria-required="true">
					</div>
					<label class="mo_oauth_halloween_sale_label" for="mo_oauth_halloween_sale_usecase">
						<?php esc_html_e( 'Tell us your SSO wish list', 'miniorange-login-with-eve-online-google-facebook' ); ?>
						<span class="mo_oauth_halloween_sale_optional"><?php esc_html_e( '(optional)', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
					</label>
					<textarea class="mo_oauth_halloween_sale_usecase" id="mo_oauth_halloween_sale_usecase" name="usecase" rows="3" maxlength="1000" placeholder="<?php esc_attr_e( 'e.g. SSO with Azure AD, role mapping, multiple providers…', 'miniorange-login-with-eve-online-google-facebook' ); ?>"></textarea>
					<p class="mo_oauth_halloween_sale_status" role="alert"></p>
					<button type="submit" class="mo_oauth_halloween_sale_submit"><?php esc_html_e( 'Submit Request', 'miniorange-login-with-eve-online-google-facebook' ); ?></button>
					<p class="mo_oauth_halloween_sale_note"><?php esc_html_e( 'Our team will get back to you with your Halloween discount on this email.', 'miniorange-login-with-eve-online-google-facebook' ); ?></p>
				</form>

				<div class="mo_oauth_halloween_sale_success" hidden>
					<span class="mo_oauth_halloween_sale_success_icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<h3 class="mo_oauth_halloween_sale_success_title" tabindex="-1"><?php esc_html_e( 'Request sent!', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
					<p><?php esc_html_e( 'Our team will reach out to you shortly with your Halloween discount at:', 'miniorange-login-with-eve-online-google-facebook' ); ?> <strong class="mo_oauth_halloween_sale_success_email"></strong></p>
					<a class="mo_oauth_halloween_sale_pricing_button" href="<?php echo esc_url( $this->get_pricing_url( 'popup' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Explore Pricing Plans', 'miniorange-login-with-eve-online-google-facebook' ); ?> &#8599;</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Snooze the notice for the site when an admin dismisses it.
	 */
	public function dismiss_notice() {
		check_ajax_referer( self::DISMISS_ACTION, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		update_option( self::SNOOZE_OPTION, time() + self::SNOOZE_DURATION );
		wp_send_json_success();
	}

	/**
	 * Email the Halloween discount request from the pop-up to miniOrange support.
	 */
	public function request_discount() {
		check_ajax_referer( self::REQUEST_ACTION, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'miniorange-login-with-eve-online-google-facebook' ) ) );
		}
		$use_case = isset( $_POST['usecase'] ) ? sanitize_textarea_field( wp_unslash( $_POST['usecase'] ) ) : '';
		$customer = new MO_OAuth_Client_Customer();
		if ( ! $customer->mo_oauth_send_halloween_sale_request( $email, $use_case ) ) {
			wp_send_json_error( array( 'message' => __( 'We could not send your request right now. Please try again, or email us at oauthsupport@xecurify.com.', 'miniorange-login-with-eve-online-google-facebook' ) ) );
		}
		wp_send_json_success();
	}
}
new MO_OAuth_Halloween_Sale();
