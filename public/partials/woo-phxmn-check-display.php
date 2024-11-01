<?php
/**
 * Provide a public-facing view for the check.
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
<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-check-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
	<div class="form-row form-row-wide">
		<label><?php esc_html_e( 'Name on Check', 'wc-phoenixgate-payment-gateway' ); ?> <span class="required">*</span></label>
		<input id="woo_phxmn_check_name" class="credit-field" name="woo_phxmn_check_name" type="text" autocomplete="off" maxlength="50" pattern="[a-zA-Z\s]{2,50}" inputmode="text" placeholder="<?php esc_attr_e( 'John Doe', 'wc-phoenixgate-payment-gateway' ); ?>">
		<small id="woo_phxmn_check_name_msg" class="credit-field-error"></small>
	</div>
	<div class="form-row form-row-wide">
		<label><?php esc_html_e( 'Routing Number', 'wc-phoenixgate-payment-gateway' ); ?> <span class="required">*</span></label>
		<input id="woo_phxmn_check_routing" class="credit-field" name="woo_phxmn_check_routing" type="text" autocomplete="off" maxlength="9" pattern="[0-9]{9}" inputmode="numeric" placeholder="<?php esc_attr_e( '123456789', 'wc-phoenixgate-payment-gateway' ); ?>">
		<small id="woo_phxmn_check_routing_msg" class="credit-field-error"></small>
	</div>
	<div class="form-row form-row-wide">
		<label><?php esc_html_e( 'Account Number', 'wc-phoenixgate-payment-gateway' ); ?> <span class="required">*</span></label>
		<input id="woo_phxmn_check_account" class="credit-field" name="woo_phxmn_check_account" type="text" autocomplete="off" maxlength="17" pattern="[0-9\s]{5,17}" inputmode="numeric" placeholder="<?php esc_attr_e( '1234567890123456', 'wc-phoenixgate-payment-gateway' ); ?>">
		<small id="woo_phxmn_check_account_msg" class="credit-field-error"></small>
	</div>
	<div class="clear"></div>
</fieldset>
