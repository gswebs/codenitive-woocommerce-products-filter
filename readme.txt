=== WooCommerce Attribute Product Filter by Codenitive ===
Contributors: codenitive
Tags: woocommerce, filter, product filter, attribute filter, shop filter
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Filter WooCommerce products by attributes on shop and archive pages using a simple widget or shortcode.

== Description ==

WooCommerce Attribute Product Filter by Codenitive is a lightweight and powerful tool that allows your customers to filter products by their attributes (like color, size, or material). It works seamlessly on the main Shop page and Product Archive pages.

The plugin provides both a **Widget** and a **Shortcode**, giving you full flexibility on where to place your filters. You can choose to display filters as dropdowns or checkboxes.

= Key Features =
* Filter by any WooCommerce attribute.
* Support for multiple display types: Dropdowns and Checkboxes.
* Custom Widget included for easy sidebar integration.
* Shortcode support for use in page builders or content areas.
* Lightweight and developer-friendly code.
* Built-in support for multiple attribute selections (OR logic).

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'Woo Attribute Filter by Codenitive' widget in **Appearance > Widgets**.
4. Alternatively, use the shortcode `[codenitive_wc_attribute_filter]` in your posts or pages.

**Parameters:**
* `attributes`: Comma-separated list of attribute slugs (e.g., color, size). Leave empty to show all.
* `display`: Choose between `dropdown` (default) or `checkbox`.
* `button_text`: Change the text of the filter button.

== Shortcode Usage ==

Use the following shortcode to display the filter:
`[codenitive_wc_attribute_filter attributes="color,size" display="checkbox"]`
`[codenitive_wc_attribute_filter attributes="color,size" display="dropdown"]`
`[codenitive_wc_attribute_filter attributes="color,size" display="anchor_list"]`

== Screenshots ==

1. The filter widget appearing in the sidebar.
2. Checkbox display vs Dropdown display.

== Changelog ==

= 1.0.2 =
* Fixed the dropdown filter issue

= 1.0.0 =
* Initial release.