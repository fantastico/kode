<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="webpage"> 
<meta name="keywords" content="kalcaddle">
<meta name="author" content="kalcaddle.">
  <head>
  	<link href="<?php echo STATIC_PATH;?>style/bootstrap.css" rel="stylesheet"/>
	<link href="<?php echo STATIC_PATH;?>style/font-awesome/style.css" rel="stylesheet"/>
	<?php if(STATIC_LESS == 'css'){ ?>
	<link href="<?php echo STATIC_PATH;?>style/skin/<?php echo $config['user']['theme'];?>app_code_edit.css
    " rel="stylesheet" id='link_css_list'/>
	<?php }else{//less_compare_online ?>
	<link rel="stylesheet/less" type="text/css
    " href="<?php echo STATIC_PATH;?>style/skin/<?php echo $config['user']['theme'];?>app_code_edit.less"/>
	<script src="<?php echo STATIC_PATH;?>js/lib/less-1.4.2.min.js"></script>
    <?php } ?>
  </head>
  <body>
	<div class="edit_main" style="height: 100%;" oncontextmenu="return core.contextmenu();">
		<ul id="codetheme"  class="dropdown-menu dropbox" role="menu" aria-labelledby="drop_codetheme">
		<?php
$tpl = "<li class='{this} list' theme='{0}'>{0}</li>\n";
echo getTplList(',', ':', $config['setting_all']['codethemeall'], $tpl, $config['user']['codetheme']);
?>
		</ul>

		<!-- 主体部分 -->
		<div class="frame_left">
			<div class="edit_body">
				<div class="introduction">
					<?php include(LANGUAGE_PATH . LANGUAGE_TYPE . '/edit.html');?>
					<div style="clear:both"></div>
				</div>
				<div class="tabs"></div>
			</div>
		</div>

	</div>


<script src="<?php echo STATIC_PATH;?>js/lib/seajs/sea.js"></script>
<script src="<?php echo STATIC_PATH;?>js/lib/ace/src-min-noconflict/ace.js"></script>
<script src="<?php echo STATIC_PATH;?>js/lib/ace/src-min-noconflict/ext-language_tools.js"></script>
<script type="text/javascript">
    var LNG = <?php echo json_encode($L);?>;
	var G = {
		is_root 	: <?php echo $GLOBALS['is_root'];?>,
		web_root 	: "<?php echo $GLOBALS['web_root'];?>",
		web_host 	: "<?php echo HOST;?>",
		static_path : "<?php echo STATIC_PATH;?>",
		public_path  : "<?php echo PUBLIC_PATH;?>",
		basic_path  : "<?php echo BASIC_PATH;?>",
		version 	: "<?php echo KOD_VERSION;?>",
		app_host 	: "<?php echo APPHOST;?>",

		myhome   	: "<?php echo MYHOME;?>",//当前绝对路径
		frist_file	: "<?php echo 'appInfo/'.$_GET['appId'];?>",
		codetheme 	: "<?php echo $config['user']['codetheme'];?>"
	};
	seajs.config({
		base: "<?php echo STATIC_PATH;?>js/",
		preload: ["lib/jquery-1.8.0.min"]
	});
	seajs.use("<?php echo STATIC_JS;?>/src/edit/appInfo_main");
    </script>
</body>
</html>