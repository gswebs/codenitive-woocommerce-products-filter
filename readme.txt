=== WooCommerce Product Filter by Codenitive ===
Contributors: codenitive
Tags: woocommerce, filter, product filter, attribute filter, shop filter
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Filter WooCommerce products by attributes, categories, tags, and price on shop and archive pages using a simple widget or shortcode.

== Description ==

WooCommerce Product Filter by Codenitive is a lightweight tool that allows your customers to filter products by attributes like color, size, or material, plus WooCommerce product categories, product tags, and price range. It works on the main Shop page and Product Archive pages.

The plugin provides both a **Widget** and a **Shortcode**, giving you full flexibility on where to place your filters. You can choose to display filters as dropdowns, checkboxes, or an anchor list.

= Key Features =
* Filter by any WooCommerce attribute.
* Filter by WooCommerce product categories.
* Filter by WooCommerce product tags.
* Optional price range slider.
* Support for multiple display types: Dropdowns, Checkboxes, and List.
* Custom Widget included for easy sidebar integration.
* Shortcode support for use in page builders or content areas.
* Lightweight and developer-friendly code.
* Built-in support for multiple selections using comma-separated query values.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'Woo Product Filter by Codenitive' widget in **Appearance > Widgets**.
4. Enable Product Categories and Product Tags from the widget options if needed.
5. Alternatively, use the shortcode `[codenitive_wc_attribute_filter]` in your posts or pages.

**Parameters:**
* `attributes`: Comma-separated list of attribute slugs, e.g. `color,size`. Leave empty to show all product attributes.
* `show_attributes`: Use `yes` or `no` to show/hide product attributes.
* `display`: Choose `dropdown`, `checkbox`, or `anchor_list`.
* `button_text`: Change the text of the filter button.
* `show_price`: Use `yes` or `no` to show/hide the price range slider.
* `show_categories`: Use `yes` or `no` to show/hide WooCommerce product categories.
* `show_tags`: Use `yes` or `no` to show/hide WooCommerce product tags.

== Shortcode Usage ==

Display attributes only:
`[codenitive_wc_attribute_filter attributes="color,size" display="checkbox"]`

Display attributes with categories and tags:
`[codenitive_wc_attribute_filter attributes="color,size" show_categories="yes" show_tags="yes" display="checkbox"]`

Display categories and tags only:
`[codenitive_wc_attribute_filter show_attributes="no" show_categories="yes" show_tags="yes" show_price="no" display="dropdown"]`

Display as an anchor list:
`[codenitive_wc_attribute_filter attributes="color,size" show_categories="yes" display="anchor_list"]`

== Screenshots ==

1. The filter widget appearing in the sidebar.
2. Checkbox display vs Dropdown display.

== Changelog ==

= 1.0.5 =
* Product category filter now shows direct child categories on product category archive pages.


= 1.0.4 =
* Added WooCommerce product category filter option.
* Added WooCommerce product tag filter option.
* Added widget settings for showing attributes, categories, and tags.
* Added shortcode parameters: `show_attributes`, `show_categories`, and `show_tags`.
* Improved checkbox submit handling for shortcode and widget forms.

= 1.0.3 =
* Add price option in filter.

= 1.0.2 =
* Fixed the dropdown filter issue.

= 1.0.0 =
* Initial release.
