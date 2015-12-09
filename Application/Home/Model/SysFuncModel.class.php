<?php
namespace Home\Model;
use Think\Model;
class SysFuncModel extends Model {
    public function getFuncByRole($user_role,$user_id = 0){
    	$userFunc = array();

 		$par_func = $this->field('func_id,func_name,url,func_order')->where('func_level = 1')->order('func_order')->select();

        //if($user_role == 109020 && $user_id = 7)
        $user_func_count = M('UserFunc')->where('user_id = %d',$user_id)->count();

        if($user_func_count > 0){
            $joinTable = 'user_func';
            $where['rf.user_id'] = $user_id;
        }else {
            $joinTable = 'role_func';
            $where['rf.role_id'] = $user_role;
        }
        foreach($par_func as $row){
 			$userFunc[$row['func_id']]['func_id'] = $row['func_id'];
 			$userFunc[$row['func_id']]['func_name'] = $row['func_name'];
 			$userFunc[$row['func_id']]['url'] = $row['url'];

            $where['su.par_func_id'] = $row['func_id'];
 			$userFunc[$row['func_id']]['nav_list'] = $this->alias('su')->field('su.func_id,su.func_name,su.url,su.func_order')->join('LEFT JOIN '.$joinTable.' rf ON rf.func_id = su.func_id')->where($where)->order('func_order')->select();
 		}

 		return $userFunc;
    }
    //根据用户角色获取允许操作的action
    public function getActionList($user_role,$user_id = 0){
    	$actionList = array();

        $user_func_count = M('UserFunc')->where('user_id = %d',$user_id)->count();

        if($user_func_count > 0){
            $joinTable = 'user_func';
            $where['rf.user_id'] = $user_id;
        }else{
            $joinTable = 'role_func';
            $where['rf.role_id'] = $user_role;
        }

    	$actions = $this->alias('su')->field('su.url')->join('LEFT JOIN '. $joinTable .' rf ON rf.func_id = su.func_id')->where($where)->select();
    	foreach($actions as $row){
    		$actionList[] = $row['url'];
    	}
    	return $actionList;
    }
}