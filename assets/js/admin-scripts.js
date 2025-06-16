window.addEventListener('load', function () {
  jQuery(document).ready(function ($) {

    // Inject bulk print option
    const optionValue = 'worp_bulk_print';
    const optionLabel = 'Print Receipts';

    $('select[name="action"], select[name="action2"]').each(function () {
      if (!$(this).find(`option[value="${optionValue}"]`).length) {
        $(this).append(`<option value="${optionValue}">${optionLabel}</option>`);
      }
    });

    // Intercept the Apply button click
    $('.bulkactions').closest('form').on('submit', function (e) {
      const selectedAction = $('select[name="action"]').val() || $('select[name="action2"]').val();

      if (selectedAction === optionValue) {
        e.preventDefault();

        const selectedOrders = $('tbody th.check-column input[type="checkbox"]:checked').map(function () {
          return $(this).val();
        }).get();

        if (selectedOrders.length === 0) {
          alert('Please select at least one order.');
          return;
        }

        $.post(worp_ajax.ajax_url, {
          action: 'worp_generate_bulk_pdf',
          order_ids: selectedOrders
        }, function (response) {

          if (response.success && response.data.url) {
            const cleanUrl = response.data.url.replaceAll('\\\\', '/').replaceAll('\\\\', '/');
            window.open(cleanUrl, '_blank');
          } else {
            alert('❌ Bulk PDF error: ' + (response.data || 'Unknown error'));
          }
        }).fail(function (xhr, status, error) {
          alert('AJAX Error: ' + error);
        });
      }
    });

    // Single print button
    $(document).on('click', '.worp-print-receipt', function (e) {
      e.preventDefault();
      e.stopPropagation();

      var orderId = $(this).data('order-id');
      var button = $(this);
      button.text('Generating...');

      $.post(worp_ajax.ajax_url, {
        action: 'worp_generate_pdf',
        order_id: orderId
      }, function (response) {
        if (response.success && response.data.url) {
          window.open(response.data.url, '_blank');
        } else {
          alert('❌ Error generating PDF: ' + (response.data || 'Unknown error'));
        }
        button.text('Print');
      }).fail(function (xhr, status, error) {
        alert('AJAX Error: ' + error);
        button.text('Print');
      });
    });
  });
});
