<?php

class ToDoList
{

    public $name;
    public $userId;
    public $createdAt;


    /**
     * Save list in database
     */
    public function save(){

        $query = 'INSERT INTO lists (name, created_at, user_id)
                    VALUES ("'.$this->name.'", "'.$this->createdAt.'", "'.$this->userId.'")';
        Query::run($query);

    }

}
?>