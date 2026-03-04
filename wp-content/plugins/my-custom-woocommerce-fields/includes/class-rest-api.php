<?php
/**
 * Handles custom REST API endpoint for WooCommerce fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class My_Custom_WC_REST_API {

    private $namespace = 'wc-fields/v1';
    private $base = 'product';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register the REST API routes.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE, // GET requests
            'callback'            => array( $this, 'get_product_data' ),
            'permission_callback' => array( $this, 'get_product_data_permissions_check' ),
            'args'                => array(
                'id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ) );
    }

    /**
     * Callback for the product data endpoint.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_product_data( $request ) {
        $product_id = (int) $request['id'];
        $product    = wc_get_product( $product_id );

        if ( ! $product || ! $product->exists() ) {
            return new WP_Error( 'woocommerce_rest_product_invalid_id', __( 'Invalid product ID.', 'my-custom-wc-fields' ), array( 'status' => 404 ) );
        }

        // Retrieve custom product fields
        $event_date     = get_post_meta( $product_id, '_event_date', true );
        $event_location = get_post_meta( $product_id, '_event_location', true );
        $seat_type      = get_post_meta( $product_id, '_seat_type', true );
        $max_seats      = get_post_meta( $product_id, '_max_seats', true );

        // Retrieve settings from Task 4
        $settings = get_option( 'my_custom_fields_settings', array(
            'enable_product_fields'  => 'no',
            'enable_checkout_fields' => 'no',
        ) );

        $data = array(
            'product_id'   => $product->get_id(),
            'product_name' => $product->get_name(),
            'price'        => $product->get_price(),
            'fields'       => array(
                'event_date'     => $event_date,
                'event_location' => $event_location,
                'seat_type'      => $seat_type,
                'max_seats'      => $max_seats,
            ),
            'settings'     => array(
                'product_fields_enabled'  => ( 'yes' === $settings['enable_product_fields'] ),
                'checkout_fields_enabled' => ( 'yes' === $settings['enable_checkout_fields'] ),
            ),
        );

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Check if a given request has access to get product data.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_product_data_permissions_check( $request ) {
        // Allow anyone to read product data (public API)
        return true; // You can change this to current_user_can('manage_woocommerce') or similar for restricted access.
    }
}
