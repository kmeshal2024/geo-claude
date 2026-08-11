# WordPress Implementation Notes (Phases 2–4)

The site's URL patterns (`/coupon/`, `/stores/`, `/blog/`, `/category/`) indicate a
WordPress coupon theme. Here's where each piece goes.

## Where to inject schema (Phase 2)

Pick ONE method and use it consistently:

- **SEO plugin (recommended):** Rank Math or Yoast → each has a schema/JSON-LD
  section per post type. Add Organization + WebSite globally; add Offer/Breadcrumb/
  FAQ at the coupon/store template level.
- **Code Snippets plugin:** paste each `schema/*.html` block into a snippet that
  runs in `wp_head`. Use conditional tags (`is_singular('coupon')`, `is_page()`) so
  each schema only fires on the right template.
- **Theme templates:** `header.php` for sitewide (Organization, WebSite);
  `single-coupon.php` / `taxonomy-store.php` for per-page (Offer, Breadcrumb, FAQ).

Generate per-coupon Offer schema inside the coupon loop so each code emits its own
block with real values — do not hardcode.

## H1, intro, last-verified, coupon format (Phase 3)

Edit the coupon/store **template** (not each post) so every store page inherits the
structure in `content-templates/store-page-template.md`:
- Template outputs a single `<h1>` from the store title + "Coupon Codes — {month year}".
- Add a template field for "last verified" that reads the post's modified date or a
  custom field, printed as the visible timestamp.
- Render each coupon (a custom post type or repeater field) in the one-line factual
  format: discount + code + verified date + expiry.

## Bylines with dates (Phase 4, Item 18)

Blog posts (`/blog/`) must show a real author byline and published/updated dates:
- Ensure each post has a real WP author with a filled-out bio (Users → Profile).
- Add `Article` schema with `author`, `datePublished`, `dateModified`.
- Display "By {Author} · Updated {date}" visibly at the top of each post.
- Add author archive pages so authors are real entities with `sameAs` links.

## Trust pages (Phase 4, Items 15–17)

Create three WordPress Pages from the files in `/pages/`:
- About → `/about/`
- Contact → `/contact/` (wire the form via WPForms or Contact Form 7)
- How We Verify Coupons → `/how-we-verify-coupons/`
Add all three to the site footer menu so they're linked from every page.
