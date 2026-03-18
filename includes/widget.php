<?php
defined( 'ABSPATH' ) || exit;

class CodeNit_WC_APF_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'codenit_wc_apf_widget',
            __( 'Woo Attribute Filter by Codenitive', 'codenit-wc-attribute-filter' )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
        }

        // Get selected attributes from widget settings
        $selected_attributes = ! empty( $instance['attributes'] )
            ? array_map( 'trim', explode( ',', $instance['attributes'] ) )
            : [];

        // Display type: dropdown or checkbox
        $display_type = ! empty( $instance['display'] ) ? $instance['display'] : 'dropdown';
        
        $show_price = isset( $instance['show_price'] ) ? (bool) $instance['show_price'] : false;

        codenit_wc_apf_render_filters( [
            'form_class' => 'codenit-wc-apf-widget-form',
            'attributes' => $selected_attributes,
            'display'    => $display_type,
            'show_price' => $show_price
        ] );

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = $instance['title'] ?? '';
        $attributes = $instance['attributes'] ?? '';
        $display = $instance['display'] ?? 'dropdown';
        $show_price = isset( $instance['show_price'] ) ? (bool) $instance['show_price'] : false;
        ?>
        <p>
            <label><?php esc_html_e( 'Title:', 'codenit-wc-attribute-filter' ); ?></label>
            <input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label><?php esc_html_e( 'Attributes (comma-separated slugs):', 'codenit-wc-attribute-filter' ); ?></label>
            <input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'attributes' ) ); ?>"
                value="<?php echo esc_attr( $attributes ); ?>" placeholder="color,size">
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
            <label><?php esc_html_e( 'Display Type:', 'codenit-wc-attribute-filter' ); ?></label>
            <select name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>">
                <option value="dropdown" <?php selected( $display, 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'codenit-wc-attribute-filter' ); ?></option>
                <option value="checkbox" <?php selected( $display, 'checkbox' ); ?>><?php esc_html_e( 'Checkboxes', 'codenit-wc-attribute-filter' ); ?></option>
                <option value="anchor_list" <?php selected( $display, 'anchor_list' ); ?>><?php esc_html_e( 'List', 'codenit-wc-attribute-filter' ); ?></option>
            </select>
        </p>
        <?php
    }
}
