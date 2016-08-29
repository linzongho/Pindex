<?php include_once TEMPLATE.'common/head.php'; ?>
<link href="./static/style/skin/<?php echo $config_theme;?>app_editor.css" rel="stylesheet" id='link_css_list'/>
	


<?php if(isset($_GET['project'])){?>
<style>.topbar{display: none;}.frame-header{top:0;}.frame-main{top:0px;}</style>
<?php } ?>

<body style="overflow:hidden;" oncontextmenu="return core.contextmenu();">
	<?php include(TEMPLATE.'common/navbar_share.html');?>
	<div class="frame-main">
		<div class='frame-left'>
			<ul id="folderList" style="margin-top:10px;" class="ztree"></ul>
		</div><!-- / frame-left end-->
		<div class='frame-resize'></div>
		<div class='frame-right'>
			<div class="frame-right-main"  style="height:100%;padding:0;margin:0;">
				<div class="resizeMask"></div>
				<div class="messageBox"><div class="content"></div></div>
				<div class="menuTreeRoot"></div>
				<div class="menuTreeFolder"></div>
				<div class="menuTreeFile"></div>				
				<div class ='frame'>
					 <iframe name="OpenopenEditor"
					  src="./<?php echo ENTRY_NAME; ?>?share/edit&user=<?php echo $_GET['user'];?>&sid=<?php echo $_GET['sid'];?>" 
					  style="width:100%;height:100%;border:0;" frameborder=0></iframe>
				</div>	
			</div>
		</div><!-- / frame-right end-->
	</div><!-- / frame-main end-->
<?php include(TEMPLATE.'common/footer.html');?>
<script src="./static/js/lib/seajs/sea.js"></script>
<script src="./<?php echo ENTRY_NAME; ?>?share/common_js&user=<?php echo $_GET['user'];?>&sid=<?php echo $_GET['sid'];?>&#=<?php echo rand_string(8);?>"></script>
<script type="text/javascript">
	AUTH  = {'explorer:fileDownload':<?php echo $can_download;?>};
	G.project = "<?php echo (isset($_GET['project'])?$_GET['project']:'') ;?>";
	G.user = "<?php echo $_GET['user'];?>";
	G.sid = "<?php echo $_GET['sid'];?>";
	G.share_info = <?php echo json_encode($share_info);?>;
	G.theme = "<?php echo $config_theme;?>";
	seajs.config({
		base: "./static/js/",
		preload: ["lib/jquery-1.8.0.min"],
		map:[
			[ /^(.*\.(?:css|js))(.*)$/i,'$1$2?ver='+G.version]
		]
	});
	seajs.use("app/src/share_editor/main");
</script>
</body>
</html>