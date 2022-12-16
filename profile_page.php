<?php
require_once 'database_functions.php';
session_start();
if(empty($_SESSION["account"]))
{
    $_SESSION["logged in"] = false;
    header('Location: Registration_form.php');
}
else
{
    $database = new database();
    $account = mysqli_fetch_array($database->runQuery("SELECT * FROM `users` WHERE username LIKE '". $_SESSION["account"] ."'"));
}
if(isset($_GET["logout"]))
{
    session_unset();
    session_destroy();
    header("Location: Registration_form.php");
}
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your account</title>
</head>
<?php require_once "header.php" ?>
<main>
    <p>You are logged in as <?php echo $_SESSION["account"]?></p>
    <div id="user_setting">
        <h2>Account settings</h2>
        <ul>
            <li><a href="order_view.php">View orders</a></li>
            <li><a href="">Edit details</a></li>
            <li><a href="?logout">Log out</a></li>
        </ul>
    </div>
    <?php if($account["admin"])
    {
        //if the user is an admin, display a way to access the admin functions
        echo "<h2>Admin settings</h2>
                <ul>
                <li><a href='admin_form.php'>View products</a></li>
                </ul>";
    }?>
    <h2></h2>
</main>
<?php require_once "footer.php"; ?>
</html>
