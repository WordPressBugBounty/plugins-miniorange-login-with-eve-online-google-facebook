<?php
/**
 * Admin Menu
 *
 * @package    admin-menu
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Adds Black Friday Sale Admin Notice for miniOrange OAuth Plugin
 */
class MO_OAuth_Black_Friday_Notice {
	/**
	 * Sale end date
	 *
	 * @var time
	 */
	private $sale_end_time;
	/**
	 * Initializing Sale banner
	 */
	public function __construct() {
		$this->sale_end_time = strtotime( '2024-12-31 23:59:59 ' . wp_timezone_string() );
		add_action( 'admin_notices', array( $this, 'display_black_friday_notice' ) );
		add_action( 'wp_ajax_mo_oauth_dismiss_black_friday_notice', array( $this, 'dismiss_black_friday_notice' ) );
	}

	/**
	 * Show sale banner
	 *
	 * @return [html]
	 */
	public function display_black_friday_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( current_time( 'timestamp' ) > $this->sale_end_time ) {
			return;
		}

		// Check if notice has been dismissed.
		if ( get_option( 'mo_oauth_black_friday_notice_dismissed' ) ) {
			return;
		}
		?>        
		<div class="notice notice-info mo_oauth_black_friday_notice">

			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../images/mini.png' ); ?>" alt="miniOrange Logo" class="mo_oauth_black_friday_logo">
			
			<div style="display: flex;" class="mo_oauth_black_friday_content">
				<div>
					<div class="mo_oauth_black_friday_headline">
						miniOrange End Year sale is here!
					</div>
					
					<div class="mo_oauth_black_friday_description">
						Go <strong>PRO</strong> with our biggest discount of the year. 
						Unlock premium features and save big on our OAuth plugin!
					</div>
				 </div>
				<div class="mo_oauth_black_friday_actions">
					<a href="https://plugins.miniorange.com/wordpress-oauth-black-friday-sale" 
					   target="_blank" 
					   class="mo_oauth_black_friday_cta">
						Claim Discount Now
					</a>
				</div>
			</div>
			
			<a href="#" 
			   class="mo_oauth_black_friday_close" 
			   id="mo_oauth_black_friday_notice_dismiss">
				&times;
			</a>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#mo_oauth_black_friday_notice_dismiss').on('click', function(e) {
				e.preventDefault();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'mo_oauth_dismiss_black_friday_notice',
						nonce: '<?php echo esc_attr( wp_create_nonce( 'mo_oauth_black_friday_notice_nonce' ) ); ?>'
					},
					success: function(response) {
						$('.mo_oauth_black_friday_notice').fadeOut();
					}
				});
			});
		});
		</script>
		<?php
	}
	/**
	 * Sale banner security
	 */
	public function dismiss_black_friday_notice() {
		// Verify nonce for security.
		check_ajax_referer( 'mo_oauth_black_friday_notice_nonce', 'nonce' );

		// Set option to dismiss notice.
		update_option( 'mo_oauth_black_friday_notice_dismissed', true );
		wp_send_json_success();
	}
}

// Initialize the admin notice.
new MO_OAuth_Black_Friday_Notice();
?>
