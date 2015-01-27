<?php
/**
 * Created by PhpStorm.
 * User: lijiyang
 * Date: 14-7-7
 * Time: 下午3:07
 */

    class Database
    {

        public $client;
        public $db;
        public $apps;
        public $repo;
        public $test;
        private static $instance;

        public function __construct()
        {
            $this->client = new MongoClient(DATABASE_IP);
            $this->db = $this->client->selectDB(DATABASE_NAME);
            $this->apps = $this->db->selectCollection(APPS);
            $this->repo = $this->db->selectCollection(REPO);
            $this->test = 1;
        }

        public static function getInstance()
        {
            if(!(self::$instance instanceof self)){
                self::$instance = new self;
            }
            return self::$instance;
        }

        public function app_list()
        {
            $cursor = $this->apps->find();
            $app_list = array();
            foreach ($cursor as $app) {
                $app_list[] = $app;
            }
            $apps = array('applist' => $app_list);
            return $apps;
        }

        public function repo_list()
        {
            $cursor = $this->repo->find(array(),array("apps" => 0));
            $repo_list = array();
            foreach ($cursor as $repo) {
                //$repo[] = array($repo)
                $repo_list[] = $repo;
            }
            $repo_list = array('type' => 'repo', 'repolist' => $repo_list);
            return $repo_list;
        }
    }


?>