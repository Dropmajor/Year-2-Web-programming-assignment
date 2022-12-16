<?php
session_start();
if(empty($_SESSION["account"]))
{
    header('Location: Registration_form.php');
}
require_once 'database_functions.php';
$connection = new database();
//get the users account for the user id
$userID = mysqli_fetch_array($connection->prepareQuery("SELECT * FROM `users` WHERE username=?", $_SESSION["account"]))["user_id"];
//select all the orders from the orders table and join all the related tables to it
$orders = $connection->prepareQuery("SELECT * FROM `orders` 
    INNER JOIN `order_product_link`
        ON `orders`.`order_id`=`order_product_link`.`order_id`
    INNER JOIN `products`
         ON `order_product_link`.`product_id`=`products`.`product_id`
    WHERE `user_id`=? ORDER BY `orders`.`order_id`", $userID);
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your orders</title>
</head>
<style>
    table
    {
        border: 1px solid black;

    }
</style>
<?php require_once "header.php" ?>
<main>
    <h2>Your Orders</h2>
    <?php
        $lastOrder = NULL;
        echo "<ul>";
        //display the orders
        while ($order = mysqli_fetch_array($orders))
        {
            if($order["order_id"] != $lastOrder)
            {
                $lastOrder = $order["order_id"];
                echo "Order: </ul><ul style='list-style: none'>";
            }
            echo "<li>    Item: ". $order["name"] ." Quantity: ". $order["quantity"] ."</li>";
        }
    ?>
    <a href="profile_page.php">Return</a>
</main>
<?php require_once "footer.php"; ?>
</html>
