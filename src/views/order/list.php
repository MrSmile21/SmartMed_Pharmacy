<?php
/**
 * src/views/order/list.php
 *
 * Displays a list of orders based on filters.
 * Included by public/orders.php for 'list_all', 'list_by_customer', 'list_by_date', 'list_by_filter'.
 * Assumes $orders, $customers (for filter dropdown), $customer_id_filter, $order_date_filter are set.
 */

// Ensure variables are set
if (!isset($orders) || !is_array($orders)) $orders = [];
if (!isset($all_customers_for_filter) || !is_array($all_customers_for_filter)) $all_customers_for_filter = []; // Renamed to avoid conflict
$customer_id_filter = $customer_id_filter ?? null;
$order_date_filter = $order_date_filter ?? null;
?>
<h2>View Orders</h2>

<form action="orders.php" method="GET" class="filter-form">
    <input type="hidden" name="action" value="list_by_filter"> <div>
        <label for="filter_customer_id">Filter by Customer:</label>
        <select name="customer_id" id="filter_customer_id">
            <option value="">All Customers</option>
            <?php foreach ($all_customers_for_filter as $cust): ?>
                <option value="<?php echo htmlspecialchars($cust['CustomerID']); ?>" <?php echo ($customer_id_filter == $cust['CustomerID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cust['FullName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="filter_order_date">Filter by Order Date:</label>
        <input type="date" name="order_date" id="filter_order_date" value="<?php echo htmlspecialchars($order_date_filter ?? ''); ?>">
    </div>
    <div class="form-actions">
        <button type="submit" class="button">Filter Orders</button>
        <a href="orders.php?action=list_all" class="button secondary">Clear Filters</a>
    </div>
</form>
<hr>
<div class="actions-bar">
     <a href="orders.php?action=place" class="button">Place New Order</a>
</div>


<?php if (isset($_SESSION['message'])): ?>
    <div class="message <?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?>">
        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
    </div>
<?php endif; ?>

<?php if (count($orders) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Payment Status</th>
                <th>Delivery Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo htmlspecialchars($order['OrderID']); ?></td>
                <td><?php echo htmlspecialchars($order['CustomerName']); ?></td>
                <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($order['OrderDate']))); ?></td>
                <td><?php echo number_format($order['TotalAmount'] ?? 0, 2); // TotalAmount from GROUP BY query ?></td>
                <td><?php echo htmlspecialchars($order['PaymentStatus']); ?></td>
                <td><?php echo htmlspecialchars($order['DeliveryMethod']); ?></td>
                <td>
                    <a href="orders.php?action=view_details&id=<?php echo $order['OrderID']; ?>" class="button-small info">View Details</a>
                    </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No orders found matching your criteria.</p>
<?php endif; ?>
