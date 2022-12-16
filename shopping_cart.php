<?php
require_once 'database_functions.php';
session_start();

//remove all instances of a given item
if(isset($_GET["mode"]) && $_GET["mode"] == "remove")
{
    //create a duplicate array that only retains unique values
    $uniqueCartItems = array_unique($_SESSION["cart"]);
    //remove the item from the unique array
    if (($key = array_search($_GET["id"], $uniqueCartItems)) !== false){
        unset($uniqueCartItems[$key]);
    }
    //intersect the cart with the unique array, since the id has been removed any duplicates wont have any intersection
    //and will be removed
    $_SESSION["cart"] = array_intersect($_SESSION["cart"], $uniqueCartItems);
}
$connection = new database();
$totalCost = 0;
if(!empty($_SESSION["cart"]))
{
    $likeQuery = "";
    //create an array with each product id acting as a key for the number of times it occurs
    $cartQuantity = array_count_values($_SESSION["cart"]);
    //build a select query to get all the items in the cart from the database
    foreach (array_unique($_SESSION["cart"]) as $id)
    {
            if(empty($likeQuery))
                $likeQuery .= "WHERE `product_id` LIKE '". $id ."'";
            else
                $likeQuery .= "OR `product_id` LIKE '". $id ."'";
    }
    $cartQuery = "SELECT * FROM `products` ". $likeQuery;
    $cartResult = $connection->runQuery($cartQuery);

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if(isset($_POST["purchase"]))
        {
            $account = mysqli_fetch_array($connection->prepareQuery("SELECT * FROM `users` WHERE username LIKE ?", $_SESSION["account"]));
            $connection->insertData("orders", array((int) $account["user_id"]));
            //fetch the last entry in the database as that would be the most recently created index, and as such the users order
            $orderID = mysqli_fetch_array($connection->runQuery("SELECT order_id FROM `orders` ORDER BY order_id DESC LIMIT 0, 1"))["order_id"];
            //link all the products to the order
            while ($product = mysqli_fetch_array($cartResult))
            {
                $connection->insertData("order_product_link", array($orderID, (int) $product["product_id"], (int) $cartQuantity[$product["product_id"]]));
            }
            //empty the cart now that the purchase has been made
            $_SESSION["cart"] = array();
            header('Location: shopping_cart.php?confirm=Order has been successfully placed');
        }
    }
}
?>
<html lang="">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart</title>
</head>
<style>
    img
    {
        max-width: 100px;
        max-height: 100px;
    }
</style>
<?php require_once "header.php" ?>
<main>
    <!--Bootstrap classes-->
    <div class="container-fluid row">
    <div class="col">
        <h1>Shopping basket</h1>
        <?php
        if(empty($_SESSION["cart"]))
        {
            echo "Your cart is empty";
        }
        else if(!empty($cartResult))
        {
            //display the items in the cart
            while ($product = mysqli_fetch_array($cartResult))
            {
                $totalCost += $product["price"] * $cartQuantity[$product["product_id"]];
                echo "<img src='". ((!empty($product["image_link"])) ? $product["image_link"] : "https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fwww.fremontgurdwara.org%2Fwp-content%2Fuploads%2F2020%2F06%2Fno-image-icon-2.png&f=1&nofb=1&ipt=6cd5a3acdd380efbd0eb95399e81ea30f041d3d19b02d23a48c9dfde91725bc6&ipo=images")."'>"
                    ."<a href='product_page.php?product_id=". $product["product_id"] ."'>". $product["name"]. "</a>"
                    ." Price ". money_format("£%i",$product["price"] * $cartQuantity[$product["product_id"]])
                ."<br>Quantity: ". $cartQuantity[$product["product_id"]]
                    ." <a href='?mode=remove&id=". $product["product_id"] ."'>Delete</a><br>";
            }
        }
        ?>
    </div>
    <div class="col">
        <h2>Subtotal: <?php echo money_format("£%i", $totalCost) ?></h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
            <?php if(!empty($_SESSION["account"]))
            {
                echo "<button type='submit' name='purchase'>Place order</a></button>";
            }
            else
            {
                echo "<p>You aren't logged in, to continue with the purchase please <a href='Registration_form.php'>Login</a></p>";
            }
            if(isset($_GET["confirm"]))
                echo "<p>". $_GET["confirm"] ."<p>";?>
        </form>
    </div>
</div>
</main>
<?php require_once "footer.php"; ?>
</html>
