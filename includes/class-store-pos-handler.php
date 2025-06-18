<?php

class Store_POS_Handler
{

    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function register_ajax_hooks()
    {
        $instance = self::get_instance();

        add_action('wp_ajax_store_pos_search_customers', [$instance, 'search_customers']);
        add_action('wp_ajax_store_pos_fetch_products', [$instance, 'fetch_products']);
        add_action('wp_ajax_store_pos_get_categories', [$instance, 'get_categories']);
        add_action('wp_ajax_store_pos_add_customer', [$instance, 'add_customer']);
        add_action('wp_ajax_store_pos_get_shipping_methods', [$instance, 'get_shipping_methods']);
        add_action('wp_ajax_store_pos_create_order', [$instance, 'store_pos_create_order']);
    }

    public function fetch_products()
    {
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];
        $products = get_posts($args);

        $data = [];
        foreach ($products as $product_post) {
            $product = wc_get_product($product_post->ID);
            if (!$product || !$product->is_in_stock()) continue;

            $data[] = [
                'id'          => $product->get_id(),
                'name'        => $product->get_name(),
                'price'       => $product->get_price(),
                'image'       => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
                'category_id' => $product->get_category_ids()[0] ?? 0,
            ];
        }

        wp_send_json_success($data);
    }

    public function get_categories()
    {
        $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        $data = array_map(function ($term) {
            return ['id' => $term->term_id, 'name' => $term->name];
        }, $terms);

        wp_send_json_success($data);
    }

    public function add_customer()
    {
        parse_str($_POST['data'], $form_data);

        $email = sanitize_email($form_data['email']);
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'Email already exists.']);
        }

        $username = sanitize_user(current(explode('@', $email)), true);
        $password = wp_generate_password();

        $userdata = [
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
            'first_name' => sanitize_text_field($form_data['first_name']),
            'last_name'  => sanitize_text_field($form_data['last_name']),
            'role'       => 'customer'
        ];

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Failed to add customer.']);
        }

        update_user_meta($user_id, 'billing_address_1', sanitize_text_field($form_data['address'] ?? ''));
        update_user_meta($user_id, 'billing_city', sanitize_text_field($form_data['city'] ?? ''));
        update_user_meta($user_id, 'billing_postcode', sanitize_text_field($form_data['postcode'] ?? ''));
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($form_data['phone'] ?? ''));

        wp_send_json_success(['message' => 'Customer has been successfully added']);
    }

    public function search_customers()
    {
        $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

        $args = [
            'role__in' => ['customer'],
            'number'   => 20,
            'search'   => '*' . esc_attr($search_term) . '*',
            'search_columns' => ['user_login', 'user_nicename', 'user_email'],
        ];

        $user_query = new WP_User_Query($args);
        $results = [];

        foreach ($user_query->get_results() as $user) {
            $user_id     = $user->ID;
            $first_name  = get_user_meta($user_id, 'billing_first_name', true);
            $last_name   = get_user_meta($user_id, 'billing_last_name', true);
            $phone       = get_user_meta($user_id, 'billing_phone', true);
            $address     = get_user_meta($user_id, 'billing_address_1', true);
            $email       = get_user_meta($user_id, 'billing_email', true) ?: '';

            // Fallback to display_name if billing name is missing
            $name = trim($first_name . ' ' . $last_name);
            if (empty($name)) {
                $name = $user->display_name ?: '(No Name)';
            }

            if (empty($phone)) {
                $phone = 'N/A';
            }

            if (empty($address)) {
                $address = '—';
            }

            $results[] = [
                'id'      => $user_id,
                'text'    => $name,
                'phone'   => $phone,
                'address' => $address,
                'email'   => $email,
            ];
        }

        wp_send_json(['results' => $results]);
    }

    public function get_shipping_methods()
    {
        $zones = WC_Shipping_Zones::get_zones();
        $available_methods = [];

        foreach ($zones as $zone) {
            foreach ($zone['shipping_methods'] as $method) {
                if ($method->enabled === 'yes') {
                    $available_methods[] = [
                        'title' => $method->get_title(),
                        'cost'  => isset($method->cost) ? $method->cost : 0
                    ];
                }
            }
        }

        // Also include methods from the "Rest of the World" zone
        $default_zone = new WC_Shipping_Zone(0);
        foreach ($default_zone->get_shipping_methods() as $method) {
            if ($method->enabled === 'yes') {
                $available_methods[] = [
                    'title' => $method->get_title(),
                    'cost'  => isset($method->cost) ? $method->cost : 0
                ];
            }
        }

        wp_send_json_success($available_methods);
    }

    function store_pos_create_order()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Unauthorized');
        }

        $data = $_POST;

        $order = wc_create_order();

        foreach ($data['items'] as $item) {
            $order->add_product(wc_get_product($item['id']), $item['quantity']);
        }

        $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;

        if ($discount > 0) {
            $fee = new WC_Order_Item_Fee();
            $fee->set_name('Discount');
            $fee->set_amount(-$discount);
            $fee->set_total(-$discount);
            $fee->set_tax_class('');
            $fee->set_taxes([]);
            $order->add_item($fee);
        }

        if (!empty($data['shipping'])) {
            $order->set_shipping_total(floatval($data['shipping']));
        }

        $order->set_address([
            'first_name' => sanitize_text_field($_POST['first_name']),
            'phone'      => sanitize_text_field($_POST['phone']),
            'address_1'  => sanitize_text_field($_POST['address']),
            'email' => sanitize_email($_POST['email']),
        ], 'billing');

        if (!empty($data['note'])) {
            $sanitized_note = sanitize_text_field($data['note']);
            $order->set_customer_note(sanitize_text_field($data['note']));
            $order->update_meta_data('_shipping_note', $sanitized_note);
        }

        $order->set_customer_id(intval($_POST['customer_id']));
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_method_title('Inside Dhaka'); // or dynamic name
        $shipping->set_method_id('flat_rate');
        $shipping->set_total(floatval($data['shipping']));
        $order->add_item($shipping);
        $order->set_billing_email(sanitize_email($_POST['email']));

        $order->calculate_totals();
        $order->save();

        wp_send_json_success(['order_id' => $order->get_id()]);
    }
}
