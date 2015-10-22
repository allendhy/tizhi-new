<?php
return array(
	//'APP_DEBUG'=>true,
    /* 数据截止上报时间 */
    //2013-12-12 12:12:12
    //'NOT_UPLOAD_TIME' => '2015-12-24 00:00:00',
    /* 数据补录截止时间 */
    //'NOT_UPDATE_HISTORY_TIME' => '2015-04-23 17:00:00',

	/**系统配置*/
	'URL_MODEL'	 =>	1,
	'WEB_SITE'				=>	'国家学生体质健康标准测试数据管理与报送系统（北京市）',


	'SESSION_PREFIX'        =>  'chzh_', // session 前缀
	'ERROR_PAGE'            =>  '/404.html',	// 错误定向页面

    /* 数据库设置 */
	'DB_TYPE'				=>'sqlsrv',					//DB类型
	'DB_HOST'				=>'211.153.66.44',			//主机名
	'DB_NAME'				=>'tizhijiankang',			//数据库名称
	'DB_USER'				=>'sa',						//用户名
	'DB_PWD'				=>'ichzh@123',				//密码
	'DB_PREFIX'				=>'',						//数据表前缀
	
	
    /* 分页设置 */
	'VAR_PAGE'				=> 'p',		
	'PAGE_ROLLPAGE'         => 5,						// 分页显示页数
	'PAGE_LISTROWS'         => 20,						// 分页每页显示记录数

	/** 表单提交unique_sqlt*/
	'UNIQUE_SALT'			=> 'chzh',
);