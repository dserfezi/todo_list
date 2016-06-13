<?php
class Route
{

    public static function get($request, $controllerAndFunction, $uriParams = ''){

        $ctrlAndFunc = explode('@', $controllerAndFunction);
        $controller = $ctrlAndFunc[0];
        $function = $ctrlAndFunc[1];

        include __DIR__.'/../../app/controllers/'.$controller.'.php';
        // Instantiate proper controller
        $executeCtrlFunc = new $controller;

        // If asset required return it immediately
        if($request=='/assets'){
            // Get requested file extension
            $extension = explode('.', $uriParams);
            $extension = $extension[count($extension)-1];
            // Return proper HTTP header based on extension
            switch($extension){
                case 'css': $conType = $extension; break;
                case 'js': $conType = 'javascript'; break;
                default : $conType = 'html';
            }
            header("Content-type: text/".$conType);
            // Return requested asset with HTTP header
            return $executeCtrlFunc->$function($uriParams);
        } elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strpos(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']), 'xmlhttprequest')!==false){
            // If ajax request
            header("Content-type: text/plain");
            // Return requested asset with HTTP header
            return $executeCtrlFunc->$function($uriParams);
        } else {
            // Redirect to proper controller' function with params
            $executeCtrlFunc->$function($uriParams);
        }

    }


    public static function post($controllerAndFunction, $uriParams){

        $ctrlAndFunc = explode('@', $controllerAndFunction);
        $controller = $ctrlAndFunc[0];
        $function = $ctrlAndFunc[1];

        include __DIR__.'/../../app/controllers/'.$controller.'.php';

        // Redirect to proper controller' function with params
        $executeCtrlFunc = new $controller;
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strpos(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']), 'xmlhttprequest')!==false){
            // If ajax request
            header("Content-type: text/plain");
            // Return requested asset with HTTP header
            return $executeCtrlFunc->$function($uriParams);
        } else {
            // Redirect to proper controller' function with params
            $executeCtrlFunc->$function($uriParams);
        }

    }


    /**
     * Parse uri and return base uri and params
     * @return stdClass
     */
    public static function parseUri(){

        $uri = $_SERVER['REQUEST_URI'];
        // Remove ending slash if there is one
        if(substr($uri, -1) == '/') $uri = substr($uri, 0, strlen($uri)-1);
        $uri = explode('/', $uri);

        // Uri has three formats
        // /assets/something/something.ext
        // /
        // /something
        // /something/param
        if(count($uri)==1 && $uri[0]=='') {
            $uri = '';
            $uriParams = '';
        } elseif(isset($uri[1]) && $uri[1]=='assets'){
            // If asset requested
            $uriParams = array();
            for($i=2; $i<count($uri); $i++){
                $uriParams[] = $uri[$i];
            }
            $uri = '/' . $uri[1];
            $uriParams = implode('/', $uriParams);
        } elseif(count($uri)==2){
            // If uri does not have params
            $uriParams = '';
            $uri = implode('/', $uri);
        } elseif(count($uri)>2){
            // If uri has params
            $uriParams = array_pop($uri);
            $uri = implode('/', $uri);
        } else {
            trigger_error('Unknown uri format');
            return false;
        }

        $uriParsed = new stdClass();
        $uriParsed->uri = $uri;
        $uriParsed->uriParams = $uriParams;
        return $uriParsed;

    }


    /**
     * Check if requested uri exists in routes.php
     * @param $request
     * @return bool|string
     */
    public static function exists($request){

        $routes = require __DIR__ . '/../../app/routes.php';

        foreach($routes as $key => $value) {
            // Remove parameter placeholder
            if (strpos($key, '{') !== false) {
                $key = explode('{', $key);
                $key = $key[0];
                // Remove ending slash
                if (substr($key, -1) == '/') $key = substr($key, 0, strlen($key) - 1);
            }
            // Check if uri matches defined route
            if ($request == $key || $request == '') {
                if(self::requestType()===true) return array('ctrlAndFunc' => $value['get'], 'login' => $value['login']);
                if(self::requestType()===false) return array('ctrlAndFunc' => $value['post'], 'login' => $value['login']);
            }
        }

        // Route not defined
        return false;

    }


    /**
     * Only post and get requests are used in app
     * @return bool|string
     */
    public static function requestType(){

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            return true;
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            return false;
        }
        return 'error';

    }

}
?>