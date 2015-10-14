<?php
namespace Home\Controller;
use Think\Controller;
class ManageController extends PublicController {
	public function _initialize(){
		parent::_initialize();
		//各页面下拉选项列表
		$school_year_options = D('SchoolYear')->getOptions($this->school_year);
		$town_id_options = get_town_options($this->town_id);
		$school_code_options = get_school_options($this->school_year,$this->town_id,$this->school_code);

		$this->assign('school_year_options',$school_year_options);
		$this->assign('town_id_options',$town_id_options);
		$this->assign('school_code_options',$school_code_options);
	}
	//用户修改密码
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
	//设置学校是否参测
	public function setSchoolTest(){
		$this->web_title = '设置学校是否参测';
	   	$this->page_template = 'Manage:setSchoolTest';
	}
	//不在测学校名单
	public function noTestSchool(){
		$this->web_title = '不在测学校名单';
	   	$this->page_template = 'Manage:noTestSchool';
	}
	//测试项目设定
	public function setItemTest(){
		$this->web_title = '测试项目设定';
	   	$this->page_template = 'Manage:setItemTest';
	}
	//用户密码重置
	public function resetUserPwd(){
		$this->web_title = '用户密码重置';
	   	$this->page_template = 'Manage:resetUserPwd';
	}
	//学年设置
	public function setSchoolYear(){
		$this->web_title = '学年设置';
	   	$this->page_template = 'Manage:setSchoolYear';
	}
}