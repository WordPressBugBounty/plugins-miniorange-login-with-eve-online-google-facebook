<?php
/**
 * Setup Wizard Attribute Mapping
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
 * Setup wizard - map Username / Email after a successful SSO test.
 * Shown when the "Attribute Mapping" button on the test screen is clicked.
 */
function mooauth_setup_wizard_attr_mapping() {
	echo '<h4>Attribute Mapping<span class="mo-oauth-setup-guide"></span></h4>
		<p class="mo-oauth-attr-map-intro">Select which attributes received from your OAuth/OIDC Provider should be used for the WordPress username and email.</p>
		<div class="field-group">
			<label for="mo-oauth-wizard-username-attr">Username <span class="mo-w-red">*</span></label>
			<select class="mo-normal-text" id="mo-oauth-wizard-username-attr"></select>
			<div class="description"><p>[ Map unique attribute with username ]</p></div>
		</div>
		<div class="field-group">
			<label for="mo-oauth-wizard-email-attr">Email <span class="mo-w-red">*</span></label>
			<select class="mo-normal-text" id="mo-oauth-wizard-email-attr"></select>
		</div>
		<p id="mo-oauth-wizard-attr-msg" class="mo-oauth-attr-map-msg" role="alert"></p>
		<div class="mo-oauth-attr-map-footer">
			<input type="button" class="mo-button--secondary" id="mo-oauth-attr-map-back" value="Back">
			<input type="button" class="mo-button mo-oauth-next-setup" id="mo-oauth-attr-map-save" value="Save &amp; Finish">
		</div>';
}
