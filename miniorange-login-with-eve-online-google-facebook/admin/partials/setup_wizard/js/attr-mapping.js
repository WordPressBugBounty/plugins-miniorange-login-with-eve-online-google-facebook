/**
 * MO OAuth Setup Wizard - Attribute Mapping panel.
 *
 * Opened by the "Attribute Mapping" button on the SSO test screen. Lets the
 * admin map Username / Email from the attributes received in the test, and
 * saves them to the same app settings used by the Attribute/Role Mapping tab.
 * Relies on the mo_oauth_ajax_object localization and the #nonce field.
 *
 * @package    setup-wizard
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

var mooauth_attr_map_troubleshoot_visible = false;

function mooauth_wizard_fill_attr_select( $select, attributes, selected ) {
	$select.empty().append( jQuery( '<option>', { value: '', text: '----------- Select an Attribute -----------' } ) );
	jQuery.each( attributes, function ( i, name ) {
		$select.append( jQuery( '<option>', { value: name, text: name, selected: name === selected } ) );
	} );
}

function mooauth_wizard_attr_msg( text, is_error ) {
	jQuery( '#mo-oauth-wizard-attr-msg' ).text( text || '' ).toggleClass( 'is-error', !! is_error );
}

function mooauth_wizard_open_attr_mapping() {
	var data = {
		action: 'mo_outh_ajax',
		mo_oauth_option: 'attr_mapping_data',
		mo_oauth_nonce: jQuery( '#nonce' ).val()
	};
	jQuery.post( mo_oauth_ajax_object.ajax_url, data )
		.done( function ( res ) {
			var ok = res && res.success;
			var d  = ( res && res.data ) || {};
			mooauth_wizard_fill_attr_select( jQuery( '#mo-oauth-wizard-username-attr' ), d.attributes || [], d.username_attr || '' );
			mooauth_wizard_fill_attr_select( jQuery( '#mo-oauth-wizard-email-attr' ), d.attributes || [], d.email_attr || '' );
			mooauth_wizard_attr_msg( ok ? '' : ( d.message || 'Could not load attributes. Please try again.' ), ! ok );
		} )
		.fail( function () {
			mooauth_wizard_attr_msg( 'Could not load attributes. Please try again.', true );
		} )
		.always( function () {
			mooauth_attr_map_troubleshoot_visible = jQuery( '.mo-oauth-troubleshooting' ).is( ':visible' );
			jQuery( '#step4, .mo-oauth-troubleshooting, .mo-button__footer' ).hide();
			jQuery( '#mo-oauth-attr-mapping-step' ).removeClass( 'mo-hidden' );
			window.scrollTo( 0, 0 );
		} );
}

jQuery( function () {
	/* Back: return to the SSO test result screen. */
	jQuery( '#mo-oauth-attr-map-back' ).on( 'click', function () {
		jQuery( '#mo-oauth-attr-mapping-step' ).addClass( 'mo-hidden' );
		jQuery( '#step4, .mo-button__footer' ).show();
		if ( mooauth_attr_map_troubleshoot_visible ) {
			jQuery( '.mo-oauth-troubleshooting' ).show();
		}
		mooauth_wizard_attr_msg( '' );
	} );

	/* Save & Finish: save the mapping, close the wizard, open the Attribute/Role Mapping tab. */
	jQuery( '#mo-oauth-attr-map-save' ).on( 'click', function () {
		var $btn          = jQuery( this );
		var username_attr = jQuery( '#mo-oauth-wizard-username-attr' ).val();
		var email_attr    = jQuery( '#mo-oauth-wizard-email-attr' ).val();
		if ( ! username_attr && ! email_attr ) {
			mooauth_wizard_attr_msg( 'Please select attributes for both Username and Email.', true );
			return;
		}
		if ( ! username_attr ) {
			mooauth_wizard_attr_msg( 'Please select an attribute for Username.', true );
			return;
		}
		if ( ! email_attr ) {
			mooauth_wizard_attr_msg( 'Please select an attribute for Email.', true );
			return;
		}
		$btn.prop( 'disabled', true );
		mooauth_wizard_attr_msg( 'Saving...' );
		jQuery.post( mo_oauth_ajax_object.ajax_url, {
			action: 'mo_outh_ajax',
			mo_oauth_option: 'save_attr_mapping',
			mo_oauth_nonce: jQuery( '#nonce' ).val(),
			mo_oauth_username_attr: username_attr,
			mo_oauth_email_attr: email_attr
		} )
			.done( function ( res ) {
				if ( ! res || ! res.success ) {
					mooauth_wizard_attr_msg( ( res && res.data && res.data.message ) || 'Could not save. Please try again.', true );
					$btn.prop( 'disabled', false );
					return;
				}
				mooauth_wizard_attr_msg( res.data.message );
				jQuery.post( mo_oauth_ajax_object.ajax_url, {
					action: 'mo_outh_ajax',
					mo_oauth_option: 'test_finish',
					mo_oauth_nonce: jQuery( '#nonce' ).val()
				} ).always( function () {
					window.location.href = window.location.pathname + '?page=mo_oauth_settings&tab=attributemapping';
				} );
			} )
			.fail( function () {
				mooauth_wizard_attr_msg( 'Network error. Please try again.', true );
				$btn.prop( 'disabled', false );
			} );
	} );
} );
