<?php

return array(
    '/' => array(
        'post' => 'AccessController@login',
        'get' => 'AccessController@index',
        'login' => false
    ),
    '/task/{id}' => array(
        'post' => 'TaskController@process',
        'get' => 'TaskController@index',
        'login' => true
    ),
    '/assets/{asset}' => array(
        'post' => '',
        'get' => 'IndexController@assets',
        'login' => false
    ),
    '/logout' => array(
        'post' => '',
        'get' => 'AccessController@logout',
        'login' => true
    ),
    '/user-create' => array(
        'post' => 'UserController@save',
        'get' => 'UserController@create',
        'login' => false
    ),
    '/register/{token}' => array(
        'post' => '',
        'get' => 'AccessController@activate',
        'login' => false
    ),
    '/list-create' => array(
        'post' => 'ListController@save',
        'get' => 'ListController@create',
        'login' => true
    ),
    '/list-edit/{id}' => array(
        'post' => 'ListController@update',
        'get' => 'ListController@edit',
        'login' => true
    ),
    '/list-delete/{id}' => array(
        'post' => '',
        'get' => 'ListController@destroy',
        'login' => true
    ),
    '/dashboard-sort/{column}' => array(
        'post' => '',
        'get' => 'ListController@dashboardTableSort',
        'login' => true
    ),
    '/task-create' => array(
        'post' => 'TaskController@create',
        'get' => '',
        'login' => true
    ),
    '/task-delete/{id}' => array(
        'post' => '',
        'get' => 'TaskController@destroy',
        'login' => true
    ),
    '/task-edit/{id}' => array(
        'post' => 'TaskController@update',
        'get' => 'TaskController@edit',
        'login' => true
    ),
    '/list-edit-sort/{columnAndList_id}' => array(
        'post' => '',
        'get' => 'TaskController@listEditTableSort',
        'login' => true
    )
);

?>