<?php
    /**
     * Created by PhpStorm.
     * User: lijiyang
     * Date: 14-7-7
     * Time: 下午3:07
     */

    function app_list()
    {
        $client = new MongoClient('localhost');
        $db = $client->sen5_app_store;
        $apps = $db->apps;
        $cursor = $apps->find();
        $app_list = array();
        foreach ($cursor as $app) {
            $app_list[] = $app;
        }
        $apps = array('applist' => $app_list);
        return $apps;
    }


?>