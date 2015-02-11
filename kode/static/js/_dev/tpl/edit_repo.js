define(function (require, exports) {
    return{
        html_hasIcon: "<form id='{{formid}}' action='{{action}}' method='post' onsubmit='return false;'>\
                <input type='hidden' name='repoId' value='{{_id}}'/>\
                <div class='pathinfo'>\
                <div class='p'>\
                    <div ><img class='icon' src='{{icon}}'/></div>\
                    <div class='title'>{{_id}}</div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='line'></div>\
                <div class='p'>\
                    <div class='title'>Cust. ID:</div>\
                    <div class='content'><input type='text' name='customerId' value='{{customerId}}' /></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='p'>\
                    <div class='title'>Cust. Name:</div>\
                    <div class='content'><input type='text' name='customerName' value='{{customerName}}' /></div>\
                    <div style='clear:both'></div>\
                </div>\
            </div>\
            <input id='btn_submit' type='submit' style='display: none'/>\
            </form>",

        html_noIcon: "<form id='{{formid}}' action='{{action}}' method='post' onsubmit='return false;'>\
                <input type='hidden' name='repoId' value='{{_id}}'/>\
                <div class='pathinfo'>\
                <div class='p'>\
                    <div class='folder ico'></div>\
                    <div class='title'>{{_id}}</div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='line'></div>\
                <div class='p'>\
                    <div class='title'>Cust. ID:</div>\
                    <div class='content'><input type='text' name='customerId' value='{{customerId}}' /></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='p'>\
                    <div class='title'>Cust. Name:</div>\
                    <div class='content'><input type='text' name='customerName' value='{{customerName}}' /></div>\
                    <div style='clear:both'></div>\
                </div>\
            </div>\
            <input id='btn_submit' type='submit' style='display: none'/>\
            </form>"
    }
});