<?php
/**
 * Seasonal Sale Notice
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
 * Adds the dismissible seasonal sale (Black Friday, End of Year, ...) admin notice and its
 * "Claim Now" request pop-up for the miniOrange OAuth plugin. The campaign shown is picked
 * by date from get_campaigns(), so a new sale only needs a new entry there.
 */
class MO_OAuth_Seasonal_Sale {

	/**
	 * Option holding, per campaign ID, the timestamp until which the notice is snoozed.
	 *
	 * @var string
	 */
	const SNOOZE_OPTION = 'mo_oauth_sale_notice_snoozed_until';

	/**
	 * Nonce action and AJAX action used to dismiss the notice.
	 *
	 * @var string
	 */
	const DISMISS_ACTION = 'mo_oauth_dismiss_sale_notice';

	/**
	 * Nonce action and AJAX action used to submit the discount request.
	 *
	 * @var string
	 */
	const REQUEST_ACTION = 'mo_oauth_sale_request';

	/**
	 * Active campaign memo: false until computed, null when no sale is running.
	 *
	 * @var array|null|false
	 */
	private $campaign = false;

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
	 * Sale campaigns, keyed by ID. Start and end are read in the site's timezone.
	 *
	 * Filterable via `mo_oauth_sale_campaigns` so QA can preview a campaign outside its dates.
	 *
	 * @return array
	 */
	private function get_campaigns() {
		$campaigns = array(
			'black-friday' => array(
				'sale_name' => 'Black Friday',
				'label'     => __( 'Black Friday', 'miniorange-login-with-eve-online-google-facebook' ),
				'start'     => '2026-11-10 00:00:00',
				'end'       => '2026-12-06 23:59:59',
				'image'     => 'black-friday.png',
				'discount'  => '25%',
				'pill'      => __( 'Biggest Savings of the Year — Hurry!', 'miniorange-login-with-eve-online-google-facebook' ),
			),
			'end-of-year'  => array(
				'sale_name' => 'End of Year',
				'label'     => __( 'End of Year', 'miniorange-login-with-eve-online-google-facebook' ),
				'start'     => '2026-12-07 00:00:00',
				'end'       => '2027-01-15 23:59:59',
				'image'     => 'end-of-year.png',
				'discount'  => '25%',
				'pill'      => __( 'Last Big Deal of the Year — Hurry!', 'miniorange-login-with-eve-online-google-facebook' ),
			),
		);
		return apply_filters( 'mo_oauth_sale_campaigns', $campaigns );
	}

	/**
	 * The campaign running right now, with its `id` and `end_time` added.
	 *
	 * @return array|null
	 */
	private function get_active_campaign() {
		if ( false !== $this->campaign ) {
			return $this->campaign;
		}
		$this->campaign = null;
		$now            = time();
		foreach ( $this->get_campaigns() as $id => $campaign ) {
			$start = ( new DateTimeImmutable( $campaign['start'], wp_timezone() ) )->getTimestamp();
			$end   = ( new DateTimeImmutable( $campaign['end'], wp_timezone() ) )->getTimestamp();
			if ( $now >= $start && $now <= $end ) {
				$this->campaign = array_merge(
					$campaign,
					array(
						'id'       => $id,
						'end_time' => $end,
					)
				);
				break;
			}
		}
		return $this->campaign;
	}

	/**
	 * Checks whether the notice should be shown to the current user right now.
	 *
	 * @return bool
	 */
	private function should_display() {
		$campaign = $this->get_active_campaign();
		if ( ! $campaign || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$snoozed = (array) get_option( self::SNOOZE_OPTION, array() );
		return empty( $snoozed[ $campaign['id'] ] ) || time() >= (int) $snoozed[ $campaign['id'] ];
	}

	/**
	 * Enqueue the notice styles and script only when the notice is shown.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}
		$admin_url = plugin_dir_url( dirname( __DIR__ ) );
		wp_enqueue_style( 'mo_oauth_sale_style', $admin_url . 'css/mo-oauth-sale.min.css', array(), MO_OAUTH_CSS_JS_VERSION );
		wp_enqueue_script( 'mo_oauth_sale_script', $admin_url . 'js/mo-oauth-sale.min.js', array(), MO_OAUTH_CSS_JS_VERSION, true );
		wp_localize_script(
			'mo_oauth_sale_script',
			'moOauthSale',
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
	 * @param array  $campaign Active campaign.
	 * @param string $medium   Where on the page the link sits.
	 * @return string
	 */
	private function get_pricing_url( $campaign, $medium ) {
		return add_query_arg(
			array(
				'utm_source'   => 'plugin',
				'utm_medium'   => $medium,
				'utm_campaign' => 'oauth-' . $campaign['id'],
			),
			MO_OAUTH_CLIENT_PRICING_PLAN
		);
	}

	/**
	 * URL of the campaign artwork in the plugin's root images folder.
	 *
	 * @param array $campaign Active campaign.
	 * @return string
	 */
	private function get_image_url( $campaign ) {
		// plugins_url() resolves relative to the directory containing its second argument,
		// so passing the admin folder resolves to the plugin root.
		return plugins_url( 'images/' . $campaign['image'], dirname( __DIR__, 2 ) );
	}

	/**
	 * Show the sale banner.
	 */
	public function display_notice() {
		if ( ! $this->should_display() ) {
			return;
		}
		$campaign = $this->get_active_campaign();
		/* translators: %s: sale name, e.g. "Black Friday". */
		$sale_title = sprintf( __( '%s Sale', 'miniorange-login-with-eve-online-google-facebook' ), $campaign['label'] );
		?>
		<div class="notice mo_oauth_sale_notice mo_oauth_sale--<?php echo esc_attr( $campaign['id'] ); ?>" role="region" aria-label="<?php echo esc_attr( 'miniOrange ' . $sale_title ); ?>">
			<div class="mo_oauth_sale_content">
				<img class="mo_oauth_sale_logo" src="<?php echo esc_url( $this->get_image_url( $campaign ) ); ?>" alt="<?php echo esc_attr( $sale_title . ' - miniOrange' ); ?>" height="44">
				<p class="mo_oauth_sale_text">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: discount, e.g. "40%". */
							esc_html__( 'FLAT %s on OAuth Single Sign-On Paid Plans', 'miniorange-login-with-eve-online-google-facebook' ),
							'<span class="mo_oauth_sale_highlight">' . esc_html( $campaign['discount'] ) . '</span>'
						)
					);
					?>
					<span class="mo_oauth_sale_sparkle" aria-hidden="true"></span>
				</p>
				<a class="mo_oauth_sale_button" href="<?php echo esc_url( $this->get_pricing_url( $campaign, 'ribbon' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog"><?php esc_html_e( 'Claim Now', 'miniorange-login-with-eve-online-google-facebook' ); ?></a>
			</div>
			<div class="mo_oauth_sale_dismiss_wrap">
				<button type="button" class="mo_oauth_sale_dismiss" aria-haspopup="true" aria-expanded="false" aria-controls="mo_oauth_sale_dismiss_menu">
					<span aria-hidden="true">&times;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
				</button>
				<div class="mo_oauth_sale_dismiss_menu" id="mo_oauth_sale_dismiss_menu" role="menu" hidden>
					<button type="button" role="menuitem" data-mo-oauth-sale-snooze="day"><?php esc_html_e( 'Remind me tomorrow', 'miniorange-login-with-eve-online-google-facebook' ); ?></button>
					<button type="button" role="menuitem" data-mo-oauth-sale-snooze="three_days"><?php esc_html_e( 'Remind me in 3 days', 'miniorange-login-with-eve-online-google-facebook' ); ?></button>
					<button type="button" role="menuitem" data-mo-oauth-sale-snooze="sale"><?php esc_html_e( "Don't show again for this sale", 'miniorange-login-with-eve-online-google-facebook' ); ?></button>
				</div>
			</div>
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
		$campaign = $this->get_active_campaign();
		$email    = wp_get_current_user()->user_email;
		?>
		<div class="mo_oauth_sale_modal" id="mo_oauth_sale_modal" hidden>
			<div class="mo_oauth_sale_modal_backdrop" data-mo-oauth-sale-close></div>
			<div class="mo_oauth_sale_modal_dialog mo_oauth_sale--<?php echo esc_attr( $campaign['id'] ); ?>" role="dialog" aria-modal="true" aria-labelledby="mo_oauth_sale_modal_title">
				<button type="button" class="mo_oauth_sale_modal_close" data-mo-oauth-sale-close>
					<span aria-hidden="true">&times;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
				</button>

				<div class="mo_oauth_sale_hero">
					<?php /* translators: %s: sale name, e.g. "Black Friday". */ ?>
					<img class="mo_oauth_sale_hero_logo" src="<?php echo esc_url( $this->get_image_url( $campaign ) ); ?>" alt="<?php echo esc_attr( sprintf( __( '%s Sale', 'miniorange-login-with-eve-online-google-facebook' ), $campaign['label'] ) . ' - miniOrange' ); ?>" height="56">
					<h2 class="mo_oauth_sale_hero_title" id="mo_oauth_sale_modal_title">
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: discount, e.g. "40% Off". */
								esc_html__( 'FLAT %s on WordPress OAuth SSO', 'miniorange-login-with-eve-online-google-facebook' ),
								'<span class="mo_oauth_sale_highlight">' . esc_html( $campaign['discount'] ) . ' ' . esc_html__( 'Off', 'miniorange-login-with-eve-online-google-facebook' ) . '</span>'
							)
						);
						?>
					</h2>
					<span class="mo_oauth_sale_pill"><?php echo esc_html( $campaign['pill'] ); ?></span>
					<p class="mo_oauth_sale_deadline">
						<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						<?php
						/* translators: %s: sale end date. */
						echo esc_html( sprintf( __( 'Now Live Till %s', 'miniorange-login-with-eve-online-google-facebook' ), wp_date( 'M j, Y', $campaign['end_time'] ) ) );
						?>
					</p>
				</div>

				<form class="mo_oauth_sale_form" novalidate>
					<label class="mo_oauth_sale_label" for="mo_oauth_sale_email">
						<?php esc_html_e( 'Where should we send your discount?', 'miniorange-login-with-eve-online-google-facebook' ); ?>
						<span class="mo_oauth_sale_required" aria-hidden="true">*</span>
					</label>
					<div class="mo_oauth_sale_field">
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 7l8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<input type="email" id="mo_oauth_sale_email" name="email" value="<?php echo esc_attr( $email ); ?>" placeholder="you@company.com" autocomplete="email" required aria-required="true">
					</div>
					<label class="mo_oauth_sale_label" for="mo_oauth_sale_usecase">
						<?php esc_html_e( 'Tell us your SSO wish list', 'miniorange-login-with-eve-online-google-facebook' ); ?>
						<span class="mo_oauth_sale_optional"><?php esc_html_e( '(optional)', 'miniorange-login-with-eve-online-google-facebook' ); ?></span>
					</label>
					<textarea class="mo_oauth_sale_usecase" id="mo_oauth_sale_usecase" name="usecase" rows="3" maxlength="1000" placeholder="<?php esc_attr_e( 'e.g. SSO with Azure AD, role mapping, multiple providers…', 'miniorange-login-with-eve-online-google-facebook' ); ?>"></textarea>
					<p class="mo_oauth_sale_status" role="alert"></p>
					<button type="submit" class="mo_oauth_sale_submit"><?php esc_html_e( 'Submit Request', 'miniorange-login-with-eve-online-google-facebook' ); ?></button>
					<p class="mo_oauth_sale_note">
						<?php
						/* translators: %s: sale name, e.g. "Black Friday". */
						echo esc_html( sprintf( __( 'Our team will get back to you with your %s discount on this email.', 'miniorange-login-with-eve-online-google-facebook' ), $campaign['label'] ) );
						?>
					</p>
				</form>

				<div class="mo_oauth_sale_success" hidden>
					<span class="mo_oauth_sale_success_icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<h3 class="mo_oauth_sale_success_title" tabindex="-1"><?php esc_html_e( 'Request sent!', 'miniorange-login-with-eve-online-google-facebook' ); ?></h3>
					<p>
						<?php
						/* translators: %s: sale name, e.g. "Black Friday". */
						echo esc_html( sprintf( __( 'Our team will reach out to you shortly with your %s discount at:', 'miniorange-login-with-eve-online-google-facebook' ), $campaign['label'] ) );
						?>
						<strong class="mo_oauth_sale_success_email"></strong>
					</p>
					<a class="mo_oauth_sale_pricing_button" href="<?php echo esc_url( $this->get_pricing_url( $campaign, 'popup' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Explore Pricing Plans', 'miniorange-login-with-eve-online-google-facebook' ); ?> &#8599;</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Snooze the notice for the site when an admin picks one of the close options.
	 */
	public function dismiss_notice() {
		check_ajax_referer( self::DISMISS_ACTION, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		$campaign = $this->get_active_campaign();
		if ( ! $campaign ) {
			wp_send_json_success();
		}
		$duration = isset( $_POST['duration'] ) ? sanitize_key( wp_unslash( $_POST['duration'] ) ) : '';
		switch ( $duration ) {
			case 'day':
				$snooze_until = time() + DAY_IN_SECONDS;
				break;
			case 'three_days':
				$snooze_until = time() + 3 * DAY_IN_SECONDS;
				break;
			case 'sale':
				$snooze_until = $campaign['end_time'];
				break;
			default:
				wp_send_json_error( null, 400 );
		}
		// Drop entries for campaigns that no longer exist so the option does not grow forever.
		$snoozed                    = array_intersect_key( (array) get_option( self::SNOOZE_OPTION, array() ), $this->get_campaigns() );
		$snoozed[ $campaign['id'] ] = $snooze_until;
		update_option( self::SNOOZE_OPTION, $snoozed );
		wp_send_json_success();
	}

	/**
	 * Email the discount request from the pop-up to miniOrange support.
	 */
	public function request_discount() {
		check_ajax_referer( self::REQUEST_ACTION, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		$campaign = $this->get_active_campaign();
		if ( ! $campaign ) {
			wp_send_json_error( array( 'message' => __( 'This offer has ended.', 'miniorange-login-with-eve-online-google-facebook' ) ) );
		}
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'miniorange-login-with-eve-online-google-facebook' ) ) );
		}
		$use_case = isset( $_POST['usecase'] ) ? sanitize_textarea_field( wp_unslash( $_POST['usecase'] ) ) : '';
		$customer = new MO_OAuth_Client_Customer();
		if ( ! $customer->mo_oauth_send_sale_request( $email, $campaign['sale_name'], $use_case ) ) {
			wp_send_json_error( array( 'message' => __( 'We could not send your request right now. Please try again, or email us at oauthsupport@xecurify.com.', 'miniorange-login-with-eve-online-google-facebook' ) ) );
		}
		wp_send_json_success();
	}
}
new MO_OAuth_Seasonal_Sale();
