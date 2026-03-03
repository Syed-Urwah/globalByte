Here's a detailed plan for coding the WooCommerce Technical Test manually, broken down by task, assuming a plugin-based approach for all custom code.

**Overall Plugin Structure:**

All code should reside within a custom WordPress plugin. This ensures your modifications are portable and don't get overwritten during theme or WooCommerce updates.

`my-custom-woocommerce-fields/`
`├── my-custom-woocommerce-fields.php`
`├── includes/`
`│   ├── class-product-fields.php`
`│   ├── class-checkout-fields.php`
`│   ├── class-admin-settings.php`
`│   └── class-rest-api.php`
`└── assets/`
`    └── css/`
`    └── js/`

---

**Task 1: Custom Product Meta Fields (3-4 Fields)**

**Goal:** Add "Event Date", "Event Location", "Seat Type", and "Max Seats" to the WooCommerce product edit page under the "General" tab.

**Plan:**

1.  **Plugin Setup (`my-custom-woocommerce-fields.php`):**
    *   Create the main plugin file.
    *   Define plugin metadata (name, description, etc.).
    *   Include necessary files from the `includes` directory.
    *   Initialize main plugin classes.

2.  **Add Fields to Product Edit Page (`includes/class-product-fields.php`):**
    *   **Hook:** Use `woocommerce_product_data_panels` to add content to product data tabs. Although the request specifies "General" tab, directly adding to this tab's content typically involves filtering its output or injecting content into existing hooks within that tab. A common approach for custom data is to add it to the "General" tab via `woocommerce_wp_text_input`, `woocommerce_wp_select`, etc., hooked into `woocommerce_product_options_general_product_data`.
    *   **Field Creation:**
        *   **Event Date:** `woocommerce_wp_text_input` with `type` set to `date`.
        *   **Event Location:** `woocommerce_wp_text_input`.
        *   **Seat Type:** `woocommerce_wp_select` with options: `['regular' => 'Regular', 'vip' => 'VIP']`.
        *   **Max Seats:** `woocommerce_wp_text_input` with `type` set to `number`.
    *   **Sanitization and Validation:** Implement client-side (HTML5 attributes like `required`, `min`, `max`) and server-side validation. For server-side, this will happen during saving.

3.  **Save Custom Fields (`includes/class-product-fields.php`):**
    *   **Hook:** Use `woocommerce_process_product_meta` to save the data when the product is updated.
    *   **Saving Logic:**
        *   Retrieve submitted values using `$_POST`.
        *   Sanitize each field appropriately (e.g., `sanitize_text_field`, `sanitize_key`, `absint`).
        *   Use `update_post_meta( $post_id, '_event_date', $sanitized_value );` for each field (prefix with `_` to hide from custom fields UI).
    *   **Support Simple Products:** The above methods automatically work for simple products.

---

**Task 2: Display Fields on Single Product Page**

**Goal:** Display custom product fields on the single product page below the product price.

**Plan:**

1.  **Display Fields on Frontend (`includes/class-product-fields.php`):**
    *   **Hook:** Use `woocommerce_single_product_summary` with a high priority (e.g., `15` or `25`) to display content after the price (price is typically priority `10`). A more precise hook might be `woocommerce_get_price_html` filter or an action right after `woocommerce_template_single_price` if available.
    *   **Retrieval:** Use `get_post_meta( get_the_ID(), '_event_date', true );` to retrieve saved values for the current product.
    *   **Escaping:** Echo all output using appropriate escaping functions (e.g., `esc_html`, `esc_attr`).
    *   **Conditional Display:** Wrap display logic in a check for the setting enabled in Task 4.

---

**Task 3: Custom Checkout Fields (3 Fields)**

**Goal:** Add "Attendee Name", "Attendee Email", and "Special Notes" to the checkout page after billing details.

**Plan:**

1.  **Add Fields to Checkout (`includes/class-checkout-fields.php`):**
    *   **Hook:** Use `woocommerce_after_checkout_billing_form` to inject the fields.
    *   **Field Creation:**
        *   **Attendee Name:** `woocommerce_form_field` with `type => 'text'`, `required => true`.
        *   **Attendee Email:** `woocommerce_form_field` with `type => 'email'`, `required => true`.
        *   **Special Notes:** `woocommerce_form_field` with `type => 'textarea'`.
    *   **Validation:** Use `woocommerce_checkout_process` to add server-side validation for required fields. Display `wc_add_notice()` messages if validation fails.

2.  **Save Checkout Fields to Order Meta (`includes/class-checkout-fields.php`):**
    *   **Hook:** Use `woocommerce_checkout_update_order_meta` to save the data after a successful order.
    *   **Saving Logic:**
        *   Retrieve submitted values from `$_POST`.
        *   Sanitize values.
        *   Use `update_post_meta( $order_id, '_attendee_name', $sanitized_value );`.

3.  **Display in Admin Order Details (`includes/class-checkout-fields.php`):**
    *   **Hook:** Use `woocommerce_admin_order_data_after_billing_address` or `woocommerce_admin_order_data_after_order_details` to display the custom fields in the admin order edit screen.
    *   **Retrieval & Escaping:** Get data using `get_post_meta( $order_id, '_attendee_name', true );` and `esc_html`.

4.  **Display on Thank-you Page (`includes/class-checkout-fields.php`):**
    *   **Hook:** Use `woocommerce_thankyou` to display the fields on the order received page.
    *   **Retrieval & Escaping:** Similar to admin order details.
    *   **Conditional Display:** Wrap display logic in a check for the setting enabled in Task 4.

---

**Task 4: Admin Menu and Settings Page**

**Goal:** Create a new admin menu under WooCommerce with settings to enable/disable product and checkout custom fields on the frontend.

**Plan:**

1.  **Add Admin Menu (`includes/class-admin-settings.php`):**
    *   **Hook:** Use `admin_menu`.
    *   **Function:** `add_submenu_page()` to add "Custom Product Field Settings" under the WooCommerce menu (`woocommerce`).
    *   **Capability Check:** Set capability to `manage_woocommerce`.

2.  **Create Settings Page (`includes/class-admin-settings.php`):**
    *   **Use WordPress Settings API:**
        *   **`register_setting()`:** Register a new setting group (e.g., `my_custom_fields_settings`).
        *   **`add_settings_section()`:** Create a section for your settings.
        *   **`add_settings_field()`:** Add individual fields:
            *   "Enable Product Fields on Frontend" (checkbox)
            *   "Enable Checkout Fields on Frontend" (checkbox)
        *   **Callback Functions:** Implement functions to render the input fields and sanitize the settings.

3.  **Control Frontend Behavior (`includes/class-admin-settings.php` and other classes):**
    *   **Retrieval:** In `class-product-fields.php` and `class-checkout-fields.php`, use `get_option( 'my_custom_fields_settings' )` (or your chosen setting name) to retrieve the saved settings.
    *   **Conditional Logic:** Use `if ( $settings['enable_product_fields'] ) { ... }` around the frontend display code from Task 2 and Task 3.

---

**Task 5: REST API Endpoint**

**Goal:** Create a custom REST API endpoint: `GET /wp-json/wc-fields/v1/product/{id}`.

**Plan:**

1.  **Register Endpoint (`includes/class-rest-api.php`):**
    *   **Hook:** Use `rest_api_init`.
    *   **Function:** `register_rest_route()`:
        *   `namespace`: `wc-fields/v1`
        *   `route`: `/product/(?P<id>\d+)`
        *   `methods`: `WP_REST_Server::READABLE` (for GET)
        *   `callback`: Point to a function within `class-rest-api.php` to handle the request.
        *   `permission_callback`: Point to a function to check user permissions (e.g., `current_user_can('read')` or `current_user_can('manage_woocommerce')`).

2.  **Implement Callback Function (`includes/class-rest-api.php`):**
    *   **Retrieve Product ID:** Get the `id` from `$request['id']`.
    *   **Validate Product ID:** Check if `wc_get_product( $id )` returns a valid product object. If not, return a `WP_Error` with `404` status.
    *   **Retrieve Product Data:**
        *   Get product object: `$product = wc_get_product( $id );`
        *   Get basic info: `$product->get_id()`, `$product->get_name()`, `$product->get_price()`.
    *   **Retrieve Custom Product Fields:**
        *   `get_post_meta( $id, '_event_date', true );` for each custom field.
    *   **Retrieve Settings:**
        *   `get_option( 'my_custom_fields_settings' );` to get the enable/disable settings.
    *   **Format Response:** Construct an array matching the JSON example provided in the PDF, then return `new WP_REST_Response( $data, 200 );`.
    *   **Error Handling:** Use `new WP_Error()` and `WP_REST_Response()` for various error scenarios (e.g., invalid product ID, permission denied) with appropriate HTTP status codes (e.g., 404, 401, 403).

---

This plan provides a structured approach to manually implementing all requirements of the WooCommerce Technical Test. Remember to always sanitize input, escape output, and use appropriate WordPress/WooCommerce functions and hooks for security and maintainability.