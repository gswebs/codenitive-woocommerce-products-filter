<?php
/**
 * Helper functions for WooCommerce Product Filter by Codenitive.
 *
 * @package CodenitiveWooCommerceProductsFilter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert common shortcode/widget values to boolean.
 *
 * @param mixed $value Value to convert.
 * @return bool
 */
function codenit_wc_apf_bool( $value ) {
    if ( is_bool( $value ) ) {
        return $value;
    }

    return in_array( strtolower( (string) $value ), [ '1', 'yes', 'true', 'on' ], true );
}

/**
 * Render price range slider.
 *
 * @return void
 */
function codenit_wc_apf_render_price_slider() {
    global $wpdb;

    $prices = $wpdb->get_row(
        "SELECT MIN(meta_value+0) as min_price, MAX(meta_value+0) as max_price
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_price'"
    );

    $min = isset( $prices->min_price ) ? floor( (float) $prices->min_price ) : 0;
    $max = isset( $prices->max_price ) ? ceil( (float) $prices->max_price ) : 0;

    if ( $max <= 0 ) {
        return;
    }

    $current_min = isset( $_GET['min_price'] ) ? absint( wp_unslash( $_GET['min_price'] ) ) : $min;
    $current_max = isset( $_GET['max_price'] ) ? absint( wp_unslash( $_GET['max_price'] ) ) : $max;

    $current_min = max( $min, min( $current_min, $max ) );
    $current_max = max( $min, min( $current_max, $max ) );
    ?>
    <div class="codenit-price-slider">
        <div class="codenit-accordion-header label">
            <strong><?php esc_html_e( 'Price Range', 'codenit-wc-attribute-filter' ); ?></strong>
        </div>

        <div class="codenit-price-inputs">
            <span class="price-from"><?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?><span id="min-price-text"><?php echo esc_html( $current_min ); ?></span></span>
            <span class="price-to"><?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?><span id="max-price-text"><?php echo esc_html( $current_max ); ?></span></span>
        </div>

        <div class="range-slider-container">
            <input type="range" name="min_price"
                min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
                value="<?php echo esc_attr( $current_min ); ?>" class="codenit-range" id="min_range">

            <input type="range" name="max_price"
                min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
                value="<?php echo esc_attr( $current_max ); ?>" class="codenit-range" id="max_range">
        </div>
    </div>
    <?php
}

/**
 * Get selected terms from URL query string.
 *
 * Supports comma-separated query values, e.g. ?color=red,blue or ?product_cat=shirts.
 *
 * @param string $query_var Query string key.
 * @return array
 */
function codenit_wc_apf_get_selected_terms( $query_var ) {
    if ( empty( $_GET[ $query_var ] ) ) {
        return [];
    }

    $raw = sanitize_text_field( wp_unslash( $_GET[ $query_var ] ) );

    return array_values(
        array_filter(
            array_map( 'sanitize_title', explode( ',', $raw ) )
        )
    );
}

/**
 * Preserve current query args as hidden inputs.
 *
 * @param array $exclude Query keys to exclude.
 * @return void
 */
function codenit_wc_apf_preserve_query_args( $exclude = [] ) {
    foreach ( $_GET as $key => $value ) {
        $key = sanitize_key( $key );

        if ( in_array( $key, $exclude, true ) ) {
            continue;
        }

        if ( is_array( $value ) ) {
            continue;
        }

        printf(
            '<input type="hidden" name="%s" value="%s">',
            esc_attr( $key ),
            esc_attr( sanitize_text_field( wp_unslash( $value ) ) )
        );
    }
}

/**
 * Get filterable WooCommerce attributes.
 *
 * @param array $allowed_attributes Attribute slugs without pa_ prefix, e.g. color, size.
 * @return array
 */
function codenit_wc_apf_get_attributes( $allowed_attributes = [] ) {
    $all_attributes = wc_get_attribute_taxonomies();

    if ( empty( $allowed_attributes ) ) {
        return $all_attributes;
    }

    $allowed_attributes = array_map( 'sanitize_title', $allowed_attributes );

    return array_filter(
        $all_attributes,
        function ( $attribute ) use ( $allowed_attributes ) {
            return in_array( $attribute->attribute_name, $allowed_attributes, true );
        }
    );
}

/**
 * Build all enabled filter items: product attributes, product categories, and product tags.
 *
 * @param array $args Render args.
 * @return array
 */
function codenit_wc_apf_get_filter_items( $args ) {
    $items      = [];
    $attributes = ! empty( $args['show_attributes'] ) ? codenit_wc_apf_get_attributes( $args['attributes'] ) : [];

    foreach ( $attributes as $attribute ) {
        $items[] = [
            'type'      => 'attribute',
            'query_var' => $attribute->attribute_name,
            'taxonomy'  => wc_attribute_taxonomy_name( $attribute->attribute_name ),
            'label'     => $attribute->attribute_label,
            'class'     => $attribute->attribute_name,
        ];
    }

    if ( ! empty( $args['show_categories'] ) ) {
        $items[] = [
            'type'      => 'taxonomy',
            'query_var' => 'product_cat',
            'taxonomy'  => 'product_cat',
            'label'     => codenit_wc_apf_is_product_category_page() ? __( 'Subcategories', 'codenit-wc-attribute-filter' ) : __( 'Categories', 'codenit-wc-attribute-filter' ),
            'class'     => 'product-cat',
        ];
    }

    if ( ! empty( $args['show_tags'] ) ) {
        $items[] = [
            'type'      => 'taxonomy',
            'query_var' => 'product_tag',
            'taxonomy'  => 'product_tag',
            'label'     => __( 'Tags', 'codenit-wc-attribute-filter' ),
            'class'     => 'product-tag',
        ];
    }

    return $items;
}

/**
 * Check if the current archive is a WooCommerce product category page.
 *
 * @return bool
 */
function codenit_wc_apf_is_product_category_page() {
    return function_exists( 'is_product_category' ) && is_product_category();
}

/**
 * Get current WooCommerce product category term.
 *
 * @return WP_Term|null
 */
function codenit_wc_apf_get_current_product_category_term() {
    if ( ! codenit_wc_apf_is_product_category_page() ) {
        return null;
    }

    $term = get_queried_object();

    if ( ! $term instanceof WP_Term || 'product_cat' !== $term->taxonomy ) {
        return null;
    }

    return $term;
}

/**
 * Get current WooCommerce product category term ID.
 *
 * @return int
 */
function codenit_wc_apf_get_current_product_category_id() {
    $term = codenit_wc_apf_get_current_product_category_term();

    return $term instanceof WP_Term ? absint( $term->term_id ) : 0;
}

/**
 * Get the parent category ID used for the category filter list.
 *
 * When ?product_cat=child-slug is present, WordPress can treat that child
 * category as the current queried object. In that case, using the child as the
 * parent gives no terms, so the filter disappears. This keeps the filter list
 * on the selected child category's parent, which keeps sibling child categories
 * visible and allows the selected option to stay checked/selected.
 *
 * @return int
 */
function codenit_wc_apf_get_category_filter_parent_id() {
    $current_term = codenit_wc_apf_get_current_product_category_term();

    if ( ! $current_term instanceof WP_Term ) {
        return 0;
    }

    $selected_categories = codenit_wc_apf_get_selected_terms( 'product_cat' );

    if ( ! empty( $selected_categories ) ) {
        $selected_term = get_term_by( 'slug', $selected_categories[0], 'product_cat' );

        if ( $selected_term instanceof WP_Term && $selected_term->parent > 0 ) {
            return absint( $selected_term->parent );
        }
    }

    return absint( $current_term->term_id );
}

/**
 * Get terms for a taxonomy.
 *
 * On product category archive pages, product_cat filters show direct child
 * categories of the current category. If the selected product_cat query var
 * causes WordPress to switch the queried object to a child category, the filter
 * falls back to that child category's parent so the category options do not
 * disappear.
 *
 * @param string $taxonomy Taxonomy name.
 * @return array|WP_Error
 */
function codenit_wc_apf_get_terms( $taxonomy ) {
    $args = [
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ];

    if ( 'product_cat' === $taxonomy ) {
        $category_filter_parent_id = codenit_wc_apf_get_category_filter_parent_id();

        if ( $category_filter_parent_id ) {
            $args['parent'] = $category_filter_parent_id;
        }
    }

    /**
     * Filter term query args used by the Codenitive WooCommerce product filter.
     *
     * @param array  $args     get_terms() args.
     * @param string $taxonomy Taxonomy name.
     */
    $args = apply_filters( 'codenit_wc_apf_get_terms_args', $args, $taxonomy );

    $terms = get_terms( $args );

    if ( 'product_cat' === $taxonomy && ( empty( $terms ) || is_wp_error( $terms ) ) ) {
        $current_term = codenit_wc_apf_get_current_product_category_term();

        if ( $current_term instanceof WP_Term && $current_term->parent > 0 ) {
            $fallback_args           = $args;
            $fallback_args['parent'] = absint( $current_term->parent );
            $terms                   = get_terms( $fallback_args );
        }
    }

    return $terms;
}

/**
 * Backward-compatible wrapper for older code.
 *
 * @param string $taxonomy Taxonomy name.
 * @return array|WP_Error
 */
function codenit_wc_apf_get_attribute_terms( $taxonomy ) {
    return codenit_wc_apf_get_terms( $taxonomy );
}

/**
 * Get selected value from request.
 *
 * @param string $taxonomy Query var.
 * @return string
 */
function codenit_wc_apf_get_selected( $taxonomy ) {
    return isset( $_GET[ $taxonomy ] )
        ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) )
        : '';
}

/**
 * Build filter URL for anchor links.
 *
 * @param string $query_var Query string key.
 * @param string $term_slug Term slug.
 * @return string
 */
function codenit_wc_apf_build_anchor_url( $query_var, $term_slug ) {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
    $base_url    = home_url( strtok( $request_uri, '?' ) );
    $query_args  = [];

    foreach ( $_GET as $key => $value ) {
        if ( is_array( $value ) ) {
            continue;
        }

        $key = sanitize_key( $key );

        if ( in_array( $key, [ 'paged', 'product-page' ], true ) ) {
            continue;
        }

        $query_args[ $key ] = sanitize_text_field( wp_unslash( $value ) );
    }

    $selected = codenit_wc_apf_get_selected_terms( $query_var );

    if ( in_array( $term_slug, $selected, true ) ) {
        $selected = array_diff( $selected, [ $term_slug ] );
    } else {
        $selected[] = $term_slug;
    }

    if ( empty( $selected ) ) {
        unset( $query_args[ $query_var ] );
    } else {
        $query_args[ $query_var ] = implode( ',', array_unique( $selected ) );
    }

    if ( empty( $query_args ) ) {
        return $base_url;
    }

    return add_query_arg( $query_args, $base_url );
}

/**
 * Render one filter item.
 *
 * @param array  $item Filter item data.
 * @param string $display Display type.
 * @return string Query var rendered, or empty string if skipped.
 */
function codenit_wc_apf_render_filter_item( $item, $display ) {
    $terms = codenit_wc_apf_get_terms( $item['taxonomy'] );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '';
    }

    $selected = codenit_wc_apf_get_selected_terms( $item['query_var'] );

    if ( 'anchor_list' === $display ) {
        echo '<div class="codenit-filter codenit-filter-' . esc_attr( $item['class'] ) . '"><ul class="codenit-filter-ul">';

        foreach ( $terms as $term ) {
            $checked = in_array( $term->slug, $selected, true );
            $url     = codenit_wc_apf_build_anchor_url( $item['query_var'], $term->slug );
            $active  = $checked ? ' is-active' : '';

            printf(
                '<li class="codenit-filter-li%1$s"><a href="%2$s" data-attribute="%3$s" data-taxonomy="%4$s" data-value="%5$s">%6$s</a></li>',
                esc_attr( $active ),
                esc_url( $url ),
                esc_attr( $item['query_var'] ),
                esc_attr( $item['taxonomy'] ),
                esc_attr( $term->slug ),
                esc_html( $term->name )
            );
        }

        echo '</ul></div>';

        return $item['query_var'];
    }

    if ( 'checkbox' === $display ) {
        echo '<div class="codenit-filter codenit-accordion-active codenit-filter-' . esc_attr( $item['class'] ) . '">';
        echo '<div class="codenit-accordion-header label"><strong>' . esc_html( $item['label'] ) . '</strong><div class="codenit-arrow"></div></div>';
        echo '<ul class="codenit-accordion-content">';

        foreach ( $terms as $term ) {
            $checked = in_array( $term->slug, $selected, true );

            printf(
                '<li><label><input type="checkbox" data-attribute="%1$s" data-taxonomy="%2$s" value="%3$s" %4$s> %5$s</label></li>',
                esc_attr( $item['query_var'] ),
                esc_attr( $item['taxonomy'] ),
                esc_attr( $term->slug ),
                checked( $checked, true, false ),
                esc_html( $term->name )
            );
        }

        echo '</ul><input type="hidden" name="' . esc_attr( $item['query_var'] ) . '" value=""></div>';

        return $item['query_var'];
    }

    $selected_value = $selected[0] ?? '';

    echo '<div class="codenit-filter codenit-accordion-active codenit-filter-' . esc_attr( $item['class'] ) . '">';
    echo '<div class="codenit-accordion-header label"><strong>' . esc_html( $item['label'] ) . '</strong><div class="codenit-arrow"></div></div>';
    echo '<div class="codenit-accordion-content">';
    echo '<select name="' . esc_attr( $item['query_var'] ) . '">';
    echo '<option value="">' . esc_html__( 'Any', 'codenit-wc-attribute-filter' ) . '</option>';

    foreach ( $terms as $term ) {
        printf(
            '<option value="%1$s" %2$s>%3$s</option>',
            esc_attr( $term->slug ),
            selected( $selected_value, $term->slug, false ),
            esc_html( $term->name )
        );
    }

    echo '</select></div></div>';

    return $item['query_var'];
}

/**
 * Render attribute/category/tag filter form.
 *
 * @param array $args Optional args.
 * @return void
 */
function codenit_wc_apf_render_filters( $args = [] ) {
    $defaults = [
        'form_class'      => 'codenit-wc-apf-widget-form',
        'button'          => true,
        'button_text'     => __( 'Filter', 'codenit-wc-attribute-filter' ),
        'attributes'      => [],
        'show_attributes' => true,
        'display'         => 'dropdown',
        'show_price'      => false,
        'show_categories' => false,
        'show_tags'       => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    $args['show_price']      = codenit_wc_apf_bool( $args['show_price'] );
    $args['show_attributes'] = codenit_wc_apf_bool( $args['show_attributes'] );
    $args['show_categories'] = codenit_wc_apf_bool( $args['show_categories'] );
    $args['show_tags']       = codenit_wc_apf_bool( $args['show_tags'] );

    $items = codenit_wc_apf_get_filter_items( $args );

    if ( empty( $items ) && ! $args['show_price'] ) {
        return;
    }

    $rendered_query_vars = [];

    if ( 'anchor_list' === $args['display'] ) {
        foreach ( $items as $item ) {
            $rendered = codenit_wc_apf_render_filter_item( $item, $args['display'] );

            if ( $rendered ) {
                $rendered_query_vars[] = $rendered;
            }
        }

        return;
    }

    $form_class = trim( 'codenit-wc-apf-form ' . $args['form_class'] );

    echo '<form method="GET" class="' . esc_attr( $form_class ) . '"><div class="product-filter-inner">';

    foreach ( $items as $item ) {
        $rendered = codenit_wc_apf_render_filter_item( $item, $args['display'] );

        if ( $rendered ) {
            $rendered_query_vars[] = $rendered;
        }
    }

    if ( $args['show_price'] ) {
        codenit_wc_apf_render_price_slider();
        $rendered_query_vars[] = 'min_price';
        $rendered_query_vars[] = 'max_price';
    }

    codenit_wc_apf_preserve_query_args(
        array_merge(
            $rendered_query_vars,
            [ 'paged', 'product-page' ]
        )
    );

    if ( $args['button'] ) {
        echo '<button type="submit">' . esc_html( $args['button_text'] ) . '</button>';
    }

    echo '</div></form>';
}

/**
 * Build tax_query from request using attribute slugs, product_cat and product_tag.
 *
 * @return array
 */
function codenit_wc_apf_build_tax_query_from_request() {
    $tax_query  = [];
    $attributes = wc_get_attribute_taxonomies();

    foreach ( $attributes as $attribute ) {
        $slug = $attribute->attribute_name;

        if ( empty( $_GET[ $slug ] ) ) {
            continue;
        }

        $terms = codenit_wc_apf_get_selected_terms( $slug );

        if ( empty( $terms ) ) {
            continue;
        }

        $tax_query[] = [
            'taxonomy' => wc_attribute_taxonomy_name( $slug ),
            'field'    => 'slug',
            'terms'    => $terms,
            'operator' => 'IN',
        ];
    }

    $extra_taxonomies = [
        'product_cat' => 'product_cat',
        'product_tag' => 'product_tag',
    ];

    foreach ( $extra_taxonomies as $query_var => $taxonomy ) {
        if ( empty( $_GET[ $query_var ] ) ) {
            continue;
        }

        $terms = codenit_wc_apf_get_selected_terms( $query_var );

        if ( empty( $terms ) ) {
            continue;
        }

        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => $terms,
            'operator' => 'IN',
        ];
    }

    return $tax_query;
}
