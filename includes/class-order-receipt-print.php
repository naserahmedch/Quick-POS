<?php
if (! defined('ABSPATH')) exit;

use Dompdf\Dompdf;

class Order_Receipt_Print
{

    public function __construct()
    {
        // ✅ Hooks for your custom orders page
        add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'add_custom_order_column']);
        add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'populate_custom_order_column'], 10, 2);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        add_filter('bulk_actions-woocommerce_page_wc-orders', [$this, 'add_bulk_print_action']);
        add_action('wp_ajax_worp_generate_bulk_pdf', [$this, 'ajax_generate_bulk_pdf']);
        add_action('wp_ajax_worp_generate_pdf', [$this, 'ajax_generate_pdf']);
    }

    public function add_custom_order_column($columns)
    {
        $columns['print_receipt'] = __('Print Receipt', 'woocommerce-order-receipt-print');
        return $columns;
    }

    public function populate_custom_order_column($column, $order_or_id)
    {
        if ('print_receipt' === $column) {
            $order_id = is_a($order_or_id, 'WC_Order') ? $order_or_id->get_id() : absint($order_or_id);
            echo '<button type="button" class="button worp-print-receipt" data-order-id="' . esc_attr($order_id) . '">Print</button>';
        }
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook === 'woocommerce_page_wc-orders') {
            wp_enqueue_script(
                'worp-admin-scripts',
                WORP_PLUGIN_URL . 'assets/js/admin-scripts.js',
                ['jquery'],
                '1.0',
                true
            );

            // ✅ This is the missing piece — localize the script
            wp_localize_script('worp-admin-scripts', 'worp_ajax', [
                'ajax_url' => admin_url('admin-ajax.php')
            ]);
        }
    }

    public function add_bulk_print_action($bulk_actions)
    {
        $bulk_actions['worp_bulk_print'] = 'Print Receipts';
        return $bulk_actions;
    }

    public function ajax_generate_pdf()
    {
        // Clean any existing output
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (! current_user_can('edit_shop_orders') || empty($_POST['order_id'])) {
            wp_send_json_error('Invalid request');
        }

        $order_id = intval($_POST['order_id']);

        require_once WORP_PLUGIN_DIR . 'includes/class-order-receipt-pdf.php';
        $generator = new Order_Receipt_PDF();
        $pdf_url = $generator->generate_receipt($order_id);

        // Ensure no trailing output
        while (ob_get_level()) {
            ob_end_clean();
        }

        if ($pdf_url) {
            wp_send_json_success(['url' => $pdf_url]);
        } else {
            wp_send_json_error('Failed to generate receipt.');
        }
    }

    public function ajax_generate_bulk_pdf()
    {
        ob_clean();

        if (! current_user_can('edit_shop_orders') || empty($_POST['order_ids'])) {
            wp_send_json_error('Invalid request');
        }

        $order_ids = array_map('absint', $_POST['order_ids']);

        require_once WORP_PLUGIN_DIR . 'includes/class-order-receipt-pdf.php';
        $generator = new Order_Receipt_PDF();

        $html = '';
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);

            if (! $order) {
                error_log("❌ Skipping invalid order ID: $order_id");
                continue;
            }

            $receipt_html = $generator->get_receipt_html($order_id);
            if (empty($receipt_html)) {
                error_log("⚠️ Empty HTML for order ID: $order_id");
                continue;
            }

            $html .= '<div class="receipt-wrapper" style="page-break-after: always;">';
            $html .= $receipt_html;
            $html .= '</div>';
        }

        if (empty(trim($html))) {
            wp_send_json_error('No valid orders found.');
        }

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
        $dompdf->getOptions()->set('isHtml5ParserEnabled', true);
        $dompdf->getOptions()->set('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226.77, 1000]); // 80mm width
        $dompdf->render();

        $upload_dir = wp_upload_dir();
        $file = 'bulk-receipts-' . time() . '.pdf';
        $path = $upload_dir['basedir'] . '/' . $file;
        $url  = $upload_dir['baseurl'] . '/' . $file;

        file_put_contents($path, $dompdf->output());

        wp_send_json_success(['url' => $url]);
    }
}
