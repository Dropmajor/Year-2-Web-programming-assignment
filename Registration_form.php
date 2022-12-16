<?php
require_once 'database_functions.php';
session_start();
if(!empty($_SESSION["account"]))
{
    header('Location: profile_page.php');
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $connection = new database();
    //validate if the database connection is working
    if(empty($connection->getError()))
    {
        if(isset($_POST["login"]) || isset($_POST["register"]))
        {
            if(!isset($usernameError) && !isset($passwordError))
            {
                if(isset($_POST["login"]))
                {
                    $result = $connection->prepareQuery("SELECT * FROM `users` WHERE username=?", array($_POST["username"]));
                    if(!empty($result))
                    {
                        $account = mysqli_fetch_array($result);
                        $password = hash('sha256', $_POST["password"]);
                        if($account["password"] == $password)
                        {
                            $_SESSION["logged in"] = true;
                            $_SESSION["account"] = $_POST["username"];
                            header('Location: profile_page.php');
                        }
                        else
                        {
                            $loginError = "Username or password does not match";

                        }
                    }
                    else
                    {
                        $loginError = "Username or password does not match";
                    }
                }
                else
                {
                    if($_POST["password"] != $_POST["confirmPassword"])
                    {
                        $loginError = "Passwords do not match!";
                    }
                    else
                    {
                        //check that the username doesnt already exist
                        $query = "SELECT * FROM `users` WHERE username LIKE '". $_POST["username"] ."'";
                        $result = $connection->runQuery($query);
                        if($result->num_rows == 0)
                        {
                            $connection->insertData("users", array($_POST["username"], hash('sha256', $_POST["password"]), 0));
                            $_SESSION["account"] = $_POST["username"];
                            header('Location: profile_page.php');
                        }
                        else
                        {
                            //if the user
                            $loginError = "This username already exists, please choose a different one";
                        }
                    }
                }
            }
        }
    }
    else
    {
        header('Location: ?connect_error='.$connection->getError());
    }
    //if a user is found, add a get request to output the error
    if(isset($loginError))
    {
        if(isset($_POST["register"]))
            $mode = "register";
        else
            $mode = "login";
        //retain the mode the user was in and output the error
        header('Location: Registration_form.php?mode='. $mode .'&loginError='. $loginError .'&username='. $_POST["username"]);
    }
}
?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php if(isset($_GET["mode"]) && $_GET["mode"] == "register") echo "Register"; else echo "Login";?></title>
    </head>
    <?php require_once "header.php"; ?>
    <main>
        <h2><?php if(isset($_GET["mode"]) && $_GET["mode"] == "register") echo "Register"; else echo "Login";?></h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" minlength="4" maxlength="16"
                <?php if(isset($_POST["username"])) echo "value='". $_POST["username"] ."'" ?> required> <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" minlength="8" maxlength="16" 
                   required> <br>
            <?php
            if(isset($_GET["mode"]) && $_GET["mode"] == "register")
            {
                echo "<label for='confirmPassword'>Confirm password:</label>
                <input type='password' id='confirmPassword' name='confirmPassword' placeholder='Confirm password' minlength='8' maxlength='16' required> <br>";
            }
            ?>
            <p><?php if(isset($_GET["loginError"])) echo $_GET["loginError"] . "<br>";?></p>
            <input type="submit" name="<?php if(isset($_GET["mode"]) && $_GET["mode"] == "register") echo "register";
            else echo "login";?>">
        </form>
        <?php
        if(isset($_GET["mode"]) && $_GET["mode"] == "register")
            echo "<p>Already have an account? <a href='?mode=login'>Login</a></p>";
        else
            echo "<p>Don't have an account? <a href='?mode=register'>Register</a></p>";
        ?>
        <p><?php if(isset($_GET["connect_error"])) echo "An error has occurred in contacting the sql server <br> 
                Error: ".$_GET["connect_error"] ?></p>
    </main>
    <?php require_once "footer.php"; ?>
</html>
