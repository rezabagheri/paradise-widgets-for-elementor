=== Paradise Widgets for Elementor ===
Contributors: rezabagheri
Tags: elementor, mobile navigation, business hours, local seo, schema
Requires at least: 6.1
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 3.2.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced custom Elementor widgets for mobile UX, contact, local SEO, and business info — all powered by a centralized Site Info store.

== Description ==

Paradise Widgets for Elementor adds powerful, mobile-focused widgets to Elementor. All widgets share a centralized **Site Info** data store (phones, emails, addresses, social links, business hours) so you configure your business details once and reuse them everywhere.

**Site Info**
Centralized business data configured under Paradise → Elementor Widgets. Exposes a `[paradise_site_info]` shortcode and Elementor Dynamic Tags. All widgets that display phone numbers, addresses, maps, social links, or business hours read from Site Info automatically.

**Phone Link**
A smart phone number widget that normalizes any input format and builds proper `tel:` or WhatsApp `wa.me` links. Supports icons, prefix text, multiple display formats, and flexible link scopes.

**Phone Button**
A styled CTA button that dials a phone number or opens WhatsApp. Full button customization with hover effects and responsive sizing.

**Floating Call Button**
A fixed-position call button that stays visible while users scroll. Includes position customization, pulse animation, label support, and breakpoint visibility.

**Bottom Navigation Bar**
A fully-featured mobile bottom navigation bar with responsive visibility, active state detection, badge support (including WooCommerce cart), speed dial, and a JavaScript hook system.

**Author Card**
A professional profile card displaying author information: photo, name, title, bio, custom meta fields, social links, and CTA button. Includes Schema.org Person markup for SEO.

**Announcement Bar**
A fixed full-width banner for announcements, promotions, or alerts. Supports icon, message, CTA button, and dismissal with session / days / permanent memory stored in localStorage.

**Cookie Consent Bar**
A GDPR/cookie consent bar with Accept and Decline buttons. Stores user choice in localStorage with configurable expiry. Dispatches `paradise:consent:accepted` and `paradise:consent:declined` events for analytics integration.

**Back to Top**
A fixed-position button that appears after scrolling past a configurable threshold and smoothly returns the user to the top of the page.

**Off-Canvas Menu**
A slide-in side panel with a WordPress menu. Triggered by an inline button or the `Paradise.openOffCanvas()` JavaScript API (useful with Bottom Nav JS Hooks).

**Sticky Header**
Place inside any Elementor section to make it sticky. Applies configurable scroll effects (shadow, background change, height shrink) after passing a scroll threshold.

**Google Map**
Embeds a Google Map via iframe. Source can be a Site Info address (Map URL field) or a manually entered URL. Supports Place and Directions modes, satellite/hybrid/terrain map types, zoom slider, border radius, and box shadow.

**Social Links**
A row or column of social media icon links. Source: Site Info socials or a custom repeater. Supports brand/uniform colors, lift/scale/color-shift hover animations, circle/rounded icon shapes, and icon-only/icon+label/label-only display modes.

**Business Hours**
Displays business hours from Site Info with a live Open Now / Closed badge. Highlights today's row. Supports 12 h and 24 h formats. The badge updates client-side using the site's timezone (independent of the visitor's browser timezone).

**LocalBusiness Schema**
An invisible widget that outputs Schema.org JSON-LD markup using Site Info data (name, phone, address, social sameAs, openingHoursSpecification). Supports 14 Schema.org business type subtypes. Helps Google display rich results (address, phone, hours).

**FAQ Accordion**
A collapsible Q&A list with accordion mode (one item open at a time) or multi-expand mode (any number open). Supports an Elementor icon picker for the open/closed state, left or right icon position, open-first-item default, and Schema.org FAQPage JSON-LD for Google rich results. Items can be entered statically in the widget or loaded from the FAQ Post Type.

= Phone Link Features =
* Accepts any phone number format — normalizes automatically
* Display formats: Raw, International, Local, Dashes, Dots, Custom Mask
* Layout modes: Number Only, Prefix + Number, Icon + Number, Icon + Prefix + Number
* Inline or Stacked direction
* Link scope: Full Widget, Number Only, or No Link
* Link type: Phone Call (`tel:`) or WhatsApp (`wa.me/`)
* Dynamic tag support on phone number and prefix

= Bottom Navigation Bar Features =
* Items source: Manual repeater or WordPress Menu
* Badge support: Static, WooCommerce cart count, or JS-driven via `Paradise.setBadge()`
* Responsive visibility via Elementor native controls
* Center button actions: Link, Speed Dial, or JS Hook
* Active state detection: URL match, Manual index, or Both
* Active indicators: Top Bar, Bottom Bar, Dot, Pill, or Glow
* Bar position: Full Width or Floating Centered
* Entrance animations: Slide Up, Fade, or Both
* JavaScript Public API: `Paradise.setBadge(id, count)`
* JS Hook system: `document.addEventListener('ebn:hook:name', fn)`

= Google Map Features =
* Mode: Place (single location) or Directions (origin → destination)
* Map types: Road map, Satellite, Hybrid, Terrain
* Source: Site Info address Map URL or manual entry
* Zoom slider control
* Height, border radius, and box shadow controls
* Travel modes for Directions: Driving, Walking, Bicycling, Transit

= Social Links Features =
* Source: Site Info socials or custom repeater
* 10 built-in platform icons: Instagram, Facebook, Twitter/X, LinkedIn, YouTube, TikTok, Pinterest, Snapchat, Threads, WhatsApp
* Color modes: Brand colors or Uniform color
* Hover animations: None, Lift, Scale, Color Shift
* Icon shapes: None, Circle, Rounded Square
* Display modes: Icon only, Icon + Label, Label only
* Layout: Horizontal row or Vertical column

== Installation ==

1. Make sure Elementor (free) is installed and activated.
2. Upload the `paradise-widgets-for-elementor` folder to `/wp-content/plugins/`.
3. Activate the plugin from **Plugins** in the WordPress admin.
4. Configure business details under **Paradise → Elementor Widgets → Site Info**.
5. Open Elementor editor — the **Paradise Widgets** category will appear in the widget panel.

== External services ==

This plugin includes an optional **Google Map** widget. When you add that widget to a page, the plugin embeds a Google Maps map using an `<iframe>` served by Google. No map is loaded unless you place the Google Map widget on a page.

What the service is and what it is used for: Google Maps, used to display an interactive map of a location (a place pin or driving directions) inside the widget.

What data is sent and when: the map is loaded directly by the visitor's browser from Google whenever a page containing the Google Map widget is viewed. As with any embedded third-party resource, Google receives the request — including the visitor's IP address, user agent, and the map location/URL you configured. The plugin itself does not collect, store, or transmit any personal data, and makes no other outbound requests.

This service is provided by Google. Please review Google's terms and privacy policy:
- Google Terms of Service: https://policies.google.com/terms
- Google Maps/Google Earth Additional Terms: https://www.google.com/help/terms_maps/
- Google Privacy Policy: https://policies.google.com/privacy

== Frequently Asked Questions ==

= Does this require Elementor Pro? =
No. The free version of Elementor is sufficient. Elementor Pro is optional and unlocks Theme Builder support and Dynamic Tags on Pro controls.

= Is this compatible with the latest version of Elementor? =
Yes. The plugin is tested with Elementor 3.5 and above.

= What is Site Info? =
Site Info is a centralized store for your business data (phones, emails, addresses, social links, business hours). Configure it once and all widgets that need this data read from it automatically. It also provides a shortcode and Elementor Dynamic Tags.

= How do I embed a Google Map? =
Go to Google Maps, search for your location, click Share → Embed a map → copy the `src` URL from the iframe code. Paste it into the Site Info address "Map URL" field, or directly in the Google Map widget. The plugin normalizes most Google Maps URL formats automatically.

= How do I set a badge count from JavaScript? =
Use the public API: `Paradise.setBadge('your-widget-css-id', count);`
Setting count to 0 hides the badge. Count above 99 displays as "99+".

= How do I trigger the Off-Canvas Menu from Bottom Navigation? =
Set the Bottom Nav **center button** action to **JS Hook** and enter a hook name (e.g. `openMenu`). This fires the event `ebn:hook:openMenu` on the document. Listen for it and open the panel by its element ID:
`document.addEventListener('ebn:hook:openMenu', function () { Paradise.toggleOffCanvas('your-offcanvas-id'); });`

= Does the Bottom Navigation Bar work with WooCommerce? =
Yes. Set the badge type to **WooCommerce Cart** and it will display the live cart item count automatically.

= How accurate is the Business Hours "Open Now" badge? =
The badge is computed in the visitor's browser using the site's timezone (from WordPress Settings → General). It does not depend on the visitor's device timezone, so it always reflects your business's local time.

= Where is the full documentation? =
A complete usage guide — every widget, its key controls, the JavaScript API, ready-made recipes, and a troubleshooting section — is available at: https://github.com/rezabagheri/paradise-widgets-for-elementor/blob/main/docs/USAGE.md

== Screenshots ==

1. A real landing page — the Announcement Bar, a gradient hero, and a WhatsApp Phone Button working together.
2. Google Map widget — a live map pulled automatically from Site Info (no embed code to paste).
3. Social Links widget — a brand-coloured social icon row.
4. Business Hours widget — the weekly schedule with a live "Open Now / Closed" badge and today highlighted.
5. Bottom Navigation Bar on mobile — icons, labels, and a floating Speed Dial center button.
6. Announcement Bar — a dismissible promo banner with a call-to-action.
7. Author Card — photo, bio, credentials, and a custom profile field.
8. Phone widgets — a contact card with Phone Link, tel + WhatsApp Phone Buttons, and the floating Call Button.

== Changelog ==

= 3.1.0 =
* Added: Initial translation template at `languages/paradise-widgets-for-elementor.pot` (651 translatable strings).
* Added: WordPress.org plugin-directory marketing assets in `.wordpress-org/` — `icon.svg` (256×256), `icon-256x256.png` / `icon-128x128.png`, `banner-772x250.svg` / `banner-1544x500.svg` plus PNG exports, and a README documenting the SVN deployment mapping.
* Changed: i18n audit pass — ~166 Elementor control strings (`label`, `label_on`, `label_off`, `description`, `placeholder`, `title`) across widgets/, admin/, and includes/ are now wrapped in `__()` so they're picked up by `wp i18n make-pot` and translatable.
* Changed: `Paradise_EW_Admin` widget and feature registries refactored from static array properties to static methods so `__()` calls are valid inside them (PHP forbids function calls in static property initialisers). Public API (`get_widget_registry()` / `get_feature_registry()`) unchanged.
* Changed: `Location %d` placeholder in Site Info now carries a `/* translators: */` comment.

= 3.0.1 =
* Fixed: International display incorrectly prepended the widget's *Country Code* setting to numbers that already had their own `+CC` prefix. The shared phone helper now carries a built-in ITU-T E.164 country-code table (~120 entries) and detects the embedded cc from inputs like `+37493583161` (Armenia) before the strip-and-format pipeline runs.
* Changed: The three phone widgets (Phone Link, Phone Button, Floating Call Button) now show an Elementor-native dashed placeholder when the phone number field is empty, instead of an inline red-text warning. Editor only — frontend stays silent.
* Added: `Paradise_Widget_Base::render_editor_placeholder( string $hint )` shared helper.

= 3.0.0 =
* Changed: Plugin renamed from "Paradise Elementor Widgets" to "Paradise Widgets for Elementor" to comply with WordPress.org trademark policy. Text domain changed from `paradise-elementor-widgets` to `paradise-widgets-for-elementor`. No data migration required.
* Added: `Tested up to: 6.9` in the plugin header.

= 2.9.0 =
* Added: Six new Custom Field types — `date`, `time`, `email`, `number`, `color`, `range`. Date and time render via WordPress's site-wide `date_format` / `time_format` by default. Number uses `FILTER_VALIDATE_INT` (rejects `'1.5'` rather than truncating). Color validates `#RRGGBB`. Range is an open-bounded integer pair (`Min,Max`) — pick any integers; sanitize swaps endpoints if out of order.
* Added: Array-shaped `el_category` in the type registry — `email` is the first type whose Elementor dynamic-tag binding is multi-category (appears in both TEXT and URL dropdowns so it can populate Heading text AND a Button URL as `mailto:`).
* Added: Live hex display next to the color picker in admin.

= 2.8.1 =
* Fixed: Cookie Consent Bar widget icon — `eicon-cookie` doesn't exist in Elementor's icon font; replaced with `eicon-info-circle` so the widget shows an icon in the editor's widget panel.

= 2.8.0 =
* Added: Custom Fields system — user-defined static fields organized into groups, accessed via `[paradise_field key="…"]` shortcode and three Elementor Dynamic Tags (Text / URL / Image). First types: text, textarea, url, image. Storage in `paradise_custom_fields` option. Type registry is filterable via `paradise_custom_field_types` for site-level extension.
* Added: "Custom Fields" admin page under Paradise menu with drag-to-reorder, type-aware value inputs, and the WP media picker for image fields. Group slug and field key auto-derive from their label when blank.

= 2.7.0 =
* Added: Feature Card example widget — a heavily-commented reference widget for developers extending Paradise. Lives in its own "Paradise Examples" Elementor category, disabled by default. New optional registry flags `example` and `default` (per-widget enabled-by-default state). Companion CSS at `assets/css/feature-card-example.css`.
* Added: Per-row Copy Shortcode buttons on Site Info phone, email, and social rows. Each button reads the row's current (unsaved) label/platform and writes the matching `[paradise_site_info ...]` shortcode to the clipboard. Click feedback: green checkmark icon swap plus a "Copied!" toast above the button.
* Added: Brand-coloured platform icons next to each social `<select>` on the Site Info page (Instagram, Facebook, X, LinkedIn, YouTube, TikTok, Pinterest, Snapchat, Threads, WhatsApp). Updates live when the platform changes. New helper `Paradise_Site_Info::social_icon_svg()`.
* Added: Settings page improvements — widgets grouped into three cards (Production / Developer Examples / Features), each with Enable all / Disable all bulk actions. Live filter input narrows the visible list as you type. "Off by default" badge for widgets that ship disabled.
* Added: "Unsaved changes" pill in the Settings and Site Info page headers. Native `beforeunload` warning blocks accidental navigation. Clears on save.
* Added: Confirm dialog before destructive actions on Site Info — wordier copy for locations (which carry phones, emails, address, hours), short copy for row-level deletes.
* Added: Visual separation between Locations and their sub-sections on Site Info — each location reads as a distinct card with phones/emails/address/hours as clearly separated panels.
* Changed: Site Info admin page — card layout reshaped, Remove/Add buttons restyled as icon-only with leading plus glyphs and a soft-red destructive tint, intro rewritten as a two-method callout (Dynamic Tags recommended; shortcode for outside Elementor).
* Changed: Import / Export admin page — header now matches the Settings page (title + version badge); inline `<style>` block moved to `assets/css/admin.css`.
* Changed: Admin asset enqueue — `admin.js` now enqueued alongside `admin.css` on every Paradise admin page. Page detection uses a prefix check so new submenu pages get the assets automatically.
* Fixed: Copy Shortcode now works on plain HTTP (e.g. local dev on Valet `*.test` domains). `navigator.clipboard` is only defined in secure contexts; falls back to a temporary `<textarea>` + `document.execCommand('copy')` so the button works regardless of HTTPS. Red "Copy failed" toast surfaces if both paths fail.
* Fixed: Google Maps preview iframe no longer flickers with 400 errors during typing or on malformed URLs. The map URL field validates the URL as an `https://google.com/maps/embed` URL before pointing the iframe at it.
* Fixed: Save confirmation visible after long-form save — Site Info now smooth-scrolls the success notice into view on the post-save reload, so users on long pages don't miss the confirmation.
* Documentation: README — Developer Guide gains a "Learning from the example widget" subsection that points new contributors at the example widget as a complete, commented blueprint.

= 2.6.0 =
* Added: Paradise_Widget_Base abstract class — a small shared base inherited by every bundled widget. Provides default get_categories(), conventional asset-handle naming from get_name(), and a get_default_handle() helper. Plugins/themes that fork Paradise can extend it to get the same conventions.
* Changed: All 15 bundled widgets now extend Paradise_Widget_Base, dropping repeated get_categories() and get_style_depends() overrides. Widgets with a JS file declare get_script_depends() through $this->get_default_handle(). Bottom Navigation Bar preserves its extra Font Awesome handles via array_merge(parent::get_style_depends(), [...]). No behaviour change for end users.
* Documentation: README — File Structure now lists includes/class-paradise-widget-base.php; the Developer Guide's "Adding a new widget" section reflects the current workflow (extend the base, registry takes label/description/js only, asset files at conventional paths get registered automatically).

= 2.5.0 =
* Changed: Asset registration is now registry-driven — `enqueue_assets()` reduced from ~170 hardcoded calls to a single loop over `Paradise_EW_Admin::$widget_registry`. Adding a new widget no longer requires editing the main plugin file.
* Changed: Asset handle naming normalized to `paradise-{slug}` for both CSS and JS of each widget (no more mixed `-style` / `-script` suffixes).
* Changed: Raised minimum PHP version from 7.4 to 8.0 to match the codebase (uses `mixed` parameter types and typed properties).
* Added: Elementor compatibility admin notices — shown when Elementor is missing or older than the required minimum (3.5.0). Widget/asset registration is skipped on outdated Elementor to avoid fatals.
* Fixed: Bottom Navigation Bar — corrected asset handles so the widget's CSS and JS actually load on the frontend. The widget previously referenced `paradise-bn-bottom-nav-style` / `-script` while the main file registered `paradise-bottom-nav-style` / `-script`; the mismatch meant nothing loaded.
* Fixed: Phone Link — removed dead `get_uwidget_type()` method (typo of a non-existent Elementor method that was never called).
* Fixed: Floating Call Button — corner offsets are now applied to the position-fixed inner wrapper rather than the static outer wrapper, so the button pins to the chosen corner with the configured offsets (previously it fell back to its document-flow position).
* Fixed: FAQ Accordion — closed items no longer leak the first line of their answer (`grid-template-rows: minmax(0, 0fr)` overrides the implicit min-content track sizing).
* Fixed: FAQ Accordion — no more `TypeError` on templates where `elementorFrontend.hooks` is not yet ready (e.g. `elementor_canvas`); the editor re-init now checks both `elementorFrontend` and `.hooks` before calling `addAction`.
* Documentation: README gains a Screenshots section with seven viewport-correct widget screenshots.

= 2.4.0 =
* Added: FAQ Accordion widget — accordion / multi-expand mode, Elementor icon picker (open/closed state), icon position (left/right), open-first-item default, Schema.org FAQPage JSON-LD, full typography and color controls
* Added: FAQ Post Type — each post is a "FAQ Set" with unlimited Q&A items stored in post meta; TinyMCE rich text editor for answers; toggle on/off in Paradise → Elementor Widgets → Settings
* Fixed: Elementor editor CSS appearing as visible text content when FAQ Accordion widget (CPT source) was on the page — caused by `apply_filters('the_content', …)` being called inside widget render

= 2.3.0 =
* Added: Site Info centralized data store (phones, emails, addresses, socials, business hours) with shortcode and Elementor Dynamic Tags
* Added: Business Hours widget — live Open Now / Closed badge, today highlight, 12 h / 24 h format
* Added: LocalBusiness Schema widget — Schema.org JSON-LD with 14 business type subtypes
* Added: Google Map widget — Place and Directions modes, satellite/hybrid/terrain, zoom, border radius, shadow
* Added: Social Links widget — Site Info or custom source, brand colors, hover animations, shapes
* Added: Announcement Bar widget — icon, message, CTA, dismiss with session/days/permanent memory
* Added: Cookie Consent Bar widget — Accept/Decline, localStorage expiry, analytics events
* Added: Back to Top widget — scroll threshold, smooth scroll
* Added: Off-Canvas Menu widget — WordPress menu in slide-in panel, JS API trigger
* Added: Sticky Header widget — scroll effects (shadow, background, shrink)
* Added: Site Info map_url field on address entries
* Added: Drag-to-reorder for all Site Info repeater sections
* Added: label attribute on [paradise_site_info] shortcode
* Changed: register_widgets() now driven by a registry array — one entry per widget
* Fixed: Google Map /maps/dir/ URLs refused to connect in iframe — now rewritten correctly
* Fixed: Google Map zoom ignored with /maps/embed?q= format — now uses reliable URL format

= 2.2.0 =
* BREAKING: Bottom Navigation widget name changed from ebn_bottom_nav to paradise_bottom_nav — re-save existing widgets
* BREAKING: Bottom Nav CSS class prefix updated to paradise-bn-
* Added: WhatsApp link support in Phone Link widget
* Added: WooCommerce cart count badge in Bottom Navigation Bar
* Added: Schema.org markup to Author Card
* Added: JS Hook system for center button custom actions
* Added: Custom CSS mask for phone number display
* Fixed: Bottom Nav items no longer interfere with speed dial
* Fixed: Phone Link properly escapes all output
* Improved: Pixel-perfect editor preview inside Elementor iframe

= 2.1.0 =
* Added: Elementor native responsive visibility for Bottom Navigation Bar
* Added: Pixel-perfect editor preview inside Elementor iframe
* Added: Speed dial auto-opens in editor for visual feedback
* Changed: Rebranded to Paradise

= 2.0.0 =
* Changed: Removed all !important declarations from CSS
* Changed: Introduced CSS variables for theming

= 1.0.0 =
* Initial release
* Phone Link widget
* Bottom Navigation Bar widget

== Upgrade Notice ==

= 3.1.0 =
i18n compliance pass: ~166 strings now wrapped in `__()`, .pot template shipped, plugin is fully translation-ready. No behavioural changes. First release using the new dev-phase batching cadence (changes since 3.0.1 accumulated under [Unreleased] in CHANGELOG.md). Safe to upgrade.

= 3.0.0 =
Plugin renamed (folder + text domain). No data migration required. If you reference the text domain in custom code, update `paradise-elementor-widgets` to `paradise-widgets-for-elementor`.

= 2.6.0 =
Internal refactor — all 15 widgets now extend a shared Paradise_Widget_Base. No behaviour change for end users; safe to upgrade from 2.5.0.

= 2.5.0 =
Internal refactor (~88% smaller asset registration), Bottom Nav asset-loading bug fixed, and new Elementor compatibility notices. Note: minimum PHP raised to 8.0 — installs still on PHP 7.4 must stay on 2.4.x.

= 2.4.0 =
New: FAQ Accordion widget and FAQ Post Type. Safe to upgrade — no breaking changes from 2.3.0.

= 2.3.0 =
New: Site Info, Business Hours, LocalBusiness Schema, Google Map, Social Links, Announcement Bar, Cookie Consent Bar, Back to Top, Off-Canvas Menu, Sticky Header. Safe to upgrade — no breaking changes from 2.2.0.

= 2.2.0 =
Important: Bottom Navigation widgets require manual re-save in Elementor after this update due to name change.
