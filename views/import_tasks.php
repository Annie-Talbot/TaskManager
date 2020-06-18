
<h1> Select Which Tasks Would You Like To Import: </h1>
<div class="task">
    <div class="taskSelect">
        SELECT
    </div>
    <div class="taskId">
        ID
    </div>
    <div class="taskTitle">
        TITLE
    </div>
    <div class="taskDate">
        DUE DATE
    </div>
</div>
<form onSubmit="return confirm('Are you sure?');" action="../index.php/add_import_tasks" method="post">
<?php
for ($x = 0; $x < sizeof($tasks); $x++) {
    echo "<div class='task'>";
        echo   '<div class="taskSelect">
                    <input type="checkbox" name="tasks['.$x.'][selected]" />
                </div>' ;
        echo   '<div class="taskId">
                    <input type="text" class="readonlyInput" name="tasks['.$x.'][id]" value="'.$tasks[$x][0].'" readonly/>
                </div>' ;
        echo   '<div class="taskTitle">
                    <input type="text" class="readonlyInput" name="tasks['.$x.'][title]" value="'.$tasks[$x][1].'" readonly/>
                </div>' ;
        echo   '<div class="taskDate">
                    <input type="text" class="readonlyInput" name="tasks['.$x.'][title]" value="'.$tasks[$x][2].'" readonly/>
                </div>' ;
    echo "</div>";
}
?>
    <div class="options_bar">
        <input type="submit" value="Import" />
    </div>
</form>