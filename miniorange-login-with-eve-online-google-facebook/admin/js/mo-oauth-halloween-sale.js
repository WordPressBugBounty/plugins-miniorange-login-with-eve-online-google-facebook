/**
 * MO OAuth Halloween Sale
 *
 * Banner dismiss (snoozed server-side) and the "Claim Now" request pop-up.
 *
 * @package    admin-notice
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

( function () {
	var config = window.moOauthHalloweenSale;
	var opener = null;

	function getModal() {
		return document.getElementById( 'mo_oauth_halloween_sale_modal' );
	}

	function post( data ) {
		return fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: new URLSearchParams( data ),
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	function getFocusable( modal ) {
		return Array.prototype.filter.call(
			modal.querySelectorAll( 'a[href], button, input, textarea' ),
			function ( element ) {
				return ! element.disabled && null !== element.offsetParent;
			}
		);
	}

	function openModal( trigger ) {
		var modal = getModal();
		opener = trigger;
		modal.hidden = false;
		document.body.classList.add( 'mo_oauth_halloween_sale_modal_open' );
		var email = modal.querySelector( '#mo_oauth_halloween_sale_email' );
		( email && null !== email.offsetParent ? email : getFocusable( modal )[ 0 ] ).focus();
	}

	function closeModal() {
		var modal = getModal();
		if ( ! modal || modal.hidden ) {
			return;
		}
		modal.hidden = true;
		document.body.classList.remove( 'mo_oauth_halloween_sale_modal_open' );
		if ( opener ) {
			opener.focus();
		}
	}

	function setStatus( form, message ) {
		form.querySelector( '.mo_oauth_halloween_sale_status' ).textContent = message;
	}

	document.addEventListener( 'click', function ( event ) {
		var dismiss = event.target.closest( '.mo_oauth_halloween_sale_dismiss' );
		if ( dismiss ) {
			dismiss.closest( '.mo_oauth_halloween_sale_notice' ).remove();
			post( { action: config.dismissAction, nonce: config.dismissNonce } );
			return;
		}
		var claim = event.target.closest( '.mo_oauth_halloween_sale_button' );
		if ( claim && getModal() ) {
			event.preventDefault();
			openModal( claim );
			return;
		}
		if ( event.target.closest( '[data-mo-oauth-halloween-sale-close]' ) ) {
			closeModal();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		var modal = getModal();
		if ( ! modal || modal.hidden ) {
			return;
		}
		if ( 'Escape' === event.key ) {
			closeModal();
			return;
		}
		if ( 'Tab' !== event.key ) {
			return;
		}
		// Keep keyboard focus inside the dialog while it is open.
		var focusable = getFocusable( modal );
		var first = focusable[ 0 ];
		var last = focusable[ focusable.length - 1 ];
		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest( '.mo_oauth_halloween_sale_form' );
		if ( ! form ) {
			return;
		}
		event.preventDefault();
		var input = form.querySelector( '#mo_oauth_halloween_sale_email' );
		var submit = form.querySelector( '.mo_oauth_halloween_sale_submit' );
		var email = input.value.trim();
		if ( ! email || ! input.checkValidity() ) {
			setStatus( form, config.i18n.invalidEmail );
			input.focus();
			return;
		}
		setStatus( form, '' );
		submit.disabled = true;
		submit.classList.add( 'is-loading' );
		post( {
			action: config.requestAction,
			nonce: config.requestNonce,
			email: email,
			usecase: form.querySelector( '#mo_oauth_halloween_sale_usecase' ).value.trim(),
		} )
			.then( function ( response ) {
				if ( ! response || ! response.success ) {
					setStatus( form, ( response && response.data && response.data.message ) || config.i18n.error );
					return;
				}
				var success = form.parentNode.querySelector( '.mo_oauth_halloween_sale_success' );
				success.querySelector( '.mo_oauth_halloween_sale_success_email' ).textContent = email;
				form.hidden = true;
				success.hidden = false;
				success.querySelector( '.mo_oauth_halloween_sale_success_title' ).focus();
			} )
			.catch( function () {
				setStatus( form, config.i18n.error );
			} )
			.then( function () {
				submit.disabled = false;
				submit.classList.remove( 'is-loading' );
			} );
	} );
} )();
