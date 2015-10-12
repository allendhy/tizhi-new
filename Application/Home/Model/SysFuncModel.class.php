<?php
namespace Home\Model;
use Think\Model;
class SysFuncModel extends Model {
    public function getFuncByRole($user_role){
    	$userFunc = array();

 		$par_func = $this->field('func_id,func_name,url,func_order')->where('func_level = 1')->order('func_order')->select();

 		foreach($par_func as $row){
 			$userFunc[$row['func_id']]['func_id'] = $row['func_id'];
 			$userFunc[$row['func_id']]['func_name'] = $row['func_name'];
 			$userFunc[$row['func_id']]['url'] = $row['url'];
 			$userFunc[$row['func_id']]['nav_list'] = $this->alias('su')->field('su.func_id,su.func_name,su.url,su.func_order')->join('LEFT JOIN role_func rf ON rf.func_id = su.func_id')->where('rf.role_id = %d AND su.par_func_id = %d',array($user_role,$row['func_id']))->order('func_order')->select();
 		}

 		return $userFunc;
    }
    //根据用户角色获取允许操作的action
    public function getActionList($user_role){
    	$actionList = array();
    	$actions = $this->alias('su')->field('su.url')->join('LEFT JOIN role_func rf ON rf.func_id = su.func_id')->where('rf.role_id = %d',$user_role)->select();
    	foreach($actions as $row){
    		$actionList[] = $row['url'];
    	}
    	return $actionList;
    }
}