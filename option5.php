<?php
//This is the fifth option for deleting a customer.
include "connecttodb.php";

$message = "";
$is_post = ($_SERVER["REQUEST_METHOD"] == "POST");
$customerList = []; // Array to hold customers for the dropdown

// --- 1. Load Customer List for Dropdown (Needed for both GET and POST) ---
$customerQuery = "SELECT cusID, firstname, lastname FROM customer ORDER BY lastname, firstname";
$customerResult = mysqli_query($connection, $customerQuery);

if ($customerResult) {
    while ($row = mysqli_fetch_assoc($customerResult)) {
        $customerList[] = $row;
    }
    mysqli_free_result($customerResult);
} else {
    $message = "<p class='error'>Error fetching customer list: " . mysqli_error($connection) . "</p>";
}


// --- 2. Handle Deletion (POST Request) ---
if ($is_post) {
    // Sanitize the selected ID (which is a CHAR(2) string)
    $cusID_to_delete = filter_input(INPUT_POST, 'cusID_to_delete', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $error = false;

    if (empty($cusID_to_delete)) {
        $message = "<p class='error'>Error: Please select a customer to delete.</p>";
        $error = true;
    }

    // Find the customer's full name for better error messages
    $found = false;
    $customerName = "";
    foreach ($customerList as $customer) {
        if ($customer['cusID'] === $cusID_to_delete) {
            $found = true;
            $customerName = $customer['firstname'] . " " . $customer['lastname'];
            break;
        }
    }

    if (!$found && !$error) {
        $message = "<p class='error'>Warning: Customer ID **$cusID_to_delete** not found in the list. Cannot proceed with deletion.</p>";
        $error = true;
    }

    // --- 3. Check for Existing Purchases (Foreign Key Check) ---
    if (!$error) {
        $purchase_check_query = "SELECT COUNT(*) FROM purchases WHERE cusID = ?";
        $stmt_check = mysqli_prepare($connection, $purchase_check_query);

        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "s", $cusID_to_delete);
            mysqli_stmt_execute($stmt_check);

            // Bind result to variable
            mysqli_stmt_bind_result($stmt_check, $purchaseCount);
            mysqli_stmt_fetch($stmt_check);
            mysqli_stmt_close($stmt_check);

            if ($purchaseCount > 0) {
                // REQUIRED: Nicely worded error message if they have purchases
                $message = "<p class='error'>Deletion Blocked: **$customerName** (ID: $cusID_to_delete) has **$purchaseCount** purchase(s) on record. We cannot delete a customer while they have an active purchase history, as this would violate our sales records. Please remove their purchases first if deletion is absolutely necessary.</p>";
                $error = true;
            }
        } else {
            $message = "<p class='error'>Database Preparation Error (Purchase Check): " . mysqli_error($connection) . "</p>";
            $error = true;
        }
    }

    // --- 4. Perform Deletion ---
    if (!$error) {
        $delete_query = "DELETE FROM customer WHERE cusID = ?";
        $stmt_delete = mysqli_prepare($connection, $delete_query);

        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "s", $cusID_to_delete);

            if (mysqli_stmt_execute($stmt_delete)) {
                $message = "<p class='success'>Success! Customer **$customerName** (ID: **$cusID_to_delete**) has been permanently deleted.</p>";
                // Re-fetch customer list to update the dropdown immediately
                $customerList = [];
                $customerQuery = "SELECT cusID, firstname, lastname FROM customer ORDER BY lastname, firstname"; // Redefine query
                $customerResult = mysqli_query($connection, $customerQuery);
                if ($customerResult) {
                    while ($row = mysqli_fetch_assoc($customerResult)) {
                        $customerList[] = $row;
                    }
                    mysqli_free_result($customerResult);
                }
            } else {
                $message = "<p class='error'>Deletion failed (Database Error): " . mysqli_stmt_error($stmt_delete) . "</p>";
                $error = true;
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            $message = "<p class='error'>Database Preparation Error (Delete): " . mysqli_error($connection) . "</p>";
            $error = true;
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
    <title>Delete Customer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainmenu.css">
    <style>
        .form-container {
            background-color: rgba(0,0,0,0.1);
            padding: 25px;
            border-radius: 8px;
            max-width: 500px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #eb3434;
            background-color: white;
            color: #333;
            box-sizing: border-box;
            font-family: 'Quicksand', sans-serif;
            font-size: 16px;
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
        a {
            color: #ffddaa;
            text-decoration: underline;
        }
        a:hover {
            color: white;
        }
        /* Custom style for the delete button to make it stand out as destructive */
        input[type=submit] {
            background-color: #ff4444; /* Brighter red */
        }
        input[type=submit]:hover {
            background-color: #cc0000; /* Darker red hover */
        }
    </style>
</head>
<body>

<h1>Delete Customer</h1>
<p>Select a customer from the list below to permanently delete them from the database.</p>
<hr>

<a href="mainmenu.php" style="font-weight: bold;"><< Back to Main Menu</a>

<!-- Display message -->
<?php if (!empty($message)) { echo $message; } ?>

<div class="form-container">
    <form method="post" action="option5.php">

        <div class="form-group">
            <label for="cusID_to_delete">Select Customer to Delete:</label>
            <select id="cusID_to_delete" name="cusID_to_delete" required>
                <?php if (empty($customerList)): ?>
                    <option value="">-- No Customers Found --</option>
                <?php else: ?>
                    <option value="">-- Select a Customer --</option>
                    <?php foreach ($customerList as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['cusID']); ?>">
                            <?php echo htmlspecialchars($customer['lastname'] . ", " . $customer['firstname']) . " (ID: " . htmlspecialchars($customer['cusID']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <input type="submit" value="Delete Selected Customer" <?php echo empty($customerList) ? 'disabled' : ''; ?>>
    </form>
</div>

</body>
</html>