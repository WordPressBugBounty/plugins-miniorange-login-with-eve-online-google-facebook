/**
 * MO OAuth Theme Script
 *
 * JS extracted from inline <script> blocks that used to live inside the
 * plugin's admin partials, consolidated here so it can be enqueued
 * instead of printed inline. Each block below is guarded so it only
 * runs when the element(s) it targets are present on the current page.
 *
 * @package    admin-js
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

jQuery( function ( $ ) {
	'use strict';

	// class-mo-oauth-client-admin-menu.php: dismiss the "MO server" IDP notice by submitting its form.
	$( '#mo_oauth_client_mo_server' ).on( 'click', function () {
		$( '#mo_oauth_client_mo_server_form' ).trigger( 'submit' );
	} );

	// class-mo-oauth-client-admin-menu.php: default (no-tab) landing redirects to the Configure OAuth tab.
	var $goregister = $( '#goregister' );
	if ( $goregister.length ) {
		location.href = $goregister.attr( 'href' );
	}

	// demo/class-mo-oauth-client-demo.php: populate the video-demo request form's
	// hidden timezone-offset field, required by the server-side validation in
	// class-mooauth.php (mo_oauth_client_video_demo_request_form).
	var $videoDemoTimeDiff = $( '#mo_oauth_video_demo_time_diff' );
	if ( $videoDemoTimeDiff.length ) {
		$videoDemoTimeDiff.val( new Date().getTimezoneOffset() );
	}

	// customization.php: "Customize SSO button" / "Write your custom code" tab switcher.
	if ( $( '.mo_oauth_custom_tab' ).length || $( '.mo_oauth_write_custom_code_tab' ).length ) {
		$( '.mo_oauth_custom_tab' ).css( 'display', 'flex' );
		$( '.mo_oauth_write_custom_code_tab' ).css( 'display', 'none' );
		$( '.mo_oauth_outer_padding input' ).prop( 'disabled', true );

		$( '#mo_oauth_customize_icon' ).on( 'click', function () {
			$( '.mo_oauth_custom_tab' ).css( 'display', 'flex' );
			$( '.mo_oauth_write_custom_code_tab' ).css( 'display', 'none' );
			$( '#mo_oauth_customize_icon' ).addClass( 'mo_active_div_css' );
			$( '#mo_oauth_write_custom_code' ).removeClass( 'mo_active_div_css' );
			$( '#mo_oauth_write_custom_code' ).css( 'border-bottom', '1px solid rgb(51, 122, 183)' );
		} );

		$( '#mo_oauth_write_custom_code' ).on( 'click', function () {
			$( '.mo_oauth_custom_tab' ).css( 'display', 'none' );
			$( '.mo_oauth_write_custom_code_tab' ).css( 'display', 'block' );
			$( '#mo_oauth_customize_icon' ).css( 'border-bottom', '1px solid rgb(51, 122, 183)' );
			$( '#mo_oauth_write_custom_code' ).addClass( 'mo_active_div_css' );
			$( '#mo_oauth_customize_icon' ).removeClass( 'mo_active_div_css' );
		} );
	}
} );

/* -------------------------------------------------------------------------
 * Global functions referenced by onclick/onkeyup attributes in the partials.
 * ---------------------------------------------------------------------- */

// app-list.php / updateapp.php: open the "Test Attribute Configuration" popup.
// The app name lives in a <td> on the app list and in an <input> on the edit screen.
function mooauth_testConfiguration() {
	var el = jQuery( '#mo_oauth_app_nameid' );
	if ( ! el.length ) {
		return false;
	}
	var appName  = el.is( 'input, select, textarea' ) ? el.val() : el.html();
	var siteUrl  = ( window.moOauthExtractedVars && window.moOauthExtractedVars.siteUrl ) || '';
	window.open( siteUrl + '/?option=testattrmappingconfig&app=' + encodeURIComponent( appName ) + '&time=' + Date.now(), 'Test Attribute Configuration', 'width=600, height=600' );
	return false;
}

// updateapp.php: jump to the Attribute Mapping tab.
function mooauth_proceedToAttributeMapping() {
	var link = jQuery( '#mo_oauth_attr_map' ).attr( 'href' );
	window.location.href = link;
}

// updateapp.php: toggle client secret visibility.
function mooauth_showClientSecret() {
	var field       = document.getElementById( 'mo_oauth_client_secret' );
	var show_button = document.getElementById( 'show_button' );
	if ( 'password' === field.type ) {
		field.type            = 'text';
		show_button.className = 'fa fa-eye-slash';
	} else {
		field.type            = 'password';
		show_button.className = 'fa fa-eye';
	}
}

// addons/class-mo-oauth-client-addons.php: upgrade / login form redirection.
function upgradeform( planType ) {
	if ( planType === '' ) {
		location.href = 'https://wordpress.org/plugins/miniorange-login-with-eve-online-google-facebook/';
		return;
	}
	jQuery( '#requestOrigin' ).val( planType );
	if ( jQuery( '#mo_customer_registered_addon' ).val() == 1 ) {
		jQuery( '#loginform_addon' ).submit();
	} else {
		location.href = jQuery( '#mobacktoaccountsetup_addon' ).attr( 'href' );
	}
}

// attr-role-mapping.php: switch username/email attribute between automatic
// (select) and manual (input) mode. Uses computed style because the initial
// hidden state now comes from a stylesheet class rather than a style attribute.
function mooauth_change_form_field( fieldType ) {
	var select_box  = document.getElementById( 'mo_oauth_' + fieldType + '_attr_select' );
	var input_tag   = document.getElementById( 'mo_oauth_' + fieldType + '_attr_input' );
	var attr_option = document.getElementById( 'mo_attr_option' );
	var change_p    = document.getElementById( 'mo_' + fieldType + '_attr_change_p' );

	if ( window.getComputedStyle( select_box ).display !== 'none' ) {
		select_box.name          = '';
		select_box.style.display = 'none';
		input_tag.name           = 'mo_oauth_' + fieldType + '_attr';
		input_tag.style.display  = 'block';
		change_p.innerHTML       = 'Change to automatic mode';
		attr_option.value        = 'manual';
	} else {
		select_box.name          = 'mo_oauth_' + fieldType + '_attr';
		select_box.style.display = 'block';
		input_tag.name           = '';
		input_tag.style.display  = 'none';
		change_p.innerHTML       = 'Change to manual mode';
		attr_option.value        = 'automatic';
	}
}

// support/class-mo-oauth-client-support.php: expand/collapse the use-case list.
function mo_oauth_show_usecases() {
	var usecase = document.getElementById( 'mo_oauth_usecase' );
	var arrow   = document.getElementById( 'mo_oauth_usecase_down_arrow' );
	if ( window.getComputedStyle( usecase ).display === 'none' ) {
		usecase.style.display = 'block';
		arrow.style.transform = 'rotate(180deg)';
	} else {
		usecase.style.display = 'none';
		arrow.style.transform = 'rotate(0deg)';
	}
}

// views/feedback-form.php: make the (readonly) email field editable.
function mooauth_editName() {
	document.querySelector( '#mo_oauth_query_mail' ).removeAttribute( 'readonly' );
	document.querySelector( '#mo_oauth_query_mail' ).focus();
	return false;
}

// views/feedback-form.php: skip button should not require a reason.
function remove_skip_required() {
	document.querySelector( '#deactivate_reason_select' ).removeAttribute( 'required' );
	return false;
}

jQuery( function ( $ ) {
	'use strict';

	// Generic redirect marker: partials that used to print an inline
	// window.location script now render this hidden element instead.
	var $redirect = $( '.mo-oauth-js-redirect' ).first();
	if ( $redirect.length && $redirect.data( 'mo-href' ) ) {
		window.location.href = $redirect.data( 'mo-href' );
		return;
	}

	// apps/partials/defaultapps.php: navigate to the selected default app.
	$( '#mo_oauth_client_default_apps li' ).on( 'click', function () {
		var appId = $( this ).data( 'appid' );
		window.location.href += '&appId=' + appId;
	} );

	// apps/partials/app-list.php: "Add New Application" opens the setup wizard.
	$( '#mo-oauth-continue-setup' ).on( 'click', function () {
		var href = $( this ).data( 'mo-href' );
		if ( href ) {
			window.location.href = href;
		}
	} );

	// account/partials/register.php: phone input + "Already have an account?".
	if ( $( '#phone' ).length && $.fn.intlTelInput ) {
		$( '#phone' ).intlTelInput();
	}
	$( '#mo_oauth_client_goto_login' ).on( 'click', function () {
		$( '#mo_oauth_client_goto_login_form' ).submit();
	} );

	// apps/partials/updateapp.php: show/hide the admin SSO warning notice.
	if ( $( 'input[name="mo_oauth_allow_admin_sso"]' ).length ) {
		var toggleAdminSSONotice = function () {
			if ( $( 'input[name="mo_oauth_allow_admin_sso"]' ).is( ':checked' ) ) {
				$( '#admin_sso_notice' ).slideDown( 300 );
			} else {
				$( '#admin_sso_notice' ).slideUp( 300 );
			}
		};
		toggleAdminSSONotice();
		$( 'input[name="mo_oauth_allow_admin_sso"]' ).on( 'change', toggleAdminSSONotice );
	}

	// support/class-mo-oauth-client-support.php: show/hide the debug-log file-size notice.
	var $debugLogCheckbox = $( '#mo_oauth_debug_check' );
	if ( $debugLogCheckbox.length ) {
		var toggleDebugLogNotice = function () {
			if ( $debugLogCheckbox.is( ':checked' ) ) {
				$( '#mo_oauth_debug_log_notice' ).slideDown( 300 );
			} else {
				$( '#mo_oauth_debug_log_notice' ).slideUp( 300 );
			}
		};
		toggleDebugLogNotice();
		$debugLogCheckbox.on( 'change', toggleDebugLogNotice );
	}

	// class-mo-oauth-client-admin-menu.php: dismiss the debug-log notice.
	$( '#mo_oauth_debug_log_notice_close' ).on( 'click', function () {
		$( '#mo_oauth_debug_log_notice' ).fadeOut( 300 );
	} );

	// apps/partials/sign-in-settings.php: show/hide the Verified IDP Key/Value rows.
	var $emailVerifiedCheckbox = $( '#email-verified-checkbox' );
	if ( $emailVerifiedCheckbox.length ) {
		var toggleEmailVerifiedRows = function () {
			var display = $emailVerifiedCheckbox.is( ':checked' ) ? 'table-row' : 'none';
			$( '#email-verified-keys-row, #email-verified-values-row' ).css( 'display', display );
		};
		toggleEmailVerifiedRows();
		$emailVerifiedCheckbox.on( 'change', toggleEmailVerifiedRows );
	}

	// notice/class-mo-oauth-admin-notice.php: dismiss the admin notice via AJAX.
	$( '#mo_oauth_client_disable_admin_notice' ).on( 'click', function () {
		$.ajax( {
			url: window.ajaxurl,
			type: 'POST',
			data: {
				action: 'mo_dismiss_admin_notice',
				security: $( this ).data( 'mo-nonce' )
			},
			success: function () {
				$( '#mo_oauth_client_admin_notice_form' ).fadeOut();
			}
		} );
	} );

	// troubleshoot/class-mo-oauth-client-troubleshoot.php: tab switching + AI toggle.
	if ( $( '.mo_oauth_error_faq_option' ).length ) {
		var ACTIVE_BG = 'rgb(237 243 255 / 61%)';
		var moOAuthShowSection = function ( which ) {
			$( '.mo_oauth_errorcodes, .mo_oauth_faq, .mo_oauth_ai_panel' ).hide();
			$( '.mo_oauth__errorcodes_options, .mo_oauth_faq_options, .mo_oauth_ai_options' ).css( { 'background-color': 'white', 'border': 'none' } );
			if ( which === 'errors' ) {
				$( '.mo_oauth_errorcodes' ).show();
				$( '.mo_oauth__errorcodes_options' ).css( 'background-color', ACTIVE_BG );
			} else if ( which === 'faq' ) {
				$( '.mo_oauth_faq' ).show();
				$( '.mo_oauth_faq_options' ).css( 'background-color', ACTIVE_BG );
			} else if ( which === 'ai' ) {
				$( '.mo_oauth_ai_panel' ).show();
				$( '.mo_oauth_ai_options' ).css( 'background-color', ACTIVE_BG );
			}
		};
		moOAuthShowSection( 'errors' );
		$( '.mo_oauth__errorcodes_options' ).on( 'click', function () { moOAuthShowSection( 'errors' ); } );
		$( '.mo_oauth_faq_options' ).on( 'click', function () { moOAuthShowSection( 'faq' ); } );
		$( '.mo_oauth_ai_options' ).on( 'click', function () { moOAuthShowSection( 'ai' ); } );
	}

	$( '#mo_oauth_abilities_toggle_input' ).on( 'change', function () {
		var $input   = $( this );
		var ajaxUrl  = $input.data( 'mo-ajax-url' );
		var nonce    = $input.data( 'mo-nonce' );
		if ( ! ajaxUrl ) {
			return;
		}
		var $badge  = $( '#mo_oauth_ai_status_badge' );
		var $msg    = $( '#mo_oauth_ai_feedback' ).removeClass( 'is_error' ).text( 'Saving...' );
		var desired = $input.is( ':checked' );
		$input.prop( 'disabled', true );
		$.post( ajaxUrl, {
			action: 'mo_oauth_abilities_toggle_ajax',
			mo_oauth_nonce: nonce,
			enable: desired ? 'true' : 'false'
		} ).done( function ( resp ) {
			if ( resp && resp.success ) {
				$badge.removeClass( 'is_on is_off' ).addClass( resp.enabled ? 'is_on' : 'is_off' ).text( resp.enabled ? 'ACTIVE' : 'OFF' );
				$msg.removeClass( 'is_error' ).text( resp.message || 'Saved.' );
			} else {
				$input.prop( 'checked', ! desired );
				$msg.addClass( 'is_error' ).text( ( resp && resp.message ) ? resp.message : 'Could not save.' );
			}
		} ).fail( function () {
			$input.prop( 'checked', ! desired );
			$msg.addClass( 'is_error' ).text( 'Network error. Please try again.' );
		} ).always( function () {
			$input.prop( 'disabled', false );
		} );
	} );

	// views/feedback-form.php: open the feedback modal when deactivating the plugin.
	$( 'a[aria-label="Deactivate OAuth Single Sign On - SSO (OAuth Client)"]' ).on( 'click', function () {
		var mo_oauth_client_modal = document.getElementById( 'oauth_client_feedback_modal' );
		var mo_oauth_client_close = document.getElementById( 'mo_oauth_client_close' );
		if ( ! mo_oauth_client_modal ) {
			return true;
		}
		mo_oauth_client_modal.style.display = 'block';

		mo_oauth_client_close.onclick = function () {
			mo_oauth_client_modal.style.display = 'none';
		};

		window.onclick = function ( event ) {
			if ( event.target === mo_oauth_client_modal ) {
				mo_oauth_client_modal.style.display = 'none';
			}
		};
		return false;
	} );
} );
