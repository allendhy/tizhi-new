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
		$ac = I('ac','');

		if($ac == 'show'){

			$user = D('SysUser')->get_user_by_org($this->town_id,$this->school_code);
			$this->assign('user',$user);
			$this->web_title = '用户密码重置';
	   		$this->page_template = 'Manage:resetUserPwd';
		}elseif($ac == 'edit' && IS_POST){
			$user_id = I('uid',0);
			$new_pwd = I('new_pwd','');
			$user = D('SysUser')->where('user_id = %d',$user_id)->find();
			if(empty($user))$this->error('没有该用户!');
			if($new_pwd == '')$this->error('新密码不能为空!');
			if(md5($new_pwd) == $user['login_pwd'])$this->error('密码未改变!');

			$return = D('SysUser')->where('user_id = %d',$user_id)->setField('login_pwd',md5($new_pwd));
			if($return == true) $this->success('密码重置成功!');
			else $this->error('密码重置失败!');
		}else{
			$this->web_title = '用户密码重置';
	   		$this->page_template = 'Manage:resetUserPwd';
		}
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

		}elseif($ac == 'editSave' && IS_AJAX){

	   		$year = I('year','');

	   		$info = D('SchoolYear')->get_info($year);

	   		if(empty($info))$this->error('参数错误,无法编辑学年!');

			if($info['state'] == 207030)$this->ajaxReturn(array('errno'=>3,'errtitle'=>'您设置的学年已经结束,不能再次设置!'));

			$data['used_year'] = I('used_year','');

			$data['not_upload_time'] = I('datepicker','') .' '. I('timepicker','');

			$is_date=strtotime($data['not_upload_time'])?strtotime($data['not_upload_time']):false;
			// 日期格式
			if(!$is_date)$this->ajaxReturn(array('errno'=>'1','errtitle'=>'日期格式错误,请重新设置日期!'));
			//日期设置
			if(strtotime($data['not_upload_time']) < strtotime(date('Y-m-d')))$this->ajaxReturn(array('errno'=>2,'errtitle'=>'设置的封库日期不能小于今天!'));

			D('SchoolYear')->startTrans();

			if($info['used_year'] != $data['used_year'] && $data['used_year'] == 1 && (time() < strtotime($info['year_year']+1 . '-09-01') && time() > strtotime($info['year_year'] . '-08-31 23:59:59'))){
				$data['used_year'] = 1;
				D('SchoolYear')->where('used_year = 1')->setField('used_year',1);
			}else{
				if($data['used_year'] == 1){
					D('SchoolYear')->rollback();
					$this->ajaxReturn(array('errno'=>5,'errtitle'=>"您不能设置{$info['year_name']}为当前学年!"));
				}
			}

			$return = D('SchoolYear')->where('year_year = %d',$year)->save($data);

			if($return == false){
				D('SchoolYear')->rollback();
				$this->ajaxReturn(array('errno'=>9,'errtitle'=>'学年设置失败!请稍候重试!'));
			}
			D('SchoolYear')->commit();
			$this->ajaxReturn(array('errno'=>0,'errtitle'=>'设置成功!'));

		}
	}
}