<?php
defined( 'ABSPATH' ) || exit;

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
        echo '<form method="GET" class="' . esc_attr( $args['form_class'] ) . '">';
    
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
                echo '<div class="codenit-filter codenit-filter-'.$attribute->attribute_name.'"><p><strong>' . esc_html( $attribute->attribute_label ) . '</strong></p><ul>';
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
            } else if ($args['display'] == 'anchor_list') {
                echo '<div class="codenit-filter codenit-filter-'.$attribute->attribute_name.'"><p><strong>' . esc_html( $attribute->attribute_label ) . '</strong></p><ul>';
                foreach ( $terms as $term ) {
                    $checked = in_array( $term->slug, $selected, true );
                
                    printf(
                        '<li><label>
                            <a href=""
                               data-attribute="%1$s"
                               data-value="%2$s"
                               %3$s>
                            %4$s</a>
                        </label></li>',
                        esc_attr( $attribute->attribute_name ),
                        esc_attr( $term->slug ),
                        checked( $checked, true, false ),
                        esc_html( $term->name )
                    );
                }
                echo '</ul>
                </div>';   
            }
            else { // default dropdown
                echo '<select name="' . esc_attr( $attribute->attribute_name ) . '">';
                echo '<option value="">' . esc_html( $attribute->attribute_label ) . '</option>';
    
                foreach ( $terms as $term ) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $term->slug ),
                        selected( in_array( $term->slug, $selected, true ), true, false ),
                        esc_html( $term->name )
                    );
                }
    
                echo '</select>';
            }
        }
    
        if ( $args['button'] ) {
            echo '<button type="submit">' . esc_html( $args['button_text'] ) . '</button>';
        }
    
        echo '</form>';
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
