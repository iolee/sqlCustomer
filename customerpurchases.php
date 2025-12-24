<?php
// This is the page that displays the purchases by a selected customer.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Purchases</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainmenu.css">
    <style>
        /* Add some specific styles for the table, codes generated from Gemini */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid white;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #eb3434;
        }
        tr:nth-child(even) {
            background-color: rgba(0,0,0,0.1);
        }
        a {
            color: #ffddaa;
            text-decoration: underline;
        }
        a:hover {
            color: white;
        }
    </style>
</head>
<body>

<a href="option1.php" style="font-weight: bold;"><< Back to Customer List</a>
<hr>

<?php
include "connecttodb.php";

// Get the customer ID from the URL
$customerID = $_GET['cusID'];

// --- 1. Get Customer's Name ---
$customerQuery = "SELECT firstname, lastname FROM customer WHERE cusID = " . $customerID;
$customerResult = mysqli_query($connection, $customerQuery);
if (!$customerResult) {
    die("Database query failed.");
}
$customer = mysqli_fetch_assoc($customerResult);
echo "<h1>Purchase History for " . $customer["firstname"] . " " . $customer["lastname"] . "</h1>";
mysqli_free_result($customerResult);


// --- 2. Get Customer's Purchases ---
$purchaseQuery = "
    SELECT 
        prod.prodID,
        prod.description, 
        prod.cost, 
        purch.quantity, 
        (prod.cost * purch.quantity) AS total_spent
    FROM 
        purchases AS purch
    JOIN 
        product AS prod ON purch.prodID = prod.prodID
    WHERE 
        purch.cusID = " . $customerID;

$purchaseResult = mysqli_query($connection, $purchaseQuery);
if (!$purchaseResult) {
    die("Database query failed.");
}
?>

<table>
    <thead>
    <tr>
        <th>Product ID</th>
        <th>Product Description</th>
        <th>Quantity</th>
        <th>Cost per Unit</th>
        <th>Total Spent</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (mysqli_num_rows($purchaseResult) == 0) {
        echo "<tr><td colspan='4'>This customer has not made any purchases.</td></tr>";
    } else {
        // Loop through the results and display them
        while ($row = mysqli_fetch_assoc($purchaseResult)) {
            echo "<tr>";
            echo "<td>" . $row["prodID"] . "</td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>" . $row["quantity"] . "</td>";
            echo "<td>$" . number_format($row["cost"], 2) . "</td>";
            echo "<td>$" . number_format($row["total_spent"], 2) . "</td>";
            echo "</tr>";
        }
    }

    // Free the result set
    mysqli_free_result($purchaseResult);
    ?>
    </tbody>
</table>

<?php
// Close the database connection
mysqli_close($connection);
?>

</body>
</html>
