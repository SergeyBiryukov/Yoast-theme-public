<?php
/**
 * @package Yoast\YoastCom
 */

namespace Yoast\YoastCom\Theme;

/**
 * Handles all the customization to the EDD checkout HTML
 */
class Checkout_HTML {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'edd_csau_html', array( $this, 'cross_selling_html' ), 10, 2 );

		add_action( 'template_redirect', array( $this, 'change_edd_csau_location' ), 15 );

		add_action( 'edd_cc_billing_bottom', array( $this, 'html_cc_billing_bottom' ) );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'html_what_happens_next' ), 175 );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'html_purchase_summary' ), 180 );
		add_action( 'edd_purchase_link_end', array( $this, 'html_button_purchase_link' ), 10, 2 );

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize filters that can't be added on construction.
	 */
	public function init() {
		$this->change_edd_payment_mode_form();
		$this->hide_discount_field();
		$this->change_terms_agreement();
		$this->change_user_info_fields();
		$this->change_cc_form();
		$this->change_tax_fields();
		$this->hide_final_total();
		$this->add_errors_on_top();
	}

	/**
	 * Changes the payment form to use our own HTML
	 */
	public function change_edd_payment_mode_form() {
		remove_action( 'edd_payment_mode_select', 'edd_payment_mode_select' );

		add_action( 'edd_payment_mode_select', array( $this, 'edd_payment_mode_select_html' ) );

	}

	/**
	 * Outputs the HTML for the payment mode selection
	 */
	public function edd_payment_mode_select_html() {
		get_template_part( 'html_includes/shop/form-select-payment' );
	}

	/**
	 * Changes the purchase form to use our own HTML
	 */
	public function hide_discount_field() {
		$yst_edd_discount = filter_input( INPUT_COOKIE, 'yst_edd_discount' );

		// Temporarily always show the discount in the checkout process.
		$always_show_discount = true;

		if ( ! $always_show_discount && ! $yst_edd_discount ) {
			remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );
		}
	}

	/**
	 * Changes the location of the cross selling on the checkout page from under the cart to under the form
	 */
	public function change_edd_csau_location() {
		remove_action( 'edd_after_checkout_cart', 'edd_csau_display_on_checkout_page' );
		add_action( 'edd_after_purchase_form', 'edd_csau_display_on_checkout_page' );
	}

	/**
	 * Changes the cross selling HTML to be more in line what we want
	 *
	 * @param string $html The current HTML, we discard this completely.
	 * @param array  $downloads The downloads to cross/up sell.
	 *
	 * @return string The new HTML.
	 */
	public function cross_selling_html( $html, $downloads ) {

		if ( edd_is_checkout() ) {
			$html = get_template_part( 'html_includes/shop/checkout-cross-sell', array(
				'return'    => true,
				'downloads' => $downloads,
			) );
		}

		return $html;
	}

	/**
	 * Change the term agreement to our customized one
	 */
	public function change_terms_agreement() {
		remove_action( 'edd_purchase_form_after_cc_form', 'edd_terms_agreement' );
		remove_action( 'edd_purchase_form_before_submit', 'edd_terms_agreement' );
		remove_action( 'edd_checkout_form_top', 'edd_agree_to_terms_js' );
		add_action( 'edd_purchase_form_after_cc_form', array( $this, 'html_terms_agreement' ), 999 );
	}

	/**
	 * Outputs the HTML for the terms agreement
	 */
	public function html_terms_agreement() {
		get_template_part( 'html_includes/shop/terms-agreement' );
	}

	/**
	 * Change the user info fields with our customized fields
	 */
	public function change_user_info_fields() {
		remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields' );
		add_action( 'edd_purchase_form_after_user_info', array( $this, 'html_user_info_fields' ) );
	}

	/**
	 * Outputs the HTML for the checkout user fields
	 */
	public function html_user_info_fields() {
		get_template_part( 'html_includes/shop/input-user-info' );
	}

	/**
	 * Change the cc form with our customized one
	 */
	public function change_cc_form() {
		remove_action( 'edd_stripe_cc_form', 'edds_credit_card_form' );
		remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
		add_action( 'edd_stripe_cc_form', array( $this, 'html_cc_form' ) );
		add_action( 'edd_after_cc_fields', array( $this, 'html_default_cc_address' ) );
	}

	/**
	 * Outputs the HTML for the credit card fields
	 */
	public function html_cc_form() {
		if ( ! wp_script_is( 'stripe-js' ) ) {
			edd_stripe_js( true );
		}

		get_template_part( 'html_includes/shop/input-creditcard' );
	}

	/**
	 * Outputs the HTML for the default CC address
	 */
	public function html_default_cc_address() {
		get_template_part( 'html_includes/shop/input-cc-address' );
	}

	/**
	 * Change tax fields to our customized version
	 */
	public function change_tax_fields() {
		remove_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_tax_fields', 999 );
		add_action( 'edd_purchase_form_after_cc_form', array( $this, 'html_tax_fields' ), 900 );
	}

	/**
	 * Outputs checkout tax fields
	 */
	public function html_tax_fields() {
		if ( edd_cart_needs_tax_address_fields() && edd_get_cart_total() ) {
			$this->html_default_cc_address();
		}
	}

	/**
	 * Removes the final total
	 */
	public function hide_final_total() {
		remove_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );
	}

	/**
	 * Adds the checkout errors on the top of the page.
	 */
	public function add_errors_on_top() {
		add_action( 'edd_checkout_form_top', function() { echo '<div class="row">'; } );
		add_action( 'edd_checkout_form_top', 'edd_print_errors' );
		add_action( 'edd_checkout_form_top', function() { echo '</div>'; } );
	}

	/**
	 * Changes error printing to our own customized version
	 */
	public function change_print_errors() {
		remove_action( 'edd_purchase_form_before_submit', 'edd_print_errors' );
		add_action( 'edd_purchase_form_top', 'edd_print_errors' );
	}

	/**
	 * Outputs credit card billing bottom
	 */
	public function html_cc_billing_bottom() {
		get_template_part( 'html_includes/shop/input-vat-number' );
	}

	/**
	 * Outputs some information as to what happens after the checkout
	 */
	public function html_what_happens_next() {
		get_template_part( 'html_includes/shop/what-happens-next' );
	}

	/**
	 * Outputs the HTML for the purchase summary
	 */
	public function html_purchase_summary() {
		get_template_part( 'html_includes/shop/purchase-summary' );
	}

	/**
	 * Outputs the HTML for the purchase button
	 *
	 * @param int   $download_ID The ID of the download to output the purchase link for.
	 * @param array $args The arguments passed to the purchase link function.
	 */
	public function html_button_purchase_link( $download_ID, $args ) {
		get_template_part( 'html_includes/shop/purchase-link', array(
			'download_id'   => $download_ID,
			'function_args' => $args,
		) );
	}
}
