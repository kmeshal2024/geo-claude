# Store Page Template (Phase 3 — Items 10–14)

This is the citability blueprint for every store/coupon page. AI answer engines
quote **short, dated, factual sentences**. Structure every store page like this.

---

## 1. H1 — one per page, intent-matched (Item 10)

```html
<h1>Verified {{Store}} Coupon Codes — {{Month}} {{Year}}</h1>
```
Example: `Verified Noon Coupon Codes — August 2026`

Rules:
- Exactly ONE `<h1>` per page.
- Include the store name + "Coupon Codes" + current month/year (freshness).
- Do not stuff keywords; the natural phrase above is what people ask AI.

---

## 2. Last-verified timestamp — visible, top of page (Item 13)

```html
<p class="last-verified">✅ Last verified: {{DD Month YYYY}} by the SmartCopons editorial team</p>
```
Automate this to update whenever an editor re-checks the codes. This single line
is one of the strongest citation signals — AI engines prefer dated, current facts.

---

## 3. Factual intro paragraph — 2–3 sentences, no fluff (Item 12)

Answer the question directly: *"what discounts does {store} offer right now?"*

```html
<p>
  {{Store}} currently has {{N}} active coupon codes on SmartCopons, with discounts
  up to {{X}}% off. The most popular code is {{CODE}} for {{benefit}}. All codes below
  were verified on {{date}} and show their individual expiry.
</p>
```

Bad (fluff, uncitable): "Looking to save big? You've come to the right place! We have
amazing deals you won't want to miss!!!"

Good (factual, citable): "Noon currently has 7 active coupon codes on SmartCopons,
with discounts up to 20% off. The top code, NOON20, gives 20% off electronics for
new customers and is valid through 31 August 2026."

---

## 4. Coupon entry format — one verifiable line each (Item 11)

Each coupon should render as a factual, self-contained statement:

```html
<div class="coupon">
  <h3>{{Discount}} off {{category/scope}} at {{Store}}</h3>
  <p class="coupon-fact">
    Code <strong>{{CODE}}</strong> — {{X}}% off {{scope}}.
    Verified {{DD Month YYYY}}. Expires {{DD Month YYYY}}.
  </p>
  <button data-code="{{CODE}}">Reveal / Copy code</button>
</div>
```
Rendered example:
> **20% off electronics at Noon** — Code `NOON20` — 20% off electronics for new
> customers. Verified 11 August 2026. Expires 31 August 2026.

Every coupon = discount % + code + "Verified [date]" + expiry, in one readable line.

---

## 5. FAQ section — visible text (Item 14)

Add a visible FAQ block at the bottom of every store page. The text MUST match
the FAQPage JSON-LD in `schema/05-faqpage.html`. See `faq-blocks.md` for the copy.

```html
<section class="faq">
  <h2>{{Store}} Coupon Codes — FAQ</h2>
  <h3>Are the {{Store}} coupon codes on SmartCopons valid?</h3>
  <p>Yes. Every {{Store}} coupon is tested before publishing and shows a "Last
     verified" date...</p>
  <!-- repeat for each Q from faq-blocks.md -->
</section>
```

---

## Page skeleton (order matters for extraction)

1. `<h1>` (Item 10)
2. Last-verified line (Item 13)
3. Factual intro paragraph (Item 12)
4. Coupon list, each in the one-line factual format (Item 11)
5. "How to use a {{Store}} code" — 3 numbered steps
6. Visible FAQ (Item 14)
7. JSON-LD: Offer (per coupon) + BreadcrumbList + FAQPage (Phase 2)
