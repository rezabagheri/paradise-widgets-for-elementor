# Paradise Widgets for Elementor — Usage Guide

A complete, practical guide to every widget, the controls that matter, and the
small "gotchas" that trip people up. If you only read one section, read
[The Site Info Hub](#the-site-info-hub) — most widgets are built around it.

- [Quick Start](#quick-start)
- [The Site Info Hub](#the-site-info-hub)
- [Widget Reference](#widget-reference)
  - [Contact & Phone](#contact--phone)
  - [Business Info & SEO](#business-info--seo)
  - [Navigation & Mobile](#navigation--mobile)
  - [Engagement & Notices](#engagement--notices)
- [JavaScript API](#javascript-api)
- [Recipes](#recipes)
- [Troubleshooting](#troubleshooting)

---

## Quick Start

1. **Install & activate** the plugin (Elementor must be active — the free version
   is enough; Elementor Pro is *not* required).
2. **Fill in Site Info** once at **Paradise → Site Info** (business name, address,
   phone numbers, hours, social profiles). Most widgets read from here so you
   edit your details in *one* place. See [below](#the-site-info-hub).
3. **Edit a page with Elementor.** In the widget panel, search for **"Paradise"** —
   every widget is grouped under the **Paradise** category.
4. Drag a widget in. Where a widget offers a **Source** control, leaving it on its
   *Site Info* option means "use the central data from step 2."

> **Tip:** Hover the small ⓘ description text under a control in the editor.
> The trickiest controls (every *Source* / *Mode* selector, phone country code,
> the "Open now" badge) explain themselves right there.

---

## The Site Info Hub

**Paradise → Site Info** is a single admin screen that stores your business
details so they aren't duplicated across dozens of widgets. It centralizes:

| Group | Fields |
|-------|--------|
| Identity | Business **name**, **label** |
| Contact | **phones** (multiple), **emails** (multiple) |
| Locations | one or more **addresses** + a **map URL** per location |
| Hours | per-day open/closed + open & close times (Mon–Sun) |
| Social | facebook, instagram, linkedin, tiktok, pinterest, snapchat, threads, … |

**Why this matters:** when your phone number or opening hours change, you edit
them **once** in Site Info and every widget that reads from it updates — the
Phone widgets, Social Links, Business Hours, Google Map, and LocalBusiness Schema
all share this data.

Widgets that read Site Info expose a **Source** control with two choices:

- **Site Info** — pull from the central data above (recommended).
- **Manual / Custom** — override with values entered on that specific widget.

If a Site-Info-backed widget looks empty, it almost always means **Site Info
hasn't been filled in yet**, or you selected a location index that doesn't exist.

---

## Widget Reference

15 production widgets (plus a "Feature Card (Example)" that ships disabled as a
starter for developers). For each, the **key controls** and any **gotchas** are
called out — styling controls (colors, spacing, typography) work the standard
Elementor way and are omitted here.

### Contact & Phone

#### Phone Link  ·  `paradise_phone_link`
An inline, click-to-call phone number.

- **Display Format** — how the number *looks* (Raw, International, Local, Dashes,
  Dots, or a Custom Mask with `#` per digit). This is cosmetic only; the
  underlying `tel:` link is always built correctly.
- **Link Type** — `tel:` call or **WhatsApp**.
- **Country Code** — the dialing prefix used to *build the link* (it is **not**
  shown in the visible number). Enter the phone number **without** the country
  code; it's prepended automatically — and won't be doubled if your number
  already starts with it.

#### Phone Button  ·  `paradise_phone_button`
A prominent call-to-action button.

- **Text Mode** — the one to watch:
  - *Prefix + Number* (default) — shows a prefix (e.g. "CALL ") + the formatted number.
  - *Custom Text* — **switch to this to reveal the "Button Text" field.** If you
    type custom wording without switching, it has no effect.
- **Display Format / Country Code** — same behavior as Phone Link.

#### Floating Call Button  ·  `paradise_floating_call_btn`
A fixed, floating call button (great for mobile).

- Renders **nothing on the front end when the phone number is empty** (by design);
  in the editor you'll see a placeholder so you can still style it.
- **Show Label** — the number/label only appears in the button when this is on.
- **Display Format / Country Code** — same as above.

#### Social Links  ·  `paradise_social_links`
A row/column of social icons.

- **Source** — *Site Info* (the accounts saved once in Site Info — recommended) or
  *Custom* (define links on this widget only).
- Layout, shape, and hover effects are styling controls.

### Business Info & SEO

#### Business Hours  ·  `paradise_business_hours`
A formatted opening-hours table with a live status badge.

- **Show Open/Closed Badge** — a live "Open now / Closed" pill. **It is computed
  in your site's timezone** (Settings → General → Timezone), *not* the visitor's,
  so set that correctly. **Overnight ranges** (e.g. 22:00–02:00) are supported.
- **Highlight Today** — emphasizes the current day's row.
- Hours themselves come from **Site Info**.

#### LocalBusiness Schema  ·  `paradise_local_business_schema`
Outputs **JSON-LD** structured data for SEO (helps Google show your name,
address, phone, and hours in rich results). No visible output — it injects a
`<script type="application/ld+json">` built from your Site Info. Add it once,
typically in the footer or on your contact page.

#### Google Map  ·  `paradise_google_map`
An embedded Google Map — **no API key required**.

- **Mode** — *Place* (a single pin) or *Directions* (a route). Switching this
  changes which settings tab appears.
- **Source** (Place mode) — *Site Info Address* (uses a saved location — fill Site
  Info first, then choose it under "Location") or *Manual URL* (paste any Google
  Maps share/embed URL).
- **Destination Source** (Directions mode) — Site Info or Manual. The "From"
  origin is intentionally left blank so Google uses the visitor's own location.

#### Author Card  ·  `paradise_author_card`
A profile card for a post author or a chosen user.

- **Author Source** — *Current Post Author* (auto-detects the author of the post
  being viewed — use inside a single-post template) or *Specific User* (reveals a
  **User ID** field; find IDs under wp-admin → Users).

#### FAQ Accordion  ·  `paradise_faq_accordion`
An expand/collapse list of questions, with FAQPage schema.

- **Source** — *Static* (type the Q&A pairs into the **Items** repeater right here)
  or *FAQ Post Type* (reuse a saved set from **Paradise → FAQs** — create one
  there first, then pick it under "FAQ Set").

### Navigation & Mobile

#### Bottom Navigation Bar  ·  `paradise_bottom_nav`
A fixed mobile bottom nav (app-style), with badges and an optional center FAB.

- **Source** — *Manual (Repeater)* (add each item by hand) or *WordPress Menu*
  (pull from a menu under Appearance → Menus).
- **Detection Method** — how the active/highlighted item is chosen: *URL Match*,
  *Manual Only*, or *URL Match + Manual Fallback* (default).
- **Badge → Badge Value Source** (per item) — *Static*, *WooCommerce Cart* (live
  cart count), or *JS-driven*. For JS-driven, set a **CSS ID** on the item and call
  `Paradise.setBadge("your-id", 3)`. See [JavaScript API](#javascript-api).

#### Off-Canvas Menu  ·  `paradise_off_canvas_menu`
A slide-in panel driven by a WordPress menu.

- **Menu** — which menu fills the panel (build menus under Appearance → Menus).
- Exposes a JS API so **other elements can open it**:
  `Paradise.toggleOffCanvas("<element-id>")`. The `<element-id>` is the Off-Canvas
  widget's Elementor element ID — select the widget and read it from
  **Advanced → CSS ID**, or inspect the DOM for `data-ocm-id`. See
  [Recipes](#recipes).

#### Sticky Header  ·  `paradise_sticky_header`
Makes a section/header stick to the top on scroll, with an optional shrink
effect. It snapshots and restores the element's original inline styles, so
toggling it off cleanly reverts.

#### Back to Top  ·  `paradise_back_to_top`
A scroll-to-top button that appears after the visitor scrolls down a threshold.

### Engagement & Notices

#### Announcement Bar  ·  `paradise_announcement_bar`
A dismissible promo/notice bar.

- **Remember Dismissal** — *session*, *X days*, or *forever*. Stored in the
  visitor's **own browser (localStorage), per device** — clearing browser data or
  switching devices shows the bar again.

#### Cookie Consent Bar  ·  `paradise_cookie_consent_bar`
A cookie-consent notice. The visitor's choice is readable from JS via
`Paradise.getCookieConsent(id)` so you can gate scripts on it.

> **Note:** this is a UI/consent banner — it does not itself block cookies or
> track anyone. Wire your own scripts to its decision.

---

## JavaScript API

All helpers live on the global `window.Paradise` object and are safe to call
after the page loads.

| Function | Purpose |
|----------|---------|
| `Paradise.setBadge(cssId, count)` | Set a Bottom Nav item's badge number (pass the item's CSS ID). |
| `Paradise.openOffCanvas(id)` | Open an Off-Canvas Menu by its element ID. |
| `Paradise.closeOffCanvas(id)` | Close an Off-Canvas Menu. |
| `Paradise.toggleOffCanvas(id)` | Toggle an Off-Canvas Menu open/closed. |
| `Paradise.getCookieConsent(id)` | The visitor's choice for that consent bar: `true` (accepted), `false` (declined), or `null` (not chosen yet). |

```js
// Example: live cart badge updated by your own AJAX
Paradise.setBadge('cart', itemCount);

// Example: only load analytics if the visitor accepted cookies
if (Paradise.getCookieConsent('cookie-bar-id') === true) {
  loadAnalytics();
}
```

---

## Recipes

### Open the Off-Canvas Menu from the Bottom Nav center button
The Bottom Nav **center button** can fire a JS hook event; listen for it and open
the panel:
1. Add an **Off-Canvas Menu** widget; note its element ID (Advanced → CSS ID, or
   the `data-ocm-id` in the DOM), e.g. `abc123`.
2. In **Bottom Navigation**, enable the **Center Button**, set **Button Action →
   JS Hook**, and a **Hook Name** of e.g. `openMenu`. This dispatches the event
   `ebn:hook:openMenu` on `document`.
3. Add an HTML widget (or theme JS) that listens and opens the panel:
   ```js
   document.addEventListener('ebn:hook:openMenu', function () {
     Paradise.toggleOffCanvas('abc123');
   });
   ```
4. Tapping the center button now slides the menu in.

### One phone number everywhere
Fill **Site Info → phones** once. Use Phone Link in your header, Floating Call
Button for mobile, and Phone Button on your contact page — change the number in
Site Info and all three update.

### Rich results for your business
Add **LocalBusiness Schema** once (footer is fine) and keep Site Info accurate.
Validate with Google's [Rich Results Test](https://search.google.com/test/rich-results).

---

## Troubleshooting

**A Site-Info widget shows nothing.**
Fill **Paradise → Site Info** first, and make sure the selected *Location* index
exists. Confirm the widget's **Source** is set to *Site Info*.

**My custom button text isn't showing (Phone Button).**
Set **Text Mode → Custom Text**. The "Button Text" field only applies in that mode.

**The "Open now" badge looks wrong.**
It uses your **site** timezone (Settings → General → Timezone), not the visitor's.
Check that setting. Overnight hours (e.g. 22:00–02:00) are handled correctly.

**The country code appears twice / the call link is malformed.**
Enter the number **without** the country code and pick the code in **Country
Code**. If your number already includes it, it won't be added twice.

**The Floating Call Button is invisible on the live site.**
That's by design when the phone number is empty — add a number (or fill Site Info).

**The Google Map is blank.**
In Place mode with *Manual URL*, paste a real Google Maps URL (Share → Embed a
map → copy the `src`). With *Site Info Address*, make sure that location is filled.

---

*Found a gap in this guide? Open an issue:*
*https://github.com/rezabagheri/paradise-widgets-for-elementor/issues*
