<?php
require_once 'database_functions.php';
session_start();

$connection = new database();
//validate whether the database has successfully connected
if(empty($connection->getError()))
{
    $productTypesQuery = $connection->runQuery("SELECT `type` FROM `product_type` ORDER BY 'type' ASC");
    $productTypes = array();
    while($type = mysqli_fetch_array($productTypesQuery))
    {
        $productTypes[] = $type["type"];
    }
    //set the default order by
    $orderBy = "ORDER BY `name` ASC";
    if(isset($_GET))
    {
        $likeQuery = "";
        if(isset($_GET["filters"]))
        {
            switch ($_GET["sort_by"])
            {
                case "name asc":
                    $orderBy = "ORDER BY `products`.`name` ASC";
                    break;
                case "name desc":
                    $orderBy = "ORDER BY `products`.`name` DESC";
                    break;
                case "price desc":
                    $orderBy = "ORDER BY `price` DESC";
                    break;
                case "price asc":
                    $orderBy = "ORDER BY `price` ASC";
                    break;
            }
            foreach ($productTypes as $type)
            {
                //check if this product type exists in the get request, if it exists it means that filter has been ticked
                if(isset($_GET[$type]))
                {
                    if(empty($likeQuery))
                        $likeQuery .= "WHERE `product_type`.`type` LIKE '". $type ."'";
                    else
                        $likeQuery .= " OR `product_type`.`type` LIKE '". $type ."'";
                }
            }
        }
        if(!empty($_GET["search_value"]))
        {
            if(empty($likeQuery))
            {
                //need a ? for sanitising and preparing statements
                $likeQuery .= "WHERE `products`.`name` LIKE ?";
            }
            else
            {
                //if the like query isnt empty, that means that there is already another where condition, enclose it in
                //brackets so that it is seperate from the search where
                $likeQuery = substr_replace($likeQuery, "(", 5, 0);
                $likeQuery .= ") AND `name` LIKE ?";
            }
        }

        if(isset($_GET['page_no']) && !empty($_GET['page_no']))
        {
            $page_no = $_GET['page_no'];
        }
        else
        {
            $page_no = 1;
        }

        $displayCount = 8;
        $allRecords = $connection->runQuery("SELECT * FROM `products`
    INNER JOIN `product_type`
        ON `products`.`product_type`=`product_type`.`type_id` "
            . $likeQuery);
        $totalPages = ceil(mysqli_num_rows($allRecords) / $displayCount);
        $offset = $displayCount * ($page_no - 1);

        $productQuery = "SELECT * FROM `products` 
    INNER JOIN `product_type`
        ON `products`.`product_type`=`product_type`.`type_id` "
            . $likeQuery ." ". $orderBy ." LIMIT ". $offset .", ". $displayCount;
    }
    if(!empty($_GET["search_value"]))
        $products = $connection->prepareQuery($productQuery, "%". $_GET["search_value"] ."%");
    else
        $products = $connection->runQuery($productQuery);
}
else
{
    header('Location: ?connect_error='.$connection->getError());
}
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Music Shop: store</title>
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
    <!--Bootstrap classes-->
<div class="container-fluid row">
    <!--Search filters-->
    <div id="filters" class="col">
        <h5>Filters</h5>
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="sort_by">Sort by:</label>
            <select name="sort_by">>
                <option value="name asc" <?php if(isset($_GET["sort_by"]) && $_GET["sort_by"] != "name asc") echo ""; else "selected"?>>
                    Alphabetically: Ascending</option>
                <option value="name desc" <?php if(isset($_GET["sort_by"]) && $_GET["sort_by"] == "name desc") echo "selected"?>>
                    Alphabetically: Descending</option>
                <option value="price desc" <?php if(isset($_GET["sort_by"]) && $_GET["sort_by"] == "price desc") echo "selected"?>>
                    Price: High to Low</option>
                <option value="price asc" <?php if(isset($_GET["sort_by"]) && $_GET["sort_by"] == "price asc") echo "selected"?>>
                    Price: Low to High</option>
            </select> <br>
            <h6>Categories</h6>
            <?php
            if(empty($connection->getError()))
            {
                //output all the product types as checkboxes for filtering
                foreach ($productTypes as $type)
                {
                    echo "<input type='checkbox' name='". $type ."'". ((!empty($_GET[$type])) ? "checked" : "") .">
            <label for='".$type."'>". $type ."</label><br>";
                }
            }
            ?>
            <input type="submit" value="Select filters" name="filters">

            <input type="hidden" name="search_value" value="<?php if(isset($_GET["search_value"])) echo $_GET["search_value"] ?>">
            <input type="hidden" name="page_no" value="<?php if(isset($page_no)) echo $page_no; else echo "1"; ?>">
        </form>
    </div>
    <div class="col-10 row">
            <?php
            if(empty($connection->getError()))
            {
                //list all product records
                while ($row = mysqli_fetch_array($products))
                {
                    //bootstrap classes are used here in the form of the col class
                    echo "<div class='col-xs-6 col-md-3.5'>
                      <a href='product_page.php?product_id=". $row["product_id"] ."'><ul class='productList'>
                      <li class='productList'><img src='". ((!empty($row["image_link"])) ? $row["image_link"] : "https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fwww.fremontgurdwara.org%2Fwp-content%2Fuploads%2F2020%2F06%2Fno-image-icon-2.png&f=1&nofb=1&ipt=6cd5a3acdd380efbd0eb95399e81ea30f041d3d19b02d23a48c9dfde91725bc6&ipo=images") ."'></li>
                      <li>".$row["name"]."</li>
                      <li>". $row["type"] ."</li>
                      <li>".money_format("£%i",$row["price"])."</li>
                      </ul>  </a>
                        </div>";
                }
            }
            else
            {
                echo "We are sorry, an error has occurred with our database, please try again later <br>
                    Error: ". $connection->getError();
            }
            ?>
    </div>
</div>
<!--Page numbers-->
<div style="align-content: center">
<p> <?php
    if(empty($connection->getError()))
    {
        //copy the current url, this will allow us to change the page number while retaining the search query and filters
        $getHeader = $_SERVER['REQUEST_URI'];
        if(!empty($_GET))
        {
            if(isset($_GET["page_no"]))
            {
                //remove the page no reference
                $getHeader = preg_replace("/page_no=".$page_no."/", "", $getHeader);
                //add a page number reference to the end with no page reference
                $getHeader .= "page_no=";
            }
        }
        else
        {
            $getHeader .= "?page_no=";
        }
        //add the page number to the url of the associated previous and next page links
        echo (($page_no > 1)? "<a href='"
                . $getHeader . ($page_no - 1) ."'>Previous page</a> " : ""). "Page no: ". $page_no.
            (($page_no < $totalPages)? "<a href='"
                .$getHeader. ($page_no + 1) ."'>Next page</a> " : "");
    }
    ?> </p>
</div>
</main>
<?php require_once "footer.php"; ?>
</html>
