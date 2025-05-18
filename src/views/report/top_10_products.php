<?php
/**
 * src/views/report/top_10_products.php
 *
 * Displays the top 10 selling products for the current month.
 * Included by public/reports.php when type is 'top_10_products'.
 * Assumes $top_products (data from vw_Top10SellingProductsMonthly) is pre-populated.
 *
 * Reminder: The SQL Server TOP 10 and GETDATE() syntax in the original view
 * needs to be adapted for MySQL (LIMIT 10, CURDATE()/NOW()).
 */

// Ensure $top_products is set and is an array
if (!isset($top_products) || !is_array($top_products)) {
    $top_products = [];
}
?>
<h2>Top 10 Selling Products (Current Month)</h2>

<?php if (count($top_products) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Total Sold This Month</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; foreach ($top_products as $product): ?>
            <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($product['ProductID']); ?></td>
                <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                <td><?php echo htmlspecialchars($product['TotalSold']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No sales data found for the current month, or the top products view did not return data.</p>
    <p><em>Ensure the <code>vw_Top10SellingProductsMonthly</code> SQL View is correctly defined for MySQL and there is sales data for the current month.</em></p>
<?php endif; ?>
