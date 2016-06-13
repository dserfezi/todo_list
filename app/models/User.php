<?php

class User
{

    public $email;
    public $password;
    public $name;
    public $surname;
    public $created_at;
    public $updated_at;
    public $active;
    public $activation_token;


    /**
     * Save user in database
     */
    public function save(){

        $query = 'INSERT INTO users (email, password, name, surname, created_at, activation_token)
                    VALUES ("'.$this->email.'", "'.$this->password.'", "'.$this->name.'", "'.$this->surname.'", "'.$this->created_at.'", "'.$this->activation_token.'")';
        Query::run($query);

    }

}
?>