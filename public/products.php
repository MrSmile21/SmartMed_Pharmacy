<?php
// public/products.php
require_once '../config/database.php';
require_once '../src/includes/functions.php';

$page_title = "Manage Products";
include '../src/views/templates/header.php';

$action = $_GET['action'] ?? 'list'; // Default action
$product_id = $_GET['id'] ?? null;

?>
<div class="container">
    <h1>Product Management</h1>

    <?php if ($action == 'list'): ?>
        <h2>All Products with Stock Info</h2>
        <?php
        // Joining Product with Stock. Assuming one main stock record per product for simplicity here,
        // or sum stock across outlets if your structure is per outlet.
        // The Stock table in your schema has OutletID and ProductID as PK[cite: 54].
        // For a general "all products with stock info", we might list products
        // and then offer a way to see stock per outlet, or show total stock.
        // Let's show total stock if available, and prescription status.

        $sql = "
            SELECT
                p.ProductID,
                p.ProductName,
                p.Category,
                p.UnitPrice,
                p.RequiresPrescription, /* This is a BIT/BOOLEAN [cite: 33, 51] */
                COALESCE(SUM(s.Quantity), 0) AS TotalStock
            FROM Product p
            LEFT JOIN Stock s ON p.ProductID = s.ProductID
            GROUP BY p.ProductID, p.ProductName, p.Category, p.UnitPrice, p.RequiresPrescription
            ORDER BY p.ProductName
        ";
        $products = db_query($sql);

        if ($products && count($products) > 0):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th>Total Stock</th>
                        <th>Prescription Required?</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['ProductID']); ?></td>
                        <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($product['Category']); ?></td>
                        <td><?php echo number_format($product['UnitPrice'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['TotalStock']); ?></td>
                        <td>
                            <?php
                                // Using the UDF fn_RequiresPrescription.
                                // The UDF fn_RequiresPrescription(@ProductID) returns BIT[cite: 65, 66].
                                // We can call it directly if the DB user has EXECUTE permissions.
                                // Alternatively, we already have p.RequiresPrescription.
                                echo $product['RequiresPrescription'] ? "Yes" : "No";

                                // If you wanted to call the UDF explicitly for each row (less efficient than selecting the column):
                                /*
                                $prescriptionResult = db_query("SELECT dbo.fn_RequiresPrescription(?) AS Requires", [$product['ProductID']], "i");
                                if ($prescriptionResult && isset($prescriptionResult[0]['Requires'])) {
                                    echo $prescriptionResult[0]['Requires'] ? "Yes" : "No";
                                } else {
                                    echo "N/A";
                                }
                                */
                            ?>
                        </td>
                         <td>
                            <a href="products.php?action=edit&id=<?php echo $product['ProductID']; ?>">Edit</a> |
                            <a href="products.php?action=delete&id=<?php echo $product['ProductID']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
        <p><a href="products.php?action=add" class="button">Add New Product</a></p>

    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <?php
        $product_data = ['ProductName' => '', 'Description' => '', 'Category' => '', 'RequiresPrescription' => 0, 'UnitPrice' => '', 'ExpiryDate' => null];
        if ($action == 'edit' && $product_id) {
            $result = db_query("SELECT ProductID, ProductName, Description, Category, RequiresPrescription, UnitPrice, ExpiryDate FROM Product WHERE ProductID = ?", [$product_id], "i");
            if ($result && count($result) > 0) {
                $product_data = $result[0];
            } else {
                echo "<p class='error'>Product not found.</p>"; $action = 'list';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $productName = trim($_POST['productName']);
            $description = trim($_POST['description']);
            $category = trim($_POST['category']);
            $requiresPrescription = isset($_POST['requiresPrescription']) ? 1 : 0;
            $unitPrice = (float)$_POST['unitPrice'];
            $expiryDate = !empty($_POST['expiryDate']) ? $_POST['expiryDate'] : null;

            if (empty($productName) || $unitPrice <= 0) {
                 echo "<p class='error'>Product Name and a valid Unit Price are required.</p>";
            } else {
                if ($action == 'add') {
                    $sql = "INSERT INTO Product (ProductName, Description, Category, RequiresPrescription, UnitPrice, ExpiryDate) VALUES (?, ?, ?, ?, ?, ?)";
                    $params = [$productName, $description, $category, $requiresPrescription, $unitPrice, $expiryDate];
                    $types = "sssids";
                     if (db_query($sql, $params, $types)) {
                        $new_product_id = $mysqli->insert_id; // Get last inserted ID
                        // Optionally, add initial stock record(s) for this new product in the Stock table
                        // For example, for each outlet:
                        // $outlets = db_query("SELECT OutletID FROM Outlet");
                        // foreach ($outlets as $outlet) {
                        //    db_query("INSERT INTO Stock (OutletID, ProductID, Quantity, ReorderLevel) VALUES (?, ?, 0, 10)", [$outlet['OutletID'], $new_product_id], "ii");
                        // }
                        echo "<p class='success'>Product added successfully!</p>";
                        $action = 'list';
                        echo '<meta http-equiv="refresh" content="2;url=products.php">';
                    } else {
                        echo "<p class='error'>Error adding product.</p>";
                    }
                } elseif ($action == 'edit' && $product_id) {
                    $sql = "UPDATE Product SET ProductName = ?, Description = ?, Category = ?, RequiresPrescription = ?, UnitPrice = ?, ExpiryDate = ? WHERE ProductID = ?";
                    $params = [$productName, $description, $category, $requiresPrescription, $unitPrice, $expiryDate, $product_id];
                    $types = "sssidssi"; // Corrected type string for ExpiryDate as string, ProductID as int
                     if (db_query($sql, $params, $types)) {
                        echo "<p class='success'>Product updated successfully!</p>";
                        $action = 'list';
                        echo '<meta http-equiv="refresh" content="2;url=products.php">';
                    } else {
                        echo "<p class='error'>Error updating product.</p>";
                    }
                }
            }
            if ($action !== 'list') { // Reload data if staying on form
                if ($action == 'edit' && $product_id) {
                    $result = db_query("SELECT ProductID, ProductName, Description, Category, RequiresPrescription, UnitPrice, ExpiryDate FROM Product WHERE ProductID = ?", [$product_id], "i");
                    if ($result && count($result) > 0) $product_data = $result[0];
                }
            }
        }

        if ($action == 'add' || $action == 'edit'):
        ?>
        <h2><?php echo $action == 'add' ? 'Add New' : 'Edit'; ?> Product</h2>
        <form action="products.php?action=<?php echo $action; ?><?php echo $product_id ? '&id='.$product_id : ''; ?>" method="POST">
            <div>
                <label for="productName">Product Name:</label>
                <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($product_data['ProductName']); ?>" required>
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($product_data['Description']); ?></textarea>
            </div>
            <div>
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product_data['Category']); ?>">
            </div>
            <div>
                <label for="unitPrice">Unit Price:</label>
                <input type="number" step="0.01" id="unitPrice" name="unitPrice" value="<?php echo htmlspecialchars($product_data['UnitPrice']); ?>" required>
            </div>
            <div>
                <label for="expiryDate">Expiry Date:</label>
                <input type="date" id="expiryDate" name="expiryDate" value="<?php echo htmlspecialchars($product_data['ExpiryDate'] ?? ''); ?>">
            </div>
            <div>
                <input type="checkbox" id="requiresPrescription" name="requiresPrescription" value="1" <?php echo !empty($product_data['RequiresPrescription']) ? 'checked' : ''; ?>>
                <label for="requiresPrescription">Requires Prescription</label>
            </div>
            <button type="submit"><?php echo $action == 'add' ? 'Add' : 'Update'; ?> Product</button>
            <a href="products.php" class="button secondary">Cancel</a>
        </form>
        <?php endif; ?>

    <?php elseif ($action == 'delete' && $product_id): ?>
        <?php
        // The trigger trg_LogProductDeletion will log this [cite: 62]
        // Consider checking for stock or existing order details before allowing delete, or handle with DB constraints.
        $sqlCheckStock = "SELECT SUM(Quantity) as TotalStock FROM Stock WHERE ProductID = ?";
        $stockResult = db_query($sqlCheckStock, [$product_id], "i");

        $sqlCheckOrders = "SELECT COUNT(*) as OrderCount FROM OrderDetails WHERE ProductID = ?";
        $orderResult = db_query($sqlCheckOrders, [$product_id], "i");

        if ($stockResult && $stockResult[0]['TotalStock'] > 0) {
            echo "<p class='error'>Cannot delete product. It is still in stock.</p>";
        } elseif ($orderResult && $orderResult[0]['OrderCount'] > 0) {
             echo "<p class='error'>Cannot delete product. It exists in past orders. Consider marking as 'discontinued' instead.</p>";
        }
        else {
            $sql = "DELETE FROM Product WHERE ProductID = ?";
            if (db_query($sql, [$product_id], "i")) {
                echo "<p class='success'>Product deleted successfully! (Audit log created)</p>";
            } else {
                echo "<p class='error'>Error deleting product. It might be referenced in other tables (e.g., prescriptions, supply).</p>";
            }
        }
        echo '<p><a href="products.php">Back to Product List</a></p>';
        echo '<meta http-equiv="refresh" content="3;url=products.php">';
        ?>
    <?php endif; ?>

</div>
<?php include '../src/views/templates/footer.php'; ?>