<?php
require "content/connect.php"; // Contains the details of the connection to the database
require "content/password.php"; // Embedded into later versions of php

/**
 * Model object. This maintains a connection to the local database and holds function to manage and use
 * both the local database and the web service found on an external server. It has a no argument constructor.
 *
 */
class Model
{
    private $connection;
    private $error = false;
    /**
     * Checks whether an error has been recorded
     * @return bool true - an error has been recorded,
     *              false - no error has been recorded
     */
    public function isError()
    {
        return $this->error !== false;
    }

    /**
     * Getter for the value of the error attribute
     * @return bool|string - no error|the error details
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Model constructor. Creates the connection to the database.
     */
    public function __construct()
    {
        // Create connection
        $this->connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($this->connection->connect_error) {
            $this->error = "Connection failed: " . $this->connection->connect_error;
        }
    }

    /**
     * Model destructor. Closes the connection to the database.
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * Getter for all tasks stored in the local database belonging to a specified user.
     * @param $userID - identifies the user of whom the tasks should belong
     * @return array - The list of tasks in the data base. Each task consists of:
     *                    #0 id
     *                    #1 title
     *                    #2 date
     *                    #3 description
     *                    #4 status
     *                    #5 external id
     */
    function getTasks($userID)
    {
        $tasks = array();
        $sql = "SELECT id, title, date, status, description, externalID FROM tasks WHERE userID = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('i', $userID);
        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return $tasks;
        }
        $stmt->store_result();
        $stmt->bind_result($id, $title, $date, $status, $description, $externalID);
        while ($row = mysqli_stmt_fetch($stmt)) {
            array_push($tasks, array($id, $title, $date, $status, $description, $externalID));
        }
        $stmt->close();
        return $tasks;
    }

    /**
     * Identifies any user error with entries for username and password and checks this information
     * against the database to provide access for a valid user.
     *
     * @param $inp_username - username input by the user
     * @param $inp_password - password input by the user
     * @return bool|string - the error whether non existent(bool) or detailed in a string.
     */
    public function checkLoginCredentials($inp_username, $inp_password)
    {
        // Check the number of attempts used to log in
        $attempts_allowed = 3;
        $expiration_time = 2 * 60;
        if (isset($_COOKIE["num_password_attempts"])) {
            setcookie("num_password_attempts", $_COOKIE["num_password_attempts"] + 1, time() + $expiration_time);
            if (!($_COOKIE["num_password_attempts"] > $attempts_allowed)) {
                return "You have entered the wrong data too many times. Wait 2 minutes before trying again.";
            }
        }
        else {
            setcookie("num_password_attempts", 1, time() + $expiration_time);
        }

        // Make sure value been entered for both input fields
        $inp_username = trim($inp_username);
        if (empty($inp_username)) {
            return "Username Error: Please enter a username.";
        }
        $inp_password = trim($inp_password);
        if (empty($inp_password)) {
            return "Password Error: Please enter a password.";
        }

        // Prepare the select user statement
        $sql = "SELECT id, username, password, salt FROM users WHERE username = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $inp_username);
        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return false;
        }
        $stmt->store_result();
        // Check if the username has given any result (if it exists)
        if ($stmt->num_rows() < 1) {
            return "Username Error: that username does not exist.";
        }
        // Save the account user details
        $user = array();
        $stmt->bind_result($id, $username, $password, $salt);
        while ($row = mysqli_stmt_fetch($stmt)) {
            $user = array($row);
        }
        $stmt->close();
        // Load pepper string
        $pepper_file = fopen("content/config.txt", "r") or die("Unable to open file!");
        $pepper = fread($pepper_file, filesize("content/config.txt"));
        fclose($pepper_file);
        // Verify password
        if (password_verify($salt . $password . $pepper, $password)) {
            // Password is correct, so start a new session
            $_SESSION["username"] = $username;
            $_SESSION["userID"] = $id;
            // Destroy number of attempts cookie
            setcookie("num_password_attempts", 1, time()  - 1);
        } else {
            // Display an error message if password is not valid
            return "Password Error: The password you entered was not valid.";
        }
        return false;
    }

    /**
     * Destroys and restarts the current session to log out the current user.
     */
    public function logout() {
        session_destroy();
        session_start();
    }

    /**
     * Getter for the theme stored in the cookies for the web page display. If no theme is set, a cookie
     * is created containing the theme.
     * @return mixed|string - 'dark' or 'light' depending on the preference set.
     */
    public function getTheme() {
        if (isset($_COOKIE["theme"])) {
            return $_COOKIE["theme"];
        }
        else {
            setcookie(
                "theme", "light"
            );
            return "light";
        }
    }

    /**
     * Enables a user to create an account on the local database, the details are first validated and any error
     * reported, otherwise the new user is added to the database.
     *
     * @param $username - new username inputted by the user
     * @param $password1 - new password inputted by the user
     * @param $password2 - comfirmer password inputted by the user
     * @return string - The error report message to be displayed to the user
     */
    public function checkRegisterDetails($username, $password1, $password2)
    {
        // Validate username
        $username = $this->connection->escape_string(htmlspecialchars(trim($username)));
        if(empty($username)){
            return "Please enter a username.";
        }

        // Check if the username is already in use
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('i', $username);
        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return "Server could not connect to the database.";
        }
        $stmt->store_result();
        $stmt->bind_param("s", $userID);
        while ($row = mysqli_stmt_fetch($stmt)) {
            if (strlen($userID) > 0) {
                return "Username Error: This username is already taken.";
            }
        }
        $stmt->close();

        // Validate password (first entry)
        $password1 = $this->connection->escape_string(htmlspecialchars(trim($password1)));
        if(empty($password1)){
            return "Password Error: Please enter a password.";
        } elseif(strlen($password1) < 6){
            return "Password Error: Password must have at least 6 characters.";
        } /*elseif(!preg_match('/[/.,;#-*&^%$£"!¬`?<>:@~}{]/', $password1)) {
        $password_err = "Password must contain a special character.";*/
        elseif(!preg_match('/[A-Z]/', $password1) || !preg_match('/[a-z]/', $password1)){
            return "Password Error: Password must contain at least one uppercase and lowercase letter.";
        }elseif(!preg_match('/[0-9]/', $password1)){
            return "Password Error: Password must contain at least one number.";
        }

        // Validate the confirm password (second password entry)
        $password2 = $this->connection->escape_string(htmlspecialchars(trim($password2)));
        if(empty($password2)){
            return "Confirm Passford Error: Please enter a confirm password.";
        } else{
            if($password1 != $password2){
                return "Confirm Password Error: Passwords do not match.";
            }
        }

        // Create password hash
        $salt = bin2hex(openssl_random_pseudo_bytes(200));
        $pepper_file = fopen("content/config.txt", "r") or die("Unable to open file!");
        $pepper = fread($pepper_file, filesize("content/config.txt"));
        fclose($pepper_file);
        $password_hash = password_hash($salt . $password1 . $pepper, PASSWORD_DEFAULT);

        // Add new account details to the database
        $sql = "INSERT INTO users (username, password, salt) VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('sss', $username, $password_hash, $salt);
        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return "";
        }
        $stmt->close();
        return "";
    }

    /**
     * Removes a task from the local database (after checking the task belongs to the user currently
     * logged in).
     *
     * @param $taskID - the id that resembles the task
     */
    public function deleteTask($taskID) {
        // Checks if the user attempting to remove that task is the user currently logged in
        $sql = "SELECT userID FROM tasks WHERE id= ?";
        if (!($stmt = $this->connection->prepare($sql))) {
            $this->error = "Database error: " . $this->connection->error;
            return;
        }
        $stmt->bind_param('i', $taskID);
        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return;
        }
        $stmt->store_result();
        $stmt->bind_result($userID);
        while ($row = mysqli_stmt_fetch($stmt)) {
            $user = $userID;
        }
        $stmt->close();
        if ($user !== $_SESSION["userID"]) {
            $this->error = "User Error: the task you have tried to delete a task that does not belong to you.";
            return;
        }
        // Removes task
        $sql2 = "DELETE FROM tasks WHERE id=" . $_GET["taskID"];
        if ($this->connection->query($sql2) === false) {
            $this->error = "Database error: " . $this->connection->error;
            return;
        }
        return;
    }

    /**
     * Receives all information stored in the local database about the specified task.
     *
     * @param $taskID - the id of the task
     * @return array - An array containing all task information:
     *                  #0 - id
     *                  #1 - task title
     *                  #2 - task due date
     *                  #3 - task description
     *                  #4 - task status (0 - not done, 1 - done)
     *                  #5 - external id
     */
    public function getTask($taskID) {
        $task = array();
        $task[0] = $taskID;
        if ($taskID == -1) {
            // A new task is to be created
            $task[1] = "Enter title here";
            $task[2] = "2020-04-28";
            $task[3] = "Enter a description here";
            $task[4] = 0;
            $task[5] = null;
            return $task;
        }
        // Gets all task details from the database
        $sql = "SELECT userID, title, date, status, description, externalID FROM tasks WHERE id = ?";
        if (!($stmt = $this->connection->prepare($sql))) {
            $this->error = "Database error: " . $this->connection->error;
            return $task;
        }
        $stmt->bind_param('i', $taskID);
        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return $task;
        }
        $stmt->store_result();
        $stmt->bind_result($userID, $title, $date, $status, $description, $externalID);
        while ($row = mysqli_stmt_fetch($stmt)) {
            if ($userID !== $_SESSION["userID"]) {
                $this->error = "User Error: Access to this task has been denied.";
                return $task;
            }
            $task[1] = $title;
            $task[2] = $date;
            $task[3] = $description;
            $task[4] = $status;
            $task[5] = $externalID;
        }
        $stmt->close();
        return $task;
    }

    /**
     * Updates the information stored about a task, a new task can be added if the given task id
     * is -1.
     *
     * @param $taskID - the task id (-1 for a new task)
     * @param $title - the new title of the task
     * @param $date - the new due date for the task
     * @param $description - the new description for the task
     * @param $status - the new task status
     * @param $externalID - the id that represents the task on the web service server
     */
    public function changeTask($taskID, $title, $date, $description, $status, $externalID) {
        $title = $this->connection->escape_string(htmlspecialchars($title));
        $description = $this->connection->escape_string(htmlspecialchars($description));
        if ($status == null) {
            $status = 0;
        }
        else {
            $status = 1;
        }
        if ($taskID == -1) {
            // If task is not already in local database
            if ($externalID == null) {
                // If task is not from an external database
                // Adds task WITHOUT eternal id to local database
                $sql = "INSERT INTO tasks (userID, title, date, description, status) VALUES (?, ?, ?, ?, ?)";
                if (!($stmt = $this->connection->prepare($sql))) {
                    $this->error = "Database error: 1" . $this->connection->error;
                    return;
                }
                $stmt->bind_param('isssi', $_SESSION["userID"], $title, $date, $description, $status);
            }
            else {
                // If the task is from an external database
                // Adds task WITH eternal id to local database
                $sql = "INSERT INTO tasks (userID, title, date, description, status, externalID) VALUES (?, ?, ?, ?, ?, ?)";
                if (!($stmt = $this->connection->prepare($sql))) {
                    $this->error = "Database error: 2" . $this->connection->error;
                    return;
                }
                $stmt->bind_param('isssii', $_SESSION["userID"], $title, $date, $description, $status, $externalID);
            }
            if ($stmt->execute() === false) {
                $this->error = "Database error: 3" . $stmt->error;
                return;
            }
            $stmt->close();
            return;
        }
        // Task already exists in the local database
        if ($externalID == null) {
            // If the task is NOT also in the external server
            $sql = "UPDATE tasks SET title=?, date=?, description=?, status=? WHERE id=?";
            if (!($stmt = $this->connection->prepare($sql))) {
                $this->error = "Database error: " . $this->connection->error;
                return;
            }
            $stmt->bind_param('sssii', $title, $date, $description, $status, $taskID);
        }
        else {
            // If the task IS not also in the external server
            $sql = "UPDATE tasks SET title=?, date=?, description=?, status=?, externalID=? WHERE id=?";
            if (!($stmt = $this->connection->prepare($sql))) {
                $this->error = "Database error: " . $this->connection->error;
                return;
            }
            $stmt->bind_param('sssiii', $title, $date, $description, $status, $externalID, $taskID);
        }

        if ($stmt->execute() === false) {
            $this->error = "Database error: " . $this->connection->error;
            return;
        }
        $stmt->close();
        return;
    }

    /**
     * Obtains all tasks from the server web service.
     *
     * @return array - An array containing every task store on the server.
     */
    public function getImportTasks() {
        $tasks = array();
        // Sends curl request to server containing web service and recieves xml encoded tasklist
        $url = "http://students.emps.ex.ac.uk/dm656/tasks.php";
        if (($handle = curl_init())===false) {
            $this->error = "Curl error: " . curl_error($handle);
            return $tasks;
        } else {
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_FAILONERROR, true);
        }
        curl_setopt($handle, CURLOPT_URL, $url);
        // Error 405 should never happen because this is hard coded in, thus there is no handling
        curl_setopt($handle, CURLOPT_HTTPGET, true);
        if (($output = curl_exec($handle))!==false) {
            $xml = simplexml_load_string($output);
            foreach ($xml->task as $task)
            {
                array_push($tasks, array($task->id, $task->name, $task->due));
            }
        }
        curl_close($handle);

        return $tasks;
    }

    /**
     * Appends a list of tasks from the web server to the local database
     *
     * @param $tasks - the list of tasks
     * @return string - a report detailing any error that occurs during the import
     */
    public function addImportTasks($tasks) {
        $report = "";
        for ($x = 0; $x < sizeof($tasks); $x++) {
            // If the tasks was selected
            if ($tasks[$x]['selected'] != null) {
                $url = "http://students.emps.ex.ac.uk/dm656/task.php/".$tasks[$x]['id'];
                if (($handle = curl_init()) === false) {
                    $this->error = "Curl error: " . curl_error($handle);
                    return $report;
                } else {
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle, CURLOPT_FAILONERROR, true);
                }
                curl_setopt($handle, CURLOPT_URL, $url);
                curl_setopt($handle, CURLOPT_HTTPGET, true);
                if (($output = curl_exec($handle)) !== false) {
                    $xml = simplexml_load_string($output);
                    $this->changeTask(-1, $xml->name, $xml->due, $xml->description, null, $xml->id);
                } else {
                    $report .= "Could not import task ". $tasks[$x]['id'] . ". " . curl_error($handle);
                }
                curl_close($handle);
            }
        }
        return $report;
    }

    /**
     * Sends the specified list of tasks to the web server, each returning the id used to store the task on that
     * system, which is then added to the local task.
     *
     * @param $tasks
     * @return string
     */
    function sendExportTasks($tasks) {
        $report = "";
        for ($x = 0; $x < sizeof($tasks); $x++) {
            // If the tasks was selected
            if ($tasks[$x]['selected'] != null) {
                echo  $tasks[$x]["due"];
                // Create task xml
                $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><taskinfo><name>";
                $xml .= $tasks[$x]["title"];
                $xml .= "</name><due>";
                $xml .= $tasks[$x]["due"];
                $xml .= " 00:00:00</due><description>";
                $xml .= $tasks[$x]["description"];
                $xml .= "</description></taskinfo>";
                $url = "http://students.emps.ex.ac.uk/dm656/add.php";
                if (($handle = curl_init()) === false) {
                    $this->error = "Curl error: " . curl_error($handle);
                    return $report;
                } else {
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle, CURLOPT_FAILONERROR, true);
                }
                curl_setopt($handle, CURLOPT_URL, $url);
                curl_setopt ($handle, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $xml);

                if (($output = curl_exec($handle)) !== false) {
                    $xml = simplexml_load_string($output);
                    $this->changeTask($tasks[$x]["localID"], $tasks[$x]["title"], $tasks[$x]["due"],
                                      $tasks[$x]["description"], $tasks[$x]["status"], $xml->id);
                    $newExternalID = $xml->id;
                } else {
                    $report .= "Could not import a task. " . curl_error($handle);
                }
                curl_close($handle);
                $this->updateTaskStatus($newExternalID, $tasks[$x]["status"], $_SESSION["userID"]);
            }
        }
        return $report;
    }

    /**
     * Changes the status of an external task.
     *
     * @param $externalID - the id of the task on the external server
     * @param $status - the new status
     * @param $userID - the user who is changing the status
     * @return string - any error to report to the user
     */
    public function updateTaskStatus($externalID, $status, $userID) {
        $report = "";
        // Creates user xml
        $userXML = "<user><id>";
        $userXML .= $userID;
        $userXML .= "</id></user>";
        if ($status == 1){
            $url = "http://students.emps.ex.ac.uk/dm656/check.php/".$externalID;
        } else {
            $url = "http://students.emps.ex.ac.uk/dm656/uncheck.php/".$externalID;
        }
        if (($handle = curl_init()) === false) {
            $this->error = "Curl error: " . curl_error($handle);
            return $report;
        } else {
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($handle, CURLOPT_FAILONERROR, true);
        }
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt ($handle, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($handle, CURLOPT_POSTFIELDS, $userXML);
        if (curl_exec($handle) == false) {
            $report .= "Could not update task status on the server. ";
            $report .= curl_error($handle);
        }
        curl_close($handle);
        return $report;
    }
}