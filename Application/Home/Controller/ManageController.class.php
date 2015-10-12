<?php
namespace Home\Controller;
use Think\Controller;
class ManageController extends PublicController {
	public function _initialize(){
		parent::_initialize();
	}

	public function editPwd(){
		if(IS_POST && I('new_pwd') != ''){

			$old_pwd = I('old_pwd','');
			$new_pwd = I('new_pwd','');
			$re_pwd = I('re_pwd','');

			$return = D('SysUser')->edit_pwd($old_pwd,$new_pwd,$re_pwd);
			//echo M()->getlastsql();
			$this->ajaxReturn($return);
			exit();
		}else{
			$this->web_title = '修改密码';
	   		$this->page_template = 'Manage:editPwd';
	   }
	}
}