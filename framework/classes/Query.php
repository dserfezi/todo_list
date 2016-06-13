<?php

class Query extends Database
{

    public static function run($query) {

        return parent::getInstance()->runQuery($query);

    }


    public static function select($query) {

        return parent::getInstance()->runSelect($query);

    }


    public static function escape($value) {

        return parent::getInstance()->runEscape($value);

    }


    public static function getConnection(){

        return parent::getInstance()->getConnectionInstance();

    }

}
?>