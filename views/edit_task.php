<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../content/stylesheet.css">
    <script type="text/javascript" src="../content/js.js"></script>
    <title>Edit Task</title>
</head>
<body>
<div class="<?php echo "container-" . $theme;?>" id = "page">
    <h1 align=left class="title" > Task Manager</h1>
    <h3 align=right style="padding-right:5%;position: fixed; top:5%;right:10%;"><a href="../index.php/mytasks" style="text-decoration: none;"> My Tasks </a></h3>
    <h3 align=right style="padding-right:5%;position: fixed; top:10%;right:10%;"><a href="../index.php/logout" style="text-decoration: none;"> Log Out </a></h3>
    <div class="centralisedContainer">
        <h1> Edit: </h1>
        <form action="../index.php/add_task" method="post">
            <input type="number" hidden="hidden" name="taskID" value="<?php echo $task[0];?>" />
            <?php
            if ($task[5] !== null){
                echo '<input type="number" hidden="hidden" name="externalID" value="'.$task[5].'>" />';
            }
            ?>

            Title:
            <input type="text" minlength="1" maxlength="50" size="60" name="title" value="<?php echo $task[1];?>" /><br>
            <br>
            Date:
            <input type="date" name="date" value="<?php echo $task[2];?>" /><br><br>
            Description: <br>
            <textarea rows="5" cols="80%" maxlength="250" name="description"><?php echo $task[3];?></textarea><br><br>
            <input hidden="hidden" type="checkbox" name="status" <?php
            if ($task[4] == 1) {
                echo "checked='true'";
            }
            ?>><br><br>
            <input type="submit" name="apply" value="Apply Changes" />
        </form>
    </div>
</div>
</body>
