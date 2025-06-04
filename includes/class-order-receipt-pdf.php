<?php

use Dompdf\Dompdf;

class Order_Receipt_PDF
{
    public function generate_receipt($order_id)
    {
        $html = $this->get_wrapped_receipt_html($order_id);

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
        $dompdf->getOptions()->set('isHtml5ParserEnabled', true);
        $dompdf->getOptions()->set('isRemoteEnabled', true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226.77, 1440]); // 80mm width, tall height
        $dompdf->render();

        $upload_dir = wp_upload_dir();
        $file = 'receipt-' . $order_id . '.pdf';
        $pdf_path = $upload_dir['basedir'] . '/' . $file;
        $pdf_url  = $upload_dir['baseurl'] . '/' . $file;

        file_put_contents($pdf_path, $dompdf->output());

        return $pdf_url;
    }

    public function get_wrapped_receipt_html($order_id)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>';
        $html .= $this->get_receipt_html($order_id);
        $html .= '</body></html>';

        return $html;
    }

    public function get_receipt_html($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) return '';

        $settings     = get_option('worp_settings', []);
        $font_size    = isset($settings['font_size']) ? $settings['font_size'] : 12;
        $phone_weight = isset($settings['phone_weight']) ? $settings['phone_weight'] : 'bold';

        ob_start();
?>
        <style>
            @page {
                margin: 0;
            }

            html,
            body {
                width: 226.77pt;
                margin: 0;
                padding: 0;
                font-family: sans-serif;
                font-size: <?php echo $font_size; ?>px;
                color: #000;
            }

            .receipt-header {
                text-align: center;
                margin-bottom: 6px;
            }

            .receipt-header h2 {
                margin: 0;
                font-size: 16px;
            }

            .receipt-details {
                margin-bottom: 6px;
                font-size: <?php echo $font_size - 1; ?>px;
                line-height: 1.4;
            }

            .receipt-details p {
                margin: 1px 0;
            }

            .order-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
            }

            .order-info .cod {
                margin-left: 100px;
            }

            .bold-phone {
                font-weight: <?php echo $phone_weight; ?>;
                font-size: <?php echo $font_size + 2; ?>px;
                margin-top: 6px !important;
                margin-bottom: 10px !important;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 4px;
            }

            th,
            td {
                padding: 3px 4px;
                text-align: left;
                font-size: <?php echo $font_size; ?>px;
            }

            th {
                border-bottom: 1px solid #000;
                padding-bottom: 8px !important;
                font-weight: bold;
            }

            .productTable tbody tr td {
                border-bottom: 1px solid #ddd;
            }

            .productTable tbody tr:last-child td {
                border-bottom: 1px solid #000;
                padding-bottom: 7px;
            }

            .total-table {
                width: 100%;
                margin-top: 5px;
                font-size: <?php echo $font_size; ?>px;
                border-collapse: collapse;
            }

            .total-table td {
                padding: 4px 0;
            }

            .total-row td {
                border-top: 1px solid #000;
                padding-top: 6px;
            }

            .shipping-row td {
                padding-bottom: 7px !important;
            }
        </style>

        <div style="max-width:226.77pt; padding:8mm 5mm; box-sizing:border-box;">
            <div class="receipt-header">
                <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
            </div>

            <div class="receipt-details">
                <div class="order-info">
                    <span>Order: #<?php echo $order->get_id(); ?></span>
                    <span class="cod"><?php echo $order->get_payment_method_title(); ?></span>
                </div>
                <p>Customer: <?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></p>
                <p>Address: <?php echo $order->get_billing_address_1() . ', ' . $order->get_billing_city(); ?></p>
                <p class="bold-phone">Phone: <?php echo $order->get_billing_phone(); ?></p>
            </div>

            <table class="productTable">
                <thead>
                    <tr>
                        <th>Product name</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->get_items() as $item) : ?>
                        <tr>
                            <td><?php echo $item->get_name(); ?></td>
                            <td><?php echo $item->get_quantity(); ?></td>
                            <td style="text-align: right;"><?php echo number_format($item->get_total(), 2) . ' Tk'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table class="total-table">
                <tbody>
                    <tr class="subTotalRow">
                        <td><strong>Subtotal:</strong></td>
                        <td style="text-align: right;"><?php echo number_format($order->get_subtotal(), 2) . ' Tk'; ?></td>
                    </tr>

                    <?php if ($order->get_shipping_total() > 0) : ?>
                        <tr class="shipping-row">
                            <td><strong>Shipping Fee:</strong></td>
                            <td style="text-align: right;"><?php echo number_format($order->get_shipping_total(), 2) . ' Tk'; ?></td>
                        </tr>
                    <?php endif; ?>

                    <tr class="total-row">
                        <td><strong>Total Price (Inc. Shipping Fee):</strong></td>
                        <td style="text-align: right;"><?php echo number_format($order->get_total(), 2) . ' Tk'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

<?php
        return ob_get_clean();
    }

    public function generate_bulk_receipts($order_ids)
    {
        $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            @page {
                margin: 0;
            }

            html, body {
                width: 226.77pt;
                margin: 0;
                padding: 0;
                font-family: sans-serif;
                font-size: 12px;
                color: #000;
            }

            .receipt-wrapper {
                max-width: 226.77pt;
                padding: 8mm 5mm;
                box-sizing: border-box;
            }

            .receipt-header {
                text-align: center;
                margin-bottom: 6px;
            }

            .receipt-header h2 {
                margin: 0;
                font-size: 16px;
            }

            .receipt-details {
                margin-bottom: 6px;
                font-size: 11px;
                line-height: 1.4;
            }

            .receipt-details p {
                margin: 1px 0;
            }

            .order-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
            }

            .bold-phone {
                font-weight: bold;
                font-size: 14px;
                margin-top: 4px;
                margin-bottom: 2px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 4px;
            }

            th, td {
                padding: 3px 4px;
                text-align: left;
                font-size: 12px;
            }

            th {
                border-bottom: 1px solid #000;
                padding-bottom: 8px;
                font-weight: bold;
            }

            tbody tr td {
                border-bottom: 1px solid #ddd;
            }

            tbody tr:last-child td {
                border-bottom: 1px solid #000;
            }

            .total-table {
                width: 100%;
                margin-top: 10px;
                font-size: 12px;
                border-collapse: collapse;
            }

            .total-table td {
                padding: 4px 0;
            }

            .total-row td {
                border-top: 1px solid #000;
                padding-top: 6px;
            }

            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
    ';

        $total = count($order_ids);
        $index = 0;
        $generator = new self(); // assuming inside same class

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;

            $index++;

            $html .= $this->get_receipt_html($order_id);

            if ($index < $total) {
                $html .= '<div class="page-break"></div>';
            }
        }

        $html .= '</body></html>';

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans']);
        $dompdf->getOptions()->set('isHtml5ParserEnabled', true);
        $dompdf->getOptions()->set('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226.77, 1440]);
        $dompdf->render();

        $upload_dir = wp_upload_dir();
        $file = 'bulk-receipts-' . time() . '.pdf';
        $pdf_path = $upload_dir['basedir'] . '/' . $file;
        $pdf_url  = $upload_dir['baseurl'] . '/' . $file;

        file_put_contents($pdf_path, $dompdf->output());

        return $pdf_url;
    }
}
