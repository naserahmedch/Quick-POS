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
                <?php
                $shop_visible = get_option('worp_shop_name_visible', true);
                $shop_name    = esc_html(get_option('worp_shop_name', get_bloginfo('name')));
                $shop_size    = esc_attr(get_option('worp_shop_name_size', '16'));
                $shop_weight  = esc_attr(get_option('worp_shop_name_weight', 'bold'));

                if ($shop_visible && $shop_name !== '') :
                ?>
                    <h2 style="margin: 0; font-size: <?php echo $shop_size; ?>px; font-weight: <?php echo $shop_weight; ?>;">
                        <?php echo $shop_name; ?>
                    </h2>
                <?php endif; ?>
            </div>

            <div class="receipt-details">
                <div class="order-info">
                    <span>Order: #<?php echo $order->get_id(); ?></span>
                    <span class="cod"><?php echo $order->get_payment_method_title(); ?></span>
                </div>
                <?php
                $customer_visible = get_option('worp_customer_name_visible', true);
                $customer_size    = esc_attr(get_option('worp_customer_name_size', '14'));
                $customer_weight  = esc_attr(get_option('worp_customer_name_weight', 'normal'));

                $address_visible  = get_option('worp_address_visible', true);
                $address_size     = esc_attr(get_option('worp_address_size', '14'));
                $address_weight   = esc_attr(get_option('worp_address_weight', 'normal'));

                $phone_visible    = get_option('worp_phone_visible', true);
                $phone_size       = esc_attr(get_option('worp_phone_size', '14'));
                $phone_weight     = esc_attr(get_option('worp_phone_weight', 'normal'));

                $billing_name     = esc_html($order->get_billing_first_name());
                $billing_phone    = esc_html($order->get_billing_phone());
                $billing_address  = esc_html($order->get_billing_address_1());
                ?>

                <div class="receipt-customer-details" style="margin-top: 10px;">
                    <?php if ($customer_visible && $billing_name): ?>
                        <div style="font-size: <?php echo $customer_size; ?>px; font-weight: <?php echo $customer_weight; ?>;">
                            Customer: <?php echo $billing_name; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($address_visible && $billing_address): ?>
                        <div style="font-size: <?php echo $address_size; ?>px; font-weight: <?php echo $address_weight; ?>;">
                            Address: <?php echo $billing_address; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($phone_visible && $billing_phone): ?>
                        <div style="font-size: <?php echo $phone_size; ?>px; font-weight: <?php echo $phone_weight; ?>;">
                            Phone: <?php echo $billing_phone; ?>
                        </div>
                    <?php endif; ?>
                </div>
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

                    <?php
                    $discount_total = 0;
                    foreach ($order->get_items('fee') as $item) {
                        if (strtolower($item->get_name()) === 'discount') {
                            $discount_total += abs($item->get_total());
                        }
                    }

                    if ($discount_total > 0): ?>
                        <tr class="discountRow">
                            <td><strong>Discount:</strong></td>
                            <td style="text-align: right;">- <?php echo number_format($discount_total, 2); ?> Tk</td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($order->get_shipping_total() > 0) : ?>
                        <tr class="shipping-row">
                            <td><strong>Shipping Fee:</strong></td>
                            <td style="text-align: right;"><?php echo number_format($order->get_shipping_total(), 2) . ' Tk'; ?></td>
                        </tr>
                    <?php endif; ?>

                    <tr class="total-row">
                        <td><strong>Total Price:</strong></td>
                        <td style="text-align: right;"><?php echo number_format($order->get_total(), 2) . ' Tk'; ?></td>
                    </tr>

                    <!-- This is a valid HTML comment -->
                    <?php
                    $note_visible = get_option('worp_note_visible', true);
                    $note_content = trim($order->get_customer_note());

                    if ($note_visible && !empty($note_content)) :
                        $note_size = esc_attr(get_option('worp_note_size', '14'));
                        $note_weight = esc_attr(get_option('worp_note_weight', 'normal'));
                    ?>
                        <tr class="noteRow">
                            <td colspan="2" style="padding-top: 10px;">
                                <em>
                                    <strong style="font-size: <?php echo $note_size; ?>px; font-weight: <?php echo $note_weight; ?>;">
                                        Note:
                                    </strong>
                                    <span style="font-size: <?php echo $note_size; ?>px; font-weight: <?php echo $note_weight; ?>;">
                                        <?php echo esc_html($note_content); ?>
                                    </span>
                                </em>
                            </td>
                        </tr>
                    <?php endif; ?>
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
