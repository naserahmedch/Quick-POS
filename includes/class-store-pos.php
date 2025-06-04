<?php
add_action('admin_menu', function() {
    add_submenu_page(
        'woocommerce-order-receipt-print',
        'Store POS',
        'Store POS',
        'manage_woocommerce',
        'store-pos',
        [new Store_POS(), 'render']
    );
});

class Store_POS {
    public function render() {
        include plugin_dir_path(__FILE__) . '/../templates/store-pos-page.php';
    }
}
