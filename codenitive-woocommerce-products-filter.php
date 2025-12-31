<?php
/**
 * Plugin Name: WooCommerce Product Filter by Codenitive
 * Plugin URI: https://codenitive.com
 * Description: Filter WooCommerce products by attributes on shop and archive pages.
 * Version: 1.0.0
 * Author: Codenitive
 * Author URI: https://codenitive.com
 * Text Domain: codenit-attribute-filter
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) || exit;

define( 'CODENIT_WC_APF_PATH', plugin_dir_path( __FILE__ ) );
define( 'CODENIT_WC_APF_URL', plugin_dir_url( __FILE__ ) );

/**
 * WooCommerce dependency check
 */
add_action( 'plugins_loaded', function () {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    /**
     * Include core files
     */
    require_once CODENIT_WC_APF_PATH . 'includes/helpers.php';
    require_once CODENIT_WC_APF_PATH . 'includes/shortcode.php';
    require_once CODENIT_WC_APF_PATH . 'includes/widget.php';

    /**
     * Register widget
     */
    add_action( 'widgets_init', function () {
        register_widget( 'CodeNit_WC_APF_Widget' );
    });

    /**
     * Apply filters to product query
     */
    add_action( 'pre_get_posts', function( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }
    
        if ( ! is_shop() && ! is_product_taxonomy() ) {
            return;
        }
    
        $tax_query = codenit_wc_apf_build_tax_query_from_request();
    
        if ( ! empty( $tax_query ) ) {
            $query->set( 'tax_query', $tax_query );
        }
    });

});