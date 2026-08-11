<?php
/**
 * SmartCopons — per-coupon Offer schema (Couponis theme)
 * -----------------------------------------------------------------------------
 * SECOND WPCode snippet. Auto-reads the coupon CODE + EXPIRY from post meta
 * (tries the common Couponis/WPCoupon field names, then falls back to scanning).
 * Emits nothing if no code is found, so it's safe on non-coupon pages.
 *
 * WPCode → + Add Snippet → PHP Snippet → paste the body → Auto Insert →
 * Site Wide Header → Save → Activate.
 * -----------------------------------------------------------------------------
 */

// ===================== PASTE FROM HERE =====================

add_action( 'wp_head', function () {

	if ( ! is_singular() ) { return; }
	$post_id = get_queried_object_id();
	if ( ! $post_id ) { return; }

	$all = get_post_meta( $post_id );

	// ---- coupon CODE ----
	$code = '';
	foreach ( array( 'wpcoupon_code', 'coupon_code', 'cs_coupon_code', 'couponis_code', 'clpr_code', '_coupon_code', 'coupon-code', 'code' ) as $k ) {
		if ( ! empty( $all[ $k ][0] ) ) { $code = trim( $all[ $k ][0] ); break; }
	}
	if ( '' === $code ) {
		foreach ( $all as $k => $v ) {
			if ( stripos( $k, 'code' ) !== false && ! empty( $v[0] ) ) {
				$val = trim( $v[0] );
				if ( strlen( $val ) >= 3 && strlen( $val ) <= 30 && preg_match( '/^[A-Za-z0-9._\-]+$/', $val ) ) { $code = $val; break; }
			}
		}
	}
	if ( '' === $code ) { return; } // no code → not a code coupon; emit nothing

	// ---- EXPIRY ----
	$expiry = '';
	foreach ( array( 'wpcoupon_expires', 'coupon_expire', 'coupon_expiry', 'coupon_expiration', 'expire_date', 'expiration', 'valid_until', 'coupon_end_date', 'end_date' ) as $k ) {
		if ( ! empty( $all[ $k ][0] ) ) { $expiry = $all[ $k ][0]; break; }
	}
	if ( '' === $expiry ) {
		foreach ( $all as $k => $v ) {
			if ( ( stripos( $k, 'expir' ) !== false || stripos( $k, 'valid' ) !== false || stripos( $k, 'end_date' ) !== false ) && ! empty( $v[0] ) ) { $expiry = $v[0]; break; }
		}
	}
	$iso = '';
	if ( $expiry ) {
		$ts = is_numeric( $expiry ) ? (int) $expiry : strtotime( $expiry );
		if ( $ts && $ts > 0 ) { $iso = gmdate( 'Y-m-d', $ts ); }
	}

	// ---- STORE / seller name ----
	$store_name = '';
	foreach ( array( 'coupon_store', 'store', 'stores', 'brand', 'brands', 'coupon_brand' ) as $tax ) {
		if ( taxonomy_exists( $tax ) ) {
			$terms = get_the_terms( $post_id, $tax );
			if ( $terms && ! is_wp_error( $terms ) ) { $store_name = $terms[0]->name; break; }
		}
	}
	if ( '' === $store_name ) { $store_name = wp_strip_all_tags( get_the_title( $post_id ) ); }

	// ---- DISCOUNT (optional) ----
	$discount = '';
	foreach ( array( 'coupon_discount', 'discount', 'wpcoupon_percentage', 'percentage', 'coupon_percent' ) as $k ) {
		if ( ! empty( $all[ $k ][0] ) ) { $discount = trim( $all[ $k ][0] ); break; }
	}
	if ( '' === $discount && preg_match( '/(\d{1,3})\s*%/u', get_the_title( $post_id ), $m ) ) { $discount = $m[1] . '%'; }

	$desc = $discount
		? "Use code {$code} for {$discount} off at {$store_name}. Verified by SmartCopons."
		: "Use code {$code} at {$store_name}. Verified by SmartCopons.";

	$offer = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'Offer',
		'name'         => wp_strip_all_tags( get_the_title( $post_id ) ),
		'description'  => $desc,
		'url'          => get_permalink( $post_id ),
		'category'     => 'DiscountCoupon',
		'seller'       => array( '@type' => 'Organization', 'name' => $store_name ),
		'availability' => 'https://schema.org/InStock',
	);
	if ( $iso ) {
		$offer['priceValidUntil'] = $iso;
		$offer['validThrough']    = $iso;
	}

	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $offer ) . "</script>\n";

}, 21 );

// ===================== END PASTE =====================
