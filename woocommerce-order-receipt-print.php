<?php

/**
 * Plugin Name: WooCommerce Order Receipt Print
 * Description: Adds a print button on WooCommerce Orders page to print PDF receipts and manage POS.
 * Version: 1.0.0
 * Author: Naser
 * Text Domain: woocommerce-order-receipt-print
 */

if (! defined('ABSPATH')) exit; // Exit if accessed directly

// Plugin Constants
define('WORP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WORP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload Dompdf
if (file_exists(WORP_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once WORP_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    error_log('WooCommerce Order Receipt Print: Dompdf autoloader not found.');
}

if (class_exists('Dompdf\Dompdf')) {
    error_log('Dompdf is loaded successfully!');
} else {
    error_log('WooCommerce Order Receipt Print: Dompdf class not loaded!');
}

// Initialize Core Plugin
add_action('plugins_loaded', 'worp_init_plugin');

function worp_init_plugin()
{
    if (class_exists('WooCommerce')) {
        require_once WORP_PLUGIN_DIR . 'includes/class-store-pos-handler.php';
        Store_POS_Handler::register_ajax_hooks(); // ✅ Register all AJAX endpoints

        require_once WORP_PLUGIN_DIR . 'includes/class-order-receipt-print.php';
        require_once WORP_PLUGIN_DIR . 'includes/class-order-receipt-pdf.php';
        require_once WORP_PLUGIN_DIR . 'includes/class-order-receipt-settings.php';
        require_once WORP_PLUGIN_DIR . 'includes/class-store-pos.php';

        new Order_Receipt_Print();
        new Order_Receipt_Settings();
        new Store_POS();
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>WooCommerce Order Receipt Print:</strong> WooCommerce is not active.</p></div>';
        });
    }
}

function worp_redirect_to_settings()
{
    wp_safe_redirect(admin_url('admin.php?page=worp_settings'));
    exit;
}

add_action('admin_menu', function () {
    // Top-level menu: Order Receipt (redirects to Settings)
    add_menu_page(
        'Order Receipt Print',                               // Page title
        'Order Receipt',                                     // Menu title
        'manage_woocommerce',                                // Capability
        'worp-dashboard',                                    // Menu slug
        'worp_redirect_to_settings',                         // Callback: redirect to Settings
        'dashicons-media-document',                          // Icon
        56                                                   // Position
    );

    // Submenu: Settings
    add_submenu_page(
        'worp-dashboard',                                    // Parent slug
        'Receipt Print Settings',                            // Page title
        'Settings',                                          // Submenu title
        'manage_woocommerce',                                // Capability
        'worp_settings',                                     // Submenu slug
        [new Order_Receipt_Settings(), 'render_settings_page']
    );

    // Submenu: Store POS
    add_submenu_page(
        'worp-dashboard',
        'Store POS',
        'Store POS',
        'manage_woocommerce',
        'store-pos',
        [new Store_POS(), 'render']
    );

    global $submenu;
    if (isset($submenu['worp-dashboard'])) {
        unset($submenu['worp-dashboard'][0]);
    }
}, 999);

// Enqueue the CSS File of the Settings Page
add_action('admin_enqueue_scripts', function ($hook) {
    // Only load on the Worp Settings page
    if (isset($_GET['page']) && $_GET['page'] === 'worp_settings') {
        wp_enqueue_style('worp-settings-css', WORP_PLUGIN_URL . 'assets/css/worp-settings.css', [], '1.0');
    }
});

// Enqueue Scripts/Styles Only for Store POS Page
add_action('admin_enqueue_scripts', 'worp_enqueue_pos_scripts');
function worp_enqueue_pos_scripts($hook)
{
    // Load only on your custom POS page
    if (isset($_GET['page']) && $_GET['page'] === 'store-pos') {
        wp_enqueue_style('worp-store-pos-style', WORP_PLUGIN_URL . 'assets/css/store-pos.css', [], '1.0');

        // Select2
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

        // POS JS
        wp_enqueue_script('store-pos-js', plugins_url('assets/js/store-pos.js', __FILE__), ['jquery'], '1.0', true);

        wp_localize_script('store-pos-js', 'store_pos', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    // ✅ Load Print Script only on WooCommerce Orders list
    if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order') {
        wp_enqueue_script('worp-admin-scripts', WORP_PLUGIN_URL . 'assets/js/admin-scripts.js', ['jquery'], '1.0', true);
        wp_localize_script('worp-admin-scripts', 'worp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}


// Hide Admin Notices on POS Page
add_action('admin_head', function () {
    $screen = get_current_screen();
    if ($screen && isset($_GET['page']) && $_GET['page'] === 'store-pos') {
        echo '<style>.notice, .updated, .error, .is-dismissible { display: none !important; }</style>';
    }
});
