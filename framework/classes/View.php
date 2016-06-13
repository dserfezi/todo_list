<?php
class View
{

    public static function show($viewName = '', $varNames = null, $value = null){

        // Check if view file exists
        if(self::viewExists($viewName)===false) return false;

        // Get data passed to view and create variables that will be used in view
        if (is_array($varNames)) {
            foreach ($varNames as $varKey => $varValue) {
                $$varKey = $varValue;
            }
        } else {
            $$varNames = $value;
        }

        // Execute view and get result html in a variable
        ob_start();
        require __DIR__.'/../../app/views/'.$viewName.'.phtml';
        $view = ob_get_contents();
        ob_end_clean();

        // Split view into pieces
        $view = self::parseView($view);

        // Concatenate layout and requested view pieces
        return self::render($view);

    }


    /**
     * Check if requested view exists in /views folder
     * @param $viewName
     * @return bool
     */
    private function viewExists($viewName){

        if($viewName == '') return false;
        return (file_exists(__DIR__.'/../../app/views/'.$viewName.'.phtml')) ?: false;

    }


    /**
     * Redirect to requested url and put data in $_SESSION variable
     * @param $url
     * @param $varNames
     * @param $value
     * @return bool
     */
    public static function redirect($url, $varNames = null, $value = null){

        // Get data passed to view and create variables that will be used in view
        if (is_array($varNames)) {
            foreach ($varNames as $varKey => $varValue) {
                $_SESSION[$varKey] = $varValue;
            }
        } else {
            $_SESSION[$varNames] = $value;
        }
        header('Location: ' . $url);
        return true;

    }


    /**
     * Cut $view string into pieces based on @extend and @section key words
     * @param string $view
     * @return array $result
     */
    private function parseView($view){

        $result = array();
        $chops = explode('@extends->', $view);
        $chops = explode('@section->', $chops[1]);
        $result['extends'] = trim($chops[0]);

        for($i=1; $i<count($chops); $i++){
            $segment = '';
            // Piece of string inside section area including section name
            $segment = $chops[$i];
            // Name of the section
            $sectionName = strtok($segment, PHP_EOL);
            // Piece of string inside section area
            $section = explode($sectionName, $segment);
            $section = $section[1];

            // Save in result
            $result[$sectionName] = $section;
        }

        return $result;

    }


    /**
     * Include parts of the evaluated view
     * Parts are separated by @section
     * @param $part
     * @param $view
     * @return string
     */
    private function includeViewParts($part, $view){

        if(isset($view[$part])){
            return $view[$part];
        } else {
            return '';
        }

    }


    /**
     * Render layout and requested view
     * @param $view
     * @return mixed
     */
    public static function render($view){

        return require __DIR__.'/../../app/views/layouts/'.$view['extends'].'.phtml';

    }

}
?>