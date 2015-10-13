<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?php echo ($web_title); ?> - <?php echo C('WEB_SITE');?></title>
		<meta name="keywords" content="体质检测,国家学生,体质健康信息" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!-- basic styles -->

		<link href="/Public/assets/css/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="/Public/assets/css/font-awesome.min.css" />

		<!--[if IE 7]>
		  <link rel="stylesheet" href="/Public/assets/css/font-awesome-ie7.min.css" />
		<![endif]-->

		<!-- page specific plugin styles -->

		<!--<link rel="stylesheet" href="/Public/assets/css/prettify.css" />-->

		<!-- fonts -->

		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" />

		<!-- ace styles -->

		<link rel="stylesheet" href="/Public/assets/css/ace.min.css" />
		<link rel="stylesheet" href="/Public/assets/css/ace-rtl.min.css" />
		<link rel="stylesheet" href="/Public/assets/css/ace-skins.min.css" />

		<link rel="stylesheet" href="/Public/assets/css/select2.css" />
		<link rel="stylesheet" href="/Public/assets/css/fileinput.min.css" />

		<!--[if lte IE 8]>
		  <link rel="stylesheet" href="/Public/assets/css/ace-ie.min.css" />
		<![endif]-->

		<!-- inline styles related to this page -->

		<!-- ace settings handler -->

		<script src="/Public/assets/js/ace-extra.min.js"></script>

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

		<!--[if lt IE 9]>
		<script src="/Public/assets/js/html5shiv.js"></script>
		<script src="/Public/assets/js/respond.min.js"></script>
		<![endif]-->

		<!-- basic scripts -->

		<!--[if !IE]> -->

		<script src="/Public/assets/js/jquery-2.0.3.min.js"></script>

		<!-- <![endif]-->

		<!--[if IE]>
		<script src="/Public/assets/js/jquery-1.10.2.min.js"></script>
		<![endif]-->

		<!--[if !IE]> -->

		<script type="text/javascript">
			window.jQuery || document.write("<script src='/Public/assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
		<script type="text/javascript">
		 window.jQuery || document.write("<script src='/Public/assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
		</script>
		<![endif]-->

		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='/Public/assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="/Public/assets/js/bootstrap.min.js"></script>
		<script src="/Public/assets/js/typeahead-bs2.min.js"></script>

		<!-- page specific plugin scripts -->

		<!--<script src="/Public/assets/js/prettify.js"></script>-->

		<!-- ace scripts -->

		<script src="/Public/assets/js/ace-elements.min.js"></script>
		<script src="/Public/assets/js/ace.min.js"></script>

		<script src="/Public/assets/js/layer/layer.js"></script>
	</head>


	<!-- navber -->
	<!-- 加载页面 // -->
		<body>
		<div class="navbar navbar-default" id="navbar">
			<script type="text/javascript">
				try{ace.settings.check('navbar' , 'fixed')}catch(e){}
			</script>

			<div class="navbar-container" id="navbar-container">
				<div class="navbar-header pull-left">
					<a href="/" class="navbar-brand">
						<small>
							<?php echo C('WEB_SITE');?>
						</small>
					</a><!-- /.brand -->
				</div><!-- /.navbar-header -->

				<div class="navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">
						<!-- 顶部菜单 -->
						<li class="light-blue">
							<a data-toggle="dropdown" href="#" class="dropdown-toggle">
								<span class="user-info">
									<small>欢迎,</small>
									<?php echo ($login_unit); ?>
								</span>
							</a>
						</li>
					</ul><!-- /.ace-nav -->
				</div><!-- /.navbar-header -->
			</div><!-- /.container -->
		</div>

		<!-- 左侧菜单 -->

		<div class="main-container" id="main-container">
			<script type="text/javascript">
				try{ace.settings.check('main-container' , 'fixed')}catch(e){}
			</script>

			<div class="main-container-inner">
				<a class="menu-toggler" id="menu-toggler" href="#">
					<span class="menu-text"></span>
				</a>
				
				<div class="sidebar" id="sidebar">
					<script type="text/javascript">
						try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
					</script>

					<ul class="nav nav-list">
					<?php if(is_array($navList)): $i = 0; $__LIST__ = $navList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li <?php if(($vo['url']) == $action_name): ?>class="active"
						<?php else: ?>
						<?php if(deep_in_array($action_name,$vo['nav_list'])): ?>class="active open"<?php endif; endif; ?>>
							<a <?php if(($vo['url']) != ""): ?>href="<?php echo U($vo['url']);?>"<?php else: ?>href="#" class="dropdown-toggle"<?php endif; ?>>
								<!--<i class="icon-text-width"></i>-->
								<span class="menu-text"> <?php echo ($vo["func_name"]); ?> </span>
								<?php if(($vo['url']) == ""): ?><b class="arrow icon-angle-down"></b><?php endif; ?>
							</a>

							<?php if(($vo['url']) == ""): ?><ul class="submenu">
							<?php if(is_array($vo['nav_list'])): $i = 0; $__LIST__ = $vo['nav_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voSon): $mod = ($i % 2 );++$i;?><li <?php if(($voSon['url']) == $action_name): ?>class="active"<?php endif; ?>>
									<a href="<?php echo U($voSon['url']);?>">
										<!--<i class="icon-double-angle-right"></i>-->
										<?php echo ($voSon["func_name"]); ?>
									</a>
								</li><?php endforeach; endif; else: echo "" ;endif; ?>
							</ul><?php endif; ?>
						</li><?php endforeach; endif; else: echo "" ;endif; ?>
					</ul><!-- /.nav-list -->

					<div class="sidebar-collapse" id="sidebar-collapse">
						<i class="icon-double-angle-left" data-icon1="icon-double-angle-left" data-icon2="icon-double-angle-right"></i>
					</div>

					<script type="text/javascript">
						try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
					</script>
				</div>


				<!--页面开始-->
				<div class="main-content">
					<div class="breadcrumbs" id="breadcrumbs">
						<script type="text/javascript">
							try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
						</script>

						<ul class="breadcrumb">
							<li>
								<i class="icon-home home-icon"></i>
								<a href="<?php echo U('Home/Index/index');?>">首页</a>
							</li>
							<li class="active"><?php echo ($web_title); ?></li>
						</ul><!-- .breadcrumb -->
					</div>

					<div class="page-content">
						<div class="page-header">
						</div><!-- /.page-header -->
							<div class="row">
								<div class="col-xs-12">
									<div class="col-sm-12">
										<div class="widget-box">
											<div class="widget-header widget-header-flat">
											<h4>打印说明</h4>
											</div>					
											<div class="widget-body">
												<div class="widget-main">
													<p>打印说明：为保证您的打印格式标准，建议使用指定浏览器进行浏览打印。在线打印推荐浏览器：IE8、9、11。如格式显示异常，请通过“登记卡下载”下载登记卡至本地进行打印。</p>

													<p>注：请不要使用迅雷等下载工具。</p>

													<p>如果去掉打印页面的页码、URL等，打印前请点击浏览器工具栏的打印机图标：</p>

													<p>1.选择“页面设置”</p>

													<p>2.页眉、页脚下拉框选择“-空-”并保存设置。</p>

													<p>如需使用页面“另存网页”功能，需对浏览器安全进行设置：</p>

													<p>1.选择浏览器“工具”</p>

													<p>2.点击“Internet选项”</p>

													<p>3.选择“安全”</p>

													<p>4.点击“自定义级别”</p>

													<p>5.将“对标记为可安全执行脚本的ActiveX控件执行脚本”及“对未标记为可安全执行脚本的ActiveX控件初始化并执行脚本（不安全）”设置为“启用”，点击“确定”进行保存设置。</p>
												</div>
											</div>
										</div>
										<hr />
										<div class="row">
											<form action="" method="get" id="showForm">
												<input value="showStuInfo" type="hidden" name="ac"/>
												<select name="school_year" id="school_year" class="select2 width-15" disabled><?php echo ($school_year_options); ?></select>
												<select name="town_id" id="town_id"  class="select2 width-15"><?php echo ($town_id_options); ?></select>
												<select name="school_id" id="school_id"  class="select2 width-25"><?php echo ($school_id_options); ?></select>
												<select name="school_grade" id="school_grade"  class="select2 width-10"><?php echo ($school_grade_options); ?></select>
												<select name="class_num" id="class_num"  class="select2 width-15"><?php echo ($class_num_options); ?></select>
												&nbsp;&nbsp;&nbsp;
												<input type="submit" class="btn btn-small btn-white" value="查看" />&nbsp;<input type="submit" class="btn btn-small  btn-white" value="打印">
											</form>
											<hr /><!--
											<div class="table-responsive">
													<table id="sample-table-1" class="table table-striped table-bordered table-hover">
														<thead>
															<tr>
																<th>教育ID号</th>
																<th>姓名</th>
																<th class="hidden-480">学校名称</th>
																<th class="hidden-480">年级</th>
																<th class="hidden-480">班级</th>
																<th class="hidden-480">性别</th>
																<th class="hidden-480">民族</th>
																<th class="hidden-480">全国学籍号</th>
																<th class="hidden-480">生源地</th>
																<?php if(($userinfo['user_kind']) == "109030"): ?><th>是否在学</th><?php endif; ?>
															</tr>
														</thead>

														<tbody>
															<?php if(is_array($stuinfos['list'])): $i = 0; $__LIST__ = $stuinfos['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
																<td><?php echo ($vo["education_id"]); ?></td>
																<td><?php echo ($vo["name"]); ?></td>
																<td class="hidden-480"><?php echo ($vo["school_name"]); ?></td>

																<td class="hidden-480"><?php echo ($vo["grade_name"]); ?></td>
																<td class="hidden-480"><?php echo ($vo["class_name"]); ?></td>
																<td class="hidden-480"><?php echo ($vo["sex"]); ?></td>
																<td class="hidden-480"><?php echo ($vo["folk"]); ?></td>
																<td class="hidden-480"><?php echo ($vo["country_education_id"]); ?></td>
																<td class="hidden-480"><?php echo ($vo["student_source"]); ?></td>
															</tr><?php endforeach; endif; else: echo "" ;endif; ?>											
														</tbody>
													</table>
											</div><!-- /.table-responsive -->
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12">
									<div class="row">
										<div class="col-sm-12">

										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">

										</div><!-- /span -->
										<!--page-->
										<?php if(($stuinfos['page']) != ""): ?><div class="message-footer clearfix"><?php echo ($stuinfos["page"]); ?></div><?php endif; ?>
										<!--/page-->
									</div><!-- /row -->
								</div>
							</div>
					</div><!-- /.page-content -->
				</div><!-- /.main-content -->
			</div><!-- /.main-container-inner -->

			<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
				<i class="icon-double-angle-up icon-only bigger-110"></i>
			</a>
		</div><!-- /.main-container -->

		<!-- inline scripts related to this page -->
		<script src="/Public/assets/js/select2.min.js"></script>
		<script src='/Public/assets/js/jquery.form.js'></script>
		<script src='/Public/assets/js/is_chzh.js'></script>
		<script type="text/javascript">
			jQuery(function($) {
				$(".select2").select2();

				//学校下拉框
				$('#town_id').change(function(){
					ajaxSelectSchool('school','school_id');
				});
				//年级下拉框
				$('#school_id').change(function(){
					ajaxSelectSchool('grade','school_grade');
				});
				//班级下拉框
				$('#school_grade').change(function(){
					ajaxSelectSchool('class','class_num');
				});
			});
			<?php if(($userinfo['user_kind']) == "109030"): ?>function setInSchool(obj,id,in_school){
				if(!id || in_school == 'undefined')return;
				$.post('<?php echo U('Home/Show/stuInfo');?>',{ac : 'chooseInSchool',id : id, in_school : in_school},function(result){
					if(result.errno != 0){
						layer.alert(result.errtitle,{icon : 2});
						return;
					}
					layer.alert(result.errtitle,{icon : 1});
				});
			}<?php endif; ?>
		</script>

</body>
</html>