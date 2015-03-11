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

        //查找仓库名为{$reponame}的所有app
        public function app_list($reponame)
        {
            $repo = $this->repo->findOne(array('_id' => $reponame), array('apps' => 1));
            $cursor = $this->apps->find(array('_id' => array('$in' => $repo['apps']) ));
            $app_list = array();
            foreach ($cursor as $app) {
                $app['icon'] = ICON_PATH.'/'.$app['icon'];
                $app_list[] = $app;
            }
            $app_list = array('type' => 'app', 'applist' => $app_list);
            return $app_list;
        }

        //按$search搜索仓库{$reponame}的所有应用
        public function search_app($search, $reponame)
        {
            $repo = $this->repo->findOne(array('_id' => $reponame), array('apps' => 1));
            $regex = new MongoRegex("/$search/");
            $or = array(array('_id' => $regex), array('name' => $regex));
            $cursor = $this->apps->find(array('$and' => array(array('$or' => $or), array('_id' => array('$in' => $repo['apps'])))));
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
                $repo_list[] = $repo;
            }
            $repo_list = array('type' => 'repo', 'repolist' => $repo_list);
            return $repo_list;
        }

        public function photo_list($appId)
        {
            $photo_list = $this->findOneApp($appId);
            $photo_list = array('type' => 'photo', 'app' => $photo_list);
            return $photo_list;
        }

        public function doesRepoExist($reponame)
        {
            $repo = $this->repo->findOne(array('_id' => $reponame), array('_id' => 1));
            return !empty($repo);
        }

        public function doesAppExist($appid)
        {
            $app = $this->apps->findOne(array('_id' => $appid), array('_id' => 1));
            return !empty($app);
        }

        public function incRepoVersion($repo)
        {
            $this->repo->update(array('_id' => $repo), array('$inc' => array( 'version' => 1)));
        }

        //含$appId应用的所有仓库版本+1
        public function incRepoVersionByAppId($appId)
        {
            $this->repo->update(array('apps' => $appId), array('$inc' => array( 'version' => 1)), array('multi'=>true));
        }

        public function deleteApp($appId, $reponame)
        {
            //删除repo里的app记录
            $this->repo->update(array('_id' => $reponame), array('$pull' => array('apps' => $appId)));
            $repo = $this->repo->findOne(array('apps' => array('$elemMatch' => array('$eq' => $appId))), array('_id' => 1));
            $this->incRepoVersion($reponame);
            //如果app没有在任何仓库中
            if(empty($repo)){
                $app = $this->apps->findOne(array('_id' => $appId));
                $this->apps->remove(array('_id' => $appId));
                return $app;
            }
            return null;
        }

        public function findOneApp($appId)
        {
            return $this->apps->findOne(array('_id' => $appId));
        }

        public function findOneRepo($repo)
        {
            return $this->repo->findOne(array('_id' => $repo));
        }


        //向仓库$reponame粘贴应用$appid
        public function pasteApp($appid, $reponame)
        {
            if(!$this->doesAppExist($appid)){ return; }
            $this->repo->update(array('_id' => $reponame), array('$push' => array('apps' => $appid)));
            $this->incRepoVersion($reponame);
        }

        //更新App信息
        public function updateApp($appid, $name, $categories, $downloads, $score, $summary, $description)
        {
            if(!$this->doesAppExist($appid)){ return; }
            $update = array();
            if(!empty($name)){
                $update['name'] = $name;
            }
            if(!empty($categories)){
                $update['categories'] = $categories;
            }
            if(!empty($downloads)){
                $downloads_int = intval($downloads);
                if(is_int($downloads_int)){
                    $update['downloads'] = $downloads_int;
                }
            }
            if(!empty($score)){
                $score_int = intval($score);
                if(is_int($score_int)){
                    $update['score'] = $score_int;
                }
            }
            if(!empty($summary)){
                $update['summary'] = $summary;
            }
            if(!empty($description)){
                $update['description'] = $description;
            }
            if(!empty($update)){
                $this->apps->update(array('_id' => $appid), array('$set' => $update));
                $this->incRepoVersionByAppId($appid);
            }
        }

        //更新Repo信息
        public function updateRepo($repoId, $customerName, $customerId)
        {
            if(!$this->doesRepoExist($repoId)){ return; }
            $update = array();
            if(empty($customerName)){
                $customerName = '';
            }
            if(empty($customerId)){
                $customerId = '';
            }
            $update['conditions.customerId'] = $customerId;
            $update['conditions.customerName'] = $customerName;
            if(!empty($update)){
                $this->repo->update(array('_id' => $repoId), array('$set' => $update));
            }
        }

        //新增Repo
        public function addRepo($repoId, $customerName, $customerId)
        {
            global $L;
            if($this->doesRepoExist($repoId)){ show_json($L['repo_exist'], false);return; }
            if(empty($customerName)){
                $customerName = '';
            }
            if(empty($customerId)){
                $customerId = '';
            }
            $repo = array();
            $repo['_id'] = $repoId;
            $repo['apps'] = array();
            $repo['conditions'] = array('customerId'  => $customerId, 'customerName' => $customerName);
            $repo['version'] = 0;
            $this->repo->insert($repo);
        }

        //删除Repo
        public function removeRepo($repoId)
        {
            $applist = $this->app_list($repoId);
            $applist = $applist['applist'];
            $count = count($applist);
            $cursor = $this->repo->find();
            $repo_list = array();
            foreach ($cursor as $repo) {
                $repo_list[] = $repo;
            }
            foreach($repo_list as $repo){
                if(!isset($repo['apps'])){continue;}
                foreach($repo['apps'] as $appid){
                    for($i=0;$i<$count;$i++){
                        if($applist[$i]['_id'] == $appid){
                            unset($applist[$i]);
                        }
                    }
                }
            }
            $this->repo->remove(array('_id' => $repoId));
            return $applist;
        }

        //添加photo
        public function addPhoto($appId, $url)
        {
            if(empty($url)){return;}
            $this->apps->update(array('_id' => $appId), array('$push' => array('pictures' => $url)));
            $this->incRepoVersionByAppId($appId);
        }

        //删除photo
        public function deletePhoto($appId, $photoUrl)
        {
            if(empty($appId) || empty($photoUrl)){return;}
            $this->apps->update(array('_id' => $appId), array('$pull' => array('pictures' => $photoUrl)));
            $this->incRepoVersionByAppId($appId);
        }
    }


?>