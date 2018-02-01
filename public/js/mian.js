$('body').ready(function () {
//	console.log(urlFirst)
    (function ($) {
        $.extend({
            /**
             * 调用方法： var timerArr = $.blinkTitle.show();
             *     $.blinkTitle.clear(timerArr);
             */
            blinkTitle: {
                show: function () { //有新消息时在title处闪烁提示
                    var step = 0,
                        _title = document.title;
                    var timer = setInterval(function () {
                        step++;
                        if (step == 3) {
                            step = 1
                        }
                        if (step == 1) {
                            document.title = '【　　　】' + _title
                        }
                        if (step == 2) {
                            document.title = '【新消息】' + _title
                        }
                    }, 800);
                    return [timer, _title];
                },
                /**
                 * @param timerArr[0], timer标记
                 * @param timerArr[1], 初始的title文本内容
                 */
                clear: function (timerArr) {
                    //去除闪烁提示，恢复初始title文本
                    if (timerArr) {
                        clearInterval(timerArr[0]);
                        document.title = timerArr[1];
                    }
                }
            }
        });
    })(jQuery);
    var timerArr;
    var to_uid;
    var socket = io(urlFirst + ':2120');
    // uid 可以为网站用户的uid，作为例子这里用session_id代替
    var kefuuid = '10000';

    // 当socket连接后发送登录请求
    socket.on('connect', function () {
        socket.emit('login', kefuuid);
    });
    // 当服务端推送来消息时触发，这里简单的aler出来，用户可做成自己的展示效果
    socket.on('new_msg', function (msg) {
        var data = JSON.parse(msg);
        var uid = data.user.uid;
        var dom;
        timerArr = $.blinkTitle.show();
//      console.log(data)
        if (uid == to_uid) {
            var currentHtml = "<div class='receive'>" +
                "<div class='receive_avtar'><img src='" + data.user.avatar + "'></div>" +
                "<p>" + data.content + "</p>" +
                "<span>" + data.user.create_at + "</span>" +
                "</div>";

            $(".chatMessage").last().append(currentHtml);
            $('.right_content').animate({
                scrollTop: $(".right_content").scrollTop() + 100
            }, 500);
        } else {
            if ($(".chat_list").children().hasClass('list_' + data.user.uid)) {
                dom = $(".list_" + data.user.uid);
                dom.remove();
                dom.find('i').fadeIn(0);
                $(".list_box").after(dom);
            } else {
                var currentHtml = "<div class='list_item list_" + data.user.uid + "' data-name='" + data.user.name + "' data-id='" + data.user.uid + "'>" +
                    "<i style='display: block'></i>" +
                    "<div class='list_colum'>" +
                    "<div class='avater'>" +
                    "<img src='" + data.user.avatar + "'>" +
                    "</div>" +
                    "<p>" + data.user.name + "(" + data.user.uid + ")</p>" +
                    "</div>" +
                    "<span>" + data.user.create_at + "</span>" +
                    "</div>";

                $(".list_box").after(currentHtml);
            }
        }
    });
    $("body").height($(window).height());
    $(".right_content").height($(window).height() * 0.99 - 200);

    //鼠标移入滚动条显示
//	$(".content_left").hover(function(){
//	    $(".content_left").css("overflow-y","auto")
//	},function(){
//	    $(".content_left").css("overflow-y","hidden")
//	})
//	$(".right_content").hover(function(){
//	    $(".right_content").css("overflow-y","auto")
//	},function(){
//	    $(".right_content").css("overflow-y","hidden")
//	})

    ajax('/app/system_talks', '', 'json', getResult, 'GET');
    function getResult(response) {
        var data = response.data;
        var html = '';
        if (response.code == 0) {
            for (var i = 0; i < data.talks.length; i++) {
                html += "<div class='list_item list_" + data.talks[i].from_uid + "' data-name='" + data.talks[i].from_name + "' data-id='" + data.talks[i].from_uid + "'>" +
                "<i></i>" +
                "<div class='list_colum'>" +
                "<div class='avater'>" +
                "<img src='" + data.talks[i].from_avatar + "'>" +
                "</div>" +
                "<p>" + data.talks[i].from_name + "(" + data.talks[i].from_uid + ")</p>" +
                "</div>" +
                "<span>" + data.talks[i].create_at + "</span>" +
                "</div>";
            }
            $(".chat_list").append(html);

        } else {

        }
    }

    var page = 1;
    var limit = 20;

    var isFlag = true;
    $(".chat_list").on("click", ".list_item", function () {
        $.blinkTitle.clear(timerArr);
        $(".right_send").fadeIn(0);
        $(this).find('i').fadeOut(0);
        page = 1;
        isFlag = true;
        $(this).addClass('check');
        $(this).siblings().removeClass('check');
        to_uid = $(this).attr('data-id');
        var name = $(this).attr('data-name');
        var dataJson = {
            url: "/app/talks_list",
            dataed: {
                to_uid: to_uid,
                page: page,
                limit: limit
            }
        };
        //聊天界面顶部文字改变
        $(".right_header p").text(name + "(" + to_uid + ")");
        //暂无消息 隐藏
        $(".chatMessage label").fadeOut(0);
        //显示loading
        $(".chatMessage i").addClass('isloading');


        //请求聊天内容接口
        ajax(dataJson.url, dataJson.dataed, "json", getChat, 'GET');
        //默认滚动到底部
//		console.log($(".right_content").height())
//			console.log($(".right_content").height())


//		$('.right_content').animate({
//			scrollTop: $(".right_content").height()+200,
//		}, 500);

//		var h = $(document).height();
//		console.log(h)
        $('.right_content').animate({
            scrollTop: 1200
        }, 500);

    });

    $(".right_content").scroll(function () {

        if ($(".right_content").scrollTop() <= 0 && isFlag) {
            $(".chatMessage i").addClass('isloading');
            page = page + 1;
            var dataJson = {
                url: "/app/talks_list",
                dataed: {
                    to_uid: to_uid,
                    page: page,
                    limit: limit
                }
            };
            setTimeout(function () {
                ajax(dataJson.url, dataJson.dataed, "json", getChat, 'GET');
                $('.right_content').animate({
                    scrollTop: 100
                }, 500);
            }, 500)
        }
    });


    function getChat(response) {
//		console.log(response)
        //清除原有的聊天天记录
        if (page == 1) {
            $(".receive,.send").remove();
        }
        var result = response.data.talks;
        var chatHtml = '';
        if (response.code == 0) {
            $(".chatMessage i").removeClass('isloading');
            if (result.length < limit) {
                isFlag = false;
            }
//			if(result.length==0&&page!=1){
//				alert("没有更多聊天记录了")
//			}
            if (result.length == 0 && page == 1) {
                $(".chatMessage label").fadeIn(0);
            } else {
                for (var i = 0; i < result.length; i++) {
                    if (result[i].from_uid == '10000') {
                        chatHtml += "<div class='send'>" +
                        "<p>" + result[i].msg + "</p>" +
                        "<span>" + result[i].create_at + "</span>" +
                        "<div class='send_avtar'><img src='" + result[i].from_avatar + "'></div>" +
                        "</div>";
                    } else {
                        chatHtml += "<div class='receive'>" +
                        "<div class='receive_avtar'><img src='" + result[i].from_avatar + "'></div>" +
                        "<p>" + result[i].msg + "</p>" +
                        "<span>" + result[i].create_at + "</span>" +
                        "</div>";
                    }
                }
                $(".chatBox").after(chatHtml);
            }
        } else {
            $(".chatMessage i").removeClass('isloading');
            $(".chatMessage label").text('接口出错');
            $(".chatMessage label").fadeIn(0);
        }
    }

    function format(value) {
        if (value < 10) {
            return "0" + value
        } else {
            return value
        }
    }

    function send() {
        var content = $("#content").val();
        var myDate = new Date();
        var cur_time = myDate.getFullYear() + '-' + format((myDate.getMonth() + 1)) + '-' + format(myDate.getDate()) + ' ' + format(myDate.getHours()) + ':' + format(myDate.getMinutes()) + ':' + format(myDate.getSeconds())
        if (content == '') {
            $(".tishi").fadeIn(100);
            setTimeout(function () {
                $(".tishi").fadeOut(100)
            }, 2000);
            return
        }
        var dataJson = {
            to_uid: to_uid,
            msg: content
        };
        $("#content").val('');
        ajax('/app/system_talk', dataJson, 'json', sendSuccess, 'POST');
        var system_avatar =$("#system").attr('src');
        var kefuHtml = "<div class='send'>" +
            "<p><b></b><s title='点击重新发送'></s>" + content + "</p>" +
            "<div class='send_avtar'><img src='"+system_avatar+"'></div>" +
            "<span>" + cur_time + "</span>" +
            "</div>";
        $(".chatMessage").last().append(kefuHtml);
        $('.right_content').animate({
            scrollTop: $(".right_content").scrollTop() + 100
        }, 500);
    }

    $(".sendBtn").on("click", function () {
        send();
    });

    function sendSuccess(response) {
        if (response.code == 0) {
            $(".send p b").fadeOut(100);
        } else {
            $(".send p b").fadeOut(100);
            $(".send p s").fadeIn(100);
        }
    }

    $(".chatMessage").on("click", ".send p s", function () {
        $(".send p b").fadeIn(100);
        $(".send p s").fadeOut(100);
        var dataJson = {
            to_uid: to_uid,
            msg: $(this).parent().text()
        };
        ajax('/app/system_talk', dataJson, 'json', sendSuccess, 'POST')
    });
    $(document).keydown(function (event) {
        if (event.keyCode == 13) {
            send();
        }
    });



});


