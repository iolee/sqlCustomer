<?php
// This is the third option for inserting new purchases.
include "connecttodb.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Sanitize inputs.
    $cusID = filter_input(INPUT_POST, 'cusID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $prodID = filter_input(INPUT_POST, 'prodID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $quantity_raw = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $quantity = (int)$quantity_raw; // The amount being bought right now

    $error = false;

    // Basic validation
    if (empty($cusID) || empty($prodID) || $quantity < 1) {
        $message = "<p class='error'>Error: All fields are required, and Quantity must be a positive number.</p>";
        $error = true;
    } else if (strlen($cusID) !== 2 || strlen($prodID) !== 2) {
        $message = "<p class='error'>Error: IDs must be exactly 2 characters long.</p>";
        $error = true;
    }

    // --- Start Transaction Logic ---
    if (!$error) {
        mysqli_autocommit($connection, FALSE);

        // 2. Validate Customer Existence
        $cus_check_query = "SELECT cusID FROM customer WHERE cusID = ?";
        $stmt_cus = mysqli_prepare($connection, $cus_check_query);

        if ($stmt_cus) {
            mysqli_stmt_bind_param($stmt_cus, "s", $cusID);
            mysqli_stmt_execute($stmt_cus);
            mysqli_stmt_store_result($stmt_cus);

            if (mysqli_stmt_num_rows($stmt_cus) == 0) {
                $message = "<p class='error'>Error: Invalid Customer ID ($cusID).</p>";
                $error = true;
            }
            mysqli_stmt_close($stmt_cus);
        } else {
            $message = "<p class='error'>Database Error (Customer Check): " . mysqli_error($connection) . "</p>";
            $error = true;
        }

        // 3. Validate Product Existence AND Deduct Inventory
        if (!$error) {
            $prod_check_query = "SELECT prodID FROM product WHERE prodID = ?";
            $stmt_prod = mysqli_prepare($connection, $prod_check_query);

            if ($stmt_prod) {
                mysqli_stmt_bind_param($stmt_prod, "s", $prodID);
                mysqli_stmt_execute($stmt_prod);
                mysqli_stmt_store_result($stmt_prod);

                if (mysqli_stmt_num_rows($stmt_prod) == 0) {
                    $message = "<p class='error'>Error: Invalid Product ID ($prodID).</p>";
                    $error = true;
                }
                mysqli_stmt_close($stmt_prod);
            } else {
                $message = "<p class='error'>Database Error (Product Check): " . mysqli_error($connection) . "</p>";
                $error = true;
            }

            // --- DEDUCT INVENTORY ---
            if (!$error) {
                $deduct_query = "UPDATE product SET quantityonhand = quantityonhand - ? WHERE prodID = ?";
                $stmt_deduct = mysqli_prepare($connection, $deduct_query);

                if ($stmt_deduct) {
                    // 'is' -> Integer (quantity), String (prodID)
                    mysqli_stmt_bind_param($stmt_deduct, "is", $quantity, $prodID);

                    if (!mysqli_stmt_execute($stmt_deduct)) {
                        $message = "<p class='error'>Inventory Update Failed: " . mysqli_stmt_error($stmt_deduct) . "</p>";
                        $error = true;
                    }
                    mysqli_stmt_close($stmt_deduct);
                } else {
                    $message = "<p class='error'>Database Error (Inventory Deduct): " . mysqli_error($connection) . "</p>";
                    $error = true;
                }
            }
        }

        // 4. Check for existing purchase and Accumulate or Insert
        if (!$error) {
            // --- Check for Existing Purchase ---
            $existing_purchase_query = "SELECT quantity FROM purchases WHERE cusID = ? AND prodID = ?";
            $stmt_exist = mysqli_prepare($connection, $existing_purchase_query);
            $currentQuantity = 0;
            $is_existing = false;

            if ($stmt_exist) {
                mysqli_stmt_bind_param($stmt_exist, "ss", $cusID, $prodID);
                mysqli_stmt_execute($stmt_exist);
                mysqli_stmt_bind_result($stmt_exist, $currentQuantity);

                if (mysqli_stmt_fetch($stmt_exist)) {
                    $is_existing = true;
                }
                mysqli_stmt_close($stmt_exist);
            } else {
                $message = "<p class='error'>Database Error (Purchase Check): " . mysqli_error($connection) . "</p>";
                $error = true;
            }

            if (!$error && $is_existing) {
                // A. Purchase Exists
                $newTotalQuantity = $currentQuantity + $quantity;

                $update_query = "UPDATE purchases SET quantity = ? WHERE cusID = ? AND prodID = ?";
                $stmt_update = mysqli_prepare($connection, $update_query);

                if ($stmt_update) {
                    mysqli_stmt_bind_param($stmt_update, "iss", $newTotalQuantity, $cusID, $prodID);

                    if (mysqli_stmt_execute($stmt_update)) {
                        $message = "<p class='success'>Success! Quantity accumulated.<br>
                                    Previous: $currentQuantity | Added: $quantity | **New Total: $newTotalQuantity**<br>
                                    (Inventory deducted by $quantity)</p>";
                    } else {
                        $message = "<p class='error'>Update failed: " . mysqli_stmt_error($stmt_update) . "</p>";
                        $error = true;
                    }
                    mysqli_stmt_close($stmt_update);
                }

            } else if (!$error) {
                // B. New Purchase
                $insert_query = "INSERT INTO purchases (cusID, prodID, quantity) VALUES (?, ?, ?)";
                $stmt_insert = mysqli_prepare($connection, $insert_query);

                if ($stmt_insert) {
                    mysqli_stmt_bind_param($stmt_insert, "ssi", $cusID, $prodID, $quantity);

                    if (mysqli_stmt_execute($stmt_insert)) {
                        $message = "<p class='success'>Success! New purchase recorded.<br>
                                    Customer: $cusID | Product: $prodID | Quantity: $quantity<br>
                                    (Inventory deducted by $quantity)</p>";
                    } else {
                        $message = "<p class='error'>Insertion failed: " . mysqli_stmt_error($stmt_insert) . "</p>";
                        $error = true;
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
        }

        // Final transaction commit or rollback
        if (!$error) {
            mysqli_commit($connection);
        } else {
            mysqli_rollback($connection);
        }
    }

    mysqli_autocommit($connection, TRUE);
}

// Close connection
if (isset($connection)) {
    mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Insert New Purchase</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainmenu.css">
    <style>
        /* Styles adjusted to match the mainmenu.css darkgoldenrod theme */
        .form-container {
            background-color: rgba(0,0,0,0.1);
            padding: 25px;
            border-radius: 8px;
            max-width: 400px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"], .form-group input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #eb3434;
            background-color: white;
            color: #333;
            box-sizing: border-box;
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
    </style>
</head>
<body>

<h1>Insert New Purchase</h1>
<p>Enter the Customer ID, Product ID, and Quantity to record a new purchase or increase an existing one.</p>
<hr>

<a href="mainmenu.php" style="font-weight: bold;"><< Back to Main Menu</a>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($message)) { echo $message; } ?>

<div class="form-container">
    <form method="post" action="option3.php">

        <div class="form-group">
            <label for="cusID">Customer ID (cusID, must be 2 characters, e.g., '10', '3A'):</label>
            <input type="text" id="cusID" name="cusID" required maxlength="2" value="<?php echo htmlspecialchars($_POST['cusID'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="prodID">Product ID (prodID, must be 2 characters, e.g., '99', '02'):</label>
            <input type="text" id="prodID" name="prodID" required maxlength="2" value="<?php echo htmlspecialchars($_POST['prodID'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="quantity">Quantity to Purchase (Numeric):</label>
            <input type="number" id="quantity" name="quantity" required min="1" value="<?php echo htmlspecialchars($_POST['quantity'] ?? ''); ?>">
            <small style="color:white;">If this purchase already exists, this value will be **added** to the current quantity.</small>
        </div>

        <input type="submit" value="Record Purchase">
    </form>
</div>

</body>
</html>