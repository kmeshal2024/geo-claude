<?php
/**
 * SmartCopons — GEO schema injector (self-hosted WordPress)
 * -----------------------------------------------------------------------------
 * PASTE-READY & SELF-HEALING: if you leave an optional value blank, that field is
 * simply omitted — the emitted schema stays VALID. You can paste and activate this
 * as-is and it will produce valid Organization + WebSite + Breadcrumb + FAQ schema.
 * Filling the optional CONFIG values below just makes it richer.
 *
 * HOW TO USE:
 *   1. Install the free "WPCode" plugin (Plugins → Add New → search "WPCode").
 *   2. WPCode → + Add Snippet → Add Your Custom Code (New Snippet) → PHP Snippet.
 *   3. Paste EVERYTHING below the "PASTE FROM HERE" line (NOT the <?php tag above).
 *   4. Insert = Auto Insert, Location = "Site Wide Header".
 *   5. Save + Activate.
 *   6. Check a store page + the homepage at https://search.google.com/test/rich-results
 * -----------------------------------------------------------------------------
 */

// ===================== PASTE FROM HERE =====================

add_action( 'wp_head', function () {

	/* ---- OPTIONAL CONFIG: leave blank to omit; fill to enrich ---- */
	$org_legal_name = '';                                   // e.g. 'SmartCopons FZ-LLC'
	$contact_email  = '';                                   // e.g. 'support@smartcopons.com'
	$logo_url       = '';                                   // e.g. 'https://smartcopons.com/logo.png'
	$founding_date  = '';                                   // e.g. '2023-01-01'
	$country_code   = '';                                   // ISO-2, e.g. 'AE'
	$city           = '';                                   // e.g. 'Dubai'
	$same_as = array(                                       // official profiles only
		// 'https://www.facebook.com/…',
		// 'https://www.instagram.com/…',
		// 'https://x.com/…',
	);
	// Adjust ONLY if your coupon theme uses different slugs:
	$coupon_post_type = 'coupon';
	$store_taxonomy   = 'coupon_store';
	/* ------------------------------------------------------------- */

	// ---- Organization (sitewide), optional fields added only if set ----
	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'@id'         => 'https://smartcopons.com/#organization',
		'name'        => 'SmartCopons',
		'url'         => 'https://smartcopons.com/',
		'description' => 'SmartCopons provides verified coupon codes, promo codes, and money-saving deals for online stores across the UAE, the Gulf region, and international retailers.',
	);
	if ( $org_legal_name ) { $organization['legalName']    = $org_legal_name; }
	if ( $contact_email )  { $organization['email']        = $contact_email; }
	if ( $founding_date )  { $organization['foundingDate'] = $founding_date; }
	if ( $logo_url ) {
		$organization['logo'] = array( '@type' => 'ImageObject', 'url' => $logo_url );
	}
	if ( $country_code || $city ) {
		$addr = array( '@type' => 'PostalAddress' );
		if ( $country_code ) { $addr['addressCountry']  = $country_code; }
		if ( $city )         { $addr['addressLocality'] = $city; }
		$organization['address'] = $addr;
	}
	$same_as = array_values( array_filter( $same_as ) );
	if ( $same_as ) { $organization['sameAs'] = $same_as; }

	// ---- WebSite + SearchAction (sitewide) ----
	$website = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => 'https://smartcopons.com/#website',
		'url'             => 'https://smartcopons.com/',
		'name'            => 'SmartCopons',
		'publisher'       => array( '@id' => 'https://smartcopons.com/#organization' ),
		'inLanguage'      => 'en',
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => 'https://smartcopons.com/?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $organization ) . "</script>\n";
	echo "<script type=\"application/ld+json\">" . wp_json_encode( $website ) . "</script>\n";

	// ---- Store/coupon pages: Breadcrumb + FAQ ----
	if ( is_singular( $coupon_post_type ) || is_tax( $store_taxonomy ) ) {

		$store_name = wp_strip_all_tags( single_term_title( '', false ) );
		if ( ! $store_name ) { $store_name = get_the_title(); }
		if ( ! $store_name ) { $store_name = 'this store'; }

		$current_url = home_url( add_query_arg( array() ) );

		$breadcrumb = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',       'item' => 'https://smartcopons.com/' ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Stores',     'item' => 'https://smartcopons.com/stores/' ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $store_name,   'item' => $current_url ),
			),
		);

		$faq = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(
				array(
					'@type'          => 'Question',
					'name'           => "Are the {$store_name} coupon codes on SmartCopons valid?",
					'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Yes. Every {$store_name} coupon on SmartCopons is tested before it is published and shows a 'Last verified' date. Codes that fail our check are removed, so the codes listed are working as of that date." ),
				),
				array(
					'@type'          => 'Question',
					'name'           => "How do I use a {$store_name} promo code?",
					'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Copy the code from SmartCopons, add your items to the cart at {$store_name}, and paste the code into the promo box at checkout before you pay." ),
				),
				array(
					'@type'          => 'Question',
					'name'           => "When do {$store_name} coupon codes expire?",
					'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Each {$store_name} coupon on SmartCopons shows its own expiry date next to the code. Check the expiry shown on the specific coupon before using it." ),
				),
			),
		);

		echo "<script type=\"application/ld+json\">" . wp_json_encode( $breadcrumb ) . "</script>\n";
		echo "<script type=\"application/ld+json\">" . wp_json_encode( $faq ) . "</script>\n";
		// Also add these same 3 Q&As as VISIBLE text on the page (faq-blocks.md).
	}

}, 20 );

// ===================== END PASTE =====================

/**
 * PER-COUPON "Offer" SCHEMA (Phase 2, item 6) — needs your theme's fields.
 * FIRST check if your coupon theme already outputs it: open a coupon page →
 * View Source → search "application/ld+json". If Offer/discount schema is already
 * there, do NOT duplicate. If not, share your theme name + the meta keys for the
 * code and expiry, and this snippet can be extended to emit one Offer per coupon.
 */
