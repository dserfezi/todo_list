<?php

class App
{

    /**
     * Run application
     */
    public static function run()
    {
        session_save_path(__DIR__.'/sessions');
        session_start();

        App::loadClasses();

        // If session marked as expired, logout
        $now = time();
        if( isset($_SESSION['expire_after']) && $now > $_SESSION['expire_after'] ){
            $_SESSION = array();
            session_destroy();
            View::redirect('/');
        }
        // Extend session lifetime for another 10 minutes
        if( isset($_SESSION['userId']) ) {
            $_SESSION['expire_after'] = $now + 600;
        }

        App::processUri();
    }


    public static function processUri(){

        $uriParsed = Route::parseUri();
        $ctrlAndFunc = Route::exists($uriParsed->uri);
        if($ctrlAndFunc!=false){
            // If this request requires user to be logged in
            if($ctrlAndFunc['login']==true) {
                // If logged in, process request
                // If not, redirect to login page
                if(isset($_SESSION['user'])) {
                    // Redirect to get function with params
                    if (Route::requestType() === true) {
                        Route::get($uriParsed->uri, $ctrlAndFunc['ctrlAndFunc'], $uriParsed->uriParams);
                    }
                    if (Route::requestType() === false) {
                        Route::post($ctrlAndFunc['ctrlAndFunc'], $uriParsed->uriParams);
                    }
                } else {
                    View::redirect('/');
                }
            } else {
                // Redirect to get function with params
                if (Route::requestType() === true) {
                    Route::get($uriParsed->uri, $ctrlAndFunc['ctrlAndFunc'], $uriParsed->uriParams);
                }
                if (Route::requestType() === false) {
                    Route::post($ctrlAndFunc['ctrlAndFunc'], $uriParsed->uriParams);
                }
            }
        } else {
            // Show 404 page
            View::show('404');
        }

    }


    /**
     * Load all files from /classes dir so they can be used globally
     */
    private function loadClasses(){

        $classesDir = __DIR__.'/../framework/classes';
        foreach (glob($classesDir.'/*.php') as $filename)
        {
            if ($filename === '.' || $filename === '..') continue;
            require_once $filename;
        }

    }


    /**
     * Load model class
     * @param $modelName
     * @return mixed
     */
    public static function getModel($modelName){

        return require __DIR__.'/models/'.$modelName.'.php';

    }

}