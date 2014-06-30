var dialog_tpl_css="<style>div.check_version_dialog .aui_header{background:transparent;opacity:1;filter: alpha(opacity=100);}div.check_version_dialog .aui_title{color:#fff;text-shadow:none;}div.check_version_dialog .aui_min,div.check_version_dialog .aui_max{display:none;}div.check_version_dialog .aui_close{border-radius: 12px;}div.dialog-simple .dialog_mouse_in{.aui_header{.opacity(100);}}div.check_version_dialog .aui_content{overflow: visible;}div.check_version_dialog .aui_title{background-color:transparent;border: none;}.update_box .hidden{display: none;}.update_box{background:#fff;font-size: 14px;box-shadow: 0 5px 30px rgba(0,0,0,0.5);margin-top:-35px;}.update_box .title{width:100%;background:#6699cc;color:#fff;height:130px;}.update_box .button_radius{text-align:center;margin: 0 auto;padding-top:50px;}.update_box .button_radius a{color:#fff;text-decoration:none;border-bottom: 2px solid #f6f6f6;border:2px solid rgba(255,255,255,0.6);    border-radius:20px;padding:5px 10px;display: inline-block;font-size: 16px;}.update_box .button_radius a i{padding-left: 8px;}.update_box .button_radius a:hover,.button_radius a:focus,.button_radius a.this{background:rgba(255,255,255,0.3);}.update_box .button_radius a.this:hover{cursor: default;}.update_box .ver_tips{float:right; ;text-align: right;text-decoration: none;color:#9CF;display:block;margin-top: -26px;padding-right:10px;}.update_box .ver_tips:hover{color:#fff;}.update_box .version{color:#fff;font-size: 13px;text-align: center;line-height:50px;height:50px;}.update_box .version_info{padding:20px;}.update_box .version_info i{font-size:15px;display: block;border-left:3px solid #9cf;padding-left:10px;}.update_box .version_info .version_info_content{color: #69c;background:#eee;margin-top: 10px;padding:10px;}.update_box .version_info p{height:140px;overflow:auto;}.update_box .version_info a{float: right;color:#69c;text-decoration: none;}.update_box .progress{box-shadow:0 0 3px #fff;border-radius:20px;margin: 0 auto;margin-bottom:10px;width:170px;height:16px;margin-top: 10px;overflow:hidden !important;}.update_box .progress img{width:170px;}</style>",dialog_tpl_html="<div class='update_box'>    <div class='title'>        <div class='button_radius'>            <div class='progress hidden'><img src='{{loading_img}}'/></div>            {{if has_new}}            <a href='javascript:;' class='update_click'><span>{{LNG.update_auto_update}}</span><i class='icon-arrow-right'></i></a>            {{else}}            <a href='javascript:;' class='this'>{{LNG.update_is_new}}<i class='icon-smile'></i></a>            {{/if}}        </div>        {{if has_new}}<a class='ver_tips ignore' href='javascript:;'>{{LNG.update_ignore}}</a>{{/if}}        <div class='version'>{{LNG.update_version_local}}：ver{{ver_local}} | {{LNG.update_version_newest}}：ver {{ver_new}}        {{if has_new}}<span class='badge' style='background:#f60;'>new</span>{{/if}}</div>        <div style='clear:both'></div>    </div>    <div class='version_info'>        <i>ver {{ver_new}} {{LNG.update_whats_new}}：</i>        <div class='version_info_content'>            <p>{{echo LNG.update_info}}</p>            <a class='more' href='{{readmore_href}}' target='_blank'>{{LNG.update_readmore}}</a>            <div style='clear:both'></div>        </div>    </div></div>";define("app/update/main",[],function(e){var t="2.1",a=G.version,i="http://kalcaddle.com/download.html",s="http://static.kalcaddle.com/download/update/2.0-2.1.zip",o="http://kalcaddle.com/tools/state/index.php",n="kod_user_online",l=function(){var e=new Date;return parseInt(e.getTime()/1e3)},r=function(e,t,a){$.ajax({url:"./index.php?explorer/serverDownload&save_path="+t+"&url="+urlEncode2(e),dataType:"json",success:function(e){"function"==typeof a&&a(e)}})},c=function(e,t,a){$.ajax({url:"index.php?explorer/unzip&path_to="+urlEncode(t)+"&path="+urlEncode(e),success:function(e){"function"==typeof a&&a(e)}})},d=function(e,t){$.ajax({url:"index.php?explorer/pathDelete",type:"POST",dataType:"json",data:e,success:function(e){"function"==typeof t&&t(e)}})},p=function(){if(1==G.is_root){var e="check_version_dialog",t=$("."+e).find(".update_click"),a=$("."+e).find(".progress"),i=$("."+e).find(".ver_tips"),o=s,l=G.basic_path+"data/";G.basic_path,i.removeClass("ignore").html(LNG.update_downloading),t.addClass("hidden"),a.removeClass("hidden").fadeIn(300),r(o,l,function(e){if(e.code){var s=e.info,o='list=[{"type":"file","path":"'+urlEncode(s)+'"}]';return c(s,G.basic_path,function(e){return e.code?(d(o,function(){Cookie.del(n),a.addClass("hidden"),i.html(LNG.update_success),t.removeClass("hidden").unbind("click").removeClass("update_click").addClass("this").html(LNG.update_success),setTimeout(function(){FrameCall.goRefresh()},2e3)}),void 0):(a.addClass("hidden"),i.html(LNG.update_unzip_fail),t.removeClass("hidden").html(LNG.update_auto_update),void 0)}),void 0}a.addClass("hidden"),i.html(LNG.update_download_fail),t.removeClass("hidden").html(LNG.update_auto_update)})}},u=function(){var e="en";"zh_CN"==LNG.config.type&&(e="zh_CN");var t={en:{update_downloading:"Downloading...",update_download_fail:"Download failed",update_unzip_fail:"Unzip update failed",update_doing:"Updating",update_title:"Update",update_success:"Update successful",update_fail:"Update failed",update_auto_update:"Update Now",update_is_new:"Aredy is the newest",update_version_newest:"Newest Version",update_version_local:"Current Version",update_ignore:"Ignore",update_readmore:"Read more",update_whats_new:"What's New",update_info:"1.muti user<br/>2.drag upload<br/>3.zip/unzip<br/>4.all path support<br/>5.New editor<br/>"},zh_CN:{update_downloading:"下载中...",update_download_fail:"下载失败",update_unzip_fail:"解压覆盖失败",update_doing:"更新中...",update_title:"更新提示",update_success:"更新成功！",update_fail:"更新失败！",update_auto_update:"自动更新",update_is_new:"已经是最新版",update_version_newest:"最新版本",update_version_local:"当前版本",update_ignore:"暂时忽略",update_readmore:"查看更多",update_whats_new:"更新说明",update_info:"1.文件夹拖拽完美实现<br/>2.文件夹拖拽上传<br/>3.解压缩优化<br/>4.非服务器路径预览&下载支持<br/>5.树目录中文问题修复<br/>"}};for(var a in t[e])LNG[a]=t[e][a]},f=function(e){var s=parseFloat(t),o=parseFloat(a),n="kod_update_ignore_timeout",r=!1;s>o&&(r=!0);var c=function(){var e="check_version_dialog";if(0==$("."+e).length){u();var s=template.compile(dialog_tpl_html),o=dialog_tpl_css+s({loading_img:G.static_path+"/images/loading_simple.gif",LNG:LNG,has_new:r,readmore_href:i,ver_new:t,ver_local:a});art.dialog.through({id:e,simple:!0,top:"50%",resize:!1,width:330,title:LNG.update_title,padding:"0",fixed:!0,content:o}),$("."+e).hide().fadeIn(600).find(".update_click").unbind("click").bind("click",function(){p(),Cookie.del(n)}),$("."+e).find(".ignore").die("click").live("click",function(){Cookie.set(n,l()+432e3,8760),art.dialog.list[e].close()})}};e&&c(),r&&(void 0==Cookie.get(n)||Cookie.get(n)<=l())&&c()},h=function(){if(void 0==Cookie.get(n)){var t=o+"?is_root="+G.is_root+"&host="+urlEncode(G.app_host)+"&version="+a;e.async(t,function(){Cookie.set(n,"check-at-"+l(),120)})}},m=function(e){switch(e){case void 0:1==G.is_root&&f(!1),h();break;case"check":f(!0);break;default:}};return{todo:m}});