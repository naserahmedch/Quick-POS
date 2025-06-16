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
                        <tr>
                            <td colspan="3" class="cart-empty">Empty Cart</td>
                        </tr>
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
            <div class="modal-header">
                <h3>Apply Discount</h3>
                <span class="close-modal" id="close-discount-modal">&times;</span>
            </div>
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
        <div class="modal-content note-modal-content">

            <div class="modal-header">
                <h3>Add Note</h3>
                <span class="close-modal" id="close-note-modal">&times;</span>
            </div>

            <div class="note-modal-body">
                <textarea id="note-textarea" placeholder="Write your note..."></textarea>

                <div class="note-actions">
                    <button id="submit-note" class="add-note-btn">Add Note</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Shipping Modal -->
    <div id="shipping-modal" class="modal" style="display:none;">
        <div class="modal-content shipping-modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h3>Select Shipping Method</h3>
                <span class="close-modal" id="close-shipping-modal">&times;</span>
            </div>

            <div class="shipping-modal-body">
                <!-- Label + Dropdown -->
                <p class="modal-label">Available Shipping Methods:</p>
                <select id="shipping-methods-dropdown">
                    <option value="">Select a method</option>
                </select>

                <!-- Label + Custom Input -->
                <p class="modal-label">Or Enter Custom Shipping Fee (Tk):</p>
                <input type="number" id="custom-shipping-fee" placeholder="e.g. 50" />

                <!-- Apply Button -->
                <div class="shipping-actions">
                    <button id="apply-shipping-fee">Apply</button>
                </div>
            </div>

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
    <div class="modal-content">
        <p></p><button class="close-modal">Close</button>
    </div>
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