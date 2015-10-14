<<<<<<< HEAD
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
							<div class="row">
									<form action="" method="post" id="editForm">
									<div class="col-xs-12">
										<div class="table-responsive">

											<table id="sample-table-1" class="table table-striped table-bordered table-hover">
												<tbody>
													<tr><td class="width-30">登录名</td><td><div class="col-sm-9"><?php echo ($userinfo["login_name"]); ?></div></td></tr>
													<tr><td>旧密码</td><td><div class="col-sm-9">
											<input type="password" id="old_pwd" name="old_pwd" check="^\S{6,20}$" warning="请输入原密码，6-20个字符长度" maxlength="20"/>
										</div></td></tr>
													<tr><td>新密码</td><td><div class="col-sm-9">
											<input type="password" id="new_pwd" name="new_pwd" check="^\S{6,20}$" warning="请输入新密码，6-20个字符长度"  maxlength="16" />
										</div></td></tr>
													<tr><td>确认新密码</td><td><div class="col-sm-9">
											<input type="password" id="re_pwd" name="re_pwd"  check="^\S{6,20}$" warning="请再次输入密码，6-20个字符长度"  maxlength="16"/>
										</div></td></tr>
												</tbody>
											</table>
										</div><!-- /.table-responsive -->
									</div><!-- /span -->
									<div class="form-group">
										<label class="col-sm-3 control-label no-padding-right" for="form-field-tags"></label>

										<div class="col-sm-9">
											<button class="btn btn-info" type="button" id="submitBtn">
												<i class="icon-ok bigger-110"></i>
												提交
											</button>

											&nbsp; &nbsp; &nbsp;
											<button class="btn" type="reset" id="resetBtn">
												<i class="icon-undo bigger-110"></i>
												重置
											</button>
										</div>
									</div>
									</form>
									<div class="hr hr-24"></div>
							</div><!-- /row -->

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
				$('#submitBtn').click(function (){
					//checkform();return;
					$('#editForm').ajaxSubmit({beforeSubmit:checkform,success:function(data){
						if(data.errno != 0){
							layer.alert(data.errtitle,{icon:0});
							return;
						}
						layer.alert(data.errtitle,{icon:1},function(){
							window.location.href = '<?php echo U('Home/Index/logout');?>';
						});
					},error:function(XMLResponse){
						layer.alert(XMLResponse.responseText,{icon:0});
					},dataType:'json'});
				});
			});

			function checkform(){
				var result = allCheckForm(document.forms[0]);
				return result;
			}

		</script>

</body>
=======
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
							<div class="row">
									<form action="" method="post" id="editForm">
									<div class="col-xs-12">
										<div class="table-responsive">

											<table id="sample-table-1" class="table table-striped table-bordered table-hover">
												<tbody>
													<tr><td class="width-30">登录名</td><td><div class="col-sm-9"><?php echo ($userinfo["login_name"]); ?></div></td></tr>
													<tr><td>旧密码</td><td><div class="col-sm-9">
											<input type="password" id="old_pwd" name="old_pwd" check="^\S{6,20}$" warning="请输入原密码，6-20个字符长度" maxlength="20"/>
										</div></td></tr>
													<tr><td>新密码</td><td><div class="col-sm-9">
											<input type="password" id="new_pwd" name="new_pwd" check="^\S{6,20}$" warning="请输入新密码，6-20个字符长度"  maxlength="16" />
										</div></td></tr>
													<tr><td>确认新密码</td><td><div class="col-sm-9">
											<input type="password" id="re_pwd" name="re_pwd"  check="^\S{6,20}$" warning="请再次输入密码，6-20个字符长度"  maxlength="16"/>
										</div></td></tr>
												</tbody>
											</table>
										</div><!-- /.table-responsive -->
									</div><!-- /span -->
									<div class="form-group">
										<label class="col-sm-3 control-label no-padding-right" for="form-field-tags"></label>

										<div class="col-sm-9">
											<button class="btn btn-info" type="button" id="submitBtn">
												<i class="icon-ok bigger-110"></i>
												提交
											</button>

											&nbsp; &nbsp; &nbsp;
											<button class="btn" type="reset" id="resetBtn">
												<i class="icon-undo bigger-110"></i>
												重置
											</button>
										</div>
									</div>
									</form>
									<div class="hr hr-24"></div>
							</div><!-- /row -->

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
				$('#submitBtn').click(function (){
					//checkform();return;
					$('#editForm').ajaxSubmit({beforeSubmit:checkform,success:function(data){
						if(data.errno != 0){
							layer.alert(data.errtitle,{icon:0});
							return;
						}
						layer.alert(data.errtitle,{icon:1},function(){
							window.location.href = '<?php echo U('Home/Index/logout');?>';
						});
					},error:function(XMLResponse){
						layer.alert(XMLResponse.responseText,{icon:0});
					},dataType:'json'});
				});
			});

			function checkform(){
				var result = allCheckForm(document.forms[0]);
				return result;
			}

		</script>

</body>
>>>>>>> cbda60dd068c1f6862e2f9ac1dd5528340857c06
</html>