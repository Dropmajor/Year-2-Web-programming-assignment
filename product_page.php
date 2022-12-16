<?php
require_once 'database_functions.php';
session_start();

//if no get request has been set this means there is no data for a product to be displayed, so the user will be redirected
if(!isset($_GET))
{
    header('Location: Home_page.php');
}

$connection = new database();
$productQuery = "SELECT * FROM `products` WHERE product_id LIKE '". $_GET["product_id"] ."'";
$productResult = mysqli_fetch_array($connection->runQuery( $productQuery));
if(!empty($_GET))
{
    if(isset($_GET["addToCart"]) && isset($_GET["product_id"]))
    {
        if(!isset($_SESSION["cart"]))
            $_SESSION["cart"] = array();
        $_SESSION["cart"][] = $_GET["product_id"];
        header("Location: ?product_id=". $_GET["product_id"] ."&confirm=". $productResult["name"] ." has been successfully added to the cart");
    }
}
?>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Display the products name in the tabs title-->
<title><?php echo $productResult["name"]?></title>
<style>
    img
    {
        max-width: 500px;
        max-height: 500px;
        overflow: hidden;
    }
    @media only screen and (max-width: 900px) {
        img
        {
            max-width: 250px;
        }
    }
</style>
<?php require_once "header.php" ?>
<main>
    <!--Bootstrap classes-->
    <div class="container-fluid row">
    <div class="col">
        <img src="<?php echo ((!empty($productResult["image_link"])) ? $productResult["image_link"] : "https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fwww.fremontgurdwara.org%2Fwp-content%2Fuploads%2F2020%2F06%2Fno-image-icon-2.png&f=1&nofb=1&ipt=6cd5a3acdd380efbd0eb95399e81ea30f041d3d19b02d23a48c9dfde91725bc6&ipo=images") ?>">
    </div>
    <div class="col">
        <?php echo "<h1>". $productResult["name"] ."</h1>
<p>". $productResult["description"] ."</p>";
        echo "<p>Price:". money_format("£%i", $productResult["price"]) ."</p>"?>
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
            <input type="hidden" name="product_id" value="<?php echo $productResult["product_id"] ?>">
            <button type="submit" name="addToCart">Add to cart</button>
        </form>
        <?php if(isset($_GET["confirm"])) echo $_GET["confirm"]?>
    </div>
</div>
</main>
<?php require_once "footer.php"; ?>
</html>
