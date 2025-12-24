<?php
/* This is the option 1 file to list all customers and their purchase history.
And provide a link to the customer_purchases.php file
to view the purchase history of a specific customer.*/
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Customers</title>
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

<?php
include "connecttodb.php";
?>

<h1>Our Customers</h1>
<p>Showing all customers, ordered by last name. Click on a customer's name to see their purchase history.</p>
<hr>

<a href="mainmenu.php" style="font-weight: bold;"><< Back to Main Menu</a>

<table>
    <thead>
    <tr>
        <th>Select</th>
        <th>Customer ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>City</th>
        <th>Phone Number</th>
        <th>Agent ID</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Query to get all customers ordered by last name
    $query = "SELECT * FROM customer ORDER BY lastname";

    $result = mysqli_query($connection, $query);

    if (!$result) {
        die("Database query failed.");
    }

    // Loop through the results and display them
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        // Create a link using the customer's name, passing their cusID in the URL
        echo "<td><a href='customerpurchases.php?cusID=" . urlencode($row["cusID"]) . "'>" . $row["firstname"] . " " . $row["lastname"] . "</a></td>";
        echo "<td>" . $row["cusID"] . "</td>";
        echo "<td>" . $row["firstname"] . "</td>";
        echo "<td>" . $row["lastname"] . "</td>";
        echo "<td>" . $row["city"] . "</td>";
        echo "<td>" . $row["phonenumber"] . "</td>";
        echo "<td>" . $row["agentID"] . "</td>";
        echo "</tr>";
    }

    // Free the result set
    mysqli_free_result($result);
    ?>
    </tbody>
</table>

<?php
// Close the database connection
mysqli_close($connection);
?>

</body>
</html>