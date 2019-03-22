<?php

/**
 * Plugin Name: All in One Invite Codes WooCommerce
 * Plugin URI:  https://themekraft.com/all-in-one-invite-codes/
 * Description: Create Invite only Products with WooCommerce
 * Version: 0.1
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/
 * Licence: GPLv3
 * Network: false
 * Text Domain: all-in-one-invite-codes
 *
 * ****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA    02111-1307    USA
 *
 ****************************************************************************
 */


/**
 * Add the field to the checkout
 **/
add_action( 'woocommerce_after_order_notes', 'all_in_one_invite_codes_checkout_field' );

function all_in_one_invite_codes_checkout_field( $checkout ) {

	$products = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => array( array(
			'key' => 'invite_only',
		) ),
	) );

	if ( $products->have_posts() ): while ( $products->have_posts() ):
		$products->the_post();
		$product_ids[] = $products->post->ID;
	endwhile;
		wp_reset_postdata();
	endif;

	$invite_only_in_cart = false;
	foreach ( $product_ids as $product_id ){
		if(all_in_one_invite_is_conditional_product_in_cart( $product_id )){
			$invite_only_in_cart = true;
		};
	}

	if ( $invite_only_in_cart === true ) {
		echo '<div id="all_in_one_invite_code"><h3>' . __( 'Invite Only Product' ) . '</h3><p style="margin: 0 0 8px;">Please add your invite code here!</p>';


		woocommerce_form_field( 'all_in_one_invite_codes_woo_product', array(
			'type'  => 'text',
			'class' => array( 'inscription-text form-row-wide' ),
			'label' => __( 'Invite Code' ),
			'required' => 'true'
		), $checkout->get_value( 'all_in_one_invite_codes_woo_product' ) );

		echo '</div>';
	}

}

/**
 * Check if Conditional Product is In cart
 *
 * @param $product_id
 *
 * @return bool
 */
function all_in_one_invite_is_conditional_product_in_cart( $product_id ) {
	global $woocommerce;

	$invite_only_in_cart = false;

	foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
		$_product = $values['data'];

		if ( $_product->id === $product_id ) {
			$invite_only_in_cart = true;

		}
	}

	return $invite_only_in_cart;

}

/**
 * Update the order meta with field value
 **/
add_action( 'woocommerce_checkout_update_order_meta', 'all_in_one_invite_codes_checkout_field_update_order_meta' );
function all_in_one_invite_codes_checkout_field_update_order_meta( $order_id ) {
	if ( $_POST['all_in_one_invite_codes_woo_product'] ) {
		update_post_meta( $order_id, 'all_in_one_invite_codes_woo_product', esc_attr( $_POST['all_in_one_invite_codes_woo_product'] ) );
	}
}

/**
 * Add the field to order emails
 **/
add_filter( 'woocommerce_email_order_meta_keys', 'all_in_one_invite_codes_order_mail_meta_keys' );
function all_in_one_invite_codes_order_mail_meta_keys( $keys ) {


	if( ! is_checkout()){
		return;
	}

	$products = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => array( array(
			'key' => 'invite_only',
		) ),
	) );

	if ( $products->have_posts() ): while ( $products->have_posts() ):
		$products->the_post();
		$product_ids[] = $products->post->ID;
	endwhile;
		wp_reset_postdata();
	endif;

	$invite_only_in_cart = false;
	foreach ( $product_ids as $product_id ){
		if(all_in_one_invite_is_conditional_product_in_cart( $product_id )){
			$invite_only_in_cart = true;
		};
	}

	if ( $invite_only_in_cart === true ) {
		$keys['Invite Code'] = 'all_in_one_invite_codes_woo_product';
	}

	return $keys;
}

add_action('woocommerce_checkout_process', 'all_in_one_invite_codes_woo_checkout_validateion');

function all_in_one_invite_codes_woo_checkout_validateion() {

	// you can add any custom validations here
	if ( ! empty( $_POST['all_in_one_invite_codes_woo_product'] ) ){

		$result = all_in_one_invite_codes_validate_code( $_POST['all_in_one_invite_codes_woo_product' ], $_POST[ 'billing_email' ] );

		if ( isset( $result['error'] ) ) {
			wc_add_notice( $result['error'], 'error' );
		}

	} else {
		wc_add_notice( __('This Product needs an invitation. Please enter a valid invite code!'), 'error' );
	}


}




/**
 * Create new invite codes after the checkout is complete
 *
 * @since  0.1
 *
 */
function all_in_one_invite_code_woo_payment_complete( $order_id ) {


	$order = wc_get_order( $order_id );
	$user = $order->get_user();
	$user_id = $user->ID;
	if( $user ){
		$code = get_post_meta( $order_id, 'all_in_one_invite_codes_woo_product', true );


		// Save the invite code as user meta data to know the relation for later query's/ stats
		update_user_meta( $user->ID, 'tk_all_in_one_invite_code', $code );

		// Get the invite code
		$args  = array(
			'post_type'  => 'tk_invite_codes',
			'meta_query' => array(
				array(
					'key'     => 'tk_all_in_one_invite_code',
					'value'   => trim( $code ),
					'compare' => '=',
				)
			)
		);
		$query = new WP_Query( $args );


		// Get the invite code id
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->the_post();
				$podt_id = get_the_ID();
			endwhile;
		}

		// get the invite code options
		$all_in_one_invite_codes_options = get_post_meta( $podt_id, 'all_in_one_invite_codes_options', true );

		// Check if and how many new invite code should get created.
		if ( isset( $all_in_one_invite_codes_options['generate_codes'] ) && $all_in_one_invite_codes_options['generate_codes'] > 0 ) {

			// Alright, loop and create all needed codes for this user.
			for ( $i = 1; $i <= $all_in_one_invite_codes_options['generate_codes']; $i ++ ) {
				$args        = array(
					'post_type'   => 'tk_invite_codes',
					'post_author' => $user_id,
					'post_parent' => $podt_id,
					'post_status' => 'publish'
				);
				$new_code_id = wp_insert_post( $args );

				// Create and save the new invite code as post meta
				$code = all_in_one_invite_codes_md5( $new_code_id );
				update_post_meta( $new_code_id, 'tk_all_in_one_invite_code', wp_filter_post_kses( $code ) );

				// Assign the amount of new codes to the code witch should get created if one of this codes get used.
				$all_in_one_invite_codes_new_options['generate_codes'] = $all_in_one_invite_codes_options['generate_codes'];
				update_post_meta( $new_code_id, 'all_in_one_invite_codes_options', $all_in_one_invite_codes_new_options );

			}

		}
	}

}

add_action( 'woocommerce_order_status_completed', 'all_in_one_invite_code_woo_payment_complete' );