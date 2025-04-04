<?php
/**
 * Displays admin notices for REST API Plugin advertisement.
 *
 * @package    Advertisement
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

namespace MO_Plugins;

use MOOAuth_Debug;

/**
 * Handles operations related to REST API Plugin advertisement.
 */
class MO_REST_API_Advertisement {

	private const REST_API_PLUGIN_NAME   = 'wp-rest-api-authentication';
	private const REST_API_PLUGIN_FILE   = '/miniorange-api-authentication.php';
	private const NOTICE_STATE_TEMPORARY = 'temporary';
	private const NOTICE_STATE_PERMANENT = 'permanent';
	private const REST_API_PLUGIN_PATH   = self::REST_API_PLUGIN_NAME . self::REST_API_PLUGIN_FILE;

	/**
	 * Instance of the class.
	 *
	 * @var MO_REST_API_Advertisement
	 */
	private static $instance = null;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display_advertisement' ) );
		add_action( 'wp_ajax_install_and_activate_rest_api_free', array( $this, 'install_and_activate_rest_api_free' ) );
		add_action( 'wp_ajax_test_api_security', array( $this, 'test_api_security' ) );
		add_action( 'wp_ajax_mo_rest_api_plugin_adv_dismiss_notice', array( $this, 'mo_rest_api_dissmiss_notice' ) );
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return MO_REST_API_Advertisement
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Display plugin advertisement.
	 *
	 * @return void
	 */
	public static function display_advertisement() {
		$plugin_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::REST_API_PLUGIN_PATH;

		$pages = array(
			'dashboard',
			'plugins',
			'toplevel_page_mo_oauth_settings',
		);

		$current_screen = get_current_screen();

		if ( ! is_null( $current_screen ) && ! in_array( $current_screen->id, $pages, true ) ) {
			return;
		}

		$dismiss_status = get_option( 'mo_rest_api_plugin_adv_notice_dismissed', 'show' );
		$dismiss_time   = get_option( 'mo_rest_api_plugin_adv_notice_dismissed_time', 0 );
		$test_status    = get_option( 'mo_adv_rest_api_security_status', array() );

		// Do not show banner again if it was dismissed after testing endpoint, dismissed temporarily for 7 days or dismissed permanently.
		if ( ! empty( $test_status ) && 'show' !== $dismiss_status || self::NOTICE_STATE_PERMANENT === $dismiss_status || ( self::NOTICE_STATE_TEMPORARY === $dismiss_status && time() < $dismiss_time ) ) {
			return;
		}

		$plugin_exists = file_exists( $plugin_path );
		$plugin_active = $plugin_exists && is_plugin_active( self::REST_API_PLUGIN_PATH );

		$apps_list = get_option( 'mo_oauth_apps_list', array() );

		// Check if REST API Plugin is active and OAuth Client Plugin is set up.
		if ( ! $plugin_active && ! empty( $apps_list ) ) {
			$has_test_status = ! empty( $test_status['status'] );
			wp_enqueue_style( 'mo_plugin_adv_style', plugins_url( '../resources/css/mo-plugin-adv.css', __FILE__ ), array(), '1.0.0' );
			wp_enqueue_script( 'mo_plugin_adv_script', plugins_url( '../resources/js/mo-plugin-adv.js', __FILE__ ), array(), '1.0.0', true );

			?>
			<div id="mo_rest_api_plugin_adv_notice" class="notice is-dismissible">
				<div id="mo_rest_api_plugin_adv_content">
					<h6 class="mo_rest_api_plugin_adv_title">
						<?php
						if ( $has_test_status && 'SECURE' === $test_status['status'] ) {
							echo '⚠️ Security Notice: Your WordPress APIs could be open and data can be accessed publicly!';
						} elseif ( $has_test_status && 'SOME_SECURE' === $test_status['status'] ) {
							echo '⚠️ Security Notice: Some of your WordPress APIs are open and can be accessed publicly!';
						} elseif ( $has_test_status && 'NONE_SECURE' === $test_status['status'] ) {
							echo '⚠️ OPEN APIs: Your WordPress APIs are open and can be accessed publicly!';
						} else {
							echo '⚠️ OPEN APIs: Your WordPress APIs could be open!';
						}
						?>
					</h6>
					<hr class="mo_rest_api_plugin_adv_separator">
					<div>
						<p class="mo_rest_api_plugin_adv_description">
							miniOrange API Security is not enabled on your site. This means your <b>WordPress API endpoints could be exposed, 
							leaving your website vulnerable to unauthorized access or attacks.</b>
							<br>
							<b><?php echo esc_html( $plugin_exists ? 'Activate' : 'Install' ); ?> our <a href="<?php echo esc_url( 'https://wordpress.org/plugins/wp-rest-api-authentication/' ); ?>" target="__blank">REST API Authentication Plugin</a></b> to protect APIs and ensure secure, controlled access to your site APIs.
						</p>
						<?php if ( $has_test_status ) : ?>
						<p id="mo_rest_api_plugin_security_notice" class="mo_rest_api_plugin_security_notice">
							<b>
							<?php
							if ( 'SECURE' === $test_status['status'] ) {
								echo 'Please check the settings of the installed plugin to verify the security of all API endpoints.';
							} elseif ( 'SOME_SECURE' === $test_status['status'] ) {
								echo 'Please find some of the open endpoints below:';
							} elseif ( 'NONE_SECURE' === $test_status['status'] ) {
								echo 'Please find some of the open endpoints below:';
							}
							?>
							</b>
						</p>
						<?php endif; ?>
						<?php if ( ! empty( $test_status['open_endpoints'] ) ) : ?>
							<ul id="mo_rest_api_plugin_open_endpoints">
							<?php foreach ( $test_status['open_endpoints'] as $endpoint ) : ?>
								<li class="mo_rest_api_plugin_security_notice"><a href="<?php echo esc_url_raw( $endpoint['url'] ); ?>" target="_blank"><?php echo esc_url_raw( $endpoint['url'] ); ?></a></li>
							<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div class="mo_rest_api_plugin_adv_action">
							<div>
								<button id="mo_rest_api_plugin_adv_button" <?php echo $has_test_status ? '' : esc_attr( 'hidden="true"' ); ?> type="button" class="mo_rest_api_plugin_adv_button"
										onclick="mo_rest_api_plugin_adv_install_activate('<?php echo esc_js( wp_create_nonce( 'mo_plugins_adv_install_and_activate_rest_api_free' ) ); ?>')">
										<?php
										echo esc_html(
											$plugin_exists
												? ( $plugin_active ? 'Go to Settings' : 'Activate Now' )
												: 'Install Now'
										);
										?>
								</button>
								<button id="mo_rest_api_plugin_adv_test_button" <?php echo ! $has_test_status ? '' : esc_attr( 'hidden="true"' ); ?> type="button" class="mo_rest_api_plugin_adv_button"
										onclick="mo_plugins_test_api_security('<?php echo esc_js( wp_create_nonce( 'mo_plugins_adv_test_api_security' ) ); ?>', this.id, '<?php echo esc_js( esc_url( site_url() ) ); ?>')">
										Test API Security
								</button>
							</div>
							<svg class="mo_rest_api_plugin_adv_logo" xmlns="http://www.w3.org/2000/svg" width="120" height="100" viewBox="0 0 500 250" preserveAspectRatio="xMidYMid meet" fill="none">
								<path d="M96.5972 207.471C96.0169 207.721 95.4233 208.195 95.2791 208.527C95.135 208.855 95.0084 210.693 95.0011 212.605C94.9871 215.53 95.096 216.226 95.6726 216.975C96.3435 217.846 96.467 217.867 100.987 217.867C105.215 217.867 105.662 217.807 106.122 217.154C106.488 216.633 106.625 215.374 106.614 212.516C106.607 210.357 106.488 208.395 106.344 208.152C106.203 207.913 105.532 207.553 104.857 207.35C104.178 207.15 102.281 206.993 100.639 207C98.9943 207.007 97.1768 207.218 96.5972 207.471ZM162.264 207.236C161.845 207.321 161.252 207.742 160.942 208.17C160.513 208.766 160.387 209.84 160.387 212.784C160.39 215.713 160.513 216.744 160.918 217.154C161.223 217.465 162.443 217.782 163.817 217.914C165.121 218.035 167.216 218.028 168.474 217.893C169.855 217.743 171.004 217.407 171.384 217.044C171.908 216.54 172.01 215.838 171.999 212.783C171.993 210.771 171.831 208.805 171.637 208.413C171.444 208.02 170.773 207.535 170.143 207.335C169.515 207.136 167.655 207 166.014 207.029C164.369 207.057 162.682 207.154 162.264 207.236Z" fill="#5F6062"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M37.0763 222.018C39.2465 222 41.1481 222.216 42.7316 222.651C44.0429 223.015 45.9516 223.73 46.9731 224.242C47.9945 224.755 49.7018 225.89 52.7166 228.359L54.2188 226.99C55.0459 226.237 56.594 225.137 57.665 224.543C58.7324 223.952 60.7224 223.139 62.0832 222.736C63.7939 222.23 65.5683 222.004 67.8268 222C69.9157 221.996 71.9552 222.226 73.4821 222.644C74.7934 223.001 76.9 223.836 78.1651 224.5C79.427 225.165 81.4173 226.661 82.5838 227.828C83.7502 228.995 85.2133 230.905 85.8317 232.072C86.4538 233.239 87.2808 235.227 87.6698 236.493C88.0619 237.756 88.5463 239.824 88.7476 241.09C88.9561 242.385 89.1188 249.143 89.1188 256.563C89.1225 263.809 89.1188 270.798 89.1188 270.798H77.4586L77.2818 241.621L76.2214 239.499C75.4862 238.024 74.6909 237.052 73.627 236.316C72.2485 235.365 71.6342 234.84 68.7288 234.72C66.6364 234.632 65.1193 235.149 64.2038 235.453C63.4262 235.712 62.2526 236.408 61.5953 237.003C60.9379 237.6 60.0648 238.721 59.6513 239.5C58.9337 240.858 58.8949 241.498 58.7252 255.414C58.6263 263.389 58.7252 270.798 58.7252 270.798H46.7151C46.7151 270.798 46.7115 263.651 46.7151 256.21C46.7221 244.288 46.6478 242.453 46.1034 240.737C45.6828 239.418 45.0006 238.308 43.9756 237.289C43.0107 236.327 41.9255 235.645 40.9641 235.394C40.137 235.178 38.2283 234.65 36.5601 234.72C33.9764 234.83 33.1918 235.397 31.9264 236.228C31.1135 236.762 30.0602 237.798 29.583 238.527C29.1059 239.256 28.5333 240.568 28.307 241.445C28.0419 242.47 27.8475 247.977 27.6213 270.798H16.0456C16.0456 270.798 15.943 262.646 16.0456 255.06C16.2189 242.332 16.293 241.048 17.0034 238.438C17.4276 236.882 18.343 234.414 19.0358 232.957C19.9689 230.994 20.9516 229.657 22.8107 227.825C24.1927 226.46 26.1969 224.9 27.2678 224.356C28.3353 223.811 30.244 223.068 31.5093 222.704C32.9655 222.287 35.005 222.036 37.0763 222.018ZM133.57 222.025C135.941 222.004 137.896 222.191 139.225 222.57C140.342 222.888 142.371 223.705 143.732 224.391C145.199 225.13 147.008 226.442 148.178 227.616C149.264 228.706 150.55 230.233 151.038 231.011C151.522 231.79 152.431 233.699 153.049 235.255C153.678 236.833 154.332 239.337 154.534 240.914C154.739 242.527 154.848 249.515 154.689 270.621L149.122 270.709C143.605 270.798 143.107 270.709 143.107 270.709C143.107 270.709 143.163 263.159 143.107 255.767C143.007 243.379 142.944 242.194 142.304 240.595C141.922 239.641 140.936 238.169 140.109 237.324C139.211 236.405 138.073 235.644 137.282 235.436C136.554 235.241 134.932 234.779 133.62 234.779C132.309 234.775 130.668 235.294 130.035 235.556C129.403 235.818 128.349 236.493 127.688 237.059C127.031 237.621 126.197 238.682 125.833 239.411C125.472 240.139 124.939 241.971 124.645 243.478C124.235 245.589 124.096 249.037 124.096 270.798L112.55 270.709C112.455 269.291 112.441 262.565 112.55 254.883C112.723 242.579 112.829 240.62 113.43 238.438C113.805 237.077 114.66 234.767 115.332 233.31C116.166 231.5 117.294 229.873 118.88 228.182C120.408 226.552 121.973 225.307 123.454 224.543C124.691 223.903 126.66 223.082 127.826 222.718C129.269 222.269 131.103 222.046 133.57 222.025ZM315.512 222.036C318.566 222.014 320.446 222.188 322.228 222.651C323.588 223.008 325.575 223.744 326.646 224.292C327.714 224.837 329.516 226.173 330.651 227.263C331.785 228.352 333.171 230.039 333.729 231.011C334.284 231.984 335.058 233.735 335.444 234.902C335.829 236.069 336.352 238.138 336.607 239.499C336.942 241.289 337.073 245.946 337.073 270.621L331.595 270.709C327.505 270.777 325.395 270.798 325.395 270.798C325.395 270.798 325.593 269.079 325.586 266.2L324.048 267.527C323.203 268.255 321.652 269.291 320.602 269.829C319.552 270.367 317.739 271.042 316.573 271.333C315.406 271.626 313.854 271.863 313.127 271.863C312.399 271.859 310.649 271.608 309.238 271.301C307.828 270.993 305.481 270.158 304.025 269.444C302.255 268.57 300.637 267.415 299.236 266.024C297.828 264.643 296.671 263.027 295.818 261.249C295.111 259.789 294.301 257.801 294.015 256.829C293.732 255.856 293.291 253.229 293.039 250.993C292.658 247.641 292.661 246.244 293.043 243.036C293.297 240.897 293.81 238.191 294.178 237.024C294.549 235.857 295.362 233.894 295.991 232.663C296.62 231.429 297.928 229.618 298.9 228.639C299.872 227.656 301.304 226.418 302.081 225.887C302.858 225.357 304.05 224.632 304.732 224.278C305.41 223.924 307.16 223.28 308.62 222.849C310.67 222.244 312.232 222.06 315.512 222.036V222.036ZM309.15 235.036C308.567 235.492 307.68 236.444 307.177 237.151C306.68 237.858 305.961 239.234 305.587 240.207C305.026 241.671 304.912 242.852 304.933 247.103C304.951 251.474 305.068 252.517 305.714 254.176C306.131 255.244 307.005 256.758 307.659 257.536C308.309 258.314 309.627 259.336 310.589 259.807C311.999 260.5 312.872 260.662 315.158 260.659C317.262 260.652 318.393 260.461 319.577 259.916C320.45 259.513 321.711 258.572 322.376 257.83C323.044 257.083 323.992 255.598 324.486 254.53C325.335 252.691 325.384 252.256 325.395 246.573C325.405 241.038 325.338 240.419 324.592 238.792C324.147 237.82 323.352 236.571 322.829 236.02C322.302 235.468 321.238 234.679 320.46 234.273C319.404 233.721 318.241 233.505 315.866 233.417C313.872 233.346 312.221 233.47 311.447 233.756C310.765 234.003 309.733 234.58 309.15 235.036ZM363.229 222.025C366.216 222.007 367.651 222.17 369.415 222.725C370.676 223.125 372.659 223.96 373.819 224.578C374.978 225.201 376.738 226.502 377.731 227.475C378.721 228.447 380.089 230.198 380.767 231.365C381.446 232.532 382.351 234.442 382.782 235.609C383.21 236.776 383.8 238.965 384.086 240.472C384.496 242.601 384.613 246.197 384.606 256.563C384.606 263.905 384.486 270.112 384.341 270.356C384.15 270.685 372.913 270.356 372.913 270.356V257.005C372.794 249.515 372.542 242.831 372.348 242.152C372.157 241.469 371.677 240.277 371.281 239.499C370.888 238.721 370.029 237.6 369.372 237.002C368.714 236.408 367.541 235.711 366.763 235.453C365.395 235.011 363.958 234.824 362.522 234.902C360.275 235.022 359.429 235.471 358.408 236.157C357.697 236.634 356.746 237.501 356.294 238.084C355.842 238.668 355.156 239.941 354.767 240.914C354.113 242.558 354.057 243.658 353.862 270.798L342.375 270.621L342.269 257.889C342.202 249.784 342.307 243.938 342.569 241.798C342.792 239.948 343.209 237.72 343.495 236.847C343.781 235.97 344.602 234.06 345.316 232.603C346.186 230.833 347.343 229.22 348.74 227.829C349.91 226.661 351.742 225.236 352.805 224.66C353.873 224.083 355.778 223.259 357.043 222.831C358.835 222.219 360.193 222.043 363.229 222.025ZM412.89 222.021C415.753 221.993 417.739 222.174 419.605 222.633C421.062 222.994 423.275 223.829 424.518 224.497C425.847 225.208 427.594 226.58 428.76 227.829C429.852 228.996 431.121 230.746 431.58 231.719C432.14 232.931 432.638 234.17 433.072 235.432C433.433 236.5 433.899 238.651 434.111 240.207C434.362 242.039 434.454 248.765 434.369 259.304C434.242 275.265 434.224 275.618 433.412 278.048C432.955 279.41 432.068 281.397 431.439 282.469C430.813 283.537 429.442 285.185 428.399 286.129C427.353 287.074 425.384 288.382 424.023 289.036C422.662 289.694 420.436 290.574 419.075 290.995C416.876 291.675 415.54 291.777 407.057 291.929C398.09 292.088 396.326 291.929 396.326 291.929V281.019C396.326 281.019 400.805 281.135 405.406 281.019C412.433 280.838 414.084 280.693 415.717 280.113C416.784 279.735 418.371 278.953 419.238 278.38C420.103 277.808 421.28 276.623 421.853 275.749C422.754 274.37 422.917 273.733 423.076 270.975C423.175 269.225 423.15 267.512 423.023 267.173C422.804 266.597 422.719 266.604 421.814 267.279C421.28 267.679 419.965 268.58 418.898 269.288C417.813 270.002 415.862 270.851 414.48 271.208C413.119 271.558 411.408 271.848 410.68 271.852C409.952 271.855 408.121 271.601 406.615 271.29C405.11 270.974 402.841 270.222 401.579 269.613C400.055 268.878 398.443 267.672 396.789 266.027C395.114 264.358 393.941 262.795 393.198 261.249C392.59 259.983 391.852 258.073 391.555 257.005C391.258 255.934 390.823 253.787 390.59 252.231C390.325 250.494 390.24 247.831 390.37 245.334C390.484 243.096 390.823 240.153 391.123 238.792C391.424 237.431 392.304 235.043 393.078 233.487C393.852 231.931 395.167 229.876 395.998 228.922C396.832 227.967 398.387 226.594 399.458 225.873C400.525 225.148 402.038 224.282 402.816 223.942C403.593 223.602 405.262 223.04 406.527 222.69C408.061 222.269 410.175 222.046 412.89 222.021ZM405.089 236.518C404.396 237.381 403.541 238.799 403.187 239.676C402.721 240.829 402.494 242.583 402.367 246.042C402.212 250.18 402.286 251.149 402.922 253.292C403.452 255.092 404.071 256.241 405.177 257.497C406.019 258.445 407.341 259.541 408.118 259.93C409.196 260.467 410.242 260.634 412.536 260.634C414.897 260.634 415.862 260.475 417.043 259.88C417.87 259.467 419.061 258.607 419.694 257.974C420.327 257.338 421.2 256.065 421.638 255.145C422.076 254.222 422.613 252.316 422.832 250.905C423.069 249.37 423.14 246.777 423.002 244.45C422.812 241.137 422.62 240.231 421.723 238.35C421.051 236.942 420.189 235.786 419.351 235.167C418.626 234.633 417.474 234.003 416.788 233.766C416.102 233.533 414.091 233.073 412.536 233.066C410.31 233.059 409.507 233.491 408.118 234.134C407.057 234.622 405.845 235.577 405.089 236.518ZM462.197 222.039C464.558 222.018 466.551 222.216 468.206 222.644C469.567 222.998 471.793 223.822 473.154 224.479C474.929 225.335 476.378 226.424 478.279 228.341C480.075 230.152 481.287 231.75 482.036 233.31C482.648 234.573 483.475 236.801 483.882 238.261C484.288 239.718 484.726 242.583 484.86 244.627C485.104 248.333 485.101 248.341 484.253 248.602C483.786 248.744 476.473 248.864 468.001 248.868C459.528 248.868 452.53 248.942 452.449 249.03C452.368 249.119 452.498 250.314 452.742 251.683C452.986 253.055 453.558 254.933 454.018 255.856C454.478 256.779 455.549 258.14 456.404 258.876C457.262 259.622 458.743 260.45 459.723 260.737C460.695 261.023 461.967 261.253 462.55 261.249C463.133 261.246 464.483 261.009 465.554 260.726C467.177 260.298 468.174 260.035 469.737 258.327C470.765 257.202 471.917 254.884 471.917 254.884H484.252C484.252 254.884 483.863 256.185 483.542 257.271C483.323 257.999 482.786 259.311 482.343 260.188C481.902 261.062 480.88 262.653 480.078 263.725C479.272 264.793 477.862 266.264 476.946 266.989C476.027 267.714 474.239 268.818 472.977 269.436C471.711 270.059 469.407 270.855 467.852 271.205C466.297 271.555 463.988 271.841 462.727 271.841C461.461 271.841 458.998 271.523 457.248 271.131C455.498 270.741 453.032 269.91 451.77 269.284C450.246 268.534 448.574 267.254 446.807 265.497C444.966 263.661 443.806 262.13 443.042 260.542C442.438 259.276 441.593 256.811 441.166 255.06C440.476 252.238 440.406 251.198 440.55 245.865C440.692 240.543 440.82 239.538 441.647 237.112C442.159 235.605 443 233.537 443.513 232.514C444.025 231.492 445.577 229.533 446.959 228.161C448.574 226.555 450.296 225.257 451.77 224.526C453.032 223.9 455.099 223.093 456.365 222.732C457.856 222.304 459.903 222.06 462.197 222.039ZM455.891 235.379C455.336 235.895 454.502 237.073 454.035 237.996C453.566 238.919 453.184 239.913 453.184 240.207C453.184 240.666 454.46 240.737 462.55 240.737C470.641 240.737 471.917 240.666 471.917 240.207C471.917 239.913 471.623 239.001 471.259 238.173C470.899 237.345 469.906 236.044 469.051 235.28C468.198 234.516 466.983 233.678 466.35 233.423C465.718 233.165 464.056 232.514 462.55 232.514C460.447 232.511 459.394 233.126 458.309 233.699C457.532 234.11 456.443 234.866 455.891 235.379ZM106.266 223.231V270.621L100.787 270.709C96.6974 270.777 94.7144 270.709 94.7144 270.709C94.7144 270.709 94.7535 259.562 94.7144 246.908C94.6471 227.375 94.7107 223.839 95.1389 223.471C95.4992 223.16 97.15 223.065 106.266 223.231ZM166.247 223.054C170.11 223.054 171.235 223.164 171.655 223.585C172.114 224.044 172.185 227.121 172.185 246.926V270.798H166.353C161.797 270.798 160.45 270.621 160.45 270.621C160.45 270.621 160.489 259.424 160.45 246.82C160.39 227.553 160.457 223.836 160.874 223.478C161.207 223.192 162.964 223.054 166.247 223.054ZM280.078 223.104C286.73 223.058 287.413 223.316 287.413 223.316V234.732C287.413 234.732 285.649 234.725 281.997 234.732C278.236 234.739 277.118 234.519 275.976 235.042C275.198 235.399 274.649 235.584 273.804 236.833C273.125 237.597 273.09 238.269 272.91 254.123C272.804 263.198 272.91 270.798 272.91 270.798H260.91C260.91 270.798 260.907 263.177 260.91 252.85C260.914 239.478 261.016 235.842 261.447 233.841C261.741 232.479 262.356 230.728 262.816 229.95C263.275 229.172 264.424 227.825 265.367 226.958C266.314 226.092 268.043 224.957 269.209 224.433C270.376 223.91 271.726 223.408 272.214 223.316C274.024 222.947 276.24 223.128 280.078 223.104Z" fill="#5F6062"/>
								<path d="M207.721 282.047C191.292 277.684 179.96 262.969 180 246.072C180.039 236.474 183.136 228.303 189.371 221.52C196.468 213.786 204.153 210.018 214.543 209.066C217.877 208.789 223.915 209.423 225.523 210.256C226.973 211.01 227.875 212.517 227.875 214.262C227.875 215.373 227.561 216.007 226.503 217.078C225.932 217.527 225.273 217.845 224.569 218.011C223.412 218.279 221.876 218.149 221.876 218.149C213.171 217.237 206.506 218.823 200.272 223.305C193.331 228.303 189.214 235.998 188.783 244.763C188.038 258.883 198.076 271.496 211.995 273.956C213.564 274.233 216.387 274.353 218.818 274.194C231.404 273.559 241.795 264.556 244.539 251.943C245.089 249.404 245.206 247.778 245.01 243.851C245.01 243.851 244.64 241.783 244.905 240.216C245.075 239.256 245.474 238.353 246.068 237.584C247.912 235.72 250.068 235.68 251.989 237.545C253.244 238.735 253.362 239.052 253.793 242.463C254.93 252.339 251.323 263.247 244.422 270.782C240.814 274.709 237.913 276.97 233.913 279.032C227.875 282.086 223.797 283.038 216.505 282.999C212.23 282.999 210.544 282.841 207.721 282.047Z" fill="#F7934D"/>
								<path d="M212.803 257.342C212.556 256.877 212.679 254.941 213.174 251.921L213.958 247.159L212.349 245.804C208.595 242.59 209.461 236.938 214.082 234.731C216.557 233.531 220.023 233.84 222.085 235.428C225.675 238.099 225.964 242.242 222.87 245.494L220.912 247.159L221.879 252.153C222.457 255.754 222.54 257.071 222.168 257.458C221.838 257.845 220.559 258 217.424 258C213.793 258 213.092 257.884 212.803 257.342Z" fill="#F7934D"/>
								<path d="M232.066 229.962C227.798 224.884 230.638 219.834 236.074 216.833C238.452 215.281 240.588 212.942 241.676 210.421L242.147 209L242.881 210.654C243.698 213.291 244.399 218.656 243.738 218.85C238.179 220.479 232.377 230.699 232.066 229.962Z" fill="#4D9965"/>
								<path d="M236.035 232.839C233.952 232.425 232 231.116 232 230.007C232 228.74 233.922 224.228 235.476 222.526C237.146 220.625 239.874 219.114 242.243 218.401C243.564 218.005 246.069 217.953 248.759 218.03C251.448 218.108 253 218.314 253 218.71C253 218.868 250.536 220.237 249.876 220.593C247.616 222.095 246.718 223.799 245.203 226.729C243.649 229.818 242.773 231.125 241.063 232.036C239.587 232.828 238.117 233.253 236.035 232.839Z" fill="#9BCE7A"/>
							</svg>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Test the API endpoints if they are secure.
	 *
	 * @param string $nonce Nonce.
	 */
	public function test_api_security( $nonce ) {
		if ( check_ajax_referer( 'mo_plugins_adv_test_api_security', 'nonce' ) ) {

			$endpoints = array(
				'posts',
				'pages',
				'comments',
				'users',
				'media',
			);

			$count          = 0;
			$status         = '';
			$open_endpoints = array();

			foreach ( $endpoints as $endpoint ) {
				$api_url = home_url( '/wp-json/wp/v2/' . $endpoint );

				$response = wp_remote_get(
					$api_url,
					array(
						'timeout' => 10,
						'cookies' => array(),
					)
				);

				if ( is_wp_error( $response ) ) {
					MOOAuth_Debug::mo_oauth_log( 'Error while checking the REST API Endpoints' );
					MOOAuth_Debug::mo_oauth_log( $response->get_error_message() );
					wp_send_json_error(
						array(
							'message' => 'Error checking the API: ' . $response->get_error_message(),
						)
					);
				}

				$status_code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $status_code ) {
					array_push(
						$open_endpoints,
						array(
							'endpoint' => $endpoint,
							'url'      => $api_url,
						)
					);
					++$count;
				}
			}

			if ( 0 === $count ) {
				$status = 'SECURE';
			} elseif ( 0 < $count && count( $endpoints ) > $count ) {
				$status = 'SOME_SECURE';
			} else {
				$status = 'NONE_SECURE';
			}

			$final_response = array(
				'open_endpoints' => $open_endpoints,
				'status'         => $status,
			);

			update_option( 'mo_adv_rest_api_security_status', $final_response );

			wp_send_json_success( $status, 200 );
		}
	}

	/**
	 * Function to install and activate REST API from the side advertisment pannel by the click of a button.
	 *
	 * @return void
	 */
	public function install_and_activate_rest_api_free() {
		$response = array();

		// Validate the request method, nonce, and permissions.
		if ( empty( $_SERVER['REQUEST_METHOD'] ) || sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) !== 'POST' || empty( $_POST['nonce'] ) || ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mo_plugins_adv_install_and_activate_rest_api_free' ) ) {
			$response = array(
				'message' => 'Invalid request. Please check your permissions and nonce.',
				'code'    => 400,
			);
			wp_send_json_error( $response, $response['code'] );
		}

		// Get the plugin download link.
		$download_link = $this->get_plugin_download_link_from_wp_org( self::REST_API_PLUGIN_NAME );
		if ( ! $download_link ) {
			$response = array(
				'message'      => 'Error while getting the plugin download link.',
				'redirect_url' => esc_url( 'https://wordpress.org/plugins/wp-rest-api-authentication' ),
			);
			wp_send_json_error( $response, 400 );
		}

		// Define the plugin path.
		$plugin_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::REST_API_PLUGIN_PATH;

		// Download and extract the plugin if it does not already exist.
		if ( ! file_exists( $plugin_path ) ) {
			$temp_plugin_file = download_url( $download_link );

			if ( is_wp_error( $temp_plugin_file ) ) {
				$response = array(
					'message'      => 'Failed to download the plugin.',
					'redirect_url' => esc_url( 'https://wordpress.org/plugins/wp-rest-api-authentication' ),
				);
				wp_send_json_error( $response, 400 );
			}

			// Extract the downloaded ZIP file.
			$zip = new \ZipArchive();
			if ( $zip->open( $temp_plugin_file ) === true ) {
				$extract_result = $zip->extractTo( WP_PLUGIN_DIR );
				$zip->close();

				// Clean up the temporary file.
				if ( ! $extract_result ) {
					$response = array(
						'message'      => 'Failed to extract the plugin ZIP file.',
						'redirect_url' => esc_url( 'https://wordpress.org/plugins/wp-rest-api-authentication' ),
					);
					wp_send_json_error( $response, 400 );
				}
				wp_delete_file( $temp_plugin_file );
			} else {
				wp_delete_file( $temp_plugin_file );
				$response = array(
					'message'      => 'Failed to open the plugin ZIP file.',
					'redirect_url' => esc_url( 'https://wordpress.org/plugins/wp-rest-api-authentication' ),
				);
				wp_send_json_error( $response, 400 );
			}
		}

		// Activate the plugin if it is not already active.
		if ( ! is_plugin_active( self::REST_API_PLUGIN_PATH ) ) {
			$result = activate_plugin( $plugin_path );

			if ( is_wp_error( $result ) ) {
				$response = array(
					'message'      => 'Plugin activation failed.',
					'error'        => $result->get_error_message(),
					'redirect_url' => esc_url( 'https://wordpress.org/plugins/wp-rest-api-authentication' ),
				);
				wp_send_json_error( $response, 400 );
			}

			$response = array(
				'message'      => 'Plugin activated successfully.',
				'redirect_url' => esc_url( admin_url( 'admin.php?page=mo_api_authentication_settings' ) ),
			);
		} else {
			$response = array(
				'message'      => 'Plugin already activated.',
				'redirect_url' => esc_url( admin_url( 'admin.php?page=mo_api_authentication_settings' ) ),
			);
		}

		// Send success response.
		wp_send_json_success( $response );
	}

	/**
	 * Function to install and activate REST API from the side advertisment pannel by the click of a button.
	 *
	 * @param string $plugin_slug slug added to the WordPress for unique identification.
	 *
	 * @return string
	 */
	private function get_plugin_download_link_from_wp_org( $plugin_slug ) {
		$api_url  = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';
		$response = wp_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || ! isset( $data['download_link'] ) ) {
			return false;
		}

		return $data['download_link'];
	}

	/**
	 * Handles temporary or permanent notice dismiss.
	 *
	 * @return void
	 */
	public function mo_rest_api_dissmiss_notice() {
		$dismiss_status = get_option( 'mo_rest_api_plugin_adv_notice_dismissed', 'show' );

		if ( self::NOTICE_STATE_TEMPORARY === $dismiss_status ) {
			update_option( 'mo_rest_api_plugin_adv_notice_dismissed', self::NOTICE_STATE_PERMANENT );
		} else {
			update_option( 'mo_rest_api_plugin_adv_notice_dismissed', self::NOTICE_STATE_TEMPORARY );
			update_option( 'mo_rest_api_plugin_adv_notice_dismissed_time', time() + ( 7 * DAY_IN_SECONDS ) );
		}

		wp_send_json_success();
	}
}
