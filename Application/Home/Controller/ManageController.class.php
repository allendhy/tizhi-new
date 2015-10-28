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

		$ac = I('ac','showList');

		$show_type = I('show_type','');

		if($ac == 'showList'){

			$this->web_title = '设置学校是否参测';
		   	$this->page_template = 'Manage:setSchoolTest';
		   	$this->assign('show_type',$show_type);

		   	$schools = D('School')->get_list_by_jointest($this->school_year,$this->town_id,$this->school_code,$show_type);

		   	$this->assign('schools',$schools);

		}elseif($ac == 'set' && IS_AJAX){
			$school_code = I('id','');
			$join_test = I('join','');

			if(!$school_code || $join_test == '')$this->ajaxReturn(array('errno'=>1,'errtitle'=>'参数错误,设置失败!'));

			$return = D('School')->set_in_school($school_code,$join_test);

			if($return == false)$this->ajaxReturn(array('errno'=>2,'errtitle'=>'设置失败,请稍后重试'));

			$this->ajaxReturn(array('errno'=>0,'errtitle'=>'操作成功!'));
		}
	}
	//不在测学校名单
	public function noTestSchool(){
		$ac = I('ac','showList');

		$schools = D('School')->get_list_by_jointest($this->school_year,$this->town_id,'0',0);
		$this->assign('schools',$schools);

		$this->web_title = '不在测学校名单';
	   	$this->page_template = 'Manage:noTestSchool';
	}
	//测试项目设定
	public function setItemTest(){
		$ac = I('ac','show');

		if($ac == 'show'){

			$this->web_title = '测试项目设定';

	   		$this->page_template = 'Manage:setItemTest';

	   		$townItems = D('TownItem')->get_list($this->town_id);

	   		$this->assign('townItems',$townItems);

		}elseif($ac == 'set'){

		}

	}
	//用户密码重置
	public function resetUserPwd(){
		$this->web_title = '用户密码重置';
	   	$this->page_template = 'Manage:resetUserPwd';
	}
	//学年设置
	public function setSchoolYear(){
		$ac = I('ac','show');

		if($ac == 'show'){
			$this->web_title = '学年设置';
	   		$this->page_template = 'Manage:setSchoolYear';

	   		$schoolYears = D('SchoolYear')->get_list();

	   		$this->assign('schoolYears',$schoolYears);
	   		// print_r($schoolYears);
		}elseif($ac == 'edit'){

	   		$year = I('year','');

	   		$info = D('SchoolYear')->get_info($year);

	   		if(empty($info))$this->error('参数错误,无法编辑学年!');

	   		$dictList = session('dictList');

	   		$info['state_name'] = $dictList['207'][$info['state']]['dict_name'];

	   		$this->assign('info',$info);

	   		$this->web_title = '学年设置';

	   		$this->page_template = 'Manage:editSchoolYear';

		}elseif($ac == 'editSave'){
			echo "aaaaaaaaaaaaaaaaaaaaa";
		}
	}
}