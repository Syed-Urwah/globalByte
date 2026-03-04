<?php
/**
 * Handles custom checkout fields for WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class My_Custom_WC_Checkout_Fields {

    public function __construct() {
        // Add custom fields to checkout using a filter
        add_filter( 'woocommerce_checkout_fields', array( $this, 'add_custom_checkout_fields_via_filter' ) );

        // Validate custom fields
        add_action( 'woocommerce_checkout_process', array( $this, 'validate_custom_checkout_fields' ) );

        // Save custom fields to order meta
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_checkout_fields' ) );

        // Display custom fields in admin order details
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_custom_checkout_fields_admin_order_meta' ), 10, 1 );

        // Display custom fields on the thank you page
        add_action( 'woocommerce_thankyou', array( $this, 'display_custom_checkout_fields_thankyou_page' ), 20 );
    }

    /**
     * Add custom fields to the checkout page using the woocommerce_checkout_fields filter.
     *
     * @param array $fields The checkout fields.
     * @return array Modified checkout fields.
     */
    public function add_custom_checkout_fields_via_filter( $fields ) {
        // --- Start: Placeholder for admin settings check (to be implemented in Task 4) ---
        // $settings = get_option( 'my_custom_fields_settings', array() );
        // if ( isset( $settings['enable_checkout_fields'] ) && $settings['enable_checkout_fields'] === 'no' ) {
        //     return $fields;
        // }
        // --- End: Placeholder ---

        $fields['billing']['attendee_name'] = array(
            'type'          => 'text',
            'required'      => true,
            'class'         => array( 'form-row-wide' ),
            'label'         => __( 'Attendee Name', 'my-custom-wc-fields' ),
            'placeholder'   => __( 'Full Name', 'my-custom-wc-fields' ),
            'priority'      => 35, // After billing address, before phone
        );

        $fields['billing']['attendee_email'] = array(
            'type'          => 'email',
            'required'      => true,
            'class'         => array( 'form-row-wide' ),
            'label'         => __( 'Attendee Email', 'my-custom-wc-fields' ),
            'placeholder'   => __( 'Email Address', 'my-custom-wc-fields' ),
            'priority'      => 36,
        );

        $fields['billing']['special_notes'] = array(
            'type'          => 'textarea',
            'required'      => false,
            'class'         => array( 'form-row-wide' ),
            'label'         => __( 'Special Notes', 'my-custom-wc-fields' ),
            'placeholder'   => __( 'Any special requests or notes?', 'my-custom-wc-fields' ),
            'priority'      => 37,
        );

        return $fields;
    }



    /**
     * Validate custom checkout fields.
     */
    public function validate_custom_checkout_fields() {
        // --- Start: Placeholder for admin settings check (to be implemented in Task 4) ---
        // $settings = get_option( 'my_custom_fields_settings', array() );
        // if ( isset( $settings['enable_checkout_fields'] ) && $settings['enable_checkout_fields'] === 'no' ) {
        //     return;
        // }
        // --- End: Placeholder ---

        if ( empty( $_POST['attendee_name'] ) ) {
            wc_add_notice( __( 'Attendee Name is a required field.', 'my-custom-wc-fields' ), 'error' );
        }

        if ( empty( $_POST['attendee_email'] ) ) {
            wc_add_notice( __( 'Attendee Email is a required field.', 'my-custom-wc-fields' ), 'error' );
        } elseif ( ! is_email( $_POST['attendee_email'] ) ) {
            wc_add_notice( __( 'Please enter a valid Attendee Email address.', 'my-custom-wc-fields' ), 'error' );
        }
    }

    /**
     * Save custom checkout fields to order meta.
     *
     * @param int $order_id The ID of the order.
     */
    public function save_custom_checkout_fields( $order_id ) {
        // Temporary debug log: check what's in $_POST
        error_log( 'WooCommerce Custom Fields: POST Data on Order Save: ' . print_r( $_POST, true ) );

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            error_log( 'WooCommerce Custom Fields: save_custom_checkout_fields - Order not found for ID: ' . $order_id );
            return;
        }

        if ( isset( $_POST['attendee_name'] ) ) {
            $order->update_meta_data( '_attendee_name', sanitize_text_field( $_POST['attendee_name'] ) );
        }
        if ( isset( $_POST['attendee_email'] ) ) {
            $order->update_meta_data( '_attendee_email', sanitize_email( $_POST['attendee_email'] ) );
        }
        if ( isset( $_POST['special_notes'] ) ) {
            $order->update_meta_data( '_special_notes', sanitize_textarea_field( $_POST['special_notes'] ) );
        }

        $order->save(); // Save the order to persist meta data
    }

    /**
     * Display custom checkout fields in the admin order details.
     *
     * @param WC_Order $order The order object.
     */
    public function display_custom_checkout_fields_admin_order_meta( $order ) {
        $attendee_name  = $order->get_meta( '_attendee_name', true );
        $attendee_email = $order->get_meta( '_attendee_email', true );
        $special_notes  = $order->get_meta( '_special_notes', true );

        if ( ! empty( $attendee_name ) || ! empty( $attendee_email ) || ! empty( $special_notes ) ) {
            echo '<div class="address">';
            echo '<h4>' . __( 'Attendee Information', 'my-custom-wc-fields' ) . '</h4>';
            echo '<p>';
            if ( ! empty( $attendee_name ) ) {
                echo '<strong>' . __( 'Attendee Name:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $attendee_name ) . '<br>';
            }
            if ( ! empty( $attendee_email ) ) {
                echo '<strong>' . __( 'Attendee Email:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $attendee_email ) . '<br>';
            }
            if ( ! empty( $special_notes ) ) {
                echo '<strong>' . __( 'Special Notes:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $special_notes ) . '<br>';
            }
            echo '</p>';
            echo '</div>';
        }
    }

    /**
     * Display custom checkout fields on the thank you page.
     *
     * @param int $order_id The ID of the order.
     */
    public function display_custom_checkout_fields_thankyou_page( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            error_log( 'WooCommerce Custom Fields: Thank You Page - Order not found for ID: ' . $order_id );
            return;
        }

        // --- Start: Placeholder for admin settings check (to be implemented in Task 4) ---
        // $settings = get_option( 'my_custom_fields_settings', array() );
        // if ( isset( $settings['enable_checkout_fields'] ) && $settings['enable_checkout_fields'] === 'no' ) {
        //     return;
        // }
        // --- End: Placeholder ---

        $attendee_name  = $order->get_meta( '_attendee_name', true );
        $attendee_email = $order->get_meta( '_attendee_email', true );
        $special_notes  = $order->get_meta( '_special_notes', true );

        // Temporary debug log: check retrieved meta
        error_log( 'WooCommerce Custom Fields: Thank You Page - Retrieved Meta: Name=' . $attendee_name . ', Email=' . $attendee_email . ', Notes=' . $special_notes );

        if ( ! empty( $attendee_name ) || ! empty( $attendee_email ) || ! empty( $special_notes ) ) {
            echo '<div class="woocommerce-order-details">';
            echo '<h2>' . __( 'Attendee Information', 'my-custom-wc-fields' ) . '</h2>';
            echo '<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">';
            if ( ! empty( $attendee_name ) ) {
                echo '<li class="attendee-name"><strong>' . __( 'Attendee Name:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $attendee_name ) . '</li>';
            }
            if ( ! empty( $attendee_email ) ) {
                echo '<li class="attendee-email"><strong>' . __( 'Attendee Email:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $attendee_email ) . '</li>';
            }
            if ( ! empty( $special_notes ) ) {
                echo '<li class="special-notes"><strong>' . __( 'Special Notes:', 'my-custom-wc-fields' ) . '</strong> ' . esc_html( $special_notes ) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
}
