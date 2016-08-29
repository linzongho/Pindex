<?php
include_once TEMPLATE.'common/head.php';
?>
<link href="./static/style/skin/<?php echo $config['user']['theme'];?>app_code_edit.css" rel="stylesheet" id='link_css_list'/>

  <body>
	<div class="edit_main" style="height: 100%;" oncontextmenu="return core.contextmenu();">
		<div class="tools">
			<div class="left">
				<!-- <div class="disable_mask"></div> -->
				<a class="toolMenu editMenuFile" href="javascript:;" draggable="false"><?php echo $L['file'];?></a>
				<a class="toolMenu editMenuEdit" href="javascript:;" draggable="false"><?php echo $L['edit'];?></a>
				<a class="toolMenu editMenuView" href="javascript:;" draggable="false"><?php echo $L['view'];?></a>
				<a class="toolMenu editMenuTools" href="javascript:;" draggable="false"><?php echo $L['tools'];?></a>
				<a class="toolMenu editMenuHelp" href="javascript:;" draggable="false"><?php echo $L['help'];?></a>
			</div>
			<div class="right">
				<a action="close" href="javascript:;" title="<?php echo $L['close'];?>"><i class="font-icon icon-remove"></i></a>
				<a action="fullscreen" href="javascript:;" title="<?php echo $L['full_screen'];?>"><i class="font-icon icon-resize-full"></i></a>
			</div>
			<div style="clear:both"></div>
		</div><!-- end tools -->

		<!-- 主体部分 -->
		<div class="frame_left">
			<div class="edit_tab">
				<div class="tabs">
					<a  href="javascript:Editor.add()" class="add icon-plus"></a>
					<div style="clear:both"></div>
				</div>
			</div>
			<div class="edit_body">
				<div class="introduction">
					<?php include(LANGUAGE_PATH.LANGUAGE_TYPE.'/edit.html');?>
					<div style="clear:both"></div>
				</div>
				<div class="tabs"></div>
			</div>			
		</div>
		<!-- 预览 -->
		<div class="frame_right">
			<div class="resize"></div>
			<div class="right_main">
				<div class="function_list" style="display:none;">
					<div class="function_list_tool">
						<div class="box">
						<span> <i class="icon-code"></i> <?php echo $L['function_list'];?></span>
						<a action="close_preview" href="javascript:preview.close();"><i class="font-icon icon-remove"></i></a>
						</div>
					</div>
					<div class="function_list_parent">
						<div class="function_list_box"></div>
					</div>
				</div>
				<div class="preview" style="display:none;">
					<div class="preview_tool">
						<input type="text" value="" />
						<div class="box">
							<a action="refresh" href="javascript:preview.refresh();" title="<?php echo $L['refresh'];?>"><i class="font-icon icon-refresh"></i></a>
							<a action="open_ie" href="javascript:preview.openUrl();" target="_blank" title="<?php echo $L['open_ie'];?>"><i class="font-icon icon-globe"></i></a>
							<a action="close_preview" href="javascript:preview.close();" title="<?php echo $L['close'];?>"><i class="font-icon icon-remove"></i></a>
						</div>
					</div>
					<div class="preview_frame">
						<iframe src="" style="width:100%;height:100%;border:0;"></iframe>
					</div>
				</div>
			</div>
		</div>
	</div>

<script src="./<?php echo ENTRY_NAME; ?>?user/common_js#id=<?php echo rand_string(8);?>"></script>
<script src="./static/js/lib/seajs/sea.js"></script>
<script src="./static/js/lib/ace/src-min-noconflict/ace.js"></script>
<script src="./static/js/lib/ace/src-min-noconflict/ext-language_tools.js"></script>
<script type="text/javascript">
	G.frist_file = "<?php echo (isset($_GET['filename']) ? $_GET['filename'] :'') ;?>";
	G.code_config = <?php echo $editor_config;?>;
	G.code_theme_all = "<?php echo $config['setting_all']['codethemeall']?>";
	seajs.config({
		base: "./static/js/",
		preload: ["lib/jquery-1.8.0.min"],
		map:[
			[ /^(.*\.(?:css|js))(.*)$/i,'$1$2?ver='+G.version]
		]
	});
	seajs.use("app/src/edit/main");	
</script>
</body>
</html>