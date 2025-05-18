<?php
// config/database.php

define('DB_HOST', 'localhost'); // Or your MySQL host
define('DB_USER', 'root');
define('DB_PASS', 'SmilE@731');
define('DB_NAME', 'smart_med'); // Your database name

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set charset to UTF-8 for consistency
$mysqli->set_charset("utf8mb4");

function db_query($sql, $params = [], $types = "") {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        error_log("MySQL prepare error: " . $mysqli->error . " SQL: " . $sql);
        return false;
    }
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log("MySQL execute error: " . $stmt->error . " SQL: " . $sql);
        return false;
    }
    if (stripos(trim($sql), "SELECT") === 0 || stripos(trim($sql), "SHOW") === 0 || stripos(trim($sql), "CALL") === 0 && strpos($sql, 'sp_ReorderNotification') !== false) {
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("MySQL get_result error: " . $stmt->error . " SQL: " . $sql);
            return false;
        }
        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();
        return $data;
    } elseif (stripos(trim($sql), "INSERT") === 0) {
        $last_id = $mysqli->insert_id;
        $stmt->close();
        return $last_id > 0 ? $last_id : true; // Return last insert ID or true for success
    } else {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows >= 0; // Return true if 0 or more rows affected, false on error
    }
}

function db_call_sp_place_order($customerID, $deliveryMethod, $products) {
    global $mysqli;

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Prepare the products data for the stored procedure
        // MySQL doesn't directly support table-valued parameters like SQL Server.
        // We'll insert into a temporary table or pass as a delimited string if the SP is adapted.
        // For this example, assuming sp_PlaceOrder in MySQL would handle products differently,
        // perhaps by iterating through them in PHP and inserting into OrderDetails.
        // The SQL Server syntax @Products TABLE (...) won't work directly.

        // Let's assume sp_PlaceOrder is modified to accept individual product inserts
        // or that we manage the transaction and individual inserts here.

        // 1. Insert into [Order] table
        $orderSQL = "INSERT INTO `Orders` (CustomerID, OrderDate, PaymentStatus, DeliveryMethod) VALUES (?, CURDATE(), 'Paid', ?)";
        $stmtOrder = $mysqli->prepare($orderSQL);
        if ($stmtOrder === false) throw new Exception("Prepare Order failed: " . $mysqli->error);
        $stmtOrder->bind_param("is", $customerID, $deliveryMethod);
        if (!$stmtOrder->execute()) throw new Exception("Execute Order failed: " . $stmtOrder->error);
        $orderID = $mysqli->insert_id;
        $stmtOrder->close();

        if (!$orderID) throw new Exception("Failed to create order.");

        // 2. Insert into OrderDetails
        $orderDetailsSQL = "INSERT INTO OrderDetails (OrderID, ProductID, Quantity, UnitPrice) VALUES (?, ?, ?, ?)";
        $stmtDetails = $mysqli->prepare($orderDetailsSQL);
        if ($stmtDetails === false) throw new Exception("Prepare OrderDetails failed: " . $mysqli->error);

        foreach ($products as $product) {
            $stmtDetails->bind_param("iiid", $orderID, $product['ProductID'], $product['Quantity'], $product['UnitPrice']);
            if (!$stmtDetails->execute()) throw new Exception("Execute OrderDetails failed for ProductID " . $product['ProductID'] . ": " . $stmtDetails->error);
        }
        $stmtDetails->close();

        // 3. Insert into Delivery (as per your sp_PlaceOrder logic)
        $deliverySQL = "INSERT INTO Delivery (OrderID, DeliveryDate, DeliveryStatus, DeliveryAddress) VALUES (?, CURDATE(), 'Shipped', 'Use Customer Address')"; // Assuming this is the logic
        $stmtDelivery = $mysqli->prepare($deliverySQL);
        if ($stmtDelivery === false) throw new Exception("Prepare Delivery failed: " . $mysqli->error);
        $stmtDelivery->bind_param("i", $orderID);
        if (!$stmtDelivery->execute()) throw new Exception("Execute Delivery failed: " . $stmtDelivery->error);
        $stmtDelivery->close();

        // Commit transaction
        $mysqli->commit();
        return $orderID;

    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        return false;
    }
}

?>