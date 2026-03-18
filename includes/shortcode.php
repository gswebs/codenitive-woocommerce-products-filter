<?php
defined( 'ABSPATH' ) || exit;

add_shortcode( 'codenitive_wc_attribute_filter', function ( $atts ) {

    $atts = shortcode_atts( [
        'attributes'  => '', // comma-separated list of attribute slugs, e.g. color,size
        'form_class'  => 'codenit-wc-apf-shortcode-form',
        'button_text' => 'Filter',
        'display'     => 'dropdown', // dropdown or checkbox
        'show_price'  => true
    ], $atts, 'codenitive_wc_attribute_filter' );

    // Convert comma-separated string into array
    $allowed_attributes = ! empty( $atts['attributes'] ) 
        ? array_map( 'trim', explode( ',', $atts['attributes'] ) ) 
        : [];

    //$show_price = isset( $instance['show_price'] ) ? (bool) $instance['show_price'] : false;

    ob_start();

    codenit_wc_apf_render_filters( [
        'form_class'  => $atts['form_class'],
        'button_text' => $atts['button_text'],
        'attributes'  => $allowed_attributes,
        'display'     => $atts['display'],
        'show_price'     => $atts['show_price'],
    ] );

    return ob_get_clean();
});
