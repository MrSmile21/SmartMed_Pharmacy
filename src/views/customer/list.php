<?php
/**
 * src/views/customer/list.php
 *
 * Displays a list of customers.
 * This script would be included by public/customers.php when action is 'list'.
 * Assumes $customers variable is pre-populated by the controller logic.
 */

// Ensure $customers is set and is an array, even if empty
if (!isset($customers) || !is_array($customers)) {
    $customers = [];
}
?>
<div class="actions-bar">
    <a href="customers.php?action=add" class="button">Add New Customer</a>
</div>

<h2>All Customers</h2>
<?php if (isset($_SESSION['message'])): ?>
    <div class="message <?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?>">
        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
    </div>
<?php endif; ?>

<?php if (count($customers) > 0): ?>
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
                    <a href="customers.php?action=edit&id=<?php echo $customer['CustomerID']; ?>" class="button-small edit">Edit</a>
                    <a href="customers.php?action=delete&id=<?php echo $customer['CustomerID']; ?>" class="button-small delete" onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">Delete</a>
                    </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No customers found. <a href="customers.php?action=add">Add one now!</a></p>
<?php endif; ?>
