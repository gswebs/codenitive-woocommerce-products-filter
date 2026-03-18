<?php
defined( 'ABSPATH' ) || exit;

function codenit_wc_apf_render_price_slider() {
    global $wpdb;

    // 1. Get the actual min/max prices from your database
    $prices = $wpdb->get_row( "
        SELECT MIN(meta_value+0) as min_price, MAX(meta_value+0) as max_price 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_price'
    " );

    $min = floor( $prices->min_price );
    $max = ceil( $prices->max_price );

    // 2. Get current values from URL (or use defaults)
    $current_min = isset( $_GET['min_price'] ) ? sanitize_text_field( $_GET['min_price'] ) : $min;
    $current_max = isset( $_GET['max_price'] ) ? sanitize_text_field( $_GET['max_price'] ) : $max;

    ?>
    <div class="codenit-price-slider">
        <div class="codenit-accordion-header label">
            <strong><?php esc_html_e( 'Price Range', 'codenit-wc-attribute-filter' ); ?></strong>
        </div>
        
        <div class="codenit-price-inputs">
            <span class="price-from"><?php echo get_woocommerce_currency_symbol(); ?><span id="min-price-text"><?php echo $current_min; ?></span></span>
            <span class="price-to"><?php echo get_woocommerce_currency_symbol(); ?><span id="max-price-text"><?php echo $current_max; ?></span></span>
        </div>

        <div class="range-slider-container">
            <input type="range" name="min_price" 
                min="<?php echo $min; ?>" max="<?php echo $max; ?>" 
                value="<?php echo $current_min; ?>" class="codenit-range" id="min_range">
            
            <input type="range" name="max_price" 
                min="<?php echo $min; ?>" max="<?php echo $max; ?>" 
                value="<?php echo $current_max; ?>" class="codenit-range" id="max_range">
        </div>
    </div>

    <?php
}


/**
 * Get selected terms from URL (comma-separated)
 *
 * Example:
 * ?mood=chill,creative
 */
function codenit_wc_apf_get_selected_terms( $attribute_slug ) {

    if ( empty( $_GET[ $attribute_slug ] ) ) {
        return [];
    }

    $raw = sanitize_text_field( wp_unslash( $_GET[ $attribute_slug ] ) );

    return array_filter(
        array_map( 'sanitize_title', explode( ',', $raw ) )
    );
}

function codenit_wc_apf_preserve_query_args( $exclude = [] ) {
    foreach ( $_GET as $key => $value ) {
        if ( in_array( $key, $exclude, true ) ) {
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
 * Get filterable WooCommerce attributes
 * Optional: pass an array of attribute slugs to limit which attributes to show
 *
 * @param array $allowed_attributes Array of attribute slugs (without 'pa_'), e.g. ['color', 'size']
 * @return array Array of WC attribute taxonomies
 */
function codenit_wc_apf_get_attributes( $allowed_attributes = [] ) {
    $all_attributes = wc_get_attribute_taxonomies();

    if ( empty( $allowed_attributes ) ) {
        return $all_attributes;
    }

    // Filter only allowed attributes
    return array_filter( $all_attributes, function ( $attribute ) use ( $allowed_attributes ) {
        return in_array( $attribute->attribute_name, $allowed_attributes, true );
    });
}

/**
 * Get terms for an attribute taxonomy
 */
function codenit_wc_apf_get_attribute_terms( $taxonomy ) {
    return get_terms( [
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ] );
}

/**
 * Get selected value from request
 */
function codenit_wc_apf_get_selected( $taxonomy ) {
    return isset( $_GET[ $taxonomy ] )
        ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) )
        : '';
}

/**
 * Build filter URL for anchor links
 */
function codenit_wc_apf_build_anchor_url( $attribute_slug, $term_slug ) {

    // Base URL without query
    $base_url = home_url( strtok( $_SERVER['REQUEST_URI'], '?' ) );

    // Get current selections
    $selected = codenit_wc_apf_get_selected_terms( $attribute_slug );

    // Toggle logic
    if ( in_array( $term_slug, $selected, true ) ) {
        // Remove if already selected
        $selected = array_diff( $selected, [ $term_slug ] );
    } else {
        // Add if not selected
        $selected[] = $term_slug;
    }

    // If nothing selected → clean URL
    if ( empty( $selected ) ) {
        return $base_url;
    }

    return add_query_arg(
        $attribute_slug,
        implode( ',', $selected ),
        $base_url
    );
}

/**
 * Render attribute filter form
 *
 * @param array $args Optional args:
 *  - 'form_class' => CSS class for form
 *  - 'button' => show submit button (true/false)
 *  - 'button_text' => submit button text
 *  - 'attributes' => array of attribute slugs to show (optional)
 */
function codenit_wc_apf_render_filters( $args = [] ) {

    $defaults = [
        'form_class'  => 'codenit-wc-apf-widget-form',
        'button'      => true,
        'button_text' => __( 'Filter', 'codenit-wc-attribute-filter' ),
        'attributes'  => [],        // optional: ['color', 'size']
        'display'     => 'dropdown', // 'dropdown' or 'checkbox'
    ];

    $args = wp_parse_args( $args, $defaults );

    $attributes = codenit_wc_apf_get_attributes( $args['attributes'] );

    if ( empty( $attributes ) ) {
        return;
    }

    if ($args['display'] == 'anchor_list') {
        
        foreach ( $attributes as $attribute ) {
    
            $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
            $terms    = codenit_wc_apf_get_attribute_terms( $taxonomy );
    
            if ( empty( $terms ) || is_wp_error( $terms ) ) {
                continue;
            }
    
            // Get selected values from URL
            $selected = codenit_wc_apf_get_selected_terms( $attribute->attribute_name );
    
            // Ensure $selected is an array (for checkboxes)
            if ( ! is_array( $selected ) ) {
                $selected = [ $selected ];
            }
            echo '<div class="codenit-filter codenit-filter-'.$attribute->attribute_name.'">
                <ul class="codenit-filter-ul">';
                    foreach ( $terms as $term ) {
                        $checked = in_array( $term->slug, $selected, true );
                        
                        $url = codenit_wc_apf_build_anchor_url(
                            $attribute->attribute_name,
                            $term->slug
                        );
                        
                        $checked_fun = checked( $checked, true, false );
                        $active = $checked ? 'is-active' : '';
                        
                        printf(
                            '<li class="codenit-filter-li '.$active.'">
                                <a href="'.esc_url( $url ).'"
                                   data-attribute="%1$s"
                                   data-value="%2$s">
                                %3$s</a>
                            </li>',
                            esc_attr( $attribute->attribute_name ),
                            esc_attr( $term->slug ),
                            esc_html( $term->name )
                        );
                    }
                echo '</ul>
            </div>';   
        }
    } else {
        echo '<form method="GET" class="' . esc_attr( $args['form_class'] ) . '"><div class="product-filter-inner">';
    
        foreach ( $attributes as $attribute ) {
    
            $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
            $terms    = codenit_wc_apf_get_attribute_terms( $taxonomy );
    
            if ( empty( $terms ) || is_wp_error( $terms ) ) {
                continue;
            }
    
            // Get selected values from URL
            $selected = codenit_wc_apf_get_selected_terms( $attribute->attribute_name );
    
            // Ensure $selected is an array (for checkboxes)
            if ( ! is_array( $selected ) ) {
                $selected = [ $selected ];
            }
    
            if ( $args['display'] === 'checkbox' ) {
                echo '<div class="codenit-filter codenit-accordion-active codenit-filter-'.$attribute->attribute_name.'"><div class="codenit-accordion-header label"><strong>' . esc_html( $attribute->attribute_label ) . '</strong><div class="codenit-arrow"></div></div><ul class="codenit-accordion-content">';
                foreach ( $terms as $term ) {
                    $checked = in_array( $term->slug, $selected, true );
                
                    printf(
                        '<li><label>
                            <input type="checkbox"
                            data-attribute="%1$s"
                            value="%2$s"
                            %3$s>
                            %4$s
                        </label></li>',
                        esc_attr( $attribute->attribute_name ),
                        esc_attr( $term->slug ),
                        checked( $checked, true, false ),
                        esc_html( $term->name )
                    );
                }
                echo '</ul>
                <input type="hidden" name="'.esc_attr( $attribute->attribute_name ).'" value="">
                </div>';
            } else { // default dropdown

                // Dropdown is single-select → normalize
                $selected_value = $selected[0] ?? '';
            
                echo '<div class="codenit-filter codenit-accordion-active codenit-filter-' . esc_attr( $attribute->attribute_name ) . '">';
                echo '<div class="codenit-accordion-header label"><strong>' . esc_html( $attribute->attribute_label ) . '</strong><div class="codenit-arrow"></div></div><div class="codenit-accordion-content">';
            
                echo '<select name="' . esc_attr( $attribute->attribute_name ) . '">';
            
                echo '<option value="">' . esc_html__( 'Any', 'codenit-wc-attribute-filter' ) . '</option>';
            
                foreach ( $terms as $term ) {
                    printf(
                        '<option value="%1$s" %2$s>%3$s</option>',
                        esc_attr( $term->slug ),
                        selected( $selected_value, $term->slug, false ),
                        esc_html( $term->name )
                    );
                }
            
                echo '</select>';
            
                // Preserve other active filters
                codenit_wc_apf_preserve_query_args( [ $attribute->attribute_name ] );
            
                echo '</div></div>';
            }

        }
        
        if ( $args['show_price'] ) {
            codenit_wc_apf_render_price_slider();
        }
            
        if ( $args['button'] ) {
            echo '<button type="submit">' . esc_html( $args['button_text'] ) . '</button>';
        }
    
        echo '</div></form>';
    }
}

/**
 * Build tax_query from request using attribute slug (without pa_ prefix)
 */
function codenit_wc_apf_build_tax_query_from_request() {

    $tax_query  = [];
    $attributes = wc_get_attribute_taxonomies();

    foreach ( $attributes as $attribute ) {

        $slug = $attribute->attribute_name;

        if ( empty( $_GET[ $slug ] ) ) {
            continue;
        }

        $terms = array_filter(
            array_map(
                'sanitize_title',
                explode( ',', sanitize_text_field( wp_unslash( $_GET[ $slug ] ) ) )
            )
        );

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

    return $tax_query;

} 
