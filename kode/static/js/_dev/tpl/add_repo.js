define(function (require, exports) {
    return{
        html: "<form id='{{formid}}' action='{{action}}' method='post' onsubmit='return false;'>\
                <div class='pathinfo'>\
                <div class='p'>\
                    <div class='title'>Repo Name:</div>\
                    <div class='content'><input type='text' name='repoId' required/></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='line'></div>\
                <div class='p'>\
                    <div class='title'>Cust. ID:</div>\
                    <div class='content'><input type='text' name='customerId'/></div>\
                    <div style='clear:both'></div>\
                </div>\
                <div class='p'>\
                    <div class='title'>Cust. Name:</div>\
                    <div class='content'><input type='text' name='customerName'/></div>\
                    <div style='clear:both'></div>\
                </div>\
            </div>\
            <input id='btn_submit' type='submit' style='display: none'/>\
            </form>"
    }
});