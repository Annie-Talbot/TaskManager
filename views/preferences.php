<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../content/stylesheet.css">
    <script type="text/javascript" src="../content/js.js"></script>
    <title>Preferences</title>
</head>
<body align="center">
<div class="<?php echo "container-" . $theme;?>" id = "container">
    <h1 align=left class="title"> Task Manager</h1>
    <h3 align=right style="padding-right:5%;position: fixed; top:5%;right:10%;"><a href="../index.php/mytasks" style="text-decoration: none;"> My Tasks </a></h3>
    <h3 align=right style="padding-right:5%;position: fixed; top:10%;right:10%;"><a href="../index.php/logout" style="text-decoration: none;"> Log Out </a></h3>
    <div class="centralisedContainer">
        <h1> Preferences </h1>
        <h3>Dark Mode:</h3>
        <input type="checkbox" onclick="switchTheme(this)" name="darkModeToggle"
               <?php
                    if ($theme == "dark") {
                        echo "checked='true'";
                    }
               ?> />
    </div>
</div>
</body>
</html>
