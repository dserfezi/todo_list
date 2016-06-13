<?php
class AccessController
{

    /**
     * Handle root page
     * @param $request
     * @return mixed
     */
    public function index(){

        // Check if user is logged in
        if(isset($_SESSION['user'])){

            // Show main dashboard page
            $lists = array();

            // Pull list data from database
            $databaseData = Query::select('SELECT id, name, DATE_FORMAT(DATE(created_at), "%d.%m.%Y") AS created_at FROM lists WHERE user_id LIKE "'.$_SESSION['userId'].'"');
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

            return View::show('dashboard', 'lists', $lists);

        } else {

            // Show login page
            return View::show('access');

        }

    }


    public function activate($activation_token){

        // Update 'active' field in database
        $token = Query::escape($activation_token);
        Query::run('UPDATE users SET active=1, activation_token=null WHERE activation_token LIKE "'.$token.'"');

        // If wrong or broken uri, 0 rows will be affected
        $connection = Query::getConnection();
        if(mysqli_affected_rows($connection)!=0){
            View::redirect('/', 'messageBag', array('Account activated.'));
        } else {
            View::redirect('/', 'messageBag', array('Activation error.'));
        }

    }


    /**
     * Login function
     * @return bool
     */
    public function login(){

        // Check if inputs are empty
        if($_POST['email']=='' || $_POST['password']=='') {
            return View::redirect('/', array('messageBag' => array('Empty input(s)'), 'email' => $_POST['email']));
        }

        // Take email and pass from login page
        $inputEmail = Query::escape($_POST['email']);
        $inputPassword = Hash::make($_POST['password']);

        // Pull user data from database
        $databaseData = Query::select('SELECT id, password, active FROM users WHERE email LIKE "'.$inputEmail.'"');
        $databasePassword = $databaseData[0]['password'];

        // Compare database data with user input
        if($inputPassword==$databasePassword){
            // Check if active
            if($databaseData[0]['active']==0){
                return View::redirect('/', array('messageBag' => array('Account is not activated.'), 'email' => $_POST['email']));
            }
            // Login
            $_SESSION['user'] = true;
            $_SESSION['userId'] = $databaseData[0]['id'];
            Query::run('UPDATE users SET updated_at = "'.date('Y-m-d H:i:s').'" WHERE id LIKE '.$_SESSION['userId']);
            return View::redirect('/');
        } else {
            // Return error message
            return View::redirect('/', array('messageBag' => array('Email or password incorrect.'), 'email' => $_POST['email']));
        }

    }


    /**
     * Logout function
     * @return bool|mixed
     */
    public function logout(){

        $_SESSION = array();
        session_destroy();
        return View::redirect('/');

    }

}
?>