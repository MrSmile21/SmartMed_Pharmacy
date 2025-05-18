<?php
/**
 * src/views/order/details.php
 *
 * Displays the detailed view of a single order.
 * Included by public/orders.php when action is 'view_details'.
 * Assumes $current_order (array with Order master info) and
 * $order_items (array of products in the order) are pre-populated.
 */

// Ensure variables are set
if (!isset($current_order) || !is_array($current_order)) {
    echo "<p class='error'>Order information is not available.</p>";
    echo '<p><a href="orders.php" class="button secondary">Back to Orders List</a></p>';
    return;
}
if (!isset($order_items) || !is_array($order_items)) {
    $order_items = [];
}
?>

<h2>Order Details #<?php echo htmlspecialchars($current_order['OrderID']); ?></h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="message <?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?>">
        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
    </div>
<?php endif; ?>

<div class="order-summary">
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($current_order['CustomerName'] ?? 'N/A'); // CustomerName from JOIN ?></p>
    <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($current_order['OrderDate']))); ?></p>
    <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($current_order['PaymentStatus']); ?></p>
    <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars($current_order['DeliveryMethod']); ?></p>
    </div>

<h3>Items in this Order:</h3>
<?php if (count($order_items) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Discount (%)</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandTotal = 0;
            foreach ($order_items as $item):
                // Calculate subtotal: Quantity * UnitPrice * (1 - Discount/100)
                $discountMultiplier = 1 - ($item['Discount'] / 100.0);
                $subtotal = $item['Quantity'] * $item['UnitPrice'] * $discountMultiplier;
                $grandTotal += $subtotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['ProductName'] ?? 'N/A'); // ProductName from JOIN ?></td>
                <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                <td><?php echo number_format($item['UnitPrice'], 2); ?></td>
                <td><?php echo number_format($item['Discount'], 2); ?>%</td>
                <td><?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;"><strong>Grand Total:</strong></td>
                <td><strong><?php echo number_format($grandTotal, 2); ?></strong></td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <p>No items found for this order. This might indicate an issue with the order details.</p>
<?php endif; ?>

<div class="actions-bar">
    <a href="orders.php" class="button secondary">Back to Orders List</a>
    </div>
