<!DOCTYPE html>
<html>
<head>
    <title>Main Menu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mainmenu.css">
</head>
<body>
<?php
    include "connecttodb.php";
?>
<h1>Main Menu </h1>
<p>Please select an option from the menu below:</p>
<hr>

<!-- Container for the six option buttons -->
<div class="button-container">
    <!-- Option 1 -->
    <form action="option1.php" method="get">
        <input type="submit" value="Information about the customers">
    </form>

    <!-- Option 2 -->
    <form action="option2.php" method="get">
        <input type="submit" value="Information about the products">
    </form>

    <!-- Option 3 -->
    <form action="option3.php" method="get">
        <input type="submit" value="Insert a new purchase">
    </form>

    <!-- Option 4 -->
    <form action="option4.php" method="get">
        <input type="submit" value="Insert a new customer">
    </form>

    <!-- Option 5 -->
    <form action="option5.php" method="get">
        <input type="submit" value="Delete a customer">
    </form>

    <!-- Option 6 -->
    <form action="option6.php" method="get">
        <input type="submit" value="Find who bought certain products">
    </form>

    <!-- Option 7 -->
    <form action="option7.php" method="get">
        <input type="submit" value="List never been purchased products">
    </form>
</div>

</body>
</html>