<?php
require_once 'database_functions.php';
session_start();
//check if the user is logged in and whether they are an admin
$connection = new database();
//validate that the database connected fine
if(empty($connection->getError()))
{
    if(empty($_SESSION["account"])) {
        header('Location: Registration_form.php');
    }
    else
    {
        $account = mysqli_fetch_array($connection->prepareQuery("SELECT * FROM `users` WHERE username=?", $_SESSION["account"]));
        if(empty($account["admin"]))
        {
            header('Location: profile_page.php');
        }
    }
    if(!empty($_POST))
    {
        
            if(isset($_POST["create"]))
            {
                $connection->insertData("products", array($_POST["product_name"], $_POST["description"], $_POST["image"],
                    $_POST["price"], $_POST["product_type"]));
                header("Location: ?confirmation=". $_POST["product_name"] ." has been successfully created");
            }
            else if(isset($_POST["update"]))
            {
                $connection->updateData("products", array($_POST["product_name"], $_POST["description"],
                    $_POST["image"], $_POST["price"], $_POST["product_type"]), $_POST["id"]);
                header("Location: ?confirmation=". $_POST["product_name"] ." has been successfully updated");
            }
            if(isset($_POST["create_category"]))
            {
                $connection->prepareQuery("INSERT INTO `product_type` VALUES (NULL, ?)", $_POST["product_type"]);
                header("Location: ?category_confirmation=". $_POST["product_name"] ." has been successfully created");
            }
        
    }
    //get the product types to display as category options in the creation form
    $productTypes = $connection->runQuery("SELECT * FROM `product_type` ORDER BY 'type' ASC");
    $productsResult = $connection->runQuery("SELECT * FROM `products` 
        INNER JOIN `product_type` ON 
            `products`.`product_type`=`product_type`.`type_id`
        ORDER BY `product_id`");
    if(!empty($_GET) && !empty(($_GET['id'])))
    {
        if($_GET['mode'] == "update")
        {
            $updateProduct = mysqli_fetch_array($connection->runQuery
            ("SELECT * FROM `products` WHERE product_id LIKE '". $_GET["id"] ."'"));
        }
        else if($_GET['mode'] == "delete")
        {
            $connection->runQuery("DELETE FROM `products` WHERE product_id='". $_GET["id"] ."'");
            header("Location: ?confirmation=Product has been deleted");
        }
    }
}
else
{
    header('Location: ?error='. $connection->getError());
}
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Form</title>
</head>
<style>
    table, th, td {
        border: 1px solid black;
    }
</style>
<?php require_once "header.php" ?>
<main>
    <!--Bootstrap classes-->
    <div class="container-fluid row">
    <div class="col-md-">
        <h2><?php if((isset($_GET["mode"]) && $_GET["mode"] == "update")) echo "Update record"; else echo "Create record"; ?></h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="product name">Product name:</label>
            <input id="product name" name="product_name" maxlength="32" required
                <?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "value='" .$updateProduct["name"]. "'"?>>
            <br>
            <label for="description">Product description:</label>
            <textarea id="description" name="description" cols="30" rows="7" maxlength="256"><?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo $updateProduct["description"]?></textarea>
            <br>
            <label>Product image source:</label>
            <input name="image" <?php if(isset($_GET["mode"]) && $_GET["mode"] == "update")
                echo "value='" .$updateProduct["image_link"]. "'"?> maxlength="128">
            <br>
            <label for="price">Price:</label>
            <input type="number" name="price" min="1" step="any" required
                <?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "value='" .$updateProduct["price"]. "'"?>>
            <br>
            <label for="product_type">Product type:</label>
            <select name="product_type"?>>
                <?php
                //output the product types to the dropdown
                if(!empty($productTypes))
                {
                    while($type = mysqli_fetch_array($productTypes))
                    {
                        echo "<option value='". $type["type_id"] ."' ".
                            ((isset($_GET["mode"]) && $_GET["mode"] == "update" && $updateProduct["product_type"] == $type["type_id"]) ? "selected" : "") .">".
                            $type["type"] ."</option>";
                    }
                }
                ?>
            </select>
            <br>
            <input type="submit" name="<?php if(isset($_GET["mode"]) && $_GET["mode"] == "update") echo "update"; else echo "create";?>">
            <?php
            if(isset($_GET["mode"]) && $_GET["mode"] == "update")
            {
                echo "<button>Cancel</button>";
                echo "<input type='hidden' name='id' value='". $updateProduct["product_id"] ."'>";
            }
            ?>
        </form>
        <?php if(isset($_GET["confirmation"])) echo $_GET["confirmation"]; ?>
        <h2>Create product category</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="text" name="product_type" maxlength="16" required>
            <input type="submit" name="create_category">
            <?php ?>
        </form>
    </div>
    <div class="col">
        <h2>Product records</h2>
        <table>
            <th>Product ID</th>
            <th>Product name</th>
            <th style="width: 25%">Description</th>
            <th>Price</th>
            <th>type</th>
            <?php
            //output all the records to the table for viewing
            if(!empty($productsResult))
            {
                while ($product = mysqli_fetch_array($productsResult))
                {
                    echo "<tr>
                    <td>".$product["product_id"]."</td>
                    <td>".$product["name"]."</td>
                    <td style='vertical-align: center'>".$product["description"]."</td>
                    <td>".money_format("£%i",$product["price"])."</td>
                    <td>".$product["type"]."</td>
                    <td><a href='?mode=update&id=". $product["product_id"] ."'>Update</a></td>
                    <td><a href='?mode=delete&id=". $product["product_id"] ."'>Delete</a></td>
            </tr>";
                }
            }
            if(isset($error)) echo "<p>". $_GET["error"] ."</p>"
            ?>
        </table>

    </div>
</div>
</main>
<?php require_once "footer.php"; ?>
</html>
