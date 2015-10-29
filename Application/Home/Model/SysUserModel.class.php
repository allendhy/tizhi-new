<?php
namespace Home\Model;
use Think\Model;
class SysUserModel extends Model {
	// 登录方法
	public function login($login_name,$login_pwd){
		$where['login_name'] = $login_name;
		$user = $this->field('user_id,login_name,login_pwd,user_kind,org_schoolcode,org_id,input_name,input_sex,input_unit')->where($where)->find();

		if(empty($user))return array('errno'=>1,'errtitle'=>'用户名不存在！');

		if($user['is_del'] == 1)return array('errno'=>2,'errtitle'=>'用户名不可用，请联系客服处理！');

		if($user['login_pwd'] != md5($login_pwd))return array('errno'=>3,'errtitle'=>'密码错误，请重试！如您忘记密码，请联系管理员！');

		return array('errno' => 0, 'data' => $user);
	}
	//修改密码
	public function edit_pwd($old_pwd,$new_pwd,$re_pwd){
		if($old_pwd == '' || $new_pwd == '' || $re_pwd == '')return array('errno'=>1,'errtitle'=>'密码不能为空！');

		$userinfo = session('userinfo');

		if($userinfo['login_pwd'] != md5($new_pwd))return array('errno'=>3,'errtitle'=>'原密码错误！');

		if($new_pwd != $re_pwd)return array('errno'=>2,'errtitle'=>'两次密码输入不一致！');

		if(md5($new_pwd) == $userinfo['login_pwd'])return array('errno'=>3,'errtitle'=>'密码无修改！');

		$return = $this->where('user_id = %d',array($userinfo['user_id']))->setField(array('login_pwd'=>md5($new_pwd)));

		if($return == true){
			return array('errno'=>0,'errtitle'=>'密码修改成功，请重新登录系统！');
		}else{
			return array('errno'=>9,'errtitle'=>'密码修改失败请重试！');
		}

	}
	//
	public function get_user_by_org($town_id,$school_code){
		$where = array();
		if($school_code != 0){
			$where['user_kind'] = 109030;
			$where['org_schoolcode'] = $school_code;
		}elseif($town_id != 0){
			$where['user_kind'] = 109020;
			$where['org_id']	= $town_id;
		}
		return $this->where($where)->find();
	}
}
?>