<?php
?>

<div id="store-pos-app" class="store-pos-wrapper">

    <!-- Left Panel -->
    <div class="pos-left">
        <div class="product-toolbar">
            <input type="text" id="product-search" placeholder="Search your product..." style="width: 60%;">
            <select id="category-filter" style="width: 38%;">
                <option value="">All Categories</option>
            </select>
        </div>
        <div class="product-grid" id="product-list">
            <!-- Products will be rendered here via JS -->
        </div>
    </div>

    <!-- Right Panel -->
    <div class="pos-right">
        <div class="cart-box">
            <div class="cart-header">
                <select id="customer-search" style="width: 100%;" data-placeholder="Search customer..."></select>
                <button id="add-customer-btn">+</button>
            </div>

            <div class="cart-body">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        <tr><td colspan="3" class="cart-empty">Empty Cart</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="cart-footer-items" id="cart-footer-items"></div>

            <div class="cart-footer-actions">
                <button id="discount-btn" class="action-btn">Add Discount</button>
                <button id="shipping-btn" class="action-btn">Shipping Fee</button>
                <button id="note-btn" class="action-btn">Add Note</button>
            </div>

            <div class="pay-btn-wrapper">
                <button class="pay-btn">Pay Now <span>৳ 0.00</span></button>
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div id="discount-modal" class="modal" style="display: none;">
        <div class="modal-content discount-modal-content">
            <span class="close-modal" id="close-discount-modal">&times;</span>
            <div class="discount-display">
                <input type="text" id="discount-input" readonly placeholder="0" />
            </div>
            <div class="keypad">
                <button data-key="1">1</button>
                <button data-key="2">2</button>
                <button data-key="3">3</button>
                <button data-key="4">4</button>
                <button data-key="5">5</button>
                <button data-key="6">6</button>
                <button data-key="7">7</button>
                <button data-key="8">8</button>
                <button data-key="9">9</button>
                <button data-key=".">.</button>
                <button data-key="0">0</button>
                <button data-key="back">⌫</button>
            </div>
            <div class="discount-actions">
                <button id="apply-percent-discount">%</button>
                <button id="apply-fixed-discount">৳</button>
            </div>
        </div>
    </div>

    <!-- Note Modal -->
    <div id="note-modal" class="modal" style="display:none;">
        <div class="modal-content" style="width: 300px;">
            <span class="close-modal" id="close-note-modal">&times;</span>
            <h4>Add Note</h4>
            <textarea id="note-textarea" placeholder="Write your note..." style="width:100%; height: 100px; padding: 10px; margin-bottom: 10px;"></textarea>
            <button id="submit-note" class="pay-btn" style="width:100%;">Add Note</button>
        </div>
    </div>

    <!-- Shipping Modal -->
    <div id="shipping-modal" class="modal" style="display:none;">
        <div class="modal-content shipping-modal-content">
            <span class="close-modal" id="close-shipping-modal">&times;</span>
            <h3>Select Shipping Method</h3>
            <select id="shipping-methods-dropdown" style="width:100%;padding:10px;margin-bottom:10px;"></select>
            <input type="number" id="custom-shipping-fee" placeholder="Or enter custom fee" style="width:100%;padding:10px;" />
            <button id="apply-shipping-fee" class="pay-btn" style="margin-top:10px;">Apply Shipping</button>
        </div>
    </div>

</div>

<!-- Add Customer Modal -->
<div class="store-pos-modal" id="add-customer">
    <div class="modal-content">
        <h2>Add New Customer</h2>
        <form id="addCustomerForm">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="address" placeholder="Address">
            <input type="text" name="city" placeholder="City">
            <input type="text" name="zip" placeholder="Zip/Postal Code">
            <input type="text" name="phone" placeholder="Phone Number" required>

            <div class="form-buttons">
                <button type="button" class="cancel-btn close-modal">Cancel</button>
                <button type="submit" class="submit-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div class="custom-confirmation-modal" style="display:none;">
    <div class="modal-content"><p></p><button class="close-modal">Close</button></div>
</div>

<!-- Order Summary Modal -->
<div id="order-summary-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Order Summary</h3>
        <div id="summary-cart"></div>
        <button id="place-order-btn">Place Order</button>
    </div>
</div>

<!-- Order Complete Modal -->
<div id="order-complete-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Order Placed!</h3>
        <button id="print-receipt-btn">Print Receipt</button>
        <button id="new-sale-btn">New Sale</button>
    </div>
</div>
