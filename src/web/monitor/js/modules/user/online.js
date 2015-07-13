var userOnline;

function UserOnline(){
    this.getCookie = function() {
        var returns = new Array();

        var items   = document.cookie.split(";");

        for(var i in items) {
            var item  = items[i].split("=");
            var key   = item[0].trim();
            var value = item[1].trim();

            returns[key] = value;
        }

        return returns;
    }

    this.refresh = function() {
        var cookie = this.getCookie();
        var uid    = cookie['USER_ID'];

        $.ajax({url: '/api/user/online/refresh?uid=' + uid})

        $.ajax({
            url: '/api/user/online/total',
            dataType: 'json',
            success: function(result) {
                var count = numberFormat(result.data, 0);
                $('.user-count .count').html(count);
            }
        })
    }
}

// launch
(function(){
    //userOnline = new UserOnline();
    //userOnline.refresh();
    setTimeout(arguments.callee, 60000)
})();

