<?php
// This is the second option for sorting the products.
include "connecttodb.php";

// --- 1. Define Sort Options ---
// Default values
$sort_by_default = 'description';
$order_by_default = 'asc';

// Whitelist of allowed sorting columns and orders to prevent SQL injection
$sort_whitelist = ['description', 'cost'];
$order_whitelist = ['asc', 'desc'];

// Get sort parameters from URL, or use defaults
$sort_by = $_GET['sort'] ?? $sort_by_default;
$order_by = $_GET['order'] ?? $order_by_default;

// Validate against whitelist
if (!in_array($sort_by, $sort_whitelist)) {
    $sort_by = $sort_by_default;
}
if (!in_array($order_by, $order_whitelist)) {
    $order_by = $order_by_default;
}

// --- 2. Build and Run Query ---
$query = "SELECT * FROM product ORDER BY " . $sort_by . " " . $order_by;
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Database query failed.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Products</title>
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

        /* Styles for the sorting form */
        .sort-form {
            background-color: rgba(0,0,0,0.1);
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px; /* Space between form elements */
        }
        .sort-form label {
            font-weight: bold;
        }
        .sort-form select {
            padding: 5px;
            font-family: 'Quicksand', sans-serif;
            font-size: 16px;
            border-radius: 4px;
            border: none;
        }
        .sort-form input[type=submit] {
            width: auto; /* Override full-width */
            padding: 8px 20px;
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>

<h1>Our Products</h1>
<p>Showing all available products. Use the form to re-sort the list.</p>
<hr>

<a href="mainmenu.php" style="font-weight: bold;"><< Back to Main Menu</a>

<!-- Sorting Form -->
<form class="sort-form" method="get" action="option2.php">
    <label for="sort">Sort By:</label>
    <select name="sort" id="sort">
        <option value="description" <?php echo ($sort_by == 'description') ? 'selected' : ''; ?>>Description</option>
        <option value="cost" <?php echo ($sort_by == 'cost') ? 'selected' : ''; ?>>Price</option>
    </select>

    <label for="order">Order:</label>
    <select name="order" id="order">
        <option value="asc" <?php echo ($order_by == 'asc') ? 'selected' : ''; ?>>Ascending</option>
        <option value="desc" <?php echo ($order_by == 'desc') ? 'selected' : ''; ?>>Descending</option>
    </select>

    <input type="submit" value="Sort">
</form>

<!-- Products Table -->
<table>
    <thead>
    <tr>
        <th>Product ID</th>
        <th>Description</th>
        <th>Price</th>
        <th>Quantity on Hand</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (mysqli_num_rows($result) == 0) {
        echo "<tr><td colspan='3'>No products found.</td></tr>";
    } else {
        // Loop through the results and display them
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row["prodID"] . "</td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>$" . number_format($row["cost"], 2) . "</td>";
            echo "<td>" . $row["quantityonhand"] . "</td>";
            echo "</tr>";
        }
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