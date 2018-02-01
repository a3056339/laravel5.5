/**
 * Created by Administrator on 2017/12/29.
 */
var Alert = function (options) {
    if (!options) {
        return false;
    }
    if (typeof options === 'string' || options.nodeType === 1) {
        options = {msg: options, type: 'success'};
    }
    if (typeof(options.type) === 'undefined') {
        options.type = 'success';
    }
    if ($("#kr_alert")) {
        $('#kr_alert').remove();
    }

    $('body').prepend('<div id="kr_alert" class="alert alert_' + options.type + '">' + options.msg + '</div>');
    //位置
    $('#kr_alert').css({left: ((document.documentElement.clientWidth / 2) - ($('#kr_alert').width() / 2)) + 'px'});
    if (typeof(options.time) === 'undefined') {
        options.time = 3;
    }
    setTimeout(function () {
        $('#kr_alert').slideUp(function () {
            $(this).remove();
        });
    }, options.time * 1000);
};

$(function () {
    $(".form_box1").on("click", function () {
        var uername = $.trim($(".username").val());
        var password = $.trim($(".pssword").val());
        if (uername == '') {
            Alert({msg: '用户名不能为空', type: 'error'});
            return false;
        }
        if (password == '') {
            Alert({msg: '登录密码不能为空', type: 'error'});
            return false;
        }
        $.ajax({
            url: '/auth',
            type: 'post',
            data: {uername: uername, password: password},
            async: false,
            dataType: 'JSON',
            success: function (res) {
                if (res.code == 0 && res.redirect_url != '') {
                    window.location.href = res.redirect_url;
                } else {
                    Alert({msg: res.msg, type: 'error'});
                }
            }
        });
    });

});
