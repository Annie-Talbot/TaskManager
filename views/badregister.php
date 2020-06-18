<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="../content/stylesheet.css">
</head>
<body align="center">
<div class="<?php echo "container-" . $theme;?>" id = "page">
    <h1 align=left class="title"> Task Manager</h1>
    <div class="centralisedContainer" style="height:90%;">
        <h1> Register: </h1>
        <form action="../index.php/register" method="post">
            <?php echo "<h5 class='err'>" . $register_err . "</h5><br>";?>
            <br>
            Username: <br>
            <input type="text" name="username" value=""><br>
            <br>
            Password: <br>
            <input type="password" name="password1" value=""><br>
            <br>
            Confirm Password: <br>
            <input type="password" name="password2" value=""><br>
            <br>
            <input type="submit" name="register" value="Register">
        </form>
        <h3> Already have an account? Log in <a href="../index.php/login"> here </a>.</h3>
    </div>
</div>
</body>
</html>