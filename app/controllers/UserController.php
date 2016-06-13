<?php

class UserController
{

    public function index(){

    }


    /**
     * Show the form for creating a new user
     * @return bool|mixed
     */
    public function create(){

        return View::show('user-create');

    }


    public static function save(){

        // Get input params
        $email = $_POST['email'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $surname= $_POST['surname'];

        // Validate input params
        $messageBag = array();
        // Must not be empty
        foreach ($_POST as $key => $value) {
            if(empty($value)){
                $messageBag[] = 'All fields are required.';
                break;
            }
        }
        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $messageBag[] = 'The Email field must be a valid email address.';
        }
        // Email must be unique
        $databaseEmails = Query::select('SELECT email FROM users');
        foreach ($databaseEmails as $databaseEmail) {
            if($databaseEmail['email']==$email){
                $messageBag[] = 'Email address is already registered.';
                break;
            }
        }
        // Password must be at least 6 chars long
        $passwordArray = str_split($password);
        if(count($passwordArray)<6){
            $messageBag[] = 'Password must be at least six characters long.';
        }

        // If fails, redirect to register
        if(count($messageBag)!==0){
            return View::redirect('/user-create', array('messageBag' => $messageBag, 'email' => $email, 'name' => $name, 'surname' => $surname));
        }

        // Create activation token
        $activation_token = uniqid();

        // Escape input params
        $email = Query::escape($email);
        $name = Query::escape($name);
        $surname = Query::escape($surname);

        // Create user
        App::getModel('User');
        $user = new User();
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->name = $name;
        $user->surname = $surname;
        $user->created_at = date('Y-m-d H:i:s');
        $user->activation_token = $activation_token;
        $user->save();

        // Send activation link
        $mail = new stdClass();
        $mail->subject = "TODO list application activation";
        $mail->from = 'MIME-Version: 1.0' . "\r\n";
        $mail->from .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $mail->from .= 'From: activation@todo-list.com';
        $mail->msg = '<html><head></head><body><div style="font-size:13pt;border: #cdcdcd medium solid;border-radius:10px;padding:20px;background-color: #f5f5f5;">';
        $mail->msg .= '<br><br><span style="font-size:16pt;">Hello ' . $user->name . ',</span>';
        $mail->msg .= '<br><br>thank you for your registration.';
        $mail->msg .= '<br><br>Here is your activation link: <a href="/register/' . $user->activation_token . '">Link</a></div></body></html>';
        $mail->mailto = $user->email;

        if (/*!mail($mail->mailto, $mail->subject, $mail->msg, $mail->from)*/false) {
            trigger_error('Failed to send an email activation link.');
        }

        return View::show('user-create-success', 'mailContents', $mail);

    }

}

?>