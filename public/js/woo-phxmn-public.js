(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
		$("body").on('updated_checkout', function(){
			function validate_cvc( cvc ) {
				let regx = new RegExp(/^[0-9]{3,4}$/);
				if (! regx.test( cvc )) {
					$("#woo_phxmn_cvc_msg").text('Invalid card code.');
				} else {
					$("#woo_phxmn_cvc_msg").text('');
				}
			}
			function validate_ccno( input_field ) {
				input_field.validateCreditCard(function(result) {
					if ( ! result.valid) {
						$("#woo_phxmn_ccno_msg").text('Invalid card number.');
					} else {
						$("#woo_phxmn_ccno_msg").text('');
					}
				});
			}
			function validate_expdate( expdate ) {
				if ( expdate.length < 5 ) {
					$("#woo_phxmn_expdate_msg").text('Invalid expiration date.');
				} else {
					$("#woo_phxmn_expdate_msg").text('');
					let datearr = expdate.split('/');
					let mm = datearr[0];
					let yy = datearr[1];
					let regMonth = new RegExp(/^01|02|03|04|05|06|07|08|09|10|11|12$/);
					let regxYear = new RegExp(/^[0-9]{2}$/);
					let date = new Date();
					if (! regMonth.test( mm )) {
						$("#woo_phxmn_expdate_msg").text('Invalid month.');
					}
					if (! regxYear.test( yy ) ) {
						$("#woo_phxmn_expdate_msg").text('Invalid year.');
					}
					let logic_date = parseInt(yy) > parseInt( date.getFullYear().toString().substring(2) ) ||
					 (  parseInt(yy) == parseInt( date.getFullYear().toString().substring(2) ) && parseInt(mm) >= ( date.getMonth() + 1 ) );
					if ( ! logic_date ) {
						$("#woo_phxmn_expdate_msg").text('Expiration date can not be in the past.');
					}
					if ( regxYear.test( yy ) && regMonth.test( mm ) && logic_date  ) {
						$("#woo_phxmn_expdate_msg").text('');
					}
				}
			}
			$("#woo_phxmn_cvc").focusout(function(){
				validate_cvc( $(this).val() );
			});
			
			$('#woo_phxmn_cvc').keyup(function(event){
				if ( event.target.value.length === 3 || event.target.value.length === 4  ) {
					validate_cvc( event.target.value );
				}
			});
			$('#woo_phxmn_ccno').focusout(function(){
				validate_ccno( $(this) );
			});
			$('#woo_phxmn_ccno').keyup(function(event){
				if ( event.target.value.length === 16 ) {
					validate_ccno( $(this) );
				}
			});
			$("#woo_phxmn_expdate").keyup(function(event){
				if ( event.target.value.length === 5 ) {
					validate_expdate( event.target.value );
				}
				event.target.value = event.target.value.replace(
					/[^\d\/]|^[\/]*$/g, '' // To allow only digits and `/`
				).replace(
					/^([2-9])$/g, '0$1' // To handle 3 > 03
				).replace(
					/^(1{1})([3-9]{1})$/g, '0$1/$2' // 13 > 01/3
				).replace(
					/^0{1,}/g, '0' // To handle 00 > 0
				).replace(
					/^1\//g, '01/' // To handle 1/ > 01/
				).replace(
					/^([0-1]{1}[0-9]{1})([0-9]{1,2}).*/g, '$1/$2' // To handle 113 > 11/3
				);
			});
			$("#woo_phxmn_expdate").focusout(function(){
				validate_expdate( $(this).val() );
			});
		});
})( jQuery );
