<?php
//This is the sixth option for listing customers who bought more than a specified quantity of a product.
include "connecttodb.php";

$message = "";
$products = []; // Array to hold products for the dropdown
$results = []; // Array to hold the final customer results
$selected_product_desc = "";
$user_quantity = "";

// --- 1. Load Product List for Dropdown (Needed for both GET and POST) ---
$productQuery = "SELECT prodID, description FROM product ORDER BY description";
$productResult = mysqli_query($connection, $productQuery);

if ($productResult) {
    while ($row = mysqli_fetch_assoc($productResult)) {
        $products[] = $row;
    }
    mysqli_free_result($productResult);
} else {
    $message .= "<p class='error'>Error fetching product list: " . mysqli_error($connection) . "</p>";
}

// --- 2. Handle Query Execution (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $selected_prodID = filter_input(INPUT_POST, 'prodID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    $error = false;

    if (empty($selected_prodID) || $user_quantity === false || $user_quantity < 0) {
        $message .= "<p class='error'>Error: Please select a product and enter a valid non-negative quantity.</p>";
        $error = true;
    }

    if (!$error) {
        // Find the description of the selected product for the heading
        foreach ($products as $product) {
            if ($product['prodID'] === $selected_prodID) {
                $selected_product_desc = $product['description'];
                break;
            }
        }

        // The requirement is to show customers who bought MORE THAN the given quantity.
        // E.g., if user enters 8, we look for quantity > 8 (i.e., 9 or more).

        $query = "
            SELECT
                c.firstname,
                c.lastname,
                p.quantity,
                pr.description
            FROM
                customer c
            JOIN
                purchases p ON c.cusID = p.cusID
            JOIN
                product pr ON p.prodID = pr.prodID
            WHERE
                pr.prodID = ?
            AND
                p.quantity > ?
            ORDER BY
                p.quantity DESC, c.lastname, c.firstname
        ";

        $stmt = mysqli_prepare($connection, $query);

        if ($stmt) {
            // Bind the Product ID (string) and Quantity (integer)
            mysqli_stmt_bind_param($stmt, "si", $selected_prodID, $user_quantity);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $results[] = $row;
                    }
                    $message .= "<p class='success'>Query successful. Showing " . count($results) . " customer(s) who purchased more than **$user_quantity** unit(s) of **$selected_product_desc**.</p>";
                } else {
                    $message .= "<p class='warning'>No customers found who purchased more than **$user_quantity** unit(s) of **$selected_product_desc**.</p>";
                }
                mysqli_free_result($result);
            } else {
                $message .= "<p class='error'>Query execution failed: " . mysqli_error($connection) . "</p>";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message .= "<p class='error'>Database Preparation Error: " . mysqli_error($connection) . "</p>";
        }
    }
}

// Close the database connection
if (isset($connection)) {
    mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Purchase Query</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainmenu.css">
    <style>
        .form-container {
            background-color: rgba(0,0,0,0.1);
            padding: 25px;
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .form-group > div {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .form-group select, .form-group input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #999;
            background-color: white;
            color: #333;
            box-sizing: border-box;
            font-family: 'Quicksand', sans-serif;
            font-size: 16px;
        }
        .form-group input[type="number"] {
            text-align: right;
        }
        .error {
            color: #ffcccc;
            background-color: #a00000;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .success {
            color: #ccffcc;
            background-color: #008000;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .warning {
            color: #ffeeaa;
            background-color: #aa7700;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        a {
            color: #ffddaa;
            text-decoration: underline;
        }
        a:hover {
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.9);
        }
        th, td {
            border: 1px solid #444;
            padding: 12px;
            text-align: left;
            color: #333;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Customer Purchase Query</h1>
<p>Find customers who bought more than a specified quantity of a particular product.</p>
<hr>

<a href="mainmenu.php" style="font-weight: bold;"><< Back to Main Menu</a>

<!-- Display message -->
<?php if (!empty($message)) { echo $message; } ?>

<div class="form-container">
    <form method="post" action="option6.php">

        <div class="form-group">
            <div>
                <label for="prodID">Select Product:</label>
                <select id="prodID" name="prodID" required>
                    <?php if (empty($products)): ?>
                        <option value="">-- No Products Found --</option>
                    <?php else: ?>
                        <option value="">-- Select a Product --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo htmlspecialchars($product['prodID']); ?>"
                                <?php echo (isset($selected_prodID) && $selected_prodID == $product['prodID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="quantity">Quantity Threshold (Find customers who bought **MORE THAN** this number):</label>
                <input type="number" id="quantity" name="quantity" min="0" required
                       value="<?php echo htmlspecialchars($user_quantity); ?>">
            </div>
        </div>

        <input type="submit" value="Search Customers">
    </form>
</div>

<?php if (!empty($results)): ?>
    <h2>Customers Who Exceeded the Threshold</h2>
    <table>
        <thead>
        <tr>
            <th>Customer Name</th>
            <th>Product Description</th>
            <th>Quantity Purchased</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $customer): ?>
            <tr>
                <td><?php echo htmlspecialchars($customer['lastname'] . ", " . $customer['firstname']); ?></td>
                <td><?php echo htmlspecialchars($customer['description']); ?></td>
                <td><?php echo htmlspecialchars($customer['quantity']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>