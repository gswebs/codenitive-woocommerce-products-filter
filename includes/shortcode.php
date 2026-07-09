<?php
/**
 * Shortcode registration.
 *
 * @package CodenitiveWooCommerceProductsFilter
 */

defined( 'ABSPATH' ) || exit;

add_shortcode(
    'codenitive_wc_attribute_filter',
    function ( $atts ) {
        $atts = shortcode_atts(
            [
                'attributes'      => '', // comma-separated list of attribute slugs, e.g. color,size.
                'show_attributes' => 'yes',
                'form_class'      => 'codenit-wc-apf-shortcode-form',
                'button_text'     => 'Filter',
                'display'         => 'dropdown', // dropdown, checkbox, or anchor_list.
                'show_price'      => 'yes',
                'show_categories' => 'no',
                'show_tags'       => 'no',
                'categories'      => '', // Alias for show_categories="yes".
                'tags'            => '', // Alias for show_tags="yes".
            ],
            $atts,
            'codenitive_wc_attribute_filter'
        );

        $allowed_attributes = ! empty( $atts['attributes'] )
            ? array_map( 'trim', explode( ',', $atts['attributes'] ) )
            : [];

        $show_categories = codenit_wc_apf_bool( $atts['show_categories'] ) || codenit_wc_apf_bool( $atts['categories'] );
        $show_tags       = codenit_wc_apf_bool( $atts['show_tags'] ) || codenit_wc_apf_bool( $atts['tags'] );

        ob_start();

        codenit_wc_apf_render_filters(
            [
                'form_class'      => $atts['form_class'],
                'button_text'     => $atts['button_text'],
                'attributes'      => $allowed_attributes,
                'show_attributes' => $atts['show_attributes'],
                'display'         => $atts['display'],
                'show_price'      => $atts['show_price'],
                'show_categories' => $show_categories,
                'show_tags'       => $show_tags,
            ]
        );

        return ob_get_clean();
    }
);
