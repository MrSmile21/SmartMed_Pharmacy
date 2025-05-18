<?php
/**
 * src/views/order/place_order_form.php
 *
 * Displays the form to place a new order.
 * Included by public/orders.php when action is 'place'.
 * Assumes $customers and $available_products are pre-populated.
 */

// Ensure variables are set
if (!isset($customers) || !is_array($customers)) $customers = [];
if (!isset($available_products) || !is_array($available_products)) $available_products = [];

?>
<h2>Place New Order</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="message error">
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success">
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<form action="orders.php?action=place" method="POST" id="placeOrderForm">
    <div>
        <label for="customerID">Customer:</label>
        <select name="customerID" id="customerID" required>
            <option value="">Select Customer</option>
            <?php foreach ($customers as $customer): ?>
                <option value="<?php echo htmlspecialchars($customer['CustomerID']); ?>">
                    <?php echo htmlspecialchars($customer['FullName']); ?> (ID: <?php echo htmlspecialchars($customer['CustomerID']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="deliveryMethod">Delivery Method:</label>
        <select name="deliveryMethod" id="deliveryMethod" required>
            <option value="Pickup">Pickup</option>
            <option value="Home Delivery">Home Delivery</option>
        </select>
    </div>

    <fieldset id="product-lines-container">
        <legend>Products</legend>
        <div class="product-line-template" style="display:none;">
            <select name="products[id][]" class="product-select" disabled>
                <option value="">Select Product</option>
                <?php foreach ($available_products as $prod): ?>
                    <option value="<?php echo htmlspecialchars($prod['ProductID']); ?>"
                            data-price="<?php echo htmlspecialchars($prod['UnitPrice']); ?>"
                            data-prescription="<?php echo $prod['RequiresPrescription'] ? '1' : '0'; ?>">
                        <?php echo htmlspecialchars($prod['ProductName']); ?>
                        (<?php echo number_format($prod['UnitPrice'], 2); ?>)
                        <?php echo $prod['RequiresPrescription'] ? ' *Rx*' : ''; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="products[quantity][]" placeholder="Qty" min="1" value="1" class="product-quantity" disabled required>
            <input type="number" name="products[price][]" placeholder="Unit Price" step="0.01" class="product-price" readonly disabled required>
            <span class="prescription-alert" style="color:red; display:none;">Requires Prescription!</span>
            <button type="button" class="remove-line-btn">Remove</button>
        </div>
        <div class="product-line">
             <select name="products[id][]" class="product-select" required>
                <option value="">Select Product</option>
                <?php foreach ($available_products as $prod): ?>
                    <option value="<?php echo htmlspecialchars($prod['ProductID']); ?>"
                            data-price="<?php echo htmlspecialchars($prod['UnitPrice']); ?>"
                            data-prescription="<?php echo $prod['RequiresPrescription'] ? '1' : '0'; ?>">
                        <?php echo htmlspecialchars($prod['ProductName']); ?>
                        (<?php echo number_format($prod['UnitPrice'], 2); ?>)
                        <?php echo $prod['RequiresPrescription'] ? ' *Rx*' : ''; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="products[quantity][]" placeholder="Qty" min="1" value="1" class="product-quantity" required>
            <input type="number" name="products[price][]" placeholder="Unit Price" step="0.01" class="product-price" readonly required>
            <span class="prescription-alert" style="color:red; display:none;">Requires Prescription!</span>
            <button type="button" class="remove-line-btn" style="display:none;">Remove</button> </div>
    </fieldset>
    <button type="button" id="add-product-line-btn" class="button secondary">Add Another Product</button>
    
    <div class="form-actions">
        <button type="submit" class="button">Place Order</button>
        <a href="orders.php" class="button secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('product-lines-container');
    const addBtn = document.getElementById('add-product-line-btn');
    const template = container.querySelector('.product-line-template');

    function addProductLine() {
        const newLine = template.cloneNode(true);
        newLine.classList.remove('product-line-template');
        newLine.style.display = ''; // Make it visible
        newLine.classList.add('product-line'); // Add the class for styling and selection

        // Enable inputs and selects in the cloned line
        newLine.querySelectorAll('input, select').forEach(el => el.disabled = false);
        
        const removeBtn = newLine.querySelector('.remove-line-btn');
        removeBtn.style.display = 'inline-block'; // Ensure remove button is visible for added lines
        
        container.appendChild(newLine);
        updateRemoveButtonVisibility();
    }

    function updateRemoveButtonVisibility() {
        const lines = container.querySelectorAll('.product-line');
        lines.forEach((line, index) => {
            const removeBtn = line.querySelector('.remove-line-btn');
            if (removeBtn) {
                removeBtn.style.display = lines.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    container.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-line-btn')) {
            event.target.closest('.product-line').remove();
            updateRemoveButtonVisibility();
        }
    });

    container.addEventListener('change', function(event) {
        if (event.target.classList.contains('product-select')) {
            const select = event.target;
            const selectedOption = select.options[select.selectedIndex];
            const priceInput = select.closest('.product-line').querySelector('.product-price');
            const prescriptionAlert = select.closest('.product-line').querySelector('.prescription-alert');
            
            priceInput.value = selectedOption.dataset.price || '';
            if (selectedOption.dataset.prescription === '1') {
                prescriptionAlert.style.display = 'inline';
                // You might want to add more robust handling for prescription products,
                // e.g., requiring a prescription ID or a pharmacist's approval.
            } else {
                prescriptionAlert.style.display = 'none';
            }
        }
    });

    addBtn.addEventListener('click', addProductLine);
    
    // Initialize remove button visibility for the first line
    updateRemoveButtonVisibility(); 
    // Initialize price and prescription alert for the first line if a product is selected by default
    const firstSelect = container.querySelector('.product-line .product-select');
    if (firstSelect && firstSelect.value) {
        const changeEvent = new Event('change', { bubbles: true });
        firstSelect.dispatchEvent(changeEvent);
    }
});
</script>
