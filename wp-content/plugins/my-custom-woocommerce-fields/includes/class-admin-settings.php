<?php
/**
 * Handles admin settings for custom WooCommerce fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class My_Custom_WC_Admin_Settings {

    private $settings_group = 'my_custom_fields_settings';
    private $settings_page_slug = 'my-custom-wc-fields-settings';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add admin submenu page under WooCommerce.
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',                              // Parent slug
            __( 'Custom Field Settings', 'my-custom-wc-fields' ), // Page title
            __( 'Custom Fields', 'my-custom-wc-fields' ),         // Menu title
            'manage_woocommerce',                       // Capability
            $this->settings_page_slug,                  // Menu slug
            array( $this, 'settings_page_content' )     // Callback function
        );
    }

    /**
     * Render the settings page content.
     */
    public function settings_page_content() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Custom Product & Checkout Field Settings', 'my-custom-wc-fields' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->settings_group );
                do_settings_sections( $this->settings_page_slug );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings, sections, and fields.
     */
    public function register_settings() {
        register_setting(
            $this->settings_group,      // Option group
            $this->settings_group,      // Option name
            array( $this, 'sanitize_settings' ) // Sanitize callback
        );

        add_settings_section(
            'my_custom_fields_general_section',         // ID
            __( 'General Settings', 'my-custom-wc-fields' ), // Title
            array( $this, 'general_settings_section_callback' ), // Callback
            $this->settings_page_slug                   // Page
        );

        add_settings_field(
            'enable_product_fields',                    // ID
            __( 'Enable Product Fields on Frontend', 'my-custom-wc-fields' ), // Title
            array( $this, 'render_product_fields_enable_field' ), // Callback
            $this->settings_page_slug,                  // Page
            'my_custom_fields_general_section'          // Section
        );

        add_settings_field(
            'enable_checkout_fields',                   // ID
            __( 'Enable Checkout Fields on Frontend', 'my-custom-wc-fields' ), // Title
            array( $this, 'render_checkout_fields_enable_field' ), // Callback
            $this->settings_page_slug,                  // Page
            'my_custom_fields_general_section'          // Section
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $input The settings input.
     * @return array The sanitized input.
     */
    public function sanitize_settings( $input ) {
        $new_input = array();
        $current_settings = get_option( $this->settings_group, array() );

        // Sanitize 'enable_product_fields'
        $new_input['enable_product_fields'] = isset( $input['enable_product_fields'] ) ? 'yes' : 'no';

        // Sanitize 'enable_checkout_fields'
        $new_input['enable_checkout_fields'] = isset( $input['enable_checkout_fields'] ) ? 'yes' : 'no';

        return $new_input;
    }

    /**
     * General settings section callback.
     */
    public function general_settings_section_callback() {
        echo '<p>' . esc_html__( 'Configure the visibility of custom product and checkout fields on the frontend.', 'my-custom-wc-fields' ) . '</p>';
    }

    /**
     * Render checkbox for 'enable_product_fields'.
     */
    public function render_product_fields_enable_field() {
        $options = get_option( $this->settings_group, array() );
        $checked = isset( $options['enable_product_fields'] ) && $options['enable_product_fields'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_product_fields" name="' . esc_attr( $this->settings_group . '[enable_product_fields]' ) . '" value="yes" ' . $checked . '/>';
        echo '<label for="enable_product_fields">' . esc_html__( 'Check to display custom product fields on the single product page.', 'my-custom-wc-fields' ) . '</label>';
    }

    /**
     * Render checkbox for 'enable_checkout_fields'.
     */
    public function render_checkout_fields_enable_field() {
        $options = get_option( $this->settings_group, array() );
        $checked = isset( $options['enable_checkout_fields'] ) && $options['enable_checkout_fields'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_checkout_fields" name="' . esc_attr( $this->settings_group . '[enable_checkout_fields]' ) . '" value="yes" ' . $checked . '/>';
        echo '<label for="enable_checkout_fields">' . esc_html__( 'Check to display custom checkout fields on the checkout page.', 'my-custom-wc-fields' ) . '</label>';
    }
}
