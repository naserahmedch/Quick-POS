jQuery(document).ready(function ($) {
    let cart = [];
    let discountItem = null;
    let shippingItem = null;
    let noteItem = null;

    function renderCart() {
        const $cartList = $('#cart-items');
        $cartList.empty();

        if (cart.length === 0) {
            $cartList.append('<tr><td colspan="3" class="cart-empty">Empty Cart</td></tr>');
            discountItem = null;
            shippingItem = null;
            noteItem = null;
            updateCartTotal();
            return;
        }

        cart.forEach((item, index) => {
            $cartList.append(`
                <tr data-index="${index}">
                    <td>${item.name}</td>
                    <td style="text-align:right">
                        <div class="qty-controls">
                            <button class="qty-minus">−</button>
                            <input type="number" class="cart-qty" min="1" value="${item.quantity}" />
                            <button class="qty-plus">+</button>
                        </div>
                    </td>
                    <td style="text-align:right">${(item.quantity * item.price).toFixed(2)} Tk 
                        <button class="remove-item" style="margin-left:10px;background:#dc3545;">X</button>
                    </td>
                </tr>
            `);
        });

        updateCartTotal();
    }

    function updateCartTotal() {
        let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        let discount = discountItem?.value || 0;
        let shipping = shippingItem?.value || 0;

        if (cart.length === 0) {
            discount = 0;
            shipping = 0;
        }

        let total = subtotal - discount + shipping;
        if (total < 0) total = 0;

        $('.cart-total').text(`৳ ${total.toFixed(2)}`);
        $('.pay-btn span').text(`৳ ${total.toFixed(2)}`);
        renderCartFooterItems();
    }

    function renderCartFooterItems() {
        const $footer = $('#cart-footer-items');
        $footer.empty();

        // 🧮 Subtotal
        let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        $footer.append(`
        <div class="footer-subtotal">
            <span>Subtotal</span>
            <span>৳ ${subtotal.toFixed(2)}</span>
        </div>
    `);

        // 🧾 Discount
        if (discountItem) {
            $footer.append(`
            <div class="footer-item" data-type="discount">
                <span class="footer-label">Discount</span>
                <span class="footer-value">৳ ${discountItem.value}</span>
                <button class="edit-line">✎</button>
                <button class="remove-line">×</button>
            </div>
        `);
        }

        // Shipping Fee
        if (shippingItem) {
            $footer.append(`
        <div class="footer-item" data-type="shipping">
            <span class="footer-label">Shipping Fee</span>
            <span class="footer-value">৳ ${shippingItem.value}</span>
            <button class="edit-line">✎</button>
            <button class="remove-line">×</button>
        </div>
    `);
        }

        // Note
        if (noteItem) {
            $footer.append(`
        <div class="footer-item" data-type="note">
            <span class="footer-label">Note:</span>
            <em class="footer-value">${noteItem.text}</em>
            <button class="edit-line">✎</button>
            <button class="remove-line">×</button>
        </div>
    `);
        }
    }

    $(document).on('dblclick', '.footer-item', function () {
        const type = $(this).data('type');
        const $el = $(this);
        let html = '';

        if (type === 'discount' || type === 'shipping') {
            const val = type === 'discount' ? discountItem?.value : shippingItem?.value;
            html = `
                <div class="footer-item edit-mode" data-type="${type}">
                    <input type="number" class="edit-input" value="${val}" />
                    <button class="apply-edit">Apply</button>
                    <button class="cancel-edit">Cancel</button>
                </div>
            `;
        } else if (type === 'note') {
            const val = noteItem?.text;
            html = `
                <div class="footer-item edit-mode" data-type="note">
                    <input type="text" class="edit-input" value="${val}" />
                    <button class="apply-edit">Apply</button>
                    <button class="cancel-edit">Cancel</button>
                </div>
            `;
        }

        $el.replaceWith(html);
    });

    $(document).on('click', '.apply-edit', function () {
        const $row = $(this).closest('.footer-item');
        const type = $row.data('type');
        const val = $row.find('.edit-input').val();

        if (type === 'discount') {
            discountItem = { value: parseFloat(val) || 0 };
        } else if (type === 'shipping') {
            shippingItem = { value: parseFloat(val) || 0 };
        } else if (type === 'note') {
            noteItem = { text: val };
        }

        renderCartFooterItems();
        updateCartTotal();
    });

    $(document).on('click', '.cancel-edit', renderCartFooterItems);

    $(document).on('click', '.remove-line', function () {
        const type = $(this).closest('.footer-item').data('type');
        if (type === 'discount') discountItem = null;
        if (type === 'shipping') shippingItem = null;
        if (type === 'note') noteItem = null;
        renderCartFooterItems();
        updateCartTotal();
    });

    function renderProducts(products) {
        const $grid = $('#product-list');
        $grid.empty();

        if (products.length === 0) {
            $grid.append('<p>No products found.</p>');
            return;
        }

        products.forEach(p => {
            $grid.append(`
                <div class="product-item" data-category="${p.category_id}">
                    <img src="${p.image}" alt="${p.name}" />
                    <h4>${p.name}</h4>
                    <p>${p.price} Tk</p>
                    <button class="add-to-cart-btn" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}">Add</button>
                </div>
            `);
        });
    }

    function fetchProducts() {
        $.post(store_pos.ajax_url, { action: 'store_pos_fetch_products' }, function (response) {
            if (response.success) renderProducts(response.data);
            else alert('Failed to load products');
        });
    }

    function loadCategories() {
        $.post(store_pos.ajax_url, { action: 'store_pos_get_categories' }, function (response) {
            if (response.success) {
                const $dropdown = $('#category-filter');
                response.data.forEach(cat => {
                    $dropdown.append(`<option value="${cat.id}">${cat.name}</option>`);
                });
            }
        });
    }

    function filterProducts() {
        const keyword = $('#product-search').val().toLowerCase();
        const selectedCat = $('#category-filter').val();

        $('.product-item').each(function () {
            const name = $(this).find('h4').text().toLowerCase();
            const cat = $(this).data('category');
            const matchName = name.includes(keyword);
            const matchCat = selectedCat === '' || cat == selectedCat;
            $(this).toggle(matchName && matchCat);
        });
    }

    fetchProducts();
    loadCategories();

    $('#product-search').on('input', filterProducts);
    $('#category-filter').on('change', filterProducts);

    // Initialize Select2 for customer search
    $('#customer-search').select2({
        placeholder: 'Search customer...',
        allowClear: true,
        minimumInputLength: 1, // 🚫 Don't query until 1 character is typed
        ajax: {
            url: store_pos.ajax_url,
            method: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    action: 'store_pos_search_customers',
                    term: params.term
                };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
        const data = e.params.data;
        // You can store selectedCustomerId for later use
        selectedCustomerId = data.id;
    });

    // Handle clear
    $('#customer-search').on('select2:clear', function () {
        selectedCustomerId = null;
    });



    // Handle opening of the Add Customer modal
    $('#add-customer-btn').on('click', function () {
        $('#add-customer').addClass('active');
    });

    // Handle closing of any modal
    $(document).on('click', '.close-modal', function () {
        $(this).closest('.store-pos-modal').removeClass('active');
    });

    // Add Customer Form Submit
    $('#addCustomerForm').off('submit').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: store_pos.ajax_url,
            type: 'POST',
            data: {
                action: 'store_pos_add_customer',
                data: formData,
            },
            success: function (response) {
                if (response.success) {
                    // ✅ Close the modal (use .removeClass instead of .hide)
                    $('.store-pos-modal#add-customer').removeClass('active');

                    // ✅ Show confirmation modal
                    showCustomModal('Customer added!', true);

                    // ✅ Insert new customer into Select2 and select
                    const newOption = new Option(response.data.text, response.data.id, true, true);
                    $('#customer-search').append(newOption).trigger('change');

                    // ✅ Optionally reset form
                    $('#addCustomerForm')[0].reset();
                } else {
                    showCustomModal(response.data.message || 'Failed to add customer.', false);
                }
            },
            error: function () {
                showCustomModal('Something went wrong.', false);
            }
        });
    });


    $(document).on('click', '.add-to-cart-btn', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = parseFloat($(this).data('price'));
        const existing = cart.find(i => i.id === id);

        if (existing) existing.quantity += 1;
        else cart.push({ id, name, price, quantity: 1 });

        renderCart();
    });

    $(document).on('change', '.cart-qty', function () {
        const index = $(this).closest('tr').data('index');
        const newQty = parseInt($(this).val());
        if (newQty > 0) cart[index].quantity = newQty;
        renderCart();
    });

    $(document).on('click', '.qty-plus', function () {
        const index = $(this).closest('tr').data('index');
        cart[index].quantity += 1;
        renderCart();
    });

    $(document).on('click', '.qty-minus', function () {
        const index = $(this).closest('tr').data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity -= 1;
            renderCart();
        }
    });

    $(document).on('click', '.remove-item', function () {
        const index = $(this).closest('tr').data('index');
        cart.splice(index, 1);
        renderCart();
    });

    $('#discount-btn').on('click', function () {
        $('#discount-input').val('');
        $('#discount-modal').show();
    });

    $('#close-discount-modal').on('click', function () {
        $('#discount-modal').hide();
    });

    $('.keypad button').off('click').on('click', function () {
        const key = $(this).data('key');
        let current = $('#discount-input').val();
        if (key === 'back') $('#discount-input').val(current.slice(0, -1));
        else if (key === '.' && current.includes('.')) return;
        else $('#discount-input').val(current + key);
    });

    $('#apply-fixed-discount').on('click', function () {
        const value = parseFloat($('#discount-input').val()) || 0;
        discountItem = { value };
        $('#discount-modal').hide();
        updateCartTotal();
    });

    $('#apply-percent-discount').on('click', function () {
        const percent = parseFloat($('#discount-input').val()) || 0;
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        discountItem = { value: (subtotal * percent) / 100 };
        $('#discount-modal').hide();
        updateCartTotal();
    });

    $('#shipping-btn').on('click', function () {
        $.post(store_pos.ajax_url, { action: 'store_pos_get_shipping_methods' }, function (response) {
            if (response.success) {
                const $dropdown = $('#shipping-methods-dropdown');
                $dropdown.empty();
                $dropdown.append('<option value="">Select a method</option>');
                response.data.forEach(method => {
                    const title = method.title || "Unnamed";
                    $dropdown.append(`<option value="${method.cost}">${title} - ৳ ${method.cost}</option>`);
                });
                $('#shipping-modal').show();
            } else {
                alert('Failed to load shipping methods');
            }
        });
    });

    $('#close-shipping-modal').on('click', function () {
        $('#shipping-modal').hide();
    });

    $('#apply-shipping-fee').on('click', function () {
        const selected = $('#shipping-methods-dropdown').val();
        const custom = parseFloat($('#custom-shipping-fee').val());

        const fee = selected !== '' ? parseFloat(selected) : (custom || 0);
        shippingItem = { value: fee };

        $('#shipping-modal').hide();
        updateCartTotal();
    });

    $('#note-btn').on('click', function () {
        $('#note-textarea').val(noteItem?.text || '');
        $('#note-modal').show();
    });

    $('#close-note-modal').on('click', function () {
        $('#note-modal').hide();
    });

    $('#submit-note').on('click', function () {
        const note = $('#note-textarea').val().trim();
        if (note !== '') {
            noteItem = { text: note };
            updateCartTotal();
        }
        $('#note-modal').hide();
    });

    function showCustomModal(message, isSuccess = true) {
        // Remove any existing modal first
        $('.custom-confirmation-modal-overlay').remove();

        // Create the new modal
        const modal = $(`
            <div class="custom-confirmation-modal-overlay">
                <div class="custom-confirmation-modal-content">
                    <span class="custom-confirmation-modal-title">
                        ${isSuccess ? 'Success' : 'Error'}
                    </span>
                    <p>${message}</p>
                    <div class="modal-actions">
                        <button class="modal-close-btn">Close</button>
                    </div>
                </div>
            </div>
        `);

        $('body').append(modal);

        // Close on button click
        modal.find('.modal-close-btn').on('click', function () {
            modal.remove();
        });
    }
});
