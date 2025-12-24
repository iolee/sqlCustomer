<?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($message)) { echo $message; } ?>
//This is the fourth option for inserting a new customer.
include "connecttodb.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Inputs
    $cusID = filter_input(INPUT_POST, 'cusID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phonenumber = filter_input(INPUT_POST, 'phonenumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $agentID = filter_input(INPUT_POST, 'agentID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $error = false;
    // 2. Validate Inputs
    if (empty($cusID) || empty($firstname) || empty($lastname) || empty($city) || empty($phonenumber) || empty($agentID)) {
        $message = "<p class='error'>Error: All fields are required.</p>";
        $error = true;
    } else if (strlen($cusID) !== 2 || strlen($agentID) !== 2) {
        $message = "<p class='error'>Error: Customer ID and Agent ID must be exactly 2 characters long (e.g., 'A1', '12').</p>";
        $error = true;
    }

    // 3. Check if cusID already exists (Crucial requirement)
    if (!$error) {
        $check_query = "SELECT cusID FROM customer WHERE cusID = ?";
        $stmt_check = mysqli_prepare($connection, $check_query);

        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "s", $cusID);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $message = "<p class='error'>Error: Customer ID **$cusID** already exists. Please choose a unique 2-character ID.</p>";
                $error = true;
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $message = "<p class='error'>Database Preparation Error (ID Check): " . mysqli_error($connection) . "</p>";
            $error = true;
        }
    }

    // 4. Perform INSERT
    if (!$error) {
        // Query to insert new customer
        $insert_query = "INSERT INTO customer (cusID, firstname, lastname, city, phonenumber, agentID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($connection, $insert_query);

        if ($stmt_insert) {
            // Bind parameters: 6 strings (s s s s s s)
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $cusID, $firstname, $lastname, $city, $phonenumber, $agentID);

            if (mysqli_stmt_execute($stmt_insert)) {
                $message = "<p class='success'>Success! Customer **$firstname $lastname** (ID: **$cusID**) has been added.</p>";
            } else {
                // Catch potential Foreign Key violation (e.g., agentID not found)
                $error_details = mysqli_stmt_error($stmt_insert);

                if (strpos($error_details, 'foreign key constraint fails') !== false || strpos($error_details, 'a foreign key constraint') !== false) {
                    $message = "<p class='error'>Insertion failed. Check that Agent ID **$agentID** exists in the Sales Agent table.</p>";
                } else {
                    $message = "<p class='error'>Insertion failed: " . $error_details . "</p>";
                }
                $error = true;
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            $message = "<p class='error'>Database Preparation Error (Insert): " . mysqli_error($connection) . "</p>";
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
    <title>Insert New Customer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainmenu.css">
    <style>
        /* Add some specific styles for the table, codes generated from Gemini */
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

<h1>Insert New Customer</h1>
<p>Enter the details for the new customer.</p>
<hr>

<a href="mainmenu.php" style="font-weight: bold;"><< Back to Main Menu</a>

<!-- Display message only if it's a POST request AND a message was generated -->
<?php if ($is_post && !empty($message)) { echo $message; } ?>

<div class="form-container">
    <form method="post" action="option4.php">

        <div class="form-group">
            <label for="cusID">Customer ID (Must be unique and 2 characters, e.g., 'A1'):</label>
            <input type="text" id="cusID" name="cusID" required maxlength="2" value="<?php echo htmlspecialchars($_POST['cusID'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" required value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="phonenumber">Phone Number (e.g., 5556661234):</label>
            <input type="text" id="phonenumber" name="phonenumber" required value="<?php echo htmlspecialchars($_POST['phonenumber'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="agentID">Sales Agent ID (Must be 2 characters, e.g., '01'):</label>
            <input type="text" id="agentID" name="agentID" required maxlength="2" value="<?php echo htmlspecialchars($_POST['agentID'] ?? ''); ?>">
            <small style="color:white;">Note: Agent ID must exist in the Sales Agent table.</small>
        </div>

        <input type="submit" value="Add New Customer">
    </form>
</div>

</body>

</html>
