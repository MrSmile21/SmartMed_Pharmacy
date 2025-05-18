<?php
/**
 * src/views/report/daily_sales.php
 *
 * Displays the daily sales summary report.
 * Included by public/reports.php when type is 'daily_sales'.
 * Assumes $sales_summary (data from vw_DailySalesSummary) is pre-populated.
 *
 * Reminder: The original vw_DailySalesSummary had a potentially problematic join:
 * JOIN Employee e ON e.OutletID = e.OutletID
 * This should be corrected in the SQL View definition for accurate reporting.
 * Assuming it's corrected to link sales to outlets properly.
 */

// Ensure $sales_summary is set and is an array
if (!isset($sales_summary) || !is_array($sales_summary)) {
    $sales_summary = [];
}
?>
<h2>Daily Sales Summary</h2>

<?php if (count($sales_summary) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order Date</th>
                <th>Outlet ID / Name</th> <th>Total Sales</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_summary as $summary): ?>
            <tr>
                <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($summary['OrderDate']))); ?></td>
                <td><?php echo htmlspecialchars($summary['OutletID']); ?>
                    <?php // If you join with Outlet table in the view: echo " (".htmlspecialchars($summary['OutletLocation']).")"; ?>
                </td>
                <td><?php echo number_format($summary['TotalSales'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No daily sales summary data found for the selected period or overall.</p>
    <p><em>Ensure the <code>vw_DailySalesSummary</code> SQL View is correctly defined and populated.</em></p>
<?php endif; ?>
