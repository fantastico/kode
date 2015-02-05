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

        public function app_list($reponame)
        {
            $repo = $this->repo->findOne(array('_id' => $reponame), array('apps' => 1));
            $test = $repo['apps'];
            $cursor = $this->apps->find(array('_id' => array('$in' => $repo['apps']) ));
            $app_list = array();
            foreach ($cursor as $app) {
                $app['icon'] = ICON_PATH.'/'.$app['icon'];
                $app_list[] = $app;
            }
            $app_list = array('type' => 'app', 'applist' => $app_list);
            return $app_list;
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

        public function doesRepoExist($reponame)
        {
            $repo = $this->repo->findOne(array('_id' => $reponame), array('_id' => 1));
            if(count($repo) == 0){
                return false;
            }
            return true;
        }

        public function deleteApp($appId, $reponame)
        {
            $this->repo->update(array('_id' => $reponame), array('$pull' => array('apps' => $appId)));
            $repo = $this->repo->findOne(array('apps' => array('$elemMatch' => array('$eq' => $appId))), array('_id' => 1));
            if(count($repo) == 0){
                $app = $this->apps->findOne(array('_id' => $appId));
                return $app;
            }
            return null;
        }

        public function findOneApp($appId)
        {
            return $this->apps->findOne(array('_id' => $appId));
        }
    }


?>