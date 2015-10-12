<?php
namespace Home\Model;
use Think\Model;
class SysUserModel extends Model {
	// 登录方法
	public function login($login_name,$login_pwd){
		$where['login_name'] = $login_name;
		$user = $this->field('login_name,login_pwd,user_kind,org_schoolcode,org_id,input_name,input_sex,input_unit')->where($where)->find();

		if(empty($user))return array('errno'=>1,'errtitle'=>'用户名不存在！');

		if($user['is_del'] == 1)return array('errno'=>2,'errtitle'=>'用户名不可用，请联系客服处理！');

		if($user['login_pwd'] != md5($login_pwd))return array('errno'=>3,'errtitle'=>'密码错误，请重试！如您忘记密码，请联系管理员！');

		return array('errno' => 0, 'data' => $user);
	}
}
?>