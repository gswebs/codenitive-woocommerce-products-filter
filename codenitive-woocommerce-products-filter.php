<?php
/**
 * Plugin Name: WooCommerce Product Filter by Codenitive
 * Plugin URI: https://github.com/gswebs/codenitive-woocommerce-products-filter
 * Description: Filter WooCommerce products by attributes on shop and archive pages.
 * Version: 1.0.3
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

    add_action( 'wp_enqueue_scripts', function () {
        
        wp_enqueue_style(
            'codenit-wc-apf-style',
            CODENIT_WC_APF_URL . 'assets/css/style.css',
            [],
            '1.4.6'
        );
        
        wp_enqueue_script(
            'codenit-wc-apf-script',
            CODENIT_WC_APF_URL . 'assets/js/script.js',
            [],
            '1.1.9',
            true
        );
        
        wp_localize_script(
            'codenit-wc-apf-script',
            'codenit_ajax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'enable_ajax' => get_option('codenit_enable_ajax', 'yes')
            ]
        );
        
    });
    

    /**
     * Include core files
     */
    require_once CODENIT_WC_APF_PATH . 'includes/helpers.php';
    //require_once CODENIT_WC_APF_PATH . 'includes/class-ajax.php';
    require_once CODENIT_WC_APF_PATH . 'includes/shortcode.php';
    require_once CODENIT_WC_APF_PATH . 'includes/widget.php';

    /**
     * Register widget
     */
    add_action( 'widgets_init', function () {
        register_widget( 'CodeNit_WC_APF_Widget' );
    });
    
    add_action( 'pre_get_posts', function( $query ) {

        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( ! is_shop() && ! is_product_taxonomy() ) {
            return;
        }
    
        // --- ATTRIBUTE FILTERING ---
        $tax_query = (array) $query->get( 'tax_query' );
        $attribute_query = codenit_wc_apf_build_tax_query_from_request();
    
        if ( ! empty( $attribute_query ) ) {
            $tax_query = array_merge( $tax_query, $attribute_query );
            $query->set( 'tax_query', $tax_query );
        }
    
        // --- PRICE SLIDER FILTERING ---
        $meta_query = (array) $query->get( 'meta_query' );
        
        // Get prices from URL, fallback to null if not set
        $min_price = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : null;
        $max_price = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : null;
    
        if ( $min_price !== null || $max_price !== null ) {
            $price_filter = [
                'key'     => '_price',
                'type'    => 'DECIMAL', // Use DECIMAL for accurate currency filtering
                'compare' => 'BETWEEN',
            ];
    
            // If only one is set, we adjust the range
            $price_filter['value'] = [
                $min_price ?? 0, 
                $max_price ?? 999999999 // High fallback for max
            ];
    
            $meta_query[] = $price_filter;
            $query->set( 'meta_query', $meta_query );
        }
    
    });

});