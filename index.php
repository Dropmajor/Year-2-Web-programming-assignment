<?php
session_start();
require_once 'database_functions.php';
$connection = new database();
//get a selection of random products to dispaly as featured items
$featuredItems = $connection->runQuery("SELECT * FROM `products` ORDER BY RAND() LIMIT 0, 4");
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Music Shop: Home</title>
</head>
<style>
    img
    {
        max-width: 250px;
        max-height: 250px;
        overflow: hidden;
    }
    .productList
    {
        list-style-type: none;
    }
</style>
<?php require_once "header.php" ?>
<main>
<h2>Welcome to the music shop</h2>
<p>We are the regions leading supplier of instruments and sheet music</p>
<h3>Featured items</h3>
    <!--Bootstrap classes-->
    <div class="container-fluid row">
    <?php
    //display the featured items
    while ($product = mysqli_fetch_array($featuredItems))
    {
        echo "<div class='col'>
        <a href='product_page.php?product_id=". $product["product_id"] ."'><ul class='productList'>
                      <li class='productList'><img src='". ((!empty($product["image_link"])) ? $product["image_link"] : "https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fwww.fremontgurdwara.org%2Fwp-content%2Fuploads%2F2020%2F06%2Fno-image-icon-2.png&f=1&nofb=1&ipt=6cd5a3acdd380efbd0eb95399e81ea30f041d3d19b02d23a48c9dfde91725bc6&ipo=images") ."'></li>
                      <li>".$product["name"]."</li>
                      <li>".money_format("£%i", $product["price"])."</li>
                      </ul>  </a>
        </div>";
    }
    ?>
</div>
</main>
<?php require_once "footer.php"; ?>
</html>
