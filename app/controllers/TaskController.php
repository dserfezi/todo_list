<?php

class TaskController
{

    /**
     * Save new task in database
     */
    public function create(){

        // Get inputs
        $inputs = $_POST['inputs'];

        // Process inputs
        foreach ($inputs as $inputKey => $inputValue) {
            // Validate
            if(empty($inputValue)){
                die('<p>All inputs are required.<p>');
            }
            // Escape
            $$inputKey = Query::escape($inputValue);
        }

        // Validate user (prevent user to save on another user' list)
        $usersLists = Query::select('SELECT id FROM lists WHERE user_id LIKE '.$_SESSION['userId']);
        foreach ($usersLists as $listId) {
            // If listId from database matches list_id from input then
            // this is one of the logged in user' list
            if($listId['id']==$list_id){
                // Save in database
                $insertOk = Query::run('INSERT INTO tasks (name, priority, deadline, status, list_id)
                  VALUES ("'.$name.'", "'.$priority.'", "'.$deadline.'", "'.$status.'", "'.$list_id.'")');

                if($insertOk!==false){
                    $lastInsertedId = mysqli_insert_id(Query::getConnection());

                    // Calculate date difference
                    $diffNow = new DateTime(date('Y-m-d'));
                    $diffDeadline  = new DateTime($deadline);
                    $difference = $diffNow->diff($diffDeadline);

                    // Pull list' tasks data from database
                    $databaseData = Query::select('SELECT status FROM tasks WHERE list_id LIKE ' . $list_id);
                    $tasks = count($databaseData);
                    $statuses = array();
                    foreach ($databaseData as $task) {
                        $statuses[] = $task['status'];
                    }
                    $undone = count(array_keys($statuses, 1));

                    // Create output
                    $output = array();
                    $output['taskId'] = $lastInsertedId;
                    $output['name'] = $name;
                    $output['priority'] = $priority;
                    $output['deadline'] = date("d.m.Y", strtotime($deadline));
                    $output['status'] = $status;
                    $output['days'] = $difference->format('%R').$difference->days;
                    $output['tasks'] = $tasks;
                    $output['undone'] = $undone;
                    $output['completed'] = round((($tasks-$undone)/$tasks)*100).' %';
                    echo json_encode($output);
                } else {
                    trigger_error('Task create error.');
                    echo false;
                }
            }
        }

    }


    /**
     * Sort task table in list-edit
     * @param $column
     */
    public function listEditTableSort($columnAndList_id){

        // Process input data
        $columnAndList_id = explode('-', $columnAndList_id);
        $column = Query::escape($columnAndList_id[0]);
        $list_id = Query::escape($columnAndList_id[1]);

        // Check if this list_id belongs to logged in user
        $validate = Query::select('SELECT id FROM lists WHERE id LIKE '.$list_id.' AND user_id LIKE '.$_SESSION['userId']);
        if(empty($validate)){
            trigger_error('Trying to sort list not owned by user.');
            echo 'Sort error.';
            exit;
        }

        $tasks = array();

        // TODO: implement asc or desc, depending on the column name

        // Pull this list' tasks from database
        $databaseData = Query::select('SELECT id, name, priority, deadline, status
                                      FROM tasks
                                      WHERE list_id LIKE '.$list_id.'
                                      ORDER BY '.$column);
        // Compose array of tasks
        foreach ($databaseData as $taskKey => $task) {
            $tasks[$taskKey]['id'] = $task['id'];
            $tasks[$taskKey]['name'] = $task['name'];
            $tasks[$taskKey]['priority'] = $task['priority'];
            $tasks[$taskKey]['deadline'] = date("d.m.Y", strtotime($task['deadline']));;
            $tasks[$taskKey]['status'] = $task['status'];
        }

        $tbody = '';
        if(!empty($tasks)){
            $taskTableId = 1;
            foreach ($tasks as $task) {
                // Calculate date difference
                $diffNow = new DateTime(date('Y-m-d'));
                $diffDeadline  = new DateTime($task['deadline']);
                $difference = $diffNow->diff($diffDeadline);

                $tbody .= '<tr>
                                <td class="hidden">'.$task['id'].'</td>
                                <td class="table-task-id">'.$taskTableId.'</td>
                                <td>'.$task['name'].'</td>
                                <td>'.$task['priority'].'</td>
                                <td>'.$task['deadline'].'</td>
                                <td>'.(($task['status']==2) ? 'Done' : 'Undone').'</td>
                                <td>'.(($task['status']==2) ? '' : $difference->format('%R').$difference->days).'</td>
                                <td class="action-icons">
                                    <a href="/task-edit/'.$task['id'].'">
                                        <span class="action-icon">
                                            <i class="fa fa-pencil-square-o"></i>
                                        </span class="action-icon">
                                    </a>
                                    <a href="/task-delete/'.$task['id'].'">
                                        <span class="action-icon">
                                            <i class="fa fa-times"></i>
                                        </span>
                                    </a>
                                </td>
                            </tr>';
                $taskTableId++;
            } // end foreach
        } // end if

        echo $tbody;

    }


    /**
     * Get task data from database
     * @param $id
     */
    public function edit($id){

        // Check if this is user' task (prevents one user from editing another user' task)
        $id = Query::escape($id);
        $validate = Query::select('SELECT list_id FROM tasks WHERE id LIKE '.$id);
        if(!empty($validate)){
            // Try to pull list with previously retrieved task' list_id and logged in user' id
            $validate = Query::select('SELECT id FROM lists WHERE id LIKE '.$validate[0]['list_id'].' AND user_id LIKE '.$_SESSION['userId']);
            if(empty($validate) && $validate!==false){
                // $validate will be empty if no record is found and false if query fails
                trigger_error('Trying to edit task not owned by user.');
                echo 'false';
                exit;
            }
        }

        $taskEdit = Query::select('SELECT * FROM tasks WHERE id LIKE '.$id);
        $task = array();
        $task['id'] = $taskEdit[0]['id'];
        $task['name'] = $taskEdit[0]['name'];
        $task['priority'] = $taskEdit[0]['priority'];
        $task['deadline'] = $taskEdit[0]['deadline'];
        $task['status'] = $taskEdit[0]['status'];

        echo json_encode($task);

    }


    public function update($id){

        // Check if this is user' task (prevents one user from editing another user' task)
        $id = Query::escape($id);
        $validate = Query::select('SELECT list_id FROM tasks WHERE id LIKE '.$id);
        $list_id = $validate[0]['list_id'];
        if(!empty($validate)){
            // Try to pull list with previously retrieved task' list_id and logged in user' id
            $validate = Query::select('SELECT id FROM lists WHERE id LIKE '.$list_id.' AND user_id LIKE '.$_SESSION['userId']);
            if(empty($validate) && $validate!==false){
                // $validate will be empty if no record is found and false if query fails
                trigger_error('Trying to edit task not owned by user.');
                echo 'false';
                exit;
            }
        }

        // Process inputs
        $inputs = $_POST['inputs'];
        foreach ($inputs as $inputKey => $inputValue) {
            // Validate
            if(empty($inputValue)){
                die('<p>All inputs are required.<p>');
            }
            // Escape
            $$inputKey = Query::escape($inputValue);
        }

        // Save in database
        $taskUpdate = Query::run('UPDATE tasks
                                  SET name="'.$name.'", priority="'.$priority.'", deadline="'.$deadline.'", status="'.$status.'"
                                  WHERE id LIKE '.$id);

        if(!$taskUpdate){
            trigger_error('Task update error.');
            echo false;
        } else {
            // Calculate date difference
            $diffNow = new DateTime(date('Y-m-d'));
            $diffDeadline  = new DateTime($deadline);
            $difference = $diffNow->diff($diffDeadline);

            // Pull list' tasks data from database
            $databaseData = Query::select('SELECT status FROM tasks WHERE list_id LIKE ' . $list_id);
            $tasks = count($databaseData);
            $statuses = array();
            foreach ($databaseData as $task) {
                $statuses[] = $task['status'];
            }
            $undone = count(array_keys($statuses, 1));

            // Create output
            $output = array();
            $output['taskId'] = $id;
            $output['name'] = $name;
            $output['priority'] = $priority;
            $output['deadline'] = date("d.m.Y", strtotime($deadline));;
            $output['status'] = $status;
            $output['days'] = $difference->format('%R').$difference->days;
            $output['tasks'] = $tasks;
            $output['undone'] = $undone;
            $output['completed'] = round((($tasks-$undone)/$tasks)*100).' %';
            echo json_encode($output);
        }

    }


    /**
     * Delete task from database
     * @param $id
     */
    public function destroy($id){

        // Check if this is user' task (prevents one user from deleting another user' task)
        $id = Query::escape($id);
        $validate = Query::select('SELECT list_id FROM tasks WHERE id LIKE '.$id);
        $list_id = $validate[0]['list_id'];
        if(!empty($validate)){
            // Try to pull list with previously retrieved task' list_id and logged in user' id
            $validate = Query::select('SELECT id FROM lists WHERE id LIKE '.$list_id.' AND user_id LIKE '.$_SESSION['userId']);
            if(empty($validate) && $validate!==false){
                // $validate will be empty if no record is found and false if query fails
                trigger_error('Trying to delete task not owned by user.');
                echo json_encode(array('success' => false));
                exit;
            }
        }

        // Delete task
        $taskDelete = Query::run('DELETE FROM tasks WHERE id LIKE '.$id);
        if(!$taskDelete){
            trigger_error('Task delete error.');
            echo json_encode(array('success' => false));
        } else {
            // Pull list' tasks data from database
            $databaseData = Query::select('SELECT status FROM tasks WHERE list_id LIKE ' . $list_id);
            $tasks = count($databaseData);
            $statuses = array();
            foreach ($databaseData as $task) {
                $statuses[] = $task['status'];
            }
            $undone = count(array_keys($statuses, 1));

            // Create output
            $output = array();
            $output['tasks'] = $tasks;
            $output['undone'] = $undone;
            $output['completed'] = round((($tasks-$undone)/$tasks)*100).' %';
            echo json_encode($output);
        }

    }

}

?>