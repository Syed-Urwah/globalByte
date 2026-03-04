<?php
/**
 * Handles custom product meta fields for WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class My_Custom_WC_Product_Fields {

    public function __construct() {
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_product_fields' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_fields' ) );
        add_action( 'woocommerce_single_product_summary', array( $this, 'display_custom_product_fields_frontend' ), 25 );

    }

    /**
     * Add custom fields to the General product data tab.
     */
    public function add_custom_product_fields() {
        global $post;

        echo '<div class="options_group show_if_simple">'; // Only show for simple products

        // Event Date Field
        woocommerce_wp_text_input(
            array(
                'id'            => '_event_date',
                'label'         => __( 'Event Date', 'my-custom-wc-fields' ),
                'placeholder'   => __( 'YYYY-MM-DD', 'my-custom-wc-fields' ),
                'description'   => __( 'Enter the date of the event.', 'my-custom-wc-fields' ),
                'type'          => 'date',
                'custom_attributes' => array(
                    'pattern' => '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])'
                ),
            )
        );

        // Event Location Field
        woocommerce_wp_text_input(
            array(
                'id'            => '_event_location',
                'label'         => __( 'Event Location', 'my-custom-wc-fields' ),
                'placeholder'   => __( 'e.g., City, Country', 'my-custom-wc-fields' ),
                'description'   => __( 'Enter the location of the event.', 'my-custom-wc-fields' ),
                'type'          => 'text',
            )
        );

        // Seat Type Field
        woocommerce_wp_select(
            array(
                'id'            => '_seat_type',
                'label'         => __( 'Seat Type', 'my-custom-wc-fields' ),
                'placeholder'   => '',
                'description'   => __( 'Select the type of seat available.', 'my-custom-wc-fields' ),
                'options'       => array(
                    ''          => __( 'Select a type', 'my-custom-wc-fields' ),
                    'regular'   => __( 'Regular', 'my-custom-wc-fields' ),
                    'vip'       => __( 'VIP', 'my-custom-wc-fields' ),
                ),
            )
        );

        // Max Seats Field
        woocommerce_wp_text_input(
            array(
                'id'            => '_max_seats',
                'label'         => __( 'Max Seats', 'my-custom-wc-fields' ),
                'placeholder'   => __( 'e.g., 100', 'my-custom-wc-fields' ),
                'description'   => __( 'Enter the maximum number of seats available.', 'my-custom-wc-fields' ),
                'type'          => 'number',
                'custom_attributes' => array(
                    'min' => '0',
                    'step' => '1',
                ),
            )
        );

        echo '</div>'; // .options_group
    }

    /**
     * Save custom fields data.
     *
     * @param int $post_id The product ID.
     */
    public function save_custom_product_fields( $post_id ) {
        // Event Date
        $event_date = isset( $_POST['_event_date'] ) ? sanitize_text_field( $_POST['_event_date'] ) : '';
        if ( ! empty( $event_date ) && preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $event_date ) ) {
            update_post_meta( $post_id, '_event_date', $event_date );
        } else {
            delete_post_meta( $post_id, '_event_date' );
        }

        // Event Location
        $event_location = isset( $_POST['_event_location'] ) ? sanitize_text_field( $_POST['_event_location'] ) : '';
        if ( ! empty( $event_location ) ) {
            update_post_meta( $post_id, '_event_location', $event_location );
        } else {
            delete_post_meta( $post_id, '_event_location' );
        }

        // Seat Type
        $seat_type = isset( $_POST['_seat_type'] ) ? sanitize_text_field( $_POST['_seat_type'] ) : '';
        $allowed_seat_types = array( 'regular', 'vip' );
        if ( in_array( $seat_type, $allowed_seat_types ) ) {
            update_post_meta( $post_id, '_seat_type', $seat_type );
        } else {
            delete_post_meta( $post_id, '_seat_type' );
        }

        // Max Seats
        $max_seats = isset( $_POST['_max_seats'] ) ? absint( $_POST['_max_seats'] ) : '';
        if ( $max_seats >= 0 ) { // Max seats can be 0 or more
            update_post_meta( $post_id, '_max_seats', $max_seats );
        } else {
            delete_post_meta( $post_id, '_max_seats' );
        }
    }

    /**
     * Display custom fields on the single product page.
     */
    public function display_custom_product_fields_frontend() {
        global $product;

        if ( ! $product ) {
            return;
        }

        // Check admin settings to see if product fields should be enabled on the frontend
        $settings = get_option( 'my_custom_fields_settings', array() );
        if ( isset( $settings['enable_product_fields'] ) && $settings['enable_product_fields'] === 'no' ) {
            return;
        }

        $product_id     = $product->get_id();
        $event_date     = get_post_meta( $product_id, '_event_date', true );
        $event_location = get_post_meta( $product_id, '_event_location', true );
        $seat_type      = get_post_meta( $product_id, '_seat_type', true );
        $max_seats      = get_post_meta( $product_id, '_max_seats', true );

        $output_fields = array();

        if ( ! empty( $event_date ) ) {
            $output_fields[] = '<li><strong>' . esc_html__( 'Event Date:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $event_date ) . '</li>';
        }
        if ( ! empty( $event_location ) ) {
            $output_fields[] = '<li><strong>' . esc_html__( 'Event Location:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $event_location ) . '</li>';
        }
        if ( ! empty( $seat_type ) ) {
            // Capitalize seat type for display
            $display_seat_type = ucfirst( $seat_type );
            $output_fields[] = '<li><strong>' . esc_html__( 'Seat Type:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $display_seat_type ) . '</li>';
        }
        if ( ! empty( $max_seats ) || $max_seats === '0' ) { // Show if 0, but not if empty string. Note: get_post_meta returns string.
            $output_fields[] = '<li><strong>' . esc_html__( 'Max Seats:', 'my-custom-wc-fields' ) . '</strong> ' . absint( $max_seats ) . '</li>';
        }

        if ( ! empty( $output_fields ) ) {
            echo '<div class="custom-product-fields">';
            echo '<h3>' . esc_html__( 'Event Details', 'my-custom-wc-fields' ) . '</h3>';
            echo '<ul>' . implode( '', $output_fields ) . '</ul>';
            echo '</div>';
        }
    }
}

