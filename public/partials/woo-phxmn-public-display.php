<?php
/**
 * Provide a public-facing view for the credit card.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wpspins.com
 * @since      1.0.0
 *
 * @package    Woo_Phxmn
 * @subpackage Woo_Phxmn/public/partials
 */

?>

<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
	<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>
	<div class="form-row form-row-wide">
		<label><?php esc_html_e( 'Card Number', 'wc-phoenixgate-payment-gateway' ); ?> <span class="required">*</span></label>
		<input id="woo_phxmn_ccno" class="credit-field" name="woo_phxmn_ccno" type="text" autocomplete="off" maxlength="16" pattern="[0-9\s]{13,19}" inputmode="numeric" placeholder="<?php esc_attr_e( '1234 1234 1234 1234', 'wc-phoenixgate-payment-gateway' ); ?>">
		<small id="woo_phxmn_ccno_msg" class="credit-field-error"></small>
	</div>
	<div class="form-row form-row-first">
		<label><?php esc_html_e( 'Expiry Date', 'wc-phoenixgate-payment-gateway' ); ?> <span class="required">*</span></label>
		<input id="woo_phxmn_expdate" class="credit-field" name="woo_phxmn_expdate" type="text" autocomplete="off" maxlength="5" inputmode="numeric" placeholder="<?php esc_attr_e( 'MM / YY', 'wc-phoenixgate-payment-gateway' ); ?>">
		<small id="woo_phxmn_expdate_msg" class="credit-field-error"></small>
	</div>
	<div class="form-row form-row-last">
		<label><?php esc_html_e( 'CVV', 'wc-phoenixgate-payment-gateway' ); ?> <span class="required">*</span></label>
		<input id="woo_phxmn_cvc" class="credit-field" name="woo_phxmn_cvc" type="password" autocomplete="off" maxlength="4" placeholder="<?php esc_attr_e( '123', 'wc-phoenixgate-payment-gateway' ); ?>">
		<small id="woo_phxmn_cvc_msg" class="credit-field-error"></small>
	</div>
	<div class="clear"></div>
	<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
	<div class="clear"></div>
</fieldset>
