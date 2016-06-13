<?php

class IndexController
{

    /**
     * Handle asset request
     * @param $request
     * @param $uriParams
     * @return mixed
     */
    public function assets($uriParams){

        return require __DIR__.'/../../public/'.$uriParams;

    }

}
?>