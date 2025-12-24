<?php
// This is the seventh option for list all products that have never been purchased.
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products Never Purchased</title>
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
<div class="header">
    <div class="container">
        <h1>Products Never Purchased Report</h1>
    </div>
</div>

<div class="container">
    <div class="content-card">
        <?php
        // 1. Include the database connection script
        include "connecttodb.php";

        // 2. SQL Query: Select ALL columns (*) for products whose prodid is NOT IN the purchases table.
        $query = "SELECT * FROM product WHERE prodid NOT IN (SELECT prodid FROM purchases)";

        $result = mysqli_query($connection, $query);

        // Check for query execution errors
        if (!$result) {
            // Display database error in the styled error box
            echo '<div class="message-box error-message">';
            echo '<strong>Database Query Failed:</strong> ' . mysqli_error($connection);
            echo '</div>';
        } else {
            // 3. Process and display results
            $num_rows = mysqli_num_rows($result);

            if ($num_rows > 0) {
                echo '<div class="message-box info-message">';
                echo "<strong>Success!</strong> Found $num_rows Product(s) Never Purchased.";
                echo '</div>';

                // Get column names for the table header dynamically
                echo '<table><thead><tr>';
                $field_info = mysqli_fetch_fields($result);
                $column_names = [];
                foreach ($field_info as $val) {
                    $column_names[] = $val->name;
                    // Display column names nicely formatted
                    echo '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $val->name))) . '</th>';
                }
                echo '</tr></thead><tbody>';

                // Loop through the results and display each row
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    foreach ($column_names as $col_name) {
                        echo '<td>' . htmlspecialchars($row[$col_name]) . '</td>';
                    }
                    echo '</tr>';
                }

                echo '</tbody></table>';

            } else {
                // Display a message if no products were found
                echo '<div class="message-box info-message">';
                echo 'All products in stock have been purchased at least once!';
                echo '</div>';
            }
        }


        // 4. Clean up
        if (isset($result)) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
        ?>

        <a href="mainmenu.php" class="back-button">← Back to Main Menu</a>
    </div>
</div>

</body>
</html>