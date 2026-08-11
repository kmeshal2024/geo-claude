<?php
/**
 * SmartCopons — GEO schema injector (self-hosted WordPress)
 * -----------------------------------------------------------------------------
 * HOW TO USE:
 *   1. Install the free "WPCode" plugin (or "Code Snippets").
 *   2. WPCode → + Add Snippet → Add Your Custom Code (New Snippet) → PHP Snippet.
 *   3. Paste EVERYTHING below the "PASTE FROM HERE" line (do NOT paste the opening
 *      <?php tag — WPCode adds its own).
 *   4. Set "Insert" = Auto Insert, Location = "Site Wide Header" (or "Run Everywhere").
 *   5. Fill in every {{PLACEHOLDER}} with real values first (see the CONFIG block).
 *   6. Save + Activate. Then validate a few URLs at
 *      https://search.google.com/test/rich-results
 *
 * WHAT IT DOES:
 *   - Organization + WebSite/SearchAction schema on every page (Phase 2, items 4–5)
 *   - BreadcrumbList + FAQPage on store/coupon/category pages (items 7–8)
 *   NOTE: per-coupon Offer schema (item 6) depends on your coupon theme's data and
 *   is handled separately — see the comment at the bottom.
 * -----------------------------------------------------------------------------
 */

// ===================== PASTE FROM HERE =====================

add_action( 'wp_head', function () {

	/* ---------------- CONFIG: fill these in ---------------- */
	$org_legal_name = '{{LEGAL_COMPANY_NAME}}';
	$contact_email  = '{{CONTACT_EMAIL}}';
	$logo_url       = 'https://smartcopons.com/logo.png';        // real logo URL
	$founding_date  = '{{YYYY-MM-DD}}';
	$country_code   = '{{AE}}';                                   // ISO country, e.g. AE
	$city           = '{{CITY}}';
	$same_as = array(                                            // official profiles only
		'{{FACEBOOK_URL}}',
		'{{INSTAGRAM_URL}}',
		'{{X_TWITTER_URL}}',
		'{{LINKEDIN_URL}}',
		'{{YOUTUBE_URL}}',
	);
	// Adjust to your coupon theme's post type / taxonomy slugs:
	$coupon_post_type = 'coupon';      // e.g. 'coupon', 'coupons', 'deal'
	$store_taxonomy   = 'coupon_store'; // e.g. 'store', 'coupon_store', 'brands'
	/* ------------------------------------------------------- */

	$same_as = array_values( array_filter( $same_as, function ( $u ) {
		return $u && strpos( $u, '{{' ) === false;
	} ) );

	// ---- Organization (sitewide) ----
	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'@id'         => 'https://smartcopons.com/#organization',
		'name'        => 'SmartCopons',
		'legalName'   => $org_legal_name,
		'url'         => 'https://smartcopons.com/',
		'logo'        => array( '@type' => 'ImageObject', 'url' => $logo_url ),
		'description' => 'SmartCopons provides verified coupon codes, promo codes, and money-saving deals for online stores across the UAE, the Gulf region, and international retailers.',
		'foundingDate'=> $founding_date,
		'email'       => $contact_email,
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressCountry'  => $country_code,
			'addressLocality' => $city,
		),
	);
	if ( $same_as ) {
		$organization['sameAs'] = $same_as;
	}

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
	$is_store_page = ( is_singular( $coupon_post_type ) || is_tax( $store_taxonomy ) );
	if ( $is_store_page ) {
		$store_name = wp_strip_all_tags( single_term_title( '', false ) );
		if ( ! $store_name ) { $store_name = get_the_title(); }
		$url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ? '/' . $GLOBALS['wp']->request . '/' : '/' ) );

		$breadcrumb = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',   'item' => 'https://smartcopons.com/' ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Stores', 'item' => 'https://smartcopons.com/stores/' ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $store_name, 'item' => $url ),
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
		// IMPORTANT: also add the SAME 3 Q&As as VISIBLE text on the page
		// (see content-templates/faq-blocks.md) — schema must match visible content.
	}

}, 20 );

// ===================== END PASTE =====================

/**
 * PER-COUPON "Offer" SCHEMA (Phase 2, item 6)
 * -------------------------------------------------------------------------
 * This needs your coupon theme's real fields (code, discount, expiry), which
 * vary by theme (RH Coupon, Couponis, Coupon WP, etc.). Two options:
 *
 * 1. CHECK FIRST: many coupon themes ALREADY output Offer/Product schema.
 *    View a coupon page's source and search for "application/ld+json" — if the
 *    theme already emits Offer schema with the code + expiry, DON'T duplicate it.
 *
 * 2. If it doesn't, tell me your theme name + the field/meta keys where the code
 *    and expiry are stored, and I'll extend this snippet to loop your coupons and
 *    emit one Offer block each with the real code, discount %, and validThrough.
 */
