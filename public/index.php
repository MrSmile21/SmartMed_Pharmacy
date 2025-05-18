<?php
// public/index.php
require_once '../config/database.php';
require_once '../src/includes/functions.php';

session_start(); // If you use sessions for user login, messages, etc.

$page_title = "SmartMed Pharmacy Dashboard";
include '../src/views/templates/header.php';
?>

<div class="container">
    <h1>Welcome to SmartMed Pharmacy Management</h1>
    <p>Select an option from the dashboard to manage your pharmacy operations efficiently.</p>

    <div class="dashboard-cards">
        <a href="customers.php" class="dashboard-card">
            <div class="card-icon"><i class="fas fa-users"></i></div>
            <div class="card-content">
                <h3>Customers</h3>
                <p>Manage customer profiles and prescriptions</p>
            </div>
        </a>
        <a href="products.php" class="dashboard-card">
            <div class="card-icon"><i class="fas fa-capsules"></i></div>
            <div class="card-content">
                <h3>Products</h3>
                <p>Manage inventory and medication catalog</p>
            </div>
        </a>
        <a href="orders.php" class="dashboard-card">
            <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="card-content">
                <h3>Orders</h3>
                <p>Process and track customer orders</p>
            </div>
        </a>
        <a href="reports.php" class="dashboard-card">
            <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="card-content">
                <h3>Reports</h3>
                <p>View sales and inventory analytics</p>
            </div>
        </a>
    </div>
</div>

<?php include '../src/views/templates/footer.php'; ?>