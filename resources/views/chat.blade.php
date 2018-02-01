<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>心理诊所聊天后台</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		<link rel="shortcut icon" href="/favicon.ico">
		<link rel="stylesheet" href="/css/base.css" />
		<script type="text/javascript" src="/js/jquery-2.1.4.js" ></script>
	</head>
	<body>
		<div class="chatContent">
			<div class="content_left">
				<div class="header">
					<div class="head_colum">
						<img src="<?=$avatar?>" alt="" id="system">
						<p><?=$name?></p>
					</div>
					<div class="icon"></div>
				</div>
				<!--<div class="logout"></div>-->
				<div class="chat_list">
					<div class="list_box"></div>
					<!--<div class="list_item">
						<i></i>
						<div class="list_colum">
							<div class="avater">
								<img src="img/2KriyDK.png">
							</div>
							<p>hous(121212)</p>
						</div>
						<span>2017-10-10111</span>
					</div>					-->
				</div>
			</div>
			<div class="content_right">
				<div class="right_header">
					<p>暂无消息</p>
				</div>
				<div class="right_content">
					<div class="chatMessage">
						<label>暂无消息！</label>
						<i></i>
						<div class="chatBox"></div>
						<!--<div class="receive">
							<div class="receive_avtar"><img src="img/2KriyDK.png"></div>
							<span>2017-10-10</span>
							<p>接受接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息信息</p>
						</div>
						<div class="send">
							<p>接受接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息接受信息信息</p>
							<span>2017-10-10</span>
							<div class="send_avtar"><img src="img/2KriyDK.png"></div>
						</div>-->
					</div>
				</div>
				<div class="right_send">
					<textarea id="content"></textarea>
					<div class="tishi">不能发送空白消息！</div>
					<button class="sendBtn">发送</button>
				</div>
			</div>
		</div>
	</body>
	<script type="text/javascript" src="/js/jquery-2.1.4.js" ></script>
	<script type="text/javascript" src="/js/sccl-util.js" ></script>
	<script type="text/javascript" src="/js/socket.js" ></script>
	<script type="text/javascript" src="/js/mian.js" ></script>
</html>
