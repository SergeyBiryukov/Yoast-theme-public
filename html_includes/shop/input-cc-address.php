<?php
/**
 * @package Yoast\YoastCom
 */

namespace Yoast\YoastCom\Theme;

$logged_in = is_user_logged_in();

if ( $logged_in ) {
	$user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );
}
$line1 = $logged_in && ! empty( $user_address['line1'] ) ? $user_address['line1'] : '';
$line2 = $logged_in && ! empty( $user_address['line2'] ) ? $user_address['line2'] : '';
$city  = $logged_in && ! empty( $user_address['city'] ) ? $user_address['city'] : '';
$zip   = $logged_in && ! empty( $user_address['zip'] ) ? $user_address['zip'] : '';
ob_start(); ?>
	<fieldset id="edd_cc_address" class="cc-address">
		<?php if ( 'stripe' === $_GET['payment-mode'] ) { ?>
			<span><legend><?php _e( 'Credit Card Billing Details', 'yoast-theme' ); ?></legend></span>
			<p><?php _e( 'Your billing address details are used for verification of your credit card, so please use your credit card\'s billing address.', 'yoast-theme' ); ?></p>
		<?php } else { ?>
			<span><legend><?php _e( 'Billing Details', 'yoast-theme' ); ?></legend></span>
			<p><?php _e( 'Your billing address details are required for tax purposes.', 'yoast-theme' ); ?></p>
		<?php } ?>
		<?php do_action( 'edd_cc_billing_top' ); ?>
		<p id="edd-card-address-wrap">
			<label for="card_address" class="edd-label">
				<?php _e( 'Address', 'yoast-theme' ); ?>
			</label>
			<input type="text" id="card_address" name="card_address" class="card-address edd-input" value="<?php echo $line1; ?>" />
		</p>

		<p id="edd-card-address-2-wrap">
			<label for="card_address_2" class="edd-label">
				<?php _e( 'Address Line 2 (optional)', 'yoast-theme' ); ?>
			</label>
			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 edd-input" value="<?php echo $line2; ?>" />
		</p>

		<p id="edd-card-city-wrap">
			<label for="card_city" class="edd-label">
				<?php _e( 'City', 'yoast-theme' ); ?>
			</label>
			<input type="text" id="card_city" name="card_city" class="card-city edd-input" value="<?php echo $city; ?>" />
		</p>

		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e( 'Zip / Postal Code', 'yoast-theme' ); ?>
			</label>
			<input type="text" id="card_zip" name="card_zip" size="8" value="<?php echo $zip; ?>" class="card-zip edd-input edd-input-small" />
		</p>

		<div class="clear"></div>

		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e( 'Country', 'yoast-theme' ); ?>
			</label>
			<select data-placeholder="Choose your country..." id="billing_country" name="billing_country" class="billing_country edd-select chosen-select">
				<?php

				$countries = edd_get_country_list();
				// This United States always first thing is bloody annoying.
				foreach ( $countries as $country_code => $country ) {
					if ( '*' == $country_code ) {
						echo '<option></option>';
						continue;
					}
					echo '<option value="' . $country_code . '"' . selected( $country_code, "", false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>

		<p id="edd-card-state-wrap">
			<label for="card_state" class="edd-label">
				<?php _e( 'State / Province', 'yoast-theme' ); ?>
			</label>
			<input type="text" size="8" id="card_state" name="card_state" id="card_state" class="card_state edd-input" />
		</p>

		<?php do_action( 'edd_cc_billing_bottom' ); ?>
	</fieldset>

<?php
echo ob_get_clean();
