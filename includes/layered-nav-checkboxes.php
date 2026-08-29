<?php
/**
 * WooCommerce layered navigation checkbox display type.
 *
 * Add this file to your child theme, for example:
 * /wp-content/themes/your-child-theme/inc/layered-nav-checkboxes.php
 *
 * Then load it from the child theme's functions.php:
 * require_once get_stylesheet_directory() . '/inc/layered-nav-checkboxes.php';
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace WooCommerce's standard layered-nav widget with an extended version.
 *
 * Priority 20 ensures WooCommerce has loaded and registered its widget first.
 */
function codenitive_register_layered_nav_checkbox_widget() {
	if ( ! class_exists( 'WC_Widget_Layered_Nav' ) ) {
		return;
	}

	if ( ! class_exists( 'Codenitive_WC_Widget_Layered_Nav' ) ) {
		/**
		 * Adds a Checkboxes display type without copying the WooCommerce widget.
		 */
		class Codenitive_WC_Widget_Layered_Nav extends WC_Widget_Layered_Nav {

			/**
			 * Whether the current widget instance uses checkbox display.
			 *
			 * @var bool
			 */
			protected $codenitive_checkbox_display = false;

			/**
			 * Add Checkboxes to the widget's Display type setting.
			 */
			public function init_settings() {
				parent::init_settings();

				if ( isset( $this->settings['display_type']['options'] ) ) {
					$this->settings['display_type']['options']['checkboxes'] = __(
						'Checkboxes',
						'woocommerce'
					);
				}
			}

			/**
			 * Record the selected display type before WooCommerce renders the widget.
			 *
			 * @param array $args     Widget arguments.
			 * @param array $instance Widget settings.
			 */
			public function widget( $args, $instance ) {
            	error_log(
            		'Codenitive layered nav called. Display type: ' .
            		( $instance['display_type'] ?? 'not set' )
            	);
            
            	$this->codenitive_checkbox_display =
            		isset( $instance['display_type'] ) &&
            		'checkboxes' === $instance['display_type'];
            
            	parent::widget( $args, $instance );
            
            	$this->codenitive_checkbox_display = false;
            }

			/**
			 * Render WooCommerce's normal layered-nav list with checkbox controls.
			 *
			 * @param array  $terms      Attribute terms.
			 * @param string $taxonomy   Attribute taxonomy.
			 * @param string $query_type Query type: and or or.
			 * @return bool
			 */
			protected function layered_nav_list( $terms, $taxonomy, $query_type ) {
				if ( ! $this->codenitive_checkbox_display ) {
					return parent::layered_nav_list( $terms, $taxonomy, $query_type );
				}

				add_filter(
					'woocommerce_layered_nav_term_html',
					array( $this, 'codenitive_add_term_checkbox' ),
					10,
					4
				);

				$found = parent::layered_nav_list( $terms, $taxonomy, $query_type );

				remove_filter(
					'woocommerce_layered_nav_term_html',
					array( $this, 'codenitive_add_term_checkbox' ),
					10
				);

				return $found;
			}

			/**
			 * Insert a checkbox into one layered-nav term.
			 *
			 * WooCommerce's existing link still performs the filtering, so its
			 * product counts and AND/OR query behavior remain unchanged.
			 *
			 * @param string  $term_html Existing term HTML.
			 * @param WP_Term $term      Attribute term.
			 * @param string  $link      Filter URL, or false when unavailable.
			 * @param int     $count     Matching product count.
			 * @return string
			 */
			public function codenitive_add_term_checkbox( $term_html, $term, $link, $count ) {
				unset( $count );

				$chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
				$current_values     = isset( $chosen_attributes[ $term->taxonomy ]['terms'] )
					? $chosen_attributes[ $term->taxonomy ]['terms']
					: array();
				$is_checked         = in_array( $term->slug, $current_values, true );

				$checkbox = sprintf(
					'<input type="checkbox" class="woocommerce-layered-nav-checkbox" tabindex="-1" aria-hidden="true"%1$s%2$s /> ',
					checked( $is_checked, true, false ),
					$link ? ' style="pointer-events:none"' : ' disabled="disabled"'
				);

				return preg_replace(
					'/(<a\b[^>]*>|<span>)/i',
					'$1' . $checkbox,
					$term_html,
					1
				);
			}
		}
	}

	unregister_widget( 'WC_Widget_Layered_Nav' );
	register_widget( 'Codenitive_WC_Widget_Layered_Nav' );
}
add_action( 'widgets_init', 'codenitive_register_layered_nav_checkbox_widget', 20 );
