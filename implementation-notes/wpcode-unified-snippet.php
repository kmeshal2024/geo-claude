<?php
/**
 * SmartCopons — UNIFIED GEO schema (replaces snippet #1; delete the 2nd Offer snippet)
 * -----------------------------------------------------------------------------
 * WHY: Rank Math already outputs WebSite + BreadcrumbList, so this snippet no
 * longer emits those (that was the triple-Breadcrumb + "undefined type"). It emits:
 *   - Organization (sitewide)
 *   - FAQPage (coupon/store pages)
 *   - Offer  (coupon pages) with robust code/expiry detection, incl. serialized meta
 *
 * HOW: WPCode → edit your existing snippet → replace ALL code with the body below →
 * Update. Then deactivate/delete the separate "SmartCopons Coupon Offers" snippet.
 * -----------------------------------------------------------------------------
 */

// ===================== PASTE FROM HERE =====================

add_action( 'wp_head', function () {

	// ---------- Organization (sitewide) ----------
	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'@id'         => 'https://smartcopons.com/#organization',
		'name'        => 'SmartCopons',
		'url'         => 'https://smartcopons.com/',
		'description' => 'SmartCopons provides verified coupon codes, promo codes, and money-saving deals for online stores across the UAE, the Gulf region, and international retailers.',
	);
	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $organization ) . "</script>\n";

	// ---------- Coupon pages only ----------
	if ( ! is_singular() ) { return; }
	$post_id = get_queried_object_id();
	if ( ! $post_id ) { return; }
	$all = get_post_meta( $post_id );

	// Deep finder: exact key → key-contains → inside serialized/array meta values.
	$find = function ( $needles ) use ( $all ) {
		foreach ( $all as $k => $v ) {                    // exact key
			foreach ( $needles as $n ) {
				if ( strcasecmp( $k, $n ) === 0 && isset( $v[0] ) && '' !== $v[0] ) {
					$val = maybe_unserialize( $v[0] );
					if ( is_string( $val ) && '' !== $val ) { return $val; }
				}
			}
		}
		foreach ( $all as $k => $v ) {                    // key contains needle
			foreach ( $needles as $n ) {
				if ( stripos( $k, $n ) !== false && isset( $v[0] ) ) {
					$val = maybe_unserialize( $v[0] );
					if ( is_string( $val ) && '' !== $val ) { return $val; }
				}
			}
		}
		foreach ( $all as $v ) {                          // inside serialized/array meta
			$val = isset( $v[0] ) ? maybe_unserialize( $v[0] ) : null;
			if ( is_array( $val ) ) {
				foreach ( $val as $sk => $sv ) {
					foreach ( $needles as $n ) {
						if ( is_string( $sk ) && stripos( $sk, $n ) !== false && is_string( $sv ) && '' !== $sv ) { return $sv; }
					}
				}
			}
		}
		return '';
	};

	// CODE
	$code = trim( (string) $find( array( 'coupon_code', 'wpcoupon_code', 'cs_coupon_code', 'couponis_code' ) ) );
	if ( '' === $code ) { $code = trim( (string) $find( array( 'code' ) ) ); }
	if ( '' !== $code && ! preg_match( '/^[A-Za-z0-9._\-]{2,40}$/', $code ) ) { $code = ''; }

	// STORE name (from a store taxonomy, else post title)
	$store_name = '';
	foreach ( array( 'coupon_store', 'store', 'stores', 'brand', 'brands', 'coupon_brand', 'coupon-store' ) as $tax ) {
		if ( taxonomy_exists( $tax ) ) {
			$terms = get_the_terms( $post_id, $tax );
			if ( $terms && ! is_wp_error( $terms ) ) { $store_name = $terms[0]->name; break; }
		}
	}
	if ( '' === $store_name ) { $store_name = wp_strip_all_tags( get_the_title( $post_id ) ); }

	// ---------- FAQPage (every coupon/store page) ----------
	$faq_name = ( '' !== $code ) ? $store_name : $store_name;
	$faq = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(
			array( '@type' => 'Question', 'name' => "Are the {$faq_name} coupon codes on SmartCopons valid?", 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Yes. Every {$faq_name} coupon on SmartCopons is tested before it is published and shows a 'Last verified' date. Codes that fail our check are removed." ) ),
			array( '@type' => 'Question', 'name' => "How do I use a {$faq_name} promo code?", 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Copy the code from SmartCopons, add your items to the cart at {$faq_name}, and paste the code into the promo box at checkout before you pay." ) ),
			array( '@type' => 'Question', 'name' => "When do {$faq_name} coupon codes expire?", 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Each {$faq_name} coupon on SmartCopons shows its own expiry date next to the code. Check the expiry shown on the specific coupon before using it." ) ),
		),
	);
	echo "<script type=\"application/ld+json\">" . wp_json_encode( $faq ) . "</script>\n";

	// ---------- Offer (only if a code was found) ----------
	if ( '' === $code ) { return; }

	$expiry = (string) $find( array( 'coupon_expire', 'coupon_expiry', 'coupon_expiration', 'wpcoupon_expires', 'expire', 'expir', 'valid_until', 'end_date' ) );
	$iso = '';
	if ( '' !== $expiry ) {
		$ts = is_numeric( $expiry ) ? (int) $expiry : strtotime( $expiry );
		if ( $ts && $ts > 0 ) { $iso = gmdate( 'Y-m-d', $ts ); }
	}

	$discount = trim( (string) $find( array( 'coupon_discount', 'discount', 'percentage', 'coupon_percent' ) ) );
	if ( '' === $discount && preg_match( '/(\d{1,3})\s*%/u', get_the_title( $post_id ), $m ) ) { $discount = $m[1] . '%'; }

	$desc = ( '' !== $discount )
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
	if ( '' !== $iso ) { $offer['priceValidUntil'] = $iso; $offer['validThrough'] = $iso; }

	echo "<script type=\"application/ld+json\">" . wp_json_encode( $offer ) . "</script>\n";

}, 20 );

// ===================== END PASTE =====================
