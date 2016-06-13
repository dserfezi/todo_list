<?php

class ListController
{

    /**
     * Show the form for creating a new list
     * @return bool|mixed
     */
    public function create(){

        return View::show('list-create');

    }


    /**
     * Save new list in database
     * @return bool
     */
    public function save(){

        // Process data
        $listName = Query::escape($_POST['name']);

        // Save list in database
        App::getModel('ToDoList');
        $list = new ToDoList();
        $list->name = $listName;
        $list->createdAt = date('Y-m-d H:i:s');
        $list->userId = $_SESSION['userId'];
        $list->save();

        $lastInsertedId = mysqli_insert_id(Query::getConnection());
        return View::redirect('/list-edit/'.$lastInsertedId);

    }


    /**
     * Show list data
     * Edit is done with ajax
     * @param $id
     * @return bool|mixed
     */
    public function edit($id){

        $listData = array();

        // Pull list data from database
        $id = Query::escape($id);
        $databaseData = Query::select('SELECT id, name, DATE_FORMAT(DATE(created_at), "%d.%m.%Y") AS created_at FROM lists WHERE id LIKE '.$id.' AND user_id LIKE '.$_SESSION['userId']);
        // Prevent users to edit each others lists
        if(empty($databaseData)) return View::redirect('/');
        // Compose array of list data
        $listData['id'] = $databaseData[0]['id'];
        $listData['name'] = $databaseData[0]['name'];
        $listData['created_at'] = $databaseData[0]['created_at'];

        // Pull list' tasks data from database
        $databaseData = Query::select('SELECT id, name, priority, deadline, status FROM tasks WHERE list_id LIKE ' . $id);
        // Insert tasks into array of list data
        foreach ($databaseData as $task) {
            $listData['tasks'][] = $task;
        }

        return View::show('list-edit', 'listData', $listData);

    }


    /**
     * Sort list table in dashboard
     * @param $column
     */
    public function dashboardTableSort($column){

        $lists = array();

        // Pull lists data from database
        $column = Query::escape($column);
        $databaseData = Query::select('SELECT id, name, DATE_FORMAT(DATE(created_at), "%d.%m.%Y") AS created_at
                                      FROM lists
                                      WHERE user_id LIKE "'.$_SESSION['userId'].'"
                                      ORDER BY '.$column);
        // Compose array of lists data
        foreach ($databaseData as $listKey => $list) {
            $lists[$listKey]['id'] = $list['id'];
            $lists[$listKey]['name'] = $list['name'];
            $lists[$listKey]['created_at'] = $list['created_at'];

            // Pull list' tasks data from database
            $databaseTaskData = Query::select('SELECT name, priority, deadline, status FROM tasks WHERE list_id LIKE ' . $list['id']);
            // Insert tasks into array of list data
            foreach ($databaseTaskData as $task) {
                $lists[$listKey]['tasks'][] = $task['status'];
            }
        }

        $tbody = '';
        if(!empty($lists)){
            $listId = 1;
            foreach ($lists as $list) {
                $tbody .= '<tr>
                                <td class="table-list-id">'.$listId.'</td>
                                <td>'.$list['name'].'</td>
                                <td>'.$list['created_at'].'</td>
                                <td>'.((isset($list['tasks'])) ? count($list['tasks']) : 0).'</td>
                                <td>'.((isset($list['tasks'])) ? $undone = count(array_keys($list['tasks'], 1)) : 0).'</td>
                                <td class="action-icons">
                                    <a href="/list-edit/'.$list['id'].'">
                                        <span class="action-icon">
                                            <i class="fa fa-file-text-o"></i>
                                        </span class="action-icon">
                                    </a>
                                    <a href="/list-delete/'.$list['id'].'">
                                        <span class="action-icon">
                                            <i class="fa fa-times"></i>
                                        </span>
                                    </a>
                                </td>
                            </tr>';
                $listId++;
            } // end foreach
        } // end if

        echo $tbody;

    }


    /**
     * Delete list from database
     * @param $id
     */
    public function destroy($id){

        // Check if this is user' list (prevents one user from deleting another user' list)
        $id = Query::escape($id);
        $validate = Query::select('SELECT id FROM lists WHERE id LIKE '.$id.' AND user_id LIKE '.$_SESSION['userId']);
        if(empty($validate)){
            trigger_error('Trying to delete list not owned by user.');
            echo json_encode(array('success' => false));
            exit;
        }

        // First delete all tasks connected to a list
        $taskDelete = Query::run('DELETE FROM tasks WHERE list_id LIKE '.$id);
        // Then delete the list itself
        $listDelete = Query::run('DELETE FROM lists WHERE id LIKE '.$id);

        if(!$taskDelete || !$listDelete){
            trigger_error('List delete error.');
            echo json_encode(array('success' => false));
        } else {
            echo json_encode(array('success' => true));
        }

    }

}
?>