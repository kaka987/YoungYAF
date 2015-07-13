$(function(){
//    $.ajax({
//        url: '/notice/get',
//        dataType: 'json',
//        success: function(result) {
//            if(result.msg.length == 0) {
//                return;
//            }
//            var n = noty({
//                text        : result.msg,
//                type        : result.type,
//                dismissQueue: true,
//                layout      : 'inline'
//            });
//            setTimeout(function(){ n.close() }, 2000);
//        }
//    })

    // 普通登陆
    $("a.login").click(function() {
        if(!$(this).attr('disabled')) {
            login();
        }
    });

    // 匿名登陆
    $("input.anonymous").click(function() {
        var data = {
            'email': 'anonymous@yeahmobi.com',
            'password': '123456'
        };
        $('#email').val(data.email);
        $('#password').val(data.password);
        login();
    });

    // 登陆
    function login() {
        // 禁用登陆按钮
        $("a.login").addClass('disabled');
        $("a.login").attr('disabled', true);

        var data = {
            'email': $("#email").val(),
            'password': $("#password").val()
        };

        $.ajax({
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(result) {
                if(result.result == 'success') {
                    loginNotice(result.text, 'success');
                    var backPage = $('#backPage').val();
                    setTimeout(function(){ n.close(); location.href = backPage; }, 2000);
                } else if(result.result == 'error') {
                    loginNotice(result.text, 'error');
                    // 关闭错误提示
                    setTimeout(function(){ n.close() }, 2000);
                    $("a.login").removeClass('disabled');
                    $("a.login").removeAttr('disabled');
                } else {
//                        $("a.login").removeClass('disabled');
//                        $("a.login").removeAttr('disabled');
                }
            }
        });
    }
    var n;
    var loginNotice = function(msg, type) {
        n = noty({
            text        : msg,
            type        : type,
            dismissQueue: true,
            layout      : 'inline'
        });
    }

})