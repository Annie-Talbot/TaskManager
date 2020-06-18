<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../content/stylesheet.css">
    <script type="text/javascript" src="../content/js.js"></script>
    <!-- Refreshes the webpage after the session has run out, so the user cannot stay logged in forever-->
    <meta http-equiv="refresh" content="60*24">
    <title>Your Tasks</title>
</head>
<body>
<div class="<?php echo "container-" . $theme;?>" id = "page">
    <h1 align=left class="title" > Task Manager</h1>
    <input id="cancel_button" type="button" onclick="cancel()" value="Cancel" style="visibility: hidden;margin-left: 10px;width:60px;height:30px;"/>
    <h3 align=right style="padding-right:5%;position: fixed; top:5%;right:10%;"><a href="../index.php/preferences" style="text-decoration: none;"> Preferences </a></h3>
    <h3 align=right style="padding-right:5%;position: fixed; top:10%;right:10%;"><a href="../index.php/logout" style="text-decoration: none;"> Log Out </a></h3>
    <div class="centralisedContainer" id="local_tasks">
        <h1> Your Tasks: </h1>
        <div class="task">
            <div class="taskTitle">
                TITLE
            </div>
            <div class="taskDate">
                DATE
            </div>
            <div class="taskStatus">
                STATUS
            </div>
        </div>
        <?php
        for ($x = 0; $x < sizeof($tasks); $x++) {
            echo "<div class='task'>";
            echo "<div style='visibility: hidden;' id='external-" . $tasks[$x][0] . "' >";
                if ($tasks[$x][5] != null) {
                    echo $tasks[$x][5];
                }
            echo "</div>";
            echo "<div class='taskTitle' id='title-".$tasks[$x][0]."'>";
            echo $tasks[$x][1];
            echo "</div>";
            echo "<div class='taskDate' id='date-".$tasks[$x][0]."'>";
            echo $tasks[$x][2];
            echo "</div>";
            echo "<div class='taskStatus'>";
            if ($tasks[$x][3] == 1) {
                echo '<input type="checkbox" onchange="switchTaskStatus('.$tasks[$x][0].', \''.
                    $tasks[$x][1].'\', \''.$tasks[$x][2].'\', this, \''.
                    $tasks[$x][4].'\', \''. $tasks[$x][5].'\')" checked="checked"/>';
            }
            else {
                echo '<input type="checkbox" onchange="switchTaskStatus('.$tasks[$x][0].', \''.
                    $tasks[$x][1].'\', \''.$tasks[$x][2].'\', this, \''.
                    $tasks[$x][4].'\', \''. $tasks[$x][5].'\')" />';
            }
            echo "</div>";
            echo '<input type="button" class="taskBtn" onclick="editTask(' . $tasks[$x][0] .')" value="Edit" />';
            echo '<input type="button" class="taskBtn" onclick="deleteTask(' .$tasks[$x][0]. ')" value="Delete" />';
            echo "<div id='description-".$tasks[$x][0]."' style='visibility: hidden'>".$tasks[$x][4]."</div>";
            echo "</div>";
        }
        ?>
    </div>
    <div class="centralisedContainer" style="visibility: hidden;" id="import_tasks">
    </div>
    <div class="centralisedContainer" style="visibility: hidden;" id="export_tasks">
    </div>
    <div class="footer">
        <input type="button" class="btn" onclick="editTask(-1)" value="New Task" />
        <input type="button" class="btn" onclick="importTasks()" value="Import Tasks" />
        <input type="button" class="btn" onclick="exportTasks()" value="Export Tasks" />
    </div>
</div>
</body>
</html>
