/**
 * This function sends a request to the web service of which responds with a list of tasks
 * that the user is able to import. The task display is then replaced with this list.
 */
function importTasks() {
    var xhttp = new XMLHttpRequest();
    var url = "../index.php/display_import_tasks";
    xhttp.open("POST", url, false);
    xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    // Set error response
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("local_tasks").style.visibility = "hidden";
            document.getElementById("export_tasks").innerHTML = "";
            document.getElementById("export_tasks").style.visibility = "hidden";
            document.getElementById("import_tasks").innerHTML = this.responseText;
            document.getElementById("import_tasks").style.visibility = "visible";
            document.getElementById("cancel_button").style.visibility = "visible";
        }
    };
    // Send request
    xhttp.send();
}

/**
 * This function sends a request to the local server of which responds with a list of tasks
 * that the user is able to export. The task display is then replaced with this list.
 */
function exportTasks() {
    var xhttp = new XMLHttpRequest();
    var url = "../index.php/display_export_tasks";
    xhttp.open("POST", url, false);
    xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    // Set error response
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("local_tasks").style.visibility = "hidden";
            document.getElementById("import_tasks").innerHTML = "";
            document.getElementById("import_tasks").style.visibility = "hidden";
            document.getElementById("export_tasks").innerHTML = this.responseText;
            document.getElementById("export_tasks").style.visibility = "visible";
            document.getElementById("cancel_button").style.visibility = "visible";
        }
    };
    // Send request
    xhttp.send();
}

/**
 * This function changes the websites theme either to dark, or to light. The details of these
 * themes are specified in the stylesheet file.
 *
 * @param e The checkbox used to select dark mode (or not)
 */
function switchTheme(e) {
    if (e.checked) {
        document.getElementById("container").className = "container-dark";
        document.cookie = "theme=dark";
    }
    else {
        document.getElementById("container").className = "container-light";
        document.cookie = "theme=light";
    }
}

/**
 * This function send a request to the server to remove the specified task (via task id) from
 * the local server database.
 * @param e task id
 */
function deleteTask(e) {
    window.location.href = "./delete_task?taskID=" + e;
}

/**
 * This function redirects the user to the edit page in order ot edit the task specified by task
 * id.
 * @param e task id
 */
function editTask(e) {
    window.location.href = "./edit_task?taskID=" + e;
}

/**
 * This function changes the state of a task. If the task has an external ID (is on the web
 * server) then an attempt is made to change the state of the external task, returning an
 * error if this is unsuccesful and thus not changing the local task's state. Otherwise,
 * the local task is updated.
 *
 * @param taskID    task id
 * @param title     task title
 * @param date      task due date
 * @param checkbox  new task status checkbox
 * @param description   task description
 * @param externalID    task external web service ID (null if task is not external)
 * @param userID    ID of user updating the task status
 */
function switchTaskStatus(taskID, title, date, checkbox, description, externalID, userID) {
    if (externalID !== null && externalID !== "undefined") {
        var vars="externalID=" + externalID;
        if (checkbox.checked) {
            vars += "&status=1";
        }
        else {
            vars +="&status=0";
        }
        var updateRequest = new XMLHttpRequest();
        var updateUrl = "../index.php/update_task_status";
        updateRequest.open("POST", updateUrl, false);
        updateRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        updateRequest.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText.length === 0) {
                    var taskvars = "taskID=" + taskID + "&title=" + title + "&date=" + date + "&description=" + description + "&externalID=" + externalID;
                    if (checkbox.checked) {
                        taskvars += "&status=1";
                    }
                    switchTaskStatusInternal(taskvars);
                }
                else {
                    if (this.responseText.includes("409")) {
                        alert(this.responseText + " - Someone else has already completed this task. Deleting the task " +
                            "now.");
                        deleteTask(taskID);
                    } else {
                        if (this.responseText.includes("401")) {
                            alert(this.responseText + " - You did not complete this task, so you cannot undo it's " +
                                "complete status");
                        }
                        else {
                            alert(this.responseText);
                        }
                        // Revert change on current tasks display
                        if (checkbox.checked) {
                            checkbox.checked = null;
                        }
                        else {
                            checkbox.checked = true;
                        }
                    }
                }
            }
        };
        // Send request
        updateRequest.send(vars);
    }
    else {
        var localvars = "taskID=" + taskID + "&title=" + title + "&date=" + date + "&description=" + description;
        if (checkbox.checked) {
            localvars += "&status=1";
        }
        switchTaskStatusInternal(localvars);
    }
}

/**
 * Updates the status of a task using the variables given as an argument
 * @param variables string containing url encoded fields of data e.g. "fname=John&lname=Doe"
 */
function switchTaskStatusInternal(variables){
    var xhttp = new XMLHttpRequest();
    var url = "../index.php/add_task";
    xhttp.open("POST", url, false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    // Set error response
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status !== 200) {
            alert("An error occured whilst updating this task locally. Error code: " + this.status);
        }
    };
    // Send request
    xhttp.send(variables);
}

/**
 * This function returns the tasks displaying web page to its normal view (with the local task and
 * their details displayed), removing and hiding both the import and export task options.
 */
function cancel() {
    document.getElementById("export_tasks").innerHTML = "";
    document.getElementById("export_tasks").style.visibility = "hidden";
    document.getElementById("import_tasks").innerHTML = "";
    document.getElementById("import_tasks").style.visibility = "hidden";
    document.getElementById("cancel_button").style.visibility = "hidden";
    document.getElementById("local_tasks").style.visibility = "visible";
}