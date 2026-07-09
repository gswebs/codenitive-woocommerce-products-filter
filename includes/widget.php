<?php
/**
 * Product filter widget.
 *
 * @package CodenitiveWooCommerceProductsFilter
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce product filter widget.
 */
class CodeNit_WC_APF_Widget extends WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'codenit_wc_apf_widget',
            __( 'Woo Product Filter by Codenitive', 'codenit-wc-attribute-filter' )
        );
    }

    /**
     * Output widget.
     *
     * @param array $args Widget args.
     * @param array $instance Widget instance.
     * @return void
     */
    public function widget( $args, $instance ) {
        echo wp_kses_post( $args['before_widget'] );

        if ( ! empty( $instance['title'] ) ) {
            echo wp_kses_post( $args['before_title'] ) . esc_html( $instance['title'] ) . wp_kses_post( $args['after_title'] );
        }

        $selected_attributes = ! empty( $instance['attributes'] )
            ? array_map( 'trim', explode( ',', $instance['attributes'] ) )
            : [];

        $display_type    = ! empty( $instance['display'] ) ? $instance['display'] : 'dropdown';
        $show_attributes = ! isset( $instance['show_attributes'] ) || ! empty( $instance['show_attributes'] );
        $show_price      = ! empty( $instance['show_price'] );
        $show_categories = ! empty( $instance['show_categories'] );
        $show_tags       = ! empty( $instance['show_tags'] );

        codenit_wc_apf_render_filters(
            [
                'form_class'      => 'codenit-wc-apf-widget-form',
                'attributes'      => $selected_attributes,
                'show_attributes' => $show_attributes,
                'display'         => $display_type,
                'show_price'      => $show_price,
                'show_categories' => $show_categories,
                'show_tags'       => $show_tags,
            ]
        );

        echo wp_kses_post( $args['after_widget'] );
    }

    /**
     * Widget admin form.
     *
     * @param array $instance Widget instance.
     * @return void
     */
    public function form( $instance ) {
        $title           = $instance['title'] ?? '';
        $attributes      = $instance['attributes'] ?? '';
        $display         = $instance['display'] ?? 'dropdown';
        $show_attributes = ! isset( $instance['show_attributes'] ) || ! empty( $instance['show_attributes'] );
        $show_price      = ! empty( $instance['show_price'] );
        $show_categories = ! empty( $instance['show_categories'] );
        $show_tags       = ! empty( $instance['show_tags'] );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'codenit-wc-attribute-filter' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'attributes' ) ); ?>"><?php esc_html_e( 'Attributes (comma-separated slugs):', 'codenit-wc-attribute-filter' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'attributes' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attributes' ) ); ?>"
                value="<?php echo esc_attr( $attributes ); ?>" placeholder="color,size">
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_attributes' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'show_attributes' ) ); ?>"
                value="1" <?php checked( $show_attributes, true ); ?> />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_attributes' ) ); ?>">
                <?php esc_html_e( 'Show Product Attributes', 'codenit-wc-attribute-filter' ); ?>
            </label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_categories' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'show_categories' ) ); ?>"
                value="1" <?php checked( $show_categories, true ); ?> />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_categories' ) ); ?>">
                <?php esc_html_e( 'Show Product Categories', 'codenit-wc-attribute-filter' ); ?>
            </label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_tags' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'show_tags' ) ); ?>"
                value="1" <?php checked( $show_tags, true ); ?> />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_tags' ) ); ?>">
                <?php esc_html_e( 'Show Product Tags', 'codenit-wc-attribute-filter' ); ?>
            </label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'show_price' ) ); ?>"
                value="1" <?php checked( $show_price, true ); ?> />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>">
                <?php esc_html_e( 'Show Price Range Slider', 'codenit-wc-attribute-filter' ); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>"><?php esc_html_e( 'Display Type:', 'codenit-wc-attribute-filter' ); ?></label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>">
                <option value="dropdown" <?php selected( $display, 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'codenit-wc-attribute-filter' ); ?></option>
                <option value="checkbox" <?php selected( $display, 'checkbox' ); ?>><?php esc_html_e( 'Checkboxes', 'codenit-wc-attribute-filter' ); ?></option>
                <option value="anchor_list" <?php selected( $display, 'anchor_list' ); ?>><?php esc_html_e( 'List', 'codenit-wc-attribute-filter' ); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Save widget settings.
     *
     * @param array $new_instance New instance.
     * @param array $old_instance Old instance.
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = [];

        $instance['title']           = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['attributes']      = isset( $new_instance['attributes'] ) ? sanitize_text_field( $new_instance['attributes'] ) : '';
        $instance['display']         = isset( $new_instance['display'] ) && in_array( $new_instance['display'], [ 'dropdown', 'checkbox', 'anchor_list' ], true ) ? $new_instance['display'] : 'dropdown';
        $instance['show_attributes'] = ! empty( $new_instance['show_attributes'] ) ? 1 : 0;
        $instance['show_price']      = ! empty( $new_instance['show_price'] ) ? 1 : 0;
        $instance['show_categories'] = ! empty( $new_instance['show_categories'] ) ? 1 : 0;
        $instance['show_tags']       = ! empty( $new_instance['show_tags'] ) ? 1 : 0;

        return $instance;
    }
}
