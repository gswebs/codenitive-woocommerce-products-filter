<?php
defined( 'ABSPATH' ) || exit;

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
        'form_class'  => 'codenit-wc-apf-form',
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

    echo '<form method="GET" class="' . esc_attr( $args['form_class'] ) . '">';

    foreach ( $attributes as $attribute ) {

        $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
        $terms    = codenit_wc_apf_get_attribute_terms( $taxonomy );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            continue;
        }

        // Get selected values from URL
        $selected = isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : [];

        // Ensure $selected is an array (for checkboxes)
        if ( ! is_array( $selected ) ) {
            $selected = [ $selected ];
        }

        if ( $args['display'] === 'checkbox' ) {
            echo '<p><strong>' . esc_html( $attribute->attribute_label ) . '</strong></p>';
            foreach ( $terms as $term ) {
                $checked = in_array( $term->slug, $selected );
                printf(
                    '<label><input type="checkbox" name="%1$s[]" value="%2$s" %3$s> %4$s</label><br>',
                    esc_attr( $attribute->attribute_name ),
                    esc_attr( $term->slug ),
                    checked( $checked, true, false ),
                    esc_html( $term->name )
                );
            }
        } else { // default dropdown
            echo '<select name="' . esc_attr( $attribute->attribute_name ) . '">';
            echo '<option value="">' . esc_html( $attribute->attribute_label ) . '</option>';

            foreach ( $terms as $term ) {
                $is_selected = in_array( $term->slug, $selected );
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $term->slug ),
                    selected( $is_selected, true, false ),
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

/**
 * Build tax_query from request using attribute slug (without pa_ prefix)
 */
function codenit_wc_apf_build_tax_query_from_request() {

    $tax_query = [];

    // Get all WooCommerce attributes
    $attributes = wc_get_attribute_taxonomies();

    // Map URL keys to real taxonomies
    $slug_to_tax = [];
    foreach ( $attributes as $attribute ) {
        $slug_to_tax[ $attribute->attribute_name ] = wc_attribute_taxonomy_name( $attribute->attribute_name );
    }

    foreach ( $_GET as $key => $value ) {

        // Check if URL key matches a real attribute slug
        if ( isset( $slug_to_tax[ $key ] ) && ! empty( $value ) ) {

            // Ensure value is array
            if ( ! is_array( $value ) ) {
                $value = [ $value ];
            }

            $value = array_map( 'sanitize_text_field', wp_unslash( $value ) );

            $tax_query[] = [
                'taxonomy' => $slug_to_tax[ $key ],
                'field'    => 'slug',
                'terms'    => $value,
                'operator' => 'IN',
            ];
        }
    }

    return $tax_query;
}
