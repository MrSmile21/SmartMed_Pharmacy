<?php
// public/reports.php
require_once '../config/database.php';
require_once '../src/includes/functions.php';

$page_title = "BI & Reports";
include '../src/views/templates/header.php';

$report_type = $_GET['type'] ?? 'daily_sales'; // Default report

?>
<div class="container">
    <h1>Business Intelligence & Reporting</h1>

    <div class="report-nav">
        <a href="reports.php?type=daily_sales" class="button <?php echo ($report_type == 'daily_sales') ? 'active' : ''; ?>">Daily Sales Summary</a>
        <a href="reports.php?type=top_10_products" class="button <?php echo ($report_type == 'top_10_products') ? 'active' : ''; ?>">Top 10 Selling Products (Monthly)</a>
        <a href="reports.php?type=low_stock" class="button <?php echo ($report_type == 'low_stock') ? 'active' : ''; ?>">Low Stock Report</a>
    </div>

    <div class="report-content">
        <?php if ($report_type == 'daily_sales'): ?>
            <h2>Daily Sales Summary</h2>
            <?php
            // Using the SQL View vw_DailySalesSummary [cite: 67]
            // Note: Your vw_DailySalesSummary joins Employee e ON e.OutletID = e.OutletID which seems incorrect.
            // It should likely join Order to Customer, then somehow link to an Outlet if orders are outlet-specific.
            // Or if Employee places the order, then Employee's OutletID.
            // Assuming the view logic is corrected in your DB to:
            // CREATE VIEW vw_DailySalesSummary AS
            // SELECT o.OrderDate, COALESCE(emp.OutletID, 'N/A_Outlet') AS OutletID_Or_NA, SUM(od.UnitPrice * od.Quantity * (1 - od.Discount/100.0)) AS TotalSales
            // FROM `Order` o
            // JOIN OrderDetails od ON o.OrderID = od.OrderID
            // LEFT JOIN Employee emp ON o.ProcessedByEmployeeID = emp.EmployeeID -- Assuming an order is processed by an employee
            // GROUP BY o.OrderDate, COALESCE(emp.OutletID, 'N/A_Outlet');
            // For this example, I'll use the provided view structure.
            $sales_summary = db_query("SELECT OrderDate, OutletID, TotalSales FROM vw_DailySalesSummary ORDER BY OrderDate DESC, OutletID");
            if ($sales_summary && count($sales_summary) > 0):
            ?>
                <table>
                    <thead><tr><th>Order Date</th><th>Outlet ID</th><th>Total Sales</th></tr></thead>
                    <tbody>
                        <?php foreach ($sales_summary as $summary): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($summary['OrderDate']); ?></td>
                            <td><?php echo htmlspecialchars($summary['OutletID']); ?></td> <td><?php echo number_format($summary['TotalSales'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No daily sales summary data found.</p>
            <?php endif; ?>

        <?php elseif ($report_type == 'top_10_products'): ?>
            <h2>Top 10 Selling Products (Current Month)</h2>
            <?php
            // Using the SQL View vw_Top10SellingProductsMonthly [cite: 68]
            // GETDATE() is SQL Server. For MySQL, use CURDATE() or NOW().
            // Assuming the view is adapted for MySQL:
            // CREATE VIEW vw_Top10SellingProductsMonthly AS
            // SELECT p.ProductID, p.ProductName, SUM(od.Quantity) AS TotalSold
            // FROM OrderDetails od
            // JOIN `Order` o ON o.OrderID = od.OrderID
            // JOIN Product p ON p.ProductID = od.ProductID
            // WHERE MONTH(o.OrderDate) = MONTH(CURDATE()) AND YEAR(o.OrderDate) = YEAR(CURDATE())
            // GROUP BY p.ProductID, p.ProductName
            // ORDER BY TotalSold DESC
            // LIMIT 10;
            $top_products = db_query("SELECT ProductID, ProductName, TotalSold FROM vw_Top10SellingProductsMonthly"); // Assumes view is corrected for MySQL
            if ($top_products && count($top_products) > 0):
            ?>
                <table>
                    <thead><tr><th>Rank</th><th>Product ID</th><th>Product Name</th><th>Total Sold This Month</th></tr></thead>
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
                <p>No sales data found for the current month, or top products view not returning data.</p>
            <?php endif; ?>

        <?php elseif ($report_type == 'low_stock'): ?>
            <h2>Low Stock Report</h2>
            <?php
            // Calling the stored procedure sp_ReorderNotification [cite: 73, 74]
            $low_stock_items = db_query("CALL sp_ReorderNotification()");
            if ($low_stock_items && count($low_stock_items) > 0):
            ?>
                <table>
                    <thead><tr><th>Outlet ID</th><th>Product ID</th><th>Product Name</th><th>Current Quantity</th><th>Reorder Level</th></tr></thead>
                    <tbody>
                        <?php foreach ($low_stock_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['OutletID']); ?></td> <td><?php echo htmlspecialchars($item['ProductID']); ?></td>
                            <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                            <td style="color:red;"><?php echo htmlspecialchars($item['Quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['ReorderLevel']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>All products are currently above their reorder levels, or the reorder procedure returned no results.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php include '../src/views/templates/footer.php'; ?>