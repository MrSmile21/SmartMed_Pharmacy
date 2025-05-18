<?php
/**
 * src/views/report/low_stock.php
 *
 * Displays the low stock report.
 * Included by public/reports.php when type is 'low_stock'.
 * Assumes $low_stock_items (data from sp_ReorderNotification) is pre-populated.
 */

// Ensure $low_stock_items is set and is an array
if (!isset($low_stock_items) || !is_array($low_stock_items)) {
    $low_stock_items = [];
}
?>
<h2>Low Stock Report</h2>
<p>This report lists products where the current quantity in stock is at or below the reorder level.</p>

<?php if (count($low_stock_items) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Outlet ID / Name</th> <th>Product ID</th>
                <th>Product Name</th>
                <th>Current Quantity</th>
                <th>Reorder Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($low_stock_items as $item): ?>
            <tr class="<?php echo ($item['Quantity'] <= 0) ? 'stock-out' : 'stock-low'; ?>">
                <td><?php echo htmlspecialchars($item['OutletID']); ?>
                     <?php // If you join with Outlet table in the SP: echo " (".htmlspecialchars($item['OutletLocation']).")"; ?>
                </td>
                <td><?php echo htmlspecialchars($item['ProductID']); ?></td>
                <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                <td><?php echo htmlspecialchars($item['ReorderLevel']); ?></td>
                <td>
                    <a href="supply.php?action=create_po&product_id=<?php echo $item['ProductID']; ?>&outlet_id=<?php echo $item['OutletID']; ?>" class="button-small action">Create PO</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>All products are currently above their reorder levels, or the reorder notification procedure returned no results.</p>
    <p><em>Ensure the <code>sp_ReorderNotification</code> Stored Procedure is correctly defined and stock levels are being managed.</em></p>
<?php endif; ?>

<style>
    /* Basic styling for highlighting stock levels */
    .stock-low td { background-color: #fff3cd; /* Light yellow for low stock */ }
    .stock-out td { background-color: #f8d7da; /* Light red for out of stock */ }
    .stock-low td:nth-child(4), .stock-out td:nth-child(4) { font-weight: bold; }
</style>
