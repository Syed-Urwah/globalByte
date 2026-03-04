<?php
/**
 * Plugin Name: My Custom WooCommerce Fields
 * Description: Adds custom fields to WooCommerce products and checkout.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: my-custom-wc-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The main plugin class
 */
class My_Custom_WooCommerce_Fields {

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define plugin constants.
     */
    private function define_constants() {
        define( 'MY_CUSTOM_WC_FIELDS_VERSION', '1.0.0' );
        define( 'MY_CUSTOM_WC_FIELDS_PLUGIN_FILE', __FILE__ );
        define( 'MY_CUSTOM_WC_FIELDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'MY_CUSTOM_WC_FIELDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include required core files.
     */
    private function includes() {
        // Include product fields class
        include_once MY_CUSTOM_WC_FIELDS_PLUGIN_DIR . 'includes/class-product-fields.php';
        // Include checkout fields class
        include_once MY_CUSTOM_WC_FIELDS_PLUGIN_DIR . 'includes/class-checkout-fields.php';
        // Include admin settings class
        include_once MY_CUSTOM_WC_FIELDS_PLUGIN_DIR . 'includes/class-admin-settings.php';
        // Include REST API class
        include_once MY_CUSTOM_WC_FIELDS_PLUGIN_DIR . 'includes/class-rest-api.php';



    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Instantiate classes
        new My_Custom_WC_Product_Fields();
        new My_Custom_WC_Checkout_Fields();
        new My_Custom_WC_Admin_Settings();
        new My_Custom_WC_REST_API();

        // Enqueue frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }

    /**
     * Enqueue frontend scripts and styles.
     */
    public function enqueue_frontend_assets() {
        // Enqueue CSS for checkout fields only on the checkout page
        if ( is_checkout() && ! is_wc_endpoint_url() ) {
            wp_enqueue_style(
                'my-custom-wc-fields-frontend',
                MY_CUSTOM_WC_FIELDS_PLUGIN_URL . 'assets/css/my-custom-wc-fields-frontend.css',
                array(),
                MY_CUSTOM_WC_FIELDS_VERSION,
                'all'
            );
        }
    }
}


// Initialize the plugin
function my_custom_woocommerce_fields_init() {
    new My_Custom_WooCommerce_Fields();
}
add_action( 'plugins_loaded', 'my_custom_woocommerce_fields_init' );
