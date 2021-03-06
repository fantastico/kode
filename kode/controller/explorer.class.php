<?php

    /*
    * @link http://www.kalcaddle.com/
    * @author warlee | e-mail:kalcaddle@qq.com
    * @copyright warlee 2014.(Shanghai)Co.,Ltd
    * @license http://kalcaddle.com/tools/licenses/license.txt
    */

    class explorer extends Controller
    {
        public $path;

        public function __construct()
        {
            parent::__construct();
            $this->tpl = TEMPLATE . 'explorer/';
            if (isset($this->in['path'])) {
                $this->path = _DIR($this->in['path']);
            }
        }

        public function index()
        {
            if (isset($this->in['path']) && $this->in['path'] != '') {
                $dir = $_GET['path'];
            } else if (isset($_SESSION['this_path'])) {
                $dir = $_SESSION['this_path'];
            } else {
                $dir = '/'; //首次进入系统,不带参数
                if ($GLOBALS['is_root']) $dir = WEB_ROOT;
            }
            $dir = rtrim($dir, '/') . '/';
            $is_frame = false; //是否以iframe方式打开
            if (isset($this->in['type']) && $this->in['type'] == 'iframe') $is_frame = true; //
            $upload_max = get_post_max();
            $this->assign('upload_max', $upload_max);
            $this->assign('is_frame', $is_frame);
            $this->assign('dir', $dir);
            $this->display('index.php');
        }

        public function pathInfo()
        {
            $info_list = json_decode($this->in['list'], true);
            foreach ($info_list as &$val) {
                $val['path'] = _DIR($val['path']);
            }

            $data = path_info_muti($info_list, $this->L['time_type_info']);
            show_json($data);
        }

        public function pathChmod()
        {
            $info_list = json_decode($this->in['list'], true);
            $mod = octdec('0' . $this->in['mod']);
            $success = 0;
            $error = 0;
            foreach ($info_list as $val) {
                $path = _DIR($val['path']);
                if (chmod_path($path, $mod)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            $state = $error == 0 ? true : false;
            $info = $success . ' success,' . $error . ' error';
            if (count($info_list) == 1 && $error == 0) {
                $info = $this->L['success'];
            }
            show_json($info, $state);
        }

        public function _pathAllow($path)
        {
            $name = get_path_this($path);
            $path_not_allow = array('*', '?', '"', '<', '>', '|');
            foreach ($path_not_allow as $tip) {
                if (strstr($name, $tip)) {
                    show_json($this->L['path_not_allow'] . "*,?,<,>,|", false);
                }
            }
        }

        public function pathRname()
        {
            if (!is_writable($this->path)) {
                show_json($this->L['no_permission_write'], false);
            }
            $rname_to = _DIR($this->in['rname_to']);
            $this->_pathAllow($rname_to);
            if (file_exists($rname_to)) {
                show_json($this->L['name_isexists'], false);
            }
            rename($this->path, $rname_to);
            show_json($this->L['rname_success']);
        }

        public function pathList()
        {
            load_class('history');
            session_start(); //re start
            $session = isset($_SESSION['history']) ? $_SESSION['history'] : false;
            $user_path = $this->in['path'];

            if (is_array($session)) {
                $hi = new history($session);
                if ($user_path == "") {
                    $user_path = $hi->getFirst();
                } else {
                    $hi->add($user_path);
                    $_SESSION['history'] = $hi->getHistory();
                }
            } else {
                $hi = new history(array(), 20);
                if ($user_path == "") $user_path = '/';
                $hi->add($user_path);
                $_SESSION['history'] = $hi->getHistory();
            }
            $_SESSION['this_path'] = $user_path;
            $list = $this->path($this->path);
            $list['history_status'] = array('back' => $hi->isback(), 'next' => $hi->isnext());
            show_json($list);
        }

        public function search()
        {
            if (!isset($this->in['search'])) show_json($this->L['please_inpute_search_words'], false);
            $is_content = false;
            $is_case = false;
            $ext = '';
            if (isset($this->in['is_content'])) $is_content = true;
            if (isset($this->in['is_case'])) $is_case = true;
            if (isset($this->in['ext'])) $ext = str_replace(' ', '', $this->in['ext']);
            $list = path_search(
                $this->path,
                iconv_system($this->in['search']),
                $is_content, $ext, $is_case);
            _DIR_OUT($list);
            show_json($list);
        }

        public function search_app()
        {
            if (!isset($this->in['search'])) show_json($this->L['please_inpute_search_words'], false);

            load_class('history');
            session_start(); //re start
            $session = isset($_SESSION['history']) ? $_SESSION['history'] : false;
            $user_path = $this->in['path'];

            if (is_array($session)) {
                $hi = new history($session);
                if ($user_path == "") {
                    $user_path = $hi->getFirst();
                } else {
                    $hi->add($user_path);
                    $_SESSION['history'] = $hi->getHistory();
                }
            } else {
                $hi = new history(array(), 20);
                if ($user_path == "") $user_path = '/';
                $hi->add($user_path);
                $_SESSION['history'] = $hi->getHistory();
            }
            $_SESSION['this_path'] = $user_path;
            //搜索app
            $path = explode('/',trim($user_path, '/'));
            if(count($path) == 1){
                $instance = Database::getInstance();
                $list = $instance->search_repo($this->in['search']);
            }
            //显示app
            if(count($path) == 2){
                $instance = Database::getInstance();
                $reponame = $path[1];
                $list = $instance->search_app($this->in['search'], $reponame);
            }
            $list['history_status'] = array('back' => $hi->isback(), 'next' => $hi->isnext());
            show_json($list);
        }

        public function treeList()
        { //树结构
            $app = $this->in['app']; //是否获取文件 传folder|file
            if (isset($this->in['type']) && $this->in['type'] == 'init') {
                $this->_tree_init($app);
            }

            if (isset($this->in['this_path'])) {
                $path = _DIR($this->in['this_path']);
            } else {
                $path = _DIR($this->in['path'] . $this->in['name']);
            }
            //if (!is_readable($path)) show_json($path,false);

            $list_file = ($app == 'editor' ? true : false); //编辑器内列出文件
            $list = $this->path($path, $list_file, true);
            function sort_by_key($a, $b)
            {
                if ($a['name'] == $b['name']) return 0;
                return ($a['name'] > $b['name']) ? 1 : -1;
            }

            usort($list['folderlist'], "sort_by_key");
            usort($list['filelist'], "sort_by_key");
            if ($app == 'editor') {
                $res = array_merge($list['folderlist'], $list['filelist']);
                show_json($res, true);
            } else {
                show_json($list['folderlist'], true);
            }
        }

        private function _tree_init($app)
        {
            $check_file = ($app == 'editor' ? true : false);
            $favData = new fileCache($this->config['user_fav_file']);
            $fav_list = $favData->get();
            $fav = array();
            foreach ($fav_list as $key => $val) {
                $fav[] = array(
                    'ext' => 'folder',
                    'name' => $val['name'],
                    'this_path' => $val['path'],
                    'iconSkin' => "fav",
                    'type' => 'folder',
                    'isParent' => path_haschildren(_DIR($val['path']), $check_file)
                );
            }

            if ($check_file) { //编辑器
                $list_root = $this->path(_DIR(MYHOME), $check_file, true);
                $list_share = $this->path(_DIR(PUBLIC_PATH), $check_file, true);
                $root = array_merge($list_root['folderlist'], $list_root['filelist']);
                $share = array_merge($list_share['folderlist'], $list_share['filelist']);
                $root_isparent = count($root) > 0 ? true : false;
                $share_isparent = count($share) > 0 ? true : false;

                $tree_data = array(
                    array('name' => $this->L['fav'], 'ext' => '__fav__', 'iconSkin' => "fav",
                        'open' => true, 'children' => $fav),
                    array('name' => $this->L['root_path'], 'ext' => '__root__', 'children' => $root,
                        'iconSkin' => "my", 'open' => true, 'this_path' => MYHOME, 'isParent' => $root_isparent),
                    array('name' => $this->L['public_path'], 'ext' => '__root__', 'children' => $share,
                        'iconSkin' => "lib", 'open' => true, 'this_path' => PUBLIC_PATH, 'isParent' => $share_isparent)
                );
            } else { //文件管理器
                $list_apps = $this->path(_DIR(REPO_PATH), $check_file, true);
                $list_repos = $this->path(_DIR(PUBLIC_PATH), $check_file, true);
                $folder_apps = [];
                $tree_data = array(
                    array('name' => $this->L['repos'], 'ext' => '__root__', 'children' => $folder_apps,
                        'iconSkin' => "my", 'open' => true, 'this_path' => APPSTORE_ROOT, 'isParent' => false),
                  /*  array('name' => $this->L['repos'], 'ext' => '__root__', 'children' => $folder_repos,
                        'iconSkin' => "lib", 'open' => true, 'this_path' => PUBLIC_PATH, 'isParent' => false),
                    array('name' => $this->L['images'], 'ext' => '__fav__', 'iconSkin' => "fav",
                        'open' => false, 'children' => $fav)*/
                );
            }
            show_json($tree_data);
        }

        public function historyBack()
        {
            load_class('history');
            session_start(); //re start
            $session = $_SESSION['history'];
            if (is_array($session)) {
                $hi = new history($session);
                $path = $hi->goback();
                $_SESSION['history'] = $hi->getHistory();
                $folderlist = $this->path(_DIR($path));
                $_SESSION['this_path'] = $path;
                show_json(array(
                    'history_status' => array('back' => $hi->isback(), 'next' => $hi->isnext()),
                    'thispath' => $path,
                    'list' => $folderlist
                ));
            }
        }

        public function historyNext()
        {
            load_class('history');
            session_start(); //re start
            $session = $_SESSION['history'];
            if (is_array($session)) {
                $hi = new history($session);
                $path = $hi->gonext();
                $_SESSION['history'] = $hi->getHistory();
                $folderlist = $this->path(_DIR($path));
                $_SESSION['this_path'] = $path;
                show_json(array(
                    'history_status' => array('back' => $hi->isback(), 'next' => $hi->isnext()),
                    'thispath' => $path,
                    'list' => $folderlist
                ));
            }
        }

        public function pathDelete()
        {
            $list = json_decode($this->in['list'], true);
            $success = 0;
            $error = 0;
            foreach ($list as $val) {
                $path_full = _DIR($val['path']);

                /********************************************************************************************************************
                 **  如果是在仓库目录下，进行app删除处理  START
                 ********************************************************************************************************************/
                $path = explode('/',trim($path_full, '/'));
                // 删除repo
                if($val['type'] === 'repo' && count($path) == 2){
                    $instance = Database::getInstance();
                    $apps = $instance->removeRepo($path[1]);
                    if(isset($apps)){
                        foreach($apps as $app){
                            foreach($app['apks'] as $apk){
                                $filename = REPO_PATH.'/repo/'.$apk['apkname'];
                                if (del_file($filename)) $success++;
                                else $error++;
                            }
                        }
                    }
                    $success++;
                }
                // 删除APP
                if($val['type'] === 'app' && count($path) == 3){
                    $instance = Database::getInstance();
                    $app = $instance->deleteApp($val['id'], $path[1]);
                    if(isset($app)){
                        foreach($app['apks'] as $apk){
                            $filename = REPO_PATH.'/repo/'.$apk['apkname'];
                            if (del_file($filename)) $success++;
                            else $error++;
                        }
                    }
                    $success++;
                }
                // 删除APP图片
                if($val['type'] === 'photo'){
                    $instance = Database::getInstance();
                    $instance->deletePhoto($val['appId'], $val['photoUrl']);
                    $filename = PHOTO_PATH.'/'.$val['appId'].'/'.substr($val['photoUrl'],strrpos($val['photoUrl'], '/')+1);
                    if (file_exists($filename)){
                        del_file($filename);
                    }
                    $success++;
                }
                /********************************************************************************************************************
                 **  如果是在仓库目录下，进行app删除处理  END
                 ********************************************************************************************************************/

                if ($val['type'] == 'folder') {
                    if (del_dir($path_full)) $success++;
                    else $error++;
                }
                if ($val['type'] == 'file') {
                    if (del_file($path_full)) $success++;
                    else $error++;
                }
            }
            if (count($list) == 1) {
                if ($success) show_json($this->L['remove_success']);
                else show_json($this->L['remove_fali'], false);
            } else {
                $code = $error == 0 ? true : false;
                show_json($this->L['remove_success'] . $success . 'success,' . $error . 'error', $code);
            }
        }

        public function mkfile()
        {
            $new = rtrim($this->path, '/');
            $this->_pathAllow($new);
            if (touch($new)) {
                show_json($this->L['create_success']);
            } else {
                show_json($this->L['create_error'], false);
            }
        }

        public function mkdir()
        {
            $new = rtrim($this->path, '/');
            $this->_pathAllow($new);
            if (mkdir($new, 0777)) {
                show_json($this->L['create_success']);
            } else {
                show_json($this->L['create_error'], false);
            }
        }

        public function pathCopy()
        {
            session_start(); //re start
            $copy_list = json_decode($this->in['list'], true);
            $list_num = count($copy_list);
            for ($i = 0; $i < $list_num; $i++) {
                $copy_list[$i]['path'] = $copy_list[$i]['path'];
            }
            $_SESSION['path_copy'] = json_encode($copy_list);
            $_SESSION['path_copy_type'] = 'copy';
            show_json($this->L['copy_success']);
        }

        public function pathCute()
        {
            session_start(); //re start
            $cute_list = json_decode($this->in['list'], true);
            $list_num = count($cute_list);
            for ($i = 0; $i < $list_num; $i++) {
                $cute_list[$i]['path'] = $cute_list[$i]['path'];
            }
            $_SESSION['path_copy'] = json_encode($cute_list);
            $_SESSION['path_copy_type'] = 'cute';
            show_json($this->L['cute_success']);
        }

        public function pathCuteDrag()
        {
            $clipboard = json_decode($this->in['list'], true);
            $path_past = $this->path;
            if (!is_writable($this->path)) show_json($this->L['no_permission_write'], false);
            $success = 0;
            $error = 0;
            foreach ($clipboard as $val) {
                $path_copy = _DIR($val['path']);
                $filename = get_path_this($path_copy);
                $filename = get_filename_auto($path_past . $filename); //已存在处理 创建副本
                if (@rename($path_copy, $filename)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            $state = $error == 0 ? true : false;
            $info = $success . ' success,' . $error . ' error';
            if (count($info_list) == 1 && $error == 0) {
                $info = $this->L['success'];
            }
            show_json($info, $state);
        }

        public function pathCopyDrag()
        {
            $clipboard = json_decode($this->in['list'], true);
            $path_past = $this->path;
            $data = array();
            if (!is_writable($this->path)) show_json($this->L['no_permission_write'], false);
            foreach ($clipboard as $val) {
                $path_copy = _DIR($val['path']);
                $filename = get_path_this($path_copy);
                $path = get_filename_auto($path_past . $filename);
                copy_dir($path_copy, $path);
                $data[] = iconv_app(get_path_this($path));
            }
            show_json($data, true);
        }

        public function clipboard()
        {
            $clipboard = json_decode($_SESSION['path_copy'], true);
            $msg = '';
            if (count($clipboard) == 0) {
                $msg = '<div style="padding:20px;">null!</div>';
            } else {
                $msg = '<div style="height:200px;overflow:auto;padding:10px;width:400px"><b>' . $this->L['clipboard_state']
                    . ($_SESSION['path_copy_type'] == 'cute' ? $this->L['cute'] : $this->L['copy']) . '</b><br/>';
                $len = 40;
                foreach ($clipboard as $val) {
                    $val['path'] = rawurldecode($val['path']);
                    $path = (strlen($val['path']) < $len) ? $val['path'] : '...' . substr($val['path'], -$len);
                    $msg .= '<br/>' . $val['type'] . ' :  ' . $path;
                }
                $msg .= "</div>";
            }
            show_json($msg);
        }

        public function pathPast()
        {
            $data = array();
            if (!isset($_SESSION['path_copy'])) {
                show_json($data, false, $this->L['clipboard_null']);
            }

            session_start(); //re start
            $error = '';
            $clipboard = json_decode($_SESSION['path_copy'], true);
            $copy_type = $_SESSION['path_copy_type'];
            $path_past = $this->path;

            /********************************************************************************************************************
             **  如果是在仓库目录下，进行app复制处理  START
             ********************************************************************************************************************/
            $path = explode('/',trim($path_past, '/'));
            $list_num = count($clipboard);
            if ($list_num == 0) {
                show_json($data, false, $this->L['clipboard_null']);
            }
            for ($i = 0; $i < $list_num; $i++) {
                $path_copy = _DIR($clipboard[$i]['path']);
                if ($path_copy == substr($path_past, 0, strlen($path_copy))) {
                    $error .= "<li style='color:#000000;'>".$this->L['current_has_parent']."</li>";
                    continue;
                }

                if ($path_past == get_path_father($path_copy)) {
                    $error .= "<li style='color:#000000;'>".$this->L['path_is_current']."</li>";
                    continue;
                }

                if ($clipboard[$i]['type'] == 'app') {
                    if(count($path) != 2){
                        $error .= "<li style='color:#000000;'>".$this->L['not_repo_path']."</li>";
                        continue;
                    }
                    $reponame = $path[1];
                    $appid = $clipboard[$i]['id'];

                    if ($copy_type == 'copy') {
                        $instance = Database::getInstance();
                        $instance->pasteApp($appid, $reponame);
                    }
                    $data[] = $appid;
                }

                if ($clipboard[$i]['type'] == 'photo') {
                    if(count($path) != 3){
                        $error .= "<li style='color:#000000;'>".$this->L['not_app_path']."</li>";
                        continue;
                    }
                    $appid = $path[2];
                    if ($copy_type == 'copy') {
                        $instance = Database::getInstance();
                        $instance->pastePhoto($appid, $reponame);
                    }
                    $data[] = $appid;
                }
            }
            if ($copy_type == 'copy') {
                $info = $this->L['past_success'] . $error;
            } else {
                $_SESSION['path_copy'] = json_encode(array());
                $_SESSION['path_copy_type'] = '';
                $info = $this->L['cute_past_success'] . $error;
            }
            $state = ($error == '' ? true : false);
            show_json($data, $state, $info);
            /********************************************************************************************************************
             **  如果是在仓库目录下，进行app复制处理  END
             ********************************************************************************************************************/
            if (!is_writable($path_past)) show_json($data, false, $this->L['no_permission_write']);

            $list_num = count($clipboard);
            if ($list_num == 0) {
                show_json($data, false, $this->L['clipboard_null']);
            }


            for ($i = 0; $i < $list_num; $i++) {
                $path_copy = _DIR($clipboard[$i]['path']);
                $filename = get_path_this($path_copy);
                $filename_out = iconv_app($filename);

                if (!file_exists($path_copy) && !is_dir($path_copy)) {
                    $error .= $path_copy . "<li>{$filename_out}'.$this->L['copy_not_exists'].'</li>";
                    continue;
                }
                if ($clipboard[$i]['type'] == 'folder') {
                    if ($path_copy == substr($path_past, 0, strlen($path_copy))) {
                        $error .= "<li style='color:#f33;'>{$filename_out}'.$this->L['current_has_parent'].'</li>";
                        continue;
                    }
                }

                $auto_path = get_filename_auto($path_past . $filename);
                $filename = get_path_this($auto_path);
                if ($copy_type == 'copy') {
                    if ($clipboard[$i]['type'] == 'folder') {
                        copy_dir($path_copy, $auto_path);
                    } else {
                        copy($path_copy, $auto_path);
                    }
                } else {
                    rename($path_copy, $auto_path);
                }
                $data[] = iconv_app($filename);
            }
            if ($copy_type == 'copy') {
                $info = $this->L['past_success'] . $error;
            } else {
                $_SESSION['path_copy'] = json_encode(array());
                $_SESSION['path_copy_type'] = '';
                $info = $this->L['cute_past_success'] . $error;
            }
            $state = ($error == '' ? true : false);
            show_json($data, $state, $info);
        }

        public function fileDownload()
        {
            file_download($this->path);
        }

        public function zip()
        {
            load_class('pclzip');
            ini_set('memory_limit', '2028M'); //2G;
            $zip_list = json_decode($this->in['list'], true);
            $list_num = count($zip_list);
            for ($i = 0; $i < $list_num; $i++) {
                $zip_list[$i]['path'] = _DIR($zip_list[$i]['path']);
            }
            $basic_path = get_path_father($zip_list[0]['path']);
            if ($list_num == 1) {
                $path_this_name = get_path_this($zip_list[0]['path']);
                $zipname = $basic_path . $path_this_name . '.zip';
            } else {
                $path_this_name = get_path_this(get_path_father($zip_list[0]['path']));
                $zipname = $basic_path . $path_this_name . '.zip';
            }
            $zipname = get_filename_auto($zipname);
            if (!is_writeable($basic_path)) {
                show_json("{$zipname}" . $this->L['no_permission_write'], false);
            } else {
                $files = array();
                for ($i = 0; $i < $list_num; $i++) {
                    $files[] = $zip_list[$i]['path'];
                }
                $archive = new PclZip($zipname);
                $v_list = $archive->create(implode(',', $files), PCLZIP_OPT_REMOVE_PATH, $basic_path);
                if ($v_list == 0) {
                    show_json("Error : " . $archive->errorInfo(true), false);
                }
                $info = $this->L['zip_success'] . $this->L['size'] . ":" . size_format(filesize($zipname));
                show_json($info);
            }
        }

        public function unzip()
        {
            load_class('pclzip');
            ini_set('memory_limit', '2028M'); //2G;
            $path = $this->path;
            $name = get_path_this($path);
            $name = substr($name, 0, strrpos($name, '.'));
            $unzip_to = get_path_father($path) . $name;
            if (isset($this->in['path_to'])) { //解压到指定位置
                $unzip_to = _DIR($this->in['path_to']);
            }
            if (!is_writeable($path)) {
                show_json("{$path}" . $this->L['no_permission_write'], false);
            }
            $zip = new PclZip($path); //
            if ($GLOBALS['is_root'] == 1) {
                $result = $zip->extract(PCLZIP_OPT_PATH, $unzip_to,
                    PCLZIP_OPT_SET_CHMOD, 0777,
                    PCLZIP_OPT_REPLACE_NEWER);
                //解压到某个地方,覆盖方式
            } else {
                $result = $zip->extract(PCLZIP_OPT_PATH, $unzip_to,
                    PCLZIP_OPT_SET_CHMOD, 0777,
                    PCLZIP_CB_PRE_EXTRACT, "checkExtUnzip",
                    PCLZIP_OPT_REPLACE_NEWER);
                //解压到某个地方,覆盖方式
            }
            if ($result == 0) {
                show_json("Error : " . $zip->errorInfo(true), fasle);
            } else {
                show_json($this->L['unzip_success']);
            }
        }

        public function image()
        {
            if (filesize($this->path) <= 1024 * 10) { //小于10k 不再生成缩略图
                file_proxy_out($this->path);
            }
            load_class('imageThumb');
            $image = $this->path;
            $image_md5 = md5($image);
            $image_thum = $this->config['pic_thumb'] . $image_md5 . '.png';
            if (!is_dir($this->config['pic_thumb'])) {
                mkdir($this->config['pic_thumb'], "0777");
            }
            if (!file_exists($image_thum)) { //如果拼装成的url不存在则没有生成过
                if ($_SESSION['this_path'] == $this->config['pic_thumb']) { //当前目录则不生成缩略图
                    $image_thum = $this->path;
                } else {
                    $cm = new CreatMiniature();
                    $cm->SetVar($image, 'file');
                    //$cm->Prorate($image_thum,72,64);//生成等比例缩略图
                    $cm->BackFill($image_thum, 72, 64, true); //等比例缩略图，空白处填填充透明色
                }
            }
            if (!file_exists($image_thum) || filesize($image_thum) < 100) { //缩略图生成失败则用默认图标
                $image_thum = STATIC_PATH . 'images/image.png';
            }
            //输出
            file_proxy_out($image_thum);
        }

        // 远程下载
        public function serverDownload()
        {
            $uuid = 'download_' . $this->in['uuid'];
            if ($this->in['type'] == 'percent') { //获取下载进度
                //show_json($_SESSION[$uuid]);
                if (isset($_SESSION[$uuid])) {
                    $info = $_SESSION[$uuid];
                    $result = array(
                        'length' => (int)$info['length'],
                        'size' => (int)filesize($info['path']),
                        'time' => mtime()
                    );
                    show_json($result);
                } else {
                    show_json('', false);
                }
            }

            //下载
            $save_path = _DIR($this->in['save_path']);
            if (!is_writeable($save_path)) show_json($this->L['no_permission_write'], false);

            $url = rawurldecode($this->in['url']);
            $header = url_header($url);
            if (!$header) show_json($this->L['download_error_exists'], false);

            $save_path = $save_path . urldecode($header['name']);
            if (checkExt($save_path) != true) $save_path = checkExt($save_path, true);
            $save_path = iconv_system($save_path);
            $save_path = get_filename_auto($save_path);

            session_start();
            $_SESSION[$uuid] = array('length' => $header['length'], 'path' => $save_path);
            session_write_close();

            if (file_download_this($url, $save_path)) {
                $name = get_path_this(iconv_app($save_path));
                show_json($this->L['download_success'], true, $name);
            } else {
                show_json($this->L['download_error_create'], false);
            }
        }

        // 远程下载
        public function fileProxy()
        {
            if (!$GLOBALS['is_root']) show_json($this->L['no_permission'], false);
            file_proxy_out($this->path);
        }

        /**
         * 上传,html5拖拽  flash 多文件
         */
        public function fileUpload()
        {
            $path = explode('/',trim($this->path, '/'));
            //上传apk文件
            if(count($path) == 2){
                $reponame = $path[1];
                $instance = Database::getInstance();
                if( !$instance->doesRepoExist($reponame)){
                    show_json($this->L['upload_repo_not_exist'], false);
                }
                $save_path = REPO_PATH.'/repo/';

                //保存文件
                if (!is_writeable($save_path)) show_json('path is not writeable', false);
                if (strlen($this->in['fullPath']) > 1) { //folder drag upload
                    $full_path = _DIR_CLEAR(rawurldecode($this->in['fullPath']));
                    $full_path = get_path_father($full_path);
                    $full_path = iconv_system($full_path);
                    if (mk_dir($save_path . $full_path)) {
                        $save_path = $save_path . $full_path;
                    }
                }

                global $config, $L;
                $file = $_FILES['file'];
                if (!isset($file)) show_json($L['upload_error_null'], false);
                $ext = substr($file['name'], strrpos($file['name'], '.')+1);
                if($ext !== 'apk'){
                    show_json($this->L['upload_not_apk'], false);
                }

                $file_name = iconv_system($file['name']);
                $info = _upload($file['tmp_name'], $file['size'], $save_path . $file_name);

                //froid update
                $output = array();
                $file_name = substr($file_name, 0, -4);
                $result = exec('fdroid 2>&1 sen5_update --apkFile='.$file_name.' --repo='.$reponame, $output);

                $info['data'] = $result;
                show_json($info['data'], $info['code'], $info['path']);
            }

            //上传APP图片
            if(count($path) == 3){
                $appId = $this->in['appId'];
                $instance = Database::getInstance();
                if( empty($appId) || !$instance->doesAppExist($appId)){
                    show_json($this->L['upload_app_not_exist'], false);
                }
                $save_path = PHOTO_PATH.'/'.$appId.'/';

                //保存文件
                if (!is_writeable($save_path)) {
                    if (!mk_dir($save_path)) {
                        show_json('path is not writeable', false);
                    }
                }

                $file = $_FILES['file'];
                $file_name = iconv_system($file['name']);
                if (file_exists ($save_path . $file_name)) {
                    show_json($this->L['file_exist'], false);
                }

                global $config, $L;
                if (!isset($file)) show_json($L['upload_error_null'], false);

                $info = _upload($file['tmp_name'], $file['size'], $save_path . $file_name);

                //database update
                $instance->addPhoto($appId, REPO_URL.'/photo/'.$appId.'/'. $file_name);
                $info['data'] = 'Success';
                show_json($info['data'], $info['code'], $info['path']);
            }
        }

        //获取文件列表&哦exe文件json解析
        private function path($dir, $list_file = true, $check_children = false)
        {
            $path = explode('/',trim($dir, '/'));
            //显示app
            if(count($path) == 2){
                $instance = Database::getInstance();
                $list = $instance->app_list($path[1]);
                return $list;
            }
            //显示APP图片
            if(count($path) == 3){
                $instance = Database::getInstance();
                $list = $instance->photo_list($path[2]);
                return $list;
            }

            //显示repo
            $instance = Database::getInstance();
            $list = $instance->repo_list();
            return $list;

            /*
            $list = path_list($dir, $list_file, $check_children);
            foreach ($list['filelist'] as $key => &$val) {
                if ($val['ext'] == 'oexe') {
                    $path = iconv_system($val['path']) . '/' . iconv_system($val['name']);
                    $json = json_decode(file_get_contents($path), true);
                    if (is_array($json)) $list['filelist'][$key] = array_merge($val, $json);
                }
            }
            _DIR_OUT($list);
            return $list;
            */
        }

        /********************************************************************************************************************
         **  Added by ken li,  START
         ********************************************************************************************************************/
        public function sen5ShowAppInfo(){
            $appid = $_POST['appid'];
            global $L;
            $instance = Database::getInstance();
            $app = $instance->findOneApp($appid);
            if($app == null){show_json($L['app_not_found'], false);}
            $app['ICON_PATH'] = ICON_PATH;
            show_json($app);
        }
        public function sen5UpdateAppInfo(){
            $appid = $_POST['appid'];
            $name = $_POST['name'];
            $categories = $_POST['categories'];
            $downloads = $_POST['downloads'];
            $score = $_POST['score'];
            $summary = $_POST['summary'];
            $description = $_POST['description'];

            $instance = Database::getInstance();
            $instance->updateApp($appid, $name, $categories, $downloads, $score, $summary, $description);
            global $L;
            show_json($L['save_success']);
        }
        public function sen5ShowRepoInfo(){
            $repoid = $_POST['repoid'];
            global $L;
            $instance = Database::getInstance();
            $repo = $instance->findOneRepo($repoid);
            if($repo == null){show_json($L['repo_not_found'], false);}
            $repo['ICON_PATH'] = ICON_PATH;
            show_json($repo);
        }
        public function sen5UpdateRepoInfo(){
            $repoId = $_POST['repoId'];
            $customerName = $_POST['customerName'];
            $customerId = $_POST['customerId'];

            $instance = Database::getInstance();
            $instance->updateRepo($repoId, $customerName, $customerId);
            global $L;
            show_json($L['save_success']);
        }
        public function sen5AddApp(){
            $repoId = $_POST['repoId'];
            $customerName = $_POST['customerName'];
            $customerId = $_POST['customerId'];

            $instance = Database::getInstance();
            $instance->addRepo($repoId, $customerName, $customerId);
            global $L;
            show_json($L['save_success']);
        }
        /********************************************************************************************************************
         **  Added by ken li,  START
         ********************************************************************************************************************/
    }