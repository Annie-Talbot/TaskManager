
<h1> Select Which Tasks Would You Like To Export: </h1>
<div class="task">
    <div class="taskSelect">
        SELECT
    </div>
    <div class="taskTitle">
        TITLE
    </div>
    <div class="taskId">
        DUE DATE
    </div>
    <div class="taskTitle">
        DESCRIPTION
    </div>
</div>
<form onSubmit="return confirm('Are you sure?');" action="../index.php/send_export_tasks" method="post">
    <?php
    for ($x = 0; $x < sizeof($tasks); $x++) {
        echo "<div class='task'>";
        echo "<input style='width:1%;visibility: hidden;' type='number' name='tasks[".$x."][localID]' value=".$tasks[$x][0]." readonly/>";
        echo "<input style='width:1%;visibility: hidden;' type='number' name='tasks[".$x."][status]' value=".$tasks[$x][4]." readonly/>";
        echo   '<div class="taskSelect">
                            <input type="checkbox" name="tasks['.$x.'][selected]" />
                        </div>' ;
        echo   '<div class="taskTitle">
                            <input type="text" class="readonlyInput" name="tasks['.$x.'][title]" value="'.$tasks[$x][1].'" readonly/>
                        </div>' ;
        echo   '<div class="taskId">
                            <input type="text" class="readonlyInput" name="tasks['.$x.'][due]" value="'.$tasks[$x][2].'" readonly/>
                        </div>' ;
        echo   '<div class="taskTitle">
                            <input type="text" class="readonlyInput" name="tasks['.$x.'][description]" value="'.$tasks[$x][4].'" readonly/>
                        </div>' ;
        echo "</div>";
    }
    ?>
    <div class="options_bar">
        <input type="submit" value="Export" />
    </div>
</form>