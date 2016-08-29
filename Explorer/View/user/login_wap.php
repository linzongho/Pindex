<!--user login-->
<!DOCTYPE >
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo $L['kod_name'].$L['kod_power_by'];?></title>
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
	<link href="./static/images/favicon.ico" rel="Shortcut Icon" >
	<link href="./static/style/bootstrap.css" rel="stylesheet"/>
	<link href="./static/style/font-awesome/css/font-awesome.css" rel="stylesheet" >
	<link rel="stylesheet" type="text/css" href="./static/style/wap/login.css">
</head>

<body>
	<div class="loginbox" >
		<div class="title">
			<div class="logo"><i class="icon-cloud"></i><?php echo $L['kod_name'];?></div>
			<div class='info'><?php echo $L['kod_name_desc'];?></div>
		</div>
		<div class="form">
			<div class="inputs">
				<div><span><?php echo $L['username'];?>：</span><input id="username" name='name' type="text" placeholder="<?php echo $L['username'];?>" required/> </div> 
				<div><span><?php echo $L['password'];?>：</span><input id="password" name='password' type="password" placeholder="<?php echo $L['password'];?>" required /></div>
				
            	<?php if(need_check_code() && isset($_SESSION['code_error_time']) && intval($_SESSION['code_error_time']) >=3){?>
				<div class='check_code'>
					<span><?php echo $L['login_code'];?>：</span>
					<input name='check_code' class="check_code" type="text" placeholder="<?php echo $L['login_code'];?>" required /> <img src='./<?php echo ENTRY_NAME; ?>?user/checkCode' onclick="this.src='./<?php echo ENTRY_NAME; ?>?user/checkCode'" />
					<div style="clear:both;"></div>
				</div>				
				<?php }?>
			</div>
			<div class="actions">
				<input type="submit" id="submit" value="<?php echo $L['login'];?>" />
				<input type="checkbox" class="checkbox" name="rember_password" id='rm' checked='checked' />
				<label for='rm'><?php echo $L['login_rember_password'];?></label>				
			</div>
			<div class="msg"><?php echo $msg;?></div>
			<div style="clear:both;"></div>
			<div class='guest'>
			<a href="./<?php echo ENTRY_NAME; ?>?user/loginSubmit&name=guest&password=guest"><?php echo $L['guest_login'];?>
			<i class=' icon-arrow-right'></i></a></div>
		</div>
	</div>
<div class="common_footer"><?php echo $L['copyright_pre'].' v'.KOD_VERSION.' | '.$L['copyright_info'];?></div>
<script src="./<?php echo ENTRY_NAME; ?>?share/common_js#id=<?php echo rand_string(8);?>"></script>
<script src="./static/js/lib/seajs/sea.js"></script>
<script type="text/javascript">
	seajs.config({
		base: "./static/js/",
		preload: ["lib/jquery-1.8.0.min"],
		map:[
			[ /^(.*\.(?:css|js))(.*)$/i,'$1$2?ver='+G.version]
		]
	});
	seajs.use("<?php echo STATIC_JS;?>/src/user/main");
</script>
</body>
</html>