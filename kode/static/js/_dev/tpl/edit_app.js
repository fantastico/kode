define(function (require, exports) {
    return{
        html: "<form id='{{formid}}' action='{{action}}' method='post' onsubmit='return false;'>\
                <input type='hidden' name='appid' value='{{_id}}'/>\
                <div class='pathinfo'>\
                <div class='p'>\
                    <div ><img class='icon' src='{{ICON_PATH}}/{{icon}}'/></div>\
                    <div class='title'>{{_id}}</div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='line'></div>\
                <div class='p'>\
                    <div class='title'>{{LNG.name}}:</div>\
                    <div class='content'><input type='text' name='name' value='{{name}}' required/></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='p'>\
                    <div class='title'>{{LNG.categories}}:</div>\
                    <div class='content'><input type='text' name='categories' value='{{categories}}' /></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='line'></div>\
                <div class='p'>\
                    <div class='title'>{{LNG.downloads}}:</div>\
                    <div class='content'><input name='downloads' value='{{downloads}}' type='number' min='0'/></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='p'>\
                    <div class='title'>{{LNG.score}}:</div>\
                    <div class='content'><input name='score' value='{{score}}' type='number' min='0' max='100'/></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='line'></div>\
                <div class='p'>\
                    <div class='title'>{{LNG.summary}}:</div>\
                    <div class='content'><textarea type='text' name='summary' >{{summary}}</textarea></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='p'>\
                    <div class='title'>{{LNG.description}}:</div>\
                    <div class='content'><textarea type='text' name='description'>{{description}}</textarea></div>\
                    <div style='clear:both'></div>\
                </div>\
            </div>\
            <input id='btn_submit' type='submit' style='display: none'/>\
            </form>"
    }
});