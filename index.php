<?php
session_start();
require_once "model.php";
$uri = basename(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
if ($uri === "at698" || $uri === "index.php")
{
    $uri = 'home';
}
if (isset($_SESSION["userID"])) { //the user is logged in
    switch ($uri) {
        case "home":
            // No path given redirects to the task display page
            header("location: index.php/mytasks");
            break;
        case "mytasks":
            // Task display page
            $model = new Model();
            $theme = $model->getTheme();
            $tasks = $model->getTasks($_SESSION["userID"]);
            if ($model->isError()) {
                die($model->getError());
            }
            require "views/tasks.php";
            break;
        case "login":
            // Required so the undo button on the browser logs you out before you try to log in again
        case "logout":
            // Restarts the session and then directs to the log in page
            $model = new Model();
            $model->logout();
            header("location: ./login");
            break;
        case "preferences":
            // Loads the page containing preference settings
            $model = new Model();
            $theme = $model->getTheme();
            require "views/preferences.php";
            break;
        case "edit_task":
            // Loads all details of a task and allows to edit
            $model = new Model();
            $theme = $model->getTheme();
            $task = $model->getTask($_GET["taskID"]);
            require "views/edit_task.php";
            break;
        case "add_task":
            // Either adds or updates the details of a task to the server
            // Then directs to task display page
            $model = new Model();
            $model->changeTask($_POST["taskID"], $_POST["title"], $_POST["date"], $_POST["description"], $_POST["status"], $_POST["externalID"]);
            if ($model->isError()) {
                die($model->getError());
            }
            header ("location: ./mytasks");
            break;
        case "delete_task":
            // Removes the specified task from the database
            //Then directs to the task display page
            $model = new Model();
            $model->deleteTask($_GET["taskID"]);
            if ($model->isError()) {
                die($model->getError());
            }
            header("location: ./mytasks");
            break;
        case "display_import_tasks":
            // Selects all tasks that can be imported and displays them to the user
            $model = new Model();
            $tasks = $model->getImportTasks();
            if ($model->isError()) {
                die($model->getError());
            }
            require "views/import_tasks.php";
            break;
        case "add_import_tasks":
            // Moves the selected tasks from the external web service to the local database
            // Then directs to the task display
            $model = new Model();
            $report = $model->addImportTasks($_POST["tasks"]);
            if ($model->isError()) {
                die($model->getError());
            }
            if (strlen($report) > 1) {
                echo "<script type='text/javascript'>alert(" . $report . ");</script>";
            }
            header("location: ./mytasks");
            break;
        case "display_export_tasks":
            // Selects all tasks that can be exported and displays them to the user
            $model = new Model();
            $tasks = $model->getTasks($_SESSION["userID"]);
            if ($model->isError()) {
                die($model->getError());
            }
            require "views/export_tasks.php";
            break;
        case "send_export_tasks":
            // Copies the selected tasks from the local database to the web service
            // Reports any errors that occur
            // Then directs to the task display
            $model = new Model();
            $report = $model->sendExportTasks($_POST["tasks"]);
            if ($model->isError()) {
                die($model->getError());
            }
            if (strlen($report) > 1) {
                echo "<script type='text/javascript'>alert('" . $report . "');</script>";
            }
            header("location: ./mytasks");
            break;
        case "update_task_status":
            // Sends a task status update to the web service
            // Reports any errors that occur
            // Then directs to the task display
            $model = new Model();
            $report = $model->updateTaskStatus($_POST["externalID"], $_POST["status"], $_SESSION["userID"]);
            if ($model->isError()) {
                die($model->getError());
            }
            echo $report;
            break;
        default:
            // Page specified does not exist so display error
            header("HTTP/1.1 404 Not Found");
            echo "<html><body><h1>Page Not Found</h1></body></html>";
    }
}
else {
    // The user is not logged in
    switch($uri) {
        // All page's that require a user to be logged in for access
        case "home":
        case "mytasks":
        case "preferences":
        case "edit_task":
        case "add_task":
        case "delete_task":
        case "display_import_tasks":
        case "add_import_tasks":
        case "display_export_tasks":
        case "send_export_tasks":
        case "update_task_status":
            // Displays welcome and log in page
            $model = new Model();
            $theme = $model->getTheme();
            require "views/welcomelogin.php";
            break;
        case "login":
            // Validates any log in details given and reports back errors
            $model = new Model();
            $theme = $model->getTheme();
            if (isset($_POST["username"]) && isset($_POST["password"])) {
                $login_err = $model->checkLoginCredentials($_POST["username"], $_POST["password"]);
                if ($model->isError()) {
                    die($model->getError());
                } elseif ($login_err !== false) {
                    require "./views/badlogin.php";
                } else {
                    // If successful a session is started and the user is directed to the task display page
                    header("location: ./mytasks");
                }
            } else {
                require "./views/badlogin.php";
            }
            break;
        case "register":
            // Validates register details given and reports any errors
            $model = new Model();
            $theme = $model->getTheme();
            $register_err = "";
            if (isset($_POST["username"]) && isset($_POST["password1"]) && isset($_POST["password2"])) {
                $register_err = $model->checkRegisterDetails($_POST["username"], $_POST["password1"],
                                                             $_POST["password2"]);
                if ($model->isError()) {
                    die($model->getError());
                } elseif ($register_err !== "") {
                    // Found error in entry details
                    require "./views/badregister.php";
                } else {
                    // If all details are fine the user is added to local database and directed to log in
                    header("location: ./login");
                }
            } else {
                require "./views/badregister.php";
            }
            break;
        default:
            // Page specified does not exist so display error
            header("HTTP/1.1 404 Not Found");
            echo "<html><body><h1>Page Not Found</h1></body></html>";
    }
}