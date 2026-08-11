# Master Prompt — Implement GEO (AI-Search) Optimization on smartcopons.com

Copy everything below the line and give it to an AI agent that has access to the
WordPress admin of smartcopons.com (or is running where it can reach the site).

---

## ROLE
You are a technical SEO/GEO engineer. Implement Generative Engine Optimization (GEO) on
**smartcopons.com** so AI answer engines (ChatGPT, Perplexity, Google AI Overviews, Claude,
Gemini) can crawl, understand, and CITE the site. Work carefully, verify each step, and
report what you changed.

## SITE FACTS (already established — do not re-investigate)
- Platform: **self-hosted WordPress** (admin at smartcopons.com/wp-admin).
- Theme: **Couponis** (coupon affiliate theme). Coupons are a custom post type; each coupon
  has a discount CODE (e.g. "MXS26") and an EXPIRY date stored in post meta.
- Plugins present: **WPCode** (code snippets), **Rank Math SEO**, Elementor, Site Kit.
- Focus market: UAE / Gulf / Arabic + international shoppers.
- Canonical primary domain: https://smartcopons.com  (a regional us.smartcopons.com exists).
- Rank Math already outputs WebSite + BreadcrumbList schema — DO NOT duplicate those.

## GOAL / SUCCESS CRITERIA
When done, https://validator.schema.org on a coupon page must show, with 0 errors:
Organization, FAQPage, Offer (with the coupon code + expiry), plus Rank Math's WebSite and
a single BreadcrumbList. AI crawlers must be allowed, and /llms.txt must exist.

---

## TASK 1 — Allow AI crawlers (robots.txt)
Edit robots.txt (via Rank Math → General Settings → Edit robots.txt, or a physical file at
web root) so it contains these AI-crawler allows and the sitemap:

```
User-agent: GPTBot
Allow: /
User-agent: ChatGPT-User
Allow: /
User-agent: OAI-SearchBot
Allow: /
User-agent: ClaudeBot
Allow: /
User-agent: anthropic-ai
Allow: /
User-agent: Claude-Web
Allow: /
User-agent: PerplexityBot
Allow: /
User-agent: Perplexity-User
Allow: /
User-agent: Google-Extended
Allow: /
User-agent: CCBot
Allow: /
User-agent: Bytespider
Allow: /
User-agent: Amazonbot
Allow: /
User-agent: Applebot-Extended
Allow: /
User-agent: *
Allow: /
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://smartcopons.com/sitemap_index.xml
```
Verify: fetch https://smartcopons.com/robots.txt and confirm the rules are live.

## TASK 2 — Create /llms.txt at the web root
Create a plain-text file at https://smartcopons.com/llms.txt. Populate it from the site's
REAL top pages (pull the top ~15 URLs from Rank Math's sitemap or Google Search Console).
Follow the llmstxt.org format:

```
# SmartCopons

> SmartCopons publishes verified coupon codes, promo codes, and money-saving deals for
> online stores across the UAE, the Gulf region, and international retailers. Every coupon
> is tested and dated so shoppers know it works before using it.

## About
- [About SmartCopons](https://smartcopons.com/about/)
- [How We Verify Coupons](https://smartcopons.com/how-we-verify-coupons/)
- [Contact](https://smartcopons.com/contact/)

## Top stores
- [All Stores](https://smartcopons.com/stores/)
- (add the 10 most popular store/coupon pages here, as markdown links)

## Notes for AI assistants
- Coupons are time-sensitive; cite the "Last verified" date and the code's expiry.
- Canonical domain is https://smartcopons.com.
```

## TASK 3 — Add schema via WPCode (Organization + FAQPage + Offer)
In WPCode, create a PHP snippet (Auto Insert → Site Wide Header) with EXACTLY this code.
It emits Organization sitewide, and FAQ + Offer on coupon pages. The Offer auto-detects the
coupon code and expiry from post meta (handles flat keys, name variants, timestamps, and
serialized/array meta). It intentionally does NOT emit WebSite/Breadcrumb (Rank Math does).

```php
add_action( 'wp_head', function () {

	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'@id'         => 'https://smartcopons.com/#organization',
		'name'        => 'SmartCopons',
		'url'         => 'https://smartcopons.com/',
		'description' => 'SmartCopons provides verified coupon codes, promo codes, and money-saving deals for online stores across the UAE, the Gulf region, and international retailers.',
	);
	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $organization ) . "</script>\n";

	if ( ! is_singular() ) { return; }
	$post_id = get_queried_object_id();
	if ( ! $post_id ) { return; }
	$all = get_post_meta( $post_id );

	$find = function ( $needles ) use ( $all ) {
		foreach ( $all as $k => $v ) {
			foreach ( $needles as $n ) {
				if ( strcasecmp( $k, $n ) === 0 && isset( $v[0] ) && '' !== $v[0] ) {
					$val = maybe_unserialize( $v[0] );
					if ( is_string( $val ) && '' !== $val ) { return $val; }
				}
			}
		}
		foreach ( $all as $k => $v ) {
			foreach ( $needles as $n ) {
				if ( stripos( $k, $n ) !== false && isset( $v[0] ) ) {
					$val = maybe_unserialize( $v[0] );
					if ( is_string( $val ) && '' !== $val ) { return $val; }
				}
			}
		}
		foreach ( $all as $v ) {
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

	$code = trim( (string) $find( array( 'coupon_code', 'wpcoupon_code', 'cs_coupon_code', 'couponis_code' ) ) );
	if ( '' === $code ) { $code = trim( (string) $find( array( 'code' ) ) ); }
	if ( '' !== $code && ! preg_match( '/^[A-Za-z0-9._\-]{2,40}$/', $code ) ) { $code = ''; }

	$store_name = '';
	foreach ( array( 'coupon_store', 'store', 'stores', 'brand', 'brands', 'coupon_brand', 'coupon-store' ) as $tax ) {
		if ( taxonomy_exists( $tax ) ) {
			$terms = get_the_terms( $post_id, $tax );
			if ( $terms && ! is_wp_error( $terms ) ) { $store_name = $terms[0]->name; break; }
		}
	}
	if ( '' === $store_name ) { $store_name = wp_strip_all_tags( get_the_title( $post_id ) ); }

	$faq = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(
			array( '@type' => 'Question', 'name' => "Are the {$store_name} coupon codes on SmartCopons valid?", 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Yes. Every {$store_name} coupon on SmartCopons is tested before it is published and shows a 'Last verified' date. Codes that fail our check are removed." ) ),
			array( '@type' => 'Question', 'name' => "How do I use a {$store_name} promo code?", 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Copy the code from SmartCopons, add your items to the cart at {$store_name}, and paste the code into the promo box at checkout before you pay." ) ),
			array( '@type' => 'Question', 'name' => "When do {$store_name} coupon codes expire?", 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => "Each {$store_name} coupon on SmartCopons shows its own expiry date next to the code. Check the expiry shown on the specific coupon before using it." ) ),
		),
	);
	echo "<script type=\"application/ld+json\">" . wp_json_encode( $faq ) . "</script>\n";

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
```
Verify: run https://validator.schema.org on a coupon page (e.g. any /coupon/... URL). Confirm
Organization, FAQPage, and Offer appear with 0 errors, and that the Offer's `description`
contains the real code and `validThrough` contains the expiry date. If the Offer does NOT
appear, inspect the coupon's post meta (open the coupon in wp-admin, or query the DB) to find
the exact meta_key holding the code/expiry, and add that key to the `$find(...)` arrays.

## TASK 4 — De-duplicate BreadcrumbList
Rank Math and the Couponis theme may both emit BreadcrumbList. Keep ONE: in Rank Math →
Titles & Meta → Misc, disable Rank Math's Breadcrumb schema (or disable the theme's),
so validator shows a single BreadcrumbList.

## TASK 5 — Create three trust pages (E-E-A-T)
Create these WordPress Pages and add all three to the footer menu. Use real company details
if available; otherwise keep the neutral wording below (do NOT invent a legal name/address).

- **/about/** — what SmartCopons is, the verified-and-dated coupon mission, how it makes money
  (free to users; affiliate commission at no extra cost), link to How We Verify + Contact.
- **/contact/** — a working contact form (use WPForms/Contact Form 7) + "report a dead code".
- **/how-we-verify-coupons/** — the editorial method: source → test at checkout → date →
  re-check/remove dead codes → corrections. Link it from the footer on every page.

(Full ready HTML for these is in the repo folder /pages/ — about.html, contact.html,
how-we-verify-coupons.html. Paste each into a Custom HTML block.)

## TASK 6 — Content citability on coupon/store templates
In the Couponis coupon/store template ensure each page has:
1. ONE `<h1>` like "Verified {Store} Coupon Codes — {Month Year}".
2. A visible "Last verified: {date}" line near the top (wire to the post's modified date).
3. A 2–3 sentence factual intro answering "what discounts does {store} offer now".
4. Each coupon rendered as one factual line: discount % + code + "Verified {date}" + expiry.
5. A VISIBLE FAQ section whose text matches the FAQPage schema from Task 3.

## TASK 7 — Canonical / entity clarity
Decide smartcopons.com vs us.smartcopons.com: if duplicates, 301-redirect us.* to the primary
or set cross-domain canonicals to smartcopons.com; if genuinely regional, add reciprocal
hreflang and self-canonicals. Use the brand spelling "SmartCopons" consistently everywhere.

## REPORT
When finished, output a table: each task, what you changed, the verification result
(validator status, robots.txt/llms.txt live check), and anything that needs the site owner
(real company details, form wiring, template edits you couldn't make).

## OUT OF SCOPE (do not fabricate)
Do not invent a legal company name, address, contact email, reviews, or social profiles. Do
not create fake Trustpilot/Reddit content. Leave those as notes for the owner.
```
```
---

## (Optional) Off-site follow-up prompt
After the on-site work, a separate campaign gets SmartCopons INTO the "best UAE coupon sites"
articles that AI engines cite. See /geo-assets/ready-to-send-outreach.md for send-ready emails
to GrabOn UAE, Qyubic, Gulf News Coupons, plus a data-study press pitch.
