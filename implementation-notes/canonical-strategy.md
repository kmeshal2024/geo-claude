# Canonical & Duplicate-Entity Strategy (Phase 4, Item 19)

**Problem:** `smartcopons.com` and `us.smartcopons.com` appear to serve overlapping
content. Search engines and AI models then can't tell which is the "real" entity,
which splits authority and suppresses citations. Pick ONE primary and make the
relationship explicit.

## Decision: primary = `https://smartcopons.com`

Use the apex domain as the canonical brand entity. Choose ONE of the two paths below
depending on whether the two sites are genuinely region-targeted.

### Case A — the two sites are DUPLICATES (same offers, different domain)
Consolidate onto the primary:
- Add a self-referencing canonical to every page on `smartcopons.com`:
  ```html
  <link rel="canonical" href="https://smartcopons.com/CURRENT-PATH/">
  ```
- On `us.smartcopons.com`, point canonicals at the primary equivalent:
  ```html
  <link rel="canonical" href="https://smartcopons.com/CURRENT-PATH/">
  ```
- Better: 301-redirect `us.smartcopons.com` to the matching `smartcopons.com` URL and
  retire the subdomain entirely. One strong entity beats two weak ones.

### Case B — the sites are GENUINELY region-specific (different currency/offers)
Keep both but declare the relationship with hreflang on BOTH sides:
```html
<link rel="alternate" hreflang="en-ae" href="https://smartcopons.com/PATH/">
<link rel="alternate" hreflang="en-us" href="https://us.smartcopons.com/PATH/">
<link rel="alternate" hreflang="x-default" href="https://smartcopons.com/PATH/">
```
Each page still gets a SELF-referencing canonical (not cross-domain) in this case.

## Also do
- Pick www vs non-www and 301 the other; enforce HTTPS.
- Ensure the sitemap lists only canonical URLs.
- Set the primary domain consistently in the Organization schema `url` and in
  Google Search Console (register both properties, set the primary).
- Use the SAME brand name spelling everywhere ("SmartCopons") so the entity is
  unambiguous across the site, schema, and social `sameAs` links.
