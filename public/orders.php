<?php
// public/orders.php
require_once '../config/database.php';
require_once '../src/includes/functions.php';

$page_title = "Manage Orders";
include '../src/views/templates/header.php';

$action = $_GET['action'] ?? 'list_all'; // Default action
$customer_id_filter = $_GET['customer_id'] ?? null;
$order_date_filter = $_GET['order_date'] ?? null;

?>
<div class="container">
    <h1>Order Management</h1>

    <?php if ($action == 'place'): ?>
        <h2>Place New Order</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $customerID = (int)$_POST['customerID'];
            $deliveryMethod = $_POST['deliveryMethod'];
            $products_input = $_POST['products']; // Expecting an array of products

            $products_for_sp = [];
            if (!empty($products_input['id'])) {
                foreach ($products_input['id'] as $key => $p_id) {
                    if (!empty($p_id) && !empty($products_input['quantity'][$key]) && !empty($products_input['price'][$key])) {
                         $products_for_sp[] = [
                            'ProductID' => (int)$p_id,
                            'Quantity' => (int)$products_input['quantity'][$key],
                            'UnitPrice' => (float)$products_input['price'][$key]
                        ];
                    }
                }
            }

            if (empty($customerID) || empty($deliveryMethod) || empty($products_for_sp)) {
                echo "<p class='error'>Customer, Delivery Method, and at least one Product are required.</p>";
            } else {
                // Check if any product requires prescription and if prescription is provided (complex logic not shown here)
                // For now, directly call the adapted order placement logic
                $orderPlacedID = db_call_sp_place_order($customerID, $deliveryMethod, $products_for_sp);

                if ($orderPlacedID) {
                    echo "<p class='success'>Orders (ID: {$orderPlacedID}) placed successfully! Stock will be updated automatically by the trigger.</p>";
                    // Clear form or redirect
                } else {
                    echo "<p class='error'>Error placing order. Please check stock or product validity.</p>";
                }
            }
        }
        ?>
        <form action="orders.php?action=place" method="POST">
            <div>
                <label for="customerID">Customer:</label>
                <select name="customerID" id="customerID" required>
                    <option value="">Select Customer</option>
                    <?php
                    $customers = db_query("SELECT CustomerID, FullName FROM Customer ORDER BY FullName");
                    if ($customers) {
                        foreach ($customers as $customer) {
                            echo "<option value='{$customer['CustomerID']}'>" . htmlspecialchars($customer['FullName']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="deliveryMethod">Delivery Method:</label>
                <select name="deliveryMethod" id="deliveryMethod" required>
                    <option value="Pickup">Pickup</option>
                    <option value="Home Delivery">Home Delivery</option>
                </select>
            </div>

            <fieldset id="product-lines">
                <legend>Products</legend>
                <div class="product-line">
                    <select name="products[id][]" class="product-select" required>
                        <option value="">Select Product</option>
                        <?php
                        $available_products = db_query("SELECT ProductID, ProductName, UnitPrice, RequiresPrescription FROM Product ORDER BY ProductName");
                        if ($available_products) {
                            foreach ($available_products as $prod) {
                                echo "<option value='{$prod['ProductID']}' data-price='{$prod['UnitPrice']}' data-prescription='" . ($prod['RequiresPrescription'] ? '1':'0') . "'>" . htmlspecialchars($prod['ProductName']) . " (Price: {$prod['UnitPrice']})" . ($prod['RequiresPrescription'] ? ' *Req. Prescription*' : '') . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <input type="number" name="products[quantity][]" placeholder="Qty" min="1" value="1" required class="product-quantity">
                    <input type="number" name="products[price][]" placeholder="Unit Price" step="0.01" required class="product-price" readonly>
                     <button type="button" class="remove-line-btn" style="display:none;">Remove</button>
                </div>
            </fieldset>
            <button type="button" id="add-product-line">Add Another Product</button>
            <br><br>
            <button type="submit">Place Order</button>
        </form>
        <script>
            // Simple script to add product lines and update price
            document.addEventListener('DOMContentLoaded', function() {
                const productLinesContainer = document.getElementById('product-lines');
                const addProductLineBtn = document.getElementById('add-product-line');

                function updatePrice(selectElement) {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    const line = selectElement.closest('.product-line');
                    const priceInput = line.querySelector('.product-price');
                    if (priceInput) priceInput.value = price || '';

                    const requiresPrescription = selectedOption.getAttribute('data-prescription') === '1';
                    // You could add a visual indicator or warning if prescription is required.
                    // For example: line.querySelector('.prescription-warning').style.display = requiresPrescription ? 'inline' : 'none';
                }

                productLinesContainer.addEventListener('change', function(event) {
                    if (event.target.classList.contains('product-select')) {
                        updatePrice(event.target);
                    }
                });

                 addProductLineBtn.addEventListener('click', function() {
                    const firstLine = productLinesContainer.querySelector('.product-line');
                    const newLine = firstLine.cloneNode(true);
                    newLine.querySelectorAll('input, select').forEach(input => {
                        if(input.type !== 'button') input.value = '';
                        if(input.tagName === 'SELECT') input.selectedIndex = 0;
                    });
                    const removeBtn = newLine.querySelector('.remove-line-btn');
                    if(removeBtn) removeBtn.style.display = 'inline-block'; // Show remove button for new lines
                    productLinesContainer.appendChild(newLine);
                });

                productLinesContainer.addEventListener('click', function(event) {
                    if (event.target.classList.contains('remove-line-btn')) {
                        event.target.closest('.product-line').remove();
                    }
                });

                // Initialize price for the first line if a product is pre-selected (not typical for new forms)
                const firstSelect = productLinesContainer.querySelector('.product-select');
                if (firstSelect && firstSelect.value) {
                    updatePrice(firstSelect);
                }
            });
        </script>

    <?php elseif ($action == 'list_all' || $action == 'list_by_customer' || $action == 'list_by_date'): ?>
        <h2>View Orders</h2>
        <form action="orders.php" method="GET">
            <input type="hidden" name="action" value="list_by_filter">
            <label for="customer_id_filter">By Customer:</label>
            <select name="customer_id" id="customer_id_filter">
                <option value="">All Customers</option>
                 <?php
                    $customers = db_query("SELECT CustomerID, FullName FROM Customer ORDER BY FullName");
                    if ($customers) {
                        foreach ($customers as $customer) {
                            $selected = ($customer_id_filter == $customer['CustomerID']) ? 'selected' : '';
                            echo "<option value='{$customer['CustomerID']}' {$selected}>" . htmlspecialchars($customer['FullName']) . "</option>";
                        }
                    }
                ?>
            </select>
            <label for="order_date_filter">By Date:</label>
            <input type="date" name="order_date" id="order_date_filter" value="<?php echo htmlspecialchars($order_date_filter ?? ''); ?>">
            <button type="submit">Filter Orders</button>
             <a href="orders.php?action=list_all" class="button secondary">Clear Filters</a>
        </form>
        <br>
        <?php
        $sql = "
            SELECT o.OrderID, c.FullName AS CustomerName, o.OrderDate, o.PaymentStatus, o.DeliveryMethod,
                   SUM(od.Quantity * od.UnitPrice * (1 - od.Discount / 100.0)) AS TotalAmount
            FROM `Orders` o
            JOIN Customer c ON o.CustomerID = c.CustomerID
            JOIN OrderDetails od ON o.OrderID = od.OrderID
        ";
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($_GET['customer_id'])) {
            $conditions[] = "o.CustomerID = ?";
            $params[] = (int)$_GET['customer_id'];
            $types .= "i";
        }
        if (!empty($_GET['order_date'])) {
            $conditions[] = "o.OrderDate = ?";
            $params[] = $_GET['order_date'];
            $types .= "s";
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " GROUP BY o.OrderID, c.FullName, o.OrderDate, o.PaymentStatus, o.DeliveryMethod ORDER BY o.OrderDate DESC, o.OrderID DESC";

        $orders = db_query($sql, $params, $types);

        if ($orders && count($orders) > 0):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Status</th>
                        <th>Delivery Method</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                        <td><?php echo htmlspecialchars($order['CustomerName']); ?></td>
                        <td><?php echo htmlspecialchars($order['OrderDate']); ?></td>
                        <td><?php echo number_format($order['TotalAmount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['PaymentStatus']); ?></td>
                        <td><?php echo htmlspecialchars($order['DeliveryMethod']); ?></td>
                        <td><a href="orders.php?action=view_details&id=<?php echo $order['OrderID']; ?>">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found matching your criteria.</p>
        <?php endif; ?>
         <p><a href="orders.php?action=place" class="button">Place New Order</a></p>

    <?php elseif ($action == 'view_details' && isset($_GET['id'])): ?>
        <?php
        $order_id_detail = (int)$_GET['id'];
        $order_info = db_query("SELECT o.*, c.FullName as CustomerName FROM `Order` o JOIN Customer c ON o.CustomerID = c.CustomerID WHERE o.OrderID = ?", [$order_id_detail], "i");
        $order_details = db_query("SELECT od.*, p.ProductName FROM OrderDetails od JOIN Product p ON od.ProductID = p.ProductID WHERE od.OrderID = ?", [$order_id_detail], "i");

        if ($order_info && count($order_info) > 0):
            $current_order = $order_info[0];
        ?>
            <h2>Order Details #<?php echo htmlspecialchars($current_order['OrderID']); ?></h2>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($current_order['CustomerName']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($current_order['OrderDate']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($current_order['PaymentStatus']); ?></p>
            <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars($current_order['DeliveryMethod']); ?></p>
            <h3>Items:</h3>
            <?php if ($order_details && count($order_details) > 0): ?>
            <table>
                <thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Discount</th><th>Subtotal</th></tr></thead>
                <tbody>
                <?php
                $grandTotal = 0;
                foreach ($order_details as $item):
                    $subtotal = $item['Quantity'] * $item['UnitPrice'] * (1 - $item['Discount'] / 100.0);
                    $grandTotal += $subtotal;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                        <td><?php echo number_format($item['UnitPrice'], 2); ?></td>
                        <td><?php echo number_format($item['Discount'], 2); ?>%</td>
                        <td><?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><td colspan="4" style="text-align:right;"><strong>Grand Total:</strong></td><td><strong><?php echo number_format($grandTotal, 2); ?></strong></td></tr></tfoot>
            </table>
            <?php else: ?>
                <p>No items found for this order.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="error">Order not found.</p>
        <?php endif; ?>
        <p><a href="orders.php" class="button secondary">Back to Orders List</a></p>

    <?php endif; ?>
</div>
<?php include '../src/views/templates/footer.php'; ?>