<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="store_style.css">
<body>
<header>
    <!--Bootstrap classes-->
    <div class="container-fluid row">
        <div class="col">
            <h1>The Music Shop</h1>

        </div>
        <div class="col">
            <form method="get" action="store_page.php" >
                <input type="text" name="search_value" id="searchBar" placeholder="Search"
                    <?php if(isset($_GET["search_value"])) echo "value='". $_GET["search_value"] ."'";?>>
                <input type="submit" name="search" value="search">
            </form>
        </div>
        <div class="col">
            <h2><a href="shopping_cart.php" class="headerLink">Cart: <?php if(isset($_SESSION["cart"])) echo count($_SESSION["cart"]); else echo 0;?> items</a></h2>
        </div>
    </div>

    <div>
        <ul id="navBar">
            <li><a href="index.php" class="headerLink">Home</a></li>
            <li><a href="store_page.php" class="headerLink">Store</a></li>
            <li><a href="profile_page.php" class="headerLink">Account</a></li>
        </ul>
    </div>
</header>