<?php
/**
 * src/views/product/stock_info.php
 *
 * Displays detailed stock information for a specific product across different outlets.
 * Included by public/products.php when action is 'stock_info'.
 * Assumes $product_info (details of the product itself) and
 * $stock_levels (array of stock quantities per outlet) are pre-populated.
 */

// Ensure variables are set
if (!isset($product_info) || !is_array($product_info)) {
    echo "<p class='error'>Product information is not available.</p>";
    // Optionally include a back link or redirect
    // echo '<p><a href="products.php">Return to Product List</a></p>';
    return; // Stop further rendering of this view if essential data is missing
}
if (!isset($stock_levels) || !is_array($stock_levels)) {
    $stock_levels = [];
}
?>

<h2>Stock Information for: <?php echo htmlspecialchars($product_info['ProductName']); ?> (ID: <?php echo htmlspecialchars($product_info['ProductID']); ?>)</h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="message <?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?>">
        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
    </div>
<?php endif; ?>

<div class="product-details">
    <p><strong>Category:</strong> <?php echo htmlspecialchars($product_info['Category']); ?></p>
    <p><strong>Unit Price:</strong> <?php echo number_format($product_info['UnitPrice'], 2); ?></p>
    <p><strong>Requires Prescription:</strong> <?php echo $product_info['RequiresPrescription'] ? "Yes" : "No"; ?></p>
</div>

<h3>Stock Levels by Outlet:</h3>
<?php if (count($stock_levels) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Outlet ID</th>
                <th>Outlet Location</th> <th>Quantity in Stock</th>
                <th>Reorder Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stock_levels as $stock): ?>
            <tr>
                <td><?php echo htmlspecialchars($stock['OutletID']); ?></td>
                <td><?php echo htmlspecialchars($stock['Location'] ?? 'N/A'); // From joined Outlet table ?></td>
                <td><?php echo htmlspecialchars($stock['Quantity']); ?></td>
                <td><?php echo htmlspecialchars($stock['ReorderLevel']); ?></td>
                <td>
                    <?php
                    if ($stock['Quantity'] <= 0) {
                        echo '<span class="status-error">Out of Stock</span>';
                    } elseif ($stock['Quantity'] <= $stock['ReorderLevel']) {
                        echo '<span class="status-warning">Low Stock</span>';
                    } else {
                        echo '<span class="status-ok">In Stock</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No stock information found for this product in any outlet. This might mean the product has not been assigned to any outlet's stock yet.</p>
<?php endif; ?>

<div class="actions-bar">
    <a href="products.php" class="button secondary">Back to Product List</a>
    </div>
