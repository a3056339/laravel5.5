//字符串转时间戳
function DateToUnix(string) {
	var f = string.split(' ', 2);
	var d = (f[0] ? f[0] : '').split('-', 3);
	var t = (f[1] ? f[1] : '').split(':', 3);
	return(new Date(
		parseInt(d[0], 10) || null,
		(parseInt(d[1], 10) || 1) - 1,
		parseInt(d[2], 10) || null,
		parseInt(t[0], 10) || null,
		parseInt(t[1], 10) || null,
		parseInt(t[2], 10) || null
	)).getTime() / 1000;
}
//时间戳转字符串
function UnixToDate(unixTime, isFull, timeZone) {
	if(typeof(timeZone) == 'number') {
		unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
	}
	var time = new Date(unixTime * 1000);
	var ymdhis = "";
	var year = time.getUTCFullYear();
	var month = time.getUTCMonth() + 1;
	var day = time.getUTCDate()
	if(month < 10) {
		month = "0" + month;
	}
	if(day < 10) {
		day = "0" + day;
	}
	ymdhis = year + "-" + month + "-" + day;
	if(isFull === true) {
		var hour = time.getUTCHours();
		var minute = time.getUTCMinutes();
		var second = time.getUTCSeconds();
		if(hour < 10) {
			hour = " " + "0" + hour;
		} else {
			hour = " " + hour;
		}
		if(minute < 10) {
			minute = "0" + minute;
		}
		if(second < 10) {
			second = "0" + second;
		}
		ymdhis += hour + ":" + minute;
	}
	return ymdhis;
}
//console.log(UnixToDate(1500713110000/1000,true,8));
//获取字段
function getRequest() {
	var url = window.location.search; //获取url中"?"符后的字串   
	var theRequest = new Object();
	if(url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i++) {
			//就是这句的问题
			theRequest[strs[i].split("=")[0]] = decodeURI(strs[i].split("=")[1]);
			//之前用了unescape()
			//才会出现乱码  
		}
	}
	return theRequest;
}

/*设置cookie*/
function setCookie(name, value) {
	//	if(days == null || days == '') {
	//		days = 300;
	//	}
	//	var exp = new Date();
	//	exp.setTime(exp.getTime() + days * 24 * 60 * 60 * 1000);
	document.cookie = name + "=" + escape(value);
}
//console.log(getCookie("loginCookie"));

/*获取cookie*/
function getCookie(name) {
	var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
	if(arr = document.cookie.match(reg))
		return unescape(arr[2]);
	else
		return null;
}
var urlFirst = "http://www.xkdapi.com"; //测试
//var urlFirst = "https://api.iqdod.com/";
/*ajax请求*/
function ajax(url, param, datat, callback,callType) {
	$.ajax({
		type: callType,
		url: urlFirst + url,
		data: param,
		dataType: datat,
		success: function(data) {
			callback(data);
		},
		error: function() {

		}
	});
}

function changeNull(s) {
	if(s == null) {
		return "";
	} else {
		return s;
	}
}