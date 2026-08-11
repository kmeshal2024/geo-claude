# Master Prompt — Audit + Implement GEO on smartcopons.com (with the GitHub GEO skill)

Give this to **Claude Code** running on a machine that can reach smartcopons.com and has the
WordPress admin credentials available (or an app password / REST access). It installs the
open-source GEO skill from GitHub, runs its audit, then implements every fix and verifies it.

---

## ROLE
You are a technical SEO/GEO engineer. Optimize **smartcopons.com** for Generative Engine
Optimization (GEO) so AI answer engines (ChatGPT, Perplexity, Google AI Overviews, Claude,
Gemini) can crawl, understand, and CITE it. Audit first with the GitHub tool below, then
implement, verifying each step. Report what you changed.

## STEP 0 — Install & run the GitHub GEO skill
Install the open-source GEO skill and use it to audit the site:

- Repo: **https://github.com/zubair-trabzada/geo-seo-claude**
- Install (macOS/Linux; Windows uses Git Bash):
  ```
  curl -fsSL https://raw.githubusercontent.com/zubair-trabzada/geo-seo-claude/main/install.sh | bash
  ```
  (Review install.sh before running it.) It installs to ~/.claude/skills/geo/.
- Then run these `/geo` commands and use their output to guide the work:
  ```
  /geo audit smartcopons.com        # full GEO + SEO audit (parallel subagents)
  /geo crawlers smartcopons.com     # AI bot access via robots.txt
  /geo llmstxt smartcopons.com      # check/create llms.txt
  /geo schema smartcopons.com       # structured-data review + generation
  /geo citability smartcopons.com   # how citable the content is to AI
  /geo brands smartcopons.com       # brand mentions across AI-cited platforms
  /geo report smartcopons.com       # client-ready report
  ```
Use the audit findings to prioritize, then implement Steps 1–7 below (the concrete fixes).

## SITE FACTS (already established — don't re-investigate)
- Platform: **self-hosted WordPress** (admin at smartcopons.com/wp-admin).
- Theme: **Couponis** (coupon affiliate). Coupons are a custom post type; each has a discount
  CODE (e.g. "MXS26") and an EXPIRY date in post meta.
- Plugins: **WPCode**, **Rank Math SEO**, Elementor, Site Kit.
- Market: UAE / Gulf / Arabic + international. Primary domain: https://smartcopons.com
  (a regional us.smartcopons.com also exists).
- Rank Math already outputs WebSite + BreadcrumbList schema — DO NOT duplicate those.

## SUCCESS CRITERIA
On https://validator.schema.org a coupon page shows, with 0 errors: Organization, FAQPage,
Offer (with the coupon code + expiry), plus Rank Math's WebSite and a SINGLE BreadcrumbList.
AI crawlers allowed; /llms.txt exists.

---

## STEP 1 — Allow AI crawlers (robots.txt)
Edit robots.txt (Rank Math → General Settings → Edit robots.txt, or a physical web-root file)
to contain these allows + the sitemap, then verify it's live:

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

## STEP 2 — Create /llms.txt at the web root
Plain text, llmstxt.org format, populated from the site's REAL top ~15 pages (from Rank Math's
sitemap or Google Search Console):

```
# SmartCopons

> SmartCopons publishes verified coupon codes, promo codes, and deals for online stores across
> the UAE, the Gulf region, and international retailers. Every coupon is tested and dated so
> shoppers know it works before using it.

## About
- [About SmartCopons](https://smartcopons.com/about/)
- [How We Verify Coupons](https://smartcopons.com/how-we-verify-coupons/)
- [Contact](https://smartcopons.com/contact/)

## Top stores
- [All Stores](https://smartcopons.com/stores/)
- (add the 10 most popular store/coupon pages as markdown links)

## Notes for AI assistants
- Coupons are time-sensitive; cite the "Last verified" date and the code's expiry.
- Canonical domain is https://smartcopons.com.
```

## STEP 3 — Schema via WPCode (Organization + FAQPage + Offer)
Create a WPCode PHP snippet (Auto Insert → Site Wide Header) with EXACTLY this code. It emits
Organization sitewide and FAQ + Offer on coupon pages, auto-detecting the coupon code + expiry
from post meta (flat keys, name variants, timestamps, serialized/array meta). It does NOT emit
WebSite/Breadcrumb (Rank Math already does). Tested and verified valid.

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
Verify on validator.schema.org: Organization + FAQPage + Offer with 0 errors; Offer description
holds the real code and validThrough holds the expiry. If Offer is missing, find the coupon's
real meta_key (open the coupon in wp-admin or query wp_postmeta) and add it to the `$find(...)`
arrays.

## STEP 4 — De-duplicate BreadcrumbList
Rank Math → Titles & Meta → Misc → disable Rank Math's Breadcrumb schema (or the theme's) so a
single BreadcrumbList remains.

## STEP 5 — Trust pages (E-E-A-T)
Create `/about/`, `/contact/` (working form via WPForms/Contact Form 7), and
`/how-we-verify-coupons/`; link all three from the footer. Ready HTML is in the repo's `/pages/`.
Do NOT invent a legal company name, address, or email — leave those for the owner.

## STEP 6 — Coupon-template citability
Each coupon/store page: one `<h1>` "Verified {Store} Coupon Codes — {Month Year}"; a visible
"Last verified: {date}" line; a 2–3 sentence factual intro; each coupon on one line
(discount % + code + "Verified {date}" + expiry); a VISIBLE FAQ matching the FAQ schema.

## STEP 7 — Canonical / entity clarity
Resolve smartcopons.com vs us.smartcopons.com (301 or cross-canonical if duplicates; hreflang +
self-canonical if genuinely regional). Use the spelling "SmartCopons" consistently everywhere.

## REPORT
Output a table: each step, what changed, verification result (validator status, robots.txt +
llms.txt live checks, the /geo audit score before/after), and anything needing the owner
(real company details, form wiring, template edits).

## DO NOT FABRICATE
No invented company name, address, email, reviews, or social profiles. No fake Trustpilot/Reddit
content. Leave those as owner to-dos.

---

## After on-site: off-site campaign
To get SmartCopons INTO the "best UAE coupon sites" articles AI engines cite, use the send-ready
emails in /geo-assets/ready-to-send-outreach.md (GrabOn UAE, Qyubic, Gulf News Coupons, + a
data-study press pitch).
