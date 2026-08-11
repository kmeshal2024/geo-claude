# GEO Implementation Kit — smartcopons.com

Make **smartcopons.com** citable by AI answer engines (ChatGPT, Perplexity, Google AI
Overviews, Claude). This repo contains **drop-in files and ready-to-paste content** for
every task in the brief.

> **Why this is a kit, not a live commit:** smartcopons.com's own source code was not
> available in the build environment, so these files can't be committed directly into
> the live site from here. Each file is production-ready — your developer drops it into
> the site (mostly WordPress). See `implementation-notes/` for exactly where each piece goes.

## Repo map
```
site-root/
  robots.txt                     → deploy at /robots.txt        (Phase 1)
  llms.txt                       → deploy at /llms.txt           (Phase 1)
schema/
  01-organization.html           → <head> sitewide              (Phase 2)
  02-website-searchaction.html   → <head> sitewide              (Phase 2)
  03-offer-coupon.html           → per coupon                   (Phase 2)
  04-breadcrumb.html             → per store/category           (Phase 2)
  05-faqpage.html                → per store/category           (Phase 2)
content-templates/
  store-page-template.md         → page structure/citability    (Phase 3)
  faq-blocks.md                  → visible FAQ copy              (Phase 3)
pages/
  about.html                     → /about/                      (Phase 4)
  contact.html                   → /contact/                    (Phase 4)
  how-we-verify-coupons.html     → /how-we-verify-coupons/       (Phase 4)
implementation-notes/
  wordpress-implementation.md    → where each piece goes         (Phases 2–4)
  canonical-strategy.md          → smartcopons vs us.smartcopons (Phase 4)
geo-assets/                      → OFF-SITE (generate, human publishes) (Phase 5)
  trustpilot-setup-checklist.md
  review-response-templates.md
  outreach-emails.md
  haro-response-template.md
  data-study-brief.md
  target-publications.csv
```

## Deploy order (do 1 → 4 in the codebase, then start 5)
1. **Phase 1** — deploy `robots.txt` + `llms.txt`. Unblocks AI crawlers. *(minutes)*
2. **Phase 2** — inject the 5 schema blocks via your SEO plugin/theme. *(hours)*
3. **Phase 3** — update the coupon/store template for H1 + intro + verified-date +
   one-line coupon format + visible FAQ. *(template edit, applies sitewide)*
4. **Phase 4** — publish About/Contact/Methodology pages, add bylines, fix canonical.
5. **Phase 5** — start outreach + Trustpilot + data study. *(ongoing; biggest GEO win)*

---

## SUMMARY TABLE — implemented vs. needs a human

| # | Task | Deliverable in this repo | Status | Human still needs to |
|---|------|--------------------------|--------|----------------------|
| 1 | Allow AI crawlers | `site-root/robots.txt` | ✅ Ready | Deploy to web root |
| 2 | Sitemap in robots | `site-root/robots.txt` | ✅ Ready | Confirm sitemap URL is real |
| 3 | llms.txt | `site-root/llms.txt` | ✅ Ready | Swap example URLs for real top pages |
| 4 | Organization schema | `schema/01-organization.html` | ✅ Ready | Fill company name, socials, logo |
| 5 | WebSite + SearchAction | `schema/02-website-searchaction.html` | ✅ Ready | Confirm search URL pattern |
| 6 | Offer schema (coupons) | `schema/03-offer-coupon.html` | ✅ Template | Wire to coupon fields in template |
| 7 | BreadcrumbList | `schema/04-breadcrumb.html` | ✅ Template | Emit dynamically per page |
| 8 | FAQPage schema | `schema/05-faqpage.html` | ✅ Template | Must match visible FAQ text |
| 9 | Validate schema | — | ⚠️ Manual | Run schema.org + Rich Results test |
| 10 | One intent-matched H1 | `content-templates/store-page-template.md` | ✅ Spec | Apply in coupon template |
| 11 | One-line factual coupons | `content-templates/store-page-template.md` | ✅ Spec | Apply in coupon loop |
| 12 | Factual intro paragraph | `content-templates/store-page-template.md` | ✅ Spec | Generate per store |
| 13 | Last-verified timestamp | `content-templates/store-page-template.md` | ✅ Spec | Wire to modified date/field |
| 14 | Visible FAQ section | `content-templates/faq-blocks.md` | ✅ Copy | Add to template |
| 15 | About page | `pages/about.html` | ✅ Ready | Fill company facts, publish |
| 16 | Contact page | `pages/contact.html` | ✅ Ready | Add real email/address, wire form |
| 17 | Methodology page | `pages/how-we-verify-coupons.html` | ✅ Ready | Publish + link from footer |
| 18 | Author bylines + dates | `implementation-notes/wordpress-implementation.md` | ✅ Guide | Add real authors + Article schema |
| 19 | Canonical strategy | `implementation-notes/canonical-strategy.md` | ✅ Guide | Pick case A/B, apply canonicals |
| 20 | Trustpilot setup | `geo-assets/trustpilot-setup-checklist.md` | ✅ Asset | Claim profile, earn real reviews |
| 21 | Outreach emails | `geo-assets/outreach-emails.md` | ✅ Asset | Personalize + send |
| 22 | HARO template | `geo-assets/haro-response-template.md` | ✅ Asset | Monitor + respond to requests |
| 23 | Data-study brief | `geo-assets/data-study-brief.md` | ✅ Asset | Pull data, build the report |
| 24 | Target publications | `geo-assets/target-publications.csv` | ✅ Asset | Work the list; log outcomes |

**Legend:** ✅ Ready = drop in as-is after filling placeholders · ✅ Template/Spec =
production logic to wire into the CMS · ⚠️ Manual = a human action no file can replace.

## Highest-leverage items (from the audit)
The audit showed smartcopons.com is **absent from the AI-cited sources** in its niche.
The three biggest wins are **#3 llms.txt + #1 crawler access** (be readable),
**#20 Trustpilot + #15–17 trust pages** (fix the "is it legit" gap the scam-checkers
flagged), and **#21 + #23 + #24 off-site** (get INTO the roundups AI cites). On-page
schema matters, but off-site presence is what puts you in the answer.

## Placeholders to fill before publishing
Search the repo for `{{` — every `{{PLACEHOLDER}}` needs a real value (legal company
name, contact email, address, social URLs, logo URL, founding date).
