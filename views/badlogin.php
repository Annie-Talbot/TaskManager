<!DOCTYPE html>
<html>
<head>
    <title>Log in</title>
    <link rel="stylesheet" type="text/css" href="../content/stylesheet.css">
</head>
<body align="center">
<div class="<?php echo "container-" . $theme;?>" id = "page">
    <h1 class="title" align=left> Task Manager</h1>
    <div class="centralisedContainer">
        <h1> Log in: </h1>
        <form action="../index.php/login" method="post">
            <?php echo "<h5 class='err'>" . $login_err . "</h5><br>";?>
            Username: <br>
            <input type="text" name="username" value=""><br>
            Password:<br>
            <input type="password" name="password" value=""><br>
            <br>
            <input type="submit" name="signin" value="Log in">
        </form>
        <h3> Don't have an account? Sign up <a href="../index.php/register"> here</a>.</h3>
    </div>
</div>
</body>
</html>
