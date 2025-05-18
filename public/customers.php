<?php
// public/customers.php
require_once '../config/database.php';
require_once '../src/includes/functions.php';

$page_title = "Manage Customers";
include '../src/views/templates/header.php';

$action = $_GET['action'] ?? 'list'; // Default action is 'list'
$customer_id = $_GET['id'] ?? null;

?>
<div class="container">
    <h1>Customer Management</h1>

    <?php if ($action == 'list'): ?>
        <a href="customers.php?action=add" class="button">Add New Customer</a>
        <h2>All Customers</h2>
        <?php
        $customers = db_query("SELECT CustomerID, FullName, Email, Phone, LoyaltyPoints FROM Customer ORDER BY FullName");
        if ($customers && count($customers) > 0):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Loyalty Points</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['CustomerID']); ?></td>
                        <td><?php echo htmlspecialchars($customer['FullName']); ?></td>
                        <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['Phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['LoyaltyPoints']); ?></td>
                        <td>
                            <a href="customers.php?action=edit&id=<?php echo $customer['CustomerID']; ?>">Edit</a> |
                            <a href="customers.php?action=delete&id=<?php echo $customer['CustomerID']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No customers found.</p>
        <?php endif; ?>

    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <?php
        $customer_data = ['FullName' => '', 'DOB' => '', 'Gender' => '', 'Phone' => '', 'Email' => '', 'Address' => '', 'LoyaltyPoints' => 0];
        if ($action == 'edit' && $customer_id) {
            $result = db_query("SELECT * FROM Customer WHERE CustomerID = ?", [$customer_id], "i");
            if ($result && count($result) > 0) {
                $customer_data = $result[0];
            } else {
                echo "<p class='error'>Customer not found.</p>";
                $action = 'list'; // Revert to list if not found
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize and validate input
            $fullName = trim($_POST['fullName']);
            $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
            $gender = $_POST['gender'] ?? null;
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $address = trim($_POST['address']);
            $loyaltyPoints = (int)($_POST['loyaltyPoints'] ?? 0);

            if (empty($fullName) || empty($email)) {
                echo "<p class='error'>Full Name and Email are required.</p>";
            } else {
                if ($action == 'add') {
                    $sql = "INSERT INTO Customer (FullName, DOB, Gender, Phone, Email, Address, LoyaltyPoints) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $params = [$fullName, $dob, $gender, $phone, $email, $address, $loyaltyPoints];
                    $types = "ssssssi";
                    if (db_query($sql, $params, $types)) {
                        echo "<p class='success'>Customer added successfully!</p>";
                        $action = 'list'; // Show list after adding
                        echo '<meta http-equiv="refresh" content="2;url=customers.php">'; // Redirect
                    } else {
                        echo "<p class='error'>Error adding customer. Phone or Email might already exist.</p>";
                    }
                } elseif ($action == 'edit' && $customer_id) {
                    $sql = "UPDATE Customer SET FullName = ?, DOB = ?, Gender = ?, Phone = ?, Email = ?, Address = ?, LoyaltyPoints = ? WHERE CustomerID = ?";
                    $params = [$fullName, $dob, $gender, $phone, $email, $address, $loyaltyPoints, $customer_id];
                    $types = "ssssssii";
                    if (db_query($sql, $params, $types)) {
                        echo "<p class='success'>Customer updated successfully!</p>";
                        $action = 'list'; // Show list after editing
                         echo '<meta http-equiv="refresh" content="2;url=customers.php">'; // Redirect
                    } else {
                        echo "<p class='error'>Error updating customer. Phone or Email might already exist for another customer.</p>";
                    }
                }
            }
             // To avoid re-submission issues and to show the list or updated form
            if ($action !== 'list') {
                 // If staying on form, reload data
                if ($action == 'edit' && $customer_id) {
                    $result = db_query("SELECT * FROM Customer WHERE CustomerID = ?", [$customer_id], "i");
                    if ($result && count($result) > 0) $customer_data = $result[0];
                }
            } else {
                 // If action changed to list, no need for form
                 // The list will be rendered below if $action is 'list'
            }
        }
        // Render form if still in add/edit mode
        if ($action == 'add' || $action == 'edit'):
        ?>
        <h2><?php echo $action == 'add' ? 'Add New' : 'Edit'; ?> Customer</h2>
        <form action="customers.php?action=<?php echo $action; ?><?php echo $customer_id ? '&id='.$customer_id : ''; ?>" method="POST">
            <div>
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($customer_data['FullName']); ?>" required>
            </div>
            <div>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($customer_data['DOB']); ?>">
            </div>
            <div>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="M" <?php echo ($customer_data['Gender'] == 'M') ? 'selected' : ''; ?>>Male</option>
                    <option value="F" <?php echo ($customer_data['Gender'] == 'F') ? 'selected' : ''; ?>>Female</option>
                    <option value="O" <?php echo ($customer_data['Gender'] == 'O') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div>
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer_data['Phone']); ?>">
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer_data['Email']); ?>" required>
            </div>
            <div>
                <label for="address">Address:</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($customer_data['Address']); ?></textarea>
            </div>
            <div>
                <label for="loyaltyPoints">Loyalty Points:</label>
                <input type="number" id="loyaltyPoints" name="loyaltyPoints" value="<?php echo htmlspecialchars($customer_data['LoyaltyPoints']); ?>" min="0">
            </div>
            <button type="submit"><?php echo $action == 'add' ? 'Add' : 'Update'; ?> Customer</button>
            <a href="customers.php" class="button secondary">Cancel</a>
        </form>
        <?php endif; // End form rendering ?>

    <?php elseif ($action == 'delete' && $customer_id): ?>
        <?php
        // You might want to check for related records (e.g., Orders) before deleting a customer
        // or handle it via database constraints (ON DELETE SET NULL or ON DELETE RESTRICT)
        $sql = "DELETE FROM Customer WHERE CustomerID = ?";
        if (db_query($sql, [$customer_id], "i")) {
            echo "<p class='success'>Customer deleted successfully!</p>";
        } else {
            echo "<p class='error'>Error deleting customer. They may have existing orders or other related data.</p>";
        }
        echo '<p><a href="customers.php">Back to Customer List</a></p>';
        echo '<meta http-equiv="refresh" content="2;url=customers.php">'; // Redirect
        ?>
    <?php endif; ?>
</div>
<?php include '../src/views/templates/footer.php'; ?>