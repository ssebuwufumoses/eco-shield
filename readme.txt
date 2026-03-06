=== Eco-Shield ===
Contributors: ssebuwufumoses
Tags: youtube, vimeo, video, privacy, lightbox
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Boost PageSpeed, reduce carbon footprint, and track engagement by replacing YouTube & Vimeo embeds with a smart, privacy-focused static player.

== Description ==

**Eco-Shield** is the ultra-lightweight solution for faster, greener, and more private WordPress sites.

Standard YouTube and Vimeo embeds are heavy. A single video embed can load over 1.7MB of extra scripts and track your users before they even click play. This hurts your PageSpeed Score, increases bounce rates, and violates strict privacy laws like GDPR.

**Eco-Shield fixes this automatically.**

It replaces heavy external iframes with a **locally hosted, optimized WebP image**. The heavy video player (and its tracking cookies) is only loaded when your visitor explicitly clicks "Play."

### 🚀 New in Version 1.3.0 (The Engagement Update)

We have transformed Eco-Shield from a simple caching tool into a full **Video Experience Suite**.

**Lightbox Mode:** Optionally open videos in a beautiful, dimmed popup overlay. This "Cinema Mode" eliminates distractions and keeps users focused entirely on your content.

**Engagement Analytics:** Stop guessing! See exactly how many users are clicking "Play" on your videos directly from your Dashboard Widget.

**Strict Privacy Mode:** Add a custom text overlay (e.g., "Click to load content from YouTube") to the thumbnail. Perfect for strict GDPR/DSGVO compliance in Germany and the EU.

**Custom Branding:** Ditch the standard YouTube Red. Change the "Play Button" color to match your brand identity.

**Smart Data Controls:** Easily reset your analytics data from the settings page when you are ready to start a new campaign.

### ⚡ Why Eco-Shield?

**Maximum Performance**
* **Zero Bloat:** We don't use heavy libraries. The plugin is ultra-lightweight (<10KB) so your site loads instantly.
* **Instant Loading:** Uses modern browser technology to load images only when they are on screen (Lazy Loading).
* **Automatic Optimization:** Automatically converts video thumbnails into super-small WebP images to save disk space and bandwidth.
* **Smart Caching:** Includes a "Purge" button in the Admin Toolbar to instantly refresh thumbnails if you change a video.

**Better User Experience**
* **YouTube Shorts Support:** Automatically detects Shorts and displays them in a sleek, vertical player.
* **Smart Deep-Linking:** Timestamps (start at 1:30) and Playlists work perfectly out of the box.
* **Custom Brand Colors:** Use the color picker to style the Play Button to match your website's theme.
* **Cinema Mode:** Toggle between standard inline play or a focused popup player.

**Privacy & Security**
* **GDPR/DSGVO Compliance:** No tracking cookies or 3rd-party connections are made until the user clicks "Play."
* **Privacy Text Overlay:** Optional legal text overlay on thumbnails to inform users before they click.
* **RSS & Email Protection:** Automatically falls back to a static image link in RSS feeds and Emails (preventing broken iframes in Outlook/Gmail).

**Analytics & Insights**
* **Engagement Tracking:** Counts total video plays across your site.
* **Carbon Calculator:** Tracks how much data (MB) and CO2 you have saved the planet.
* **Dashboard Widget:** View your impact stats at a glance.

### 🛡️ Comprehensive Protection

Eco-Shield automatically detects and optimizes:

* **YouTube Shorts:** Displays Shorts in vertical mode.
* **Gutenberg Blocks:** Core YouTube and Vimeo blocks.
* **Auto-Embeds:** Plain URLs pasted into the editor.
* **Legacy Iframes:** Hard-coded iframes from YouTube and Vimeo.
* **Widgets & Sidebars:** Protects videos in Sidebars and Custom HTML widgets.
* **RSS Feeds:** Smart fallback ensures emails never show broken video players.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/eco-shield` directory, or install directly through the WordPress plugins screen.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Go to **Settings -> Eco-Shield** to configure your Brand Color, Lightbox preferences, and Privacy settings.
4.  That's it! Your existing YouTube and Vimeo embeds are now optimized.

== Frequently Asked Questions ==

= How do I use Eco-Shield? =
Just install and activate it. It works automatically on all existing YouTube and Vimeo links in your posts, pages, and widgets. No manual editing required.

= Does this work with Page Builders (Elementor, Divi, Beaver Builder)? =
Yes. Eco-Shield filters `the_content`, `render_block`, and `embed_oembed_html`. It works with the Block Editor (Gutenberg), Classic Editor, and most page builders that output standard WordPress oEmbeds.

= Does this replace iframes and embeds? =
Yes. It intercepts **oEmbeds** (plain URLs) and **Gutenberg Blocks** *before* WordPress converts them into heavy iframes. It also finds hard-coded iframes and replaces them. It does not touch self-hosted video files.

= Can I change the color of the Play Button? =
Yes! Go to **Settings -> Eco-Shield**. Under "Player Design," use the color picker to select any color that matches your brand.

= How does the Lightbox work? =
Go to Settings and check "Enable Lightbox Mode". When a user clicks a video, instead of playing inside the post, it will open in a large, dimmed popup overlay for a cinematic experience.

= Is this GDPR/DSGVO compliant? =
Yes. By default, no connection is made to YouTube/Vimeo servers until the user clicks. For stricter compliance (like in Germany), enable the **"Show Privacy Notice"** feature in settings to display a warning text on the thumbnail.

= How does the Analytics tracking work? =
We use a lightweight signal to increment a counter in your database when a user clicks "Play". We implement **Session De-duplication** to prevent inflated numbers if a user reloads the page and plays the same video again.

= Can I reset my statistics? =
Yes. If you want to clear your "Total Plays" and "MB Saved" data, go to **Settings -> Eco-Shield**, scroll to the bottom, and click the red **"Reset Data"** button.

= Why isn't the Privacy Text or new Color showing up? =
If you just changed a setting, your site might be serving an old cached version. Click the **"Purge Eco-Shield"** button in your Admin Toolbar (top of the screen) to clear the cache and regenerate the player.

= Does this support Vimeo private videos? =
Eco-Shield relies on the public Vimeo API to fetch thumbnails. If a video is password-protected or restricted to a specific domain, the API may block the thumbnail request. In these cases, the plugin will attempt to fall back gracefully, but a public thumbnail is recommended.

= Where are the thumbnails stored? =
Thumbnails are cached locally in your `wp-content/uploads/eco-shield-thumbs/` directory. They are converted to WebP format to minimize disk usage and load times.

== Screenshots ==

1.  **The Player:** A sleek, branded video facade that replaces heavy iframes.
2.  **Dashboard Widget:** Track Bandwidth Saved, CO2 Reduced, and Total Video Plays.
3.  **Lightbox Mode:** The immersive popup player in action.
4.  **Settings Page:** Easy toggles for Privacy, Branding, and Interface options.

== Changelog ==

= 1.2.1 =
* **Feature:** Added **Lightbox Mode** (Play videos in a popup modal).
* **Feature:** Added **Engagement Analytics** (Track total video plays in Dashboard).
* **Feature:** Added **Custom Branding** (Change Play Button color via Color Picker).
* **Feature:** Added **Privacy Text Overlay** (GDPR/DSGVO compliance warning text).
* **Feature:** Added **Reset Data** button in Settings for clearing stats.
* **Improvement:** Implemented Session De-duplication for accurate analytics.
* **Improvement:** Refactored Core Class for better performance and reduced line count.
* **Fix:** Ensure Admin Toolbar "Purge" button appears on frontend for admins.

= 1.2.0 =
* **Feature:** Native Lazy-Loading (`loading="lazy"`) for faster LCP.
* **Feature:** YouTube Shorts detection (Vertical 9:16 Aspect Ratio).
* **Feature:** RSS Feed & Email fallback protection.
* **Fix:** Resolved aspect ratio issues on mobile devices.

= 1.1.0 =
* **Feature:** Local WebP Thumbnail generation.
* **Feature:** Admin Dashboard Widget for Carbon Savings.
* **Feature:** Cache Purging system.

= 1.0.0 =
* Initial Release.