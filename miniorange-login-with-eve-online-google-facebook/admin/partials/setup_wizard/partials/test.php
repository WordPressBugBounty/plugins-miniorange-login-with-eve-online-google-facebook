<?php
/**
 * Setup Wizard Test Config
 *
 * @package    setup-wizard
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Setup wizard step 4 - show sso test progress
 */
function mooauth_setup_wizard_test() {
	echo '<h4>SSO Test<span class="mo-oauth-setup-guide"></span></h4>';

	echo '<center>
				<div class="mo-oauth-test-in-progress">
					<img class="mo-oauth-loader"src="' . esc_attr( plugins_url( '/images/loader.gif', dirname( __FILE__ ) ) ) . '"/>
					<h5>Test is in progress. Please wait!!</h5>
				</div>
				<div class="mo-oauth-test-in-failed" hidden>
					<h5 class="mo-oauth-test-fail-msg">Test Failed!!</h5>
				</div>
			</center>
				<div class="mo-oauth-result-test">
				</div>
			<center>
				<div class="mo-oauth-test-successed" hidden><div class="mo-w-test-grid">
					<h5 class="mo-oauth-test-success-msg mo-w-test-msg">Congratulations!! Test completed successfully.</h5>
					<div><input type="submit" class="mo-button mo-oauth-next-setup mo-btn-test-finish_class" value="Finish"  id="mo-btn-test-finish"></div>
                </div>
					<p class="mo-oauth-test-prefered-attr"></p>
					<table>
						<tbody class="mo-oauth-test-attr-table">
							<tr><th>Attribute Name</th><th> Attribute Value</th></tr>
						</tbody>
					</table>
				</div>
			</center>';

}

