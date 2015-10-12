<?php
namespace Home\Model;
use Think\Model;
class SysDictModel extends Model {
	//实例化sys_dict
	function get_sys_dict(){
		$dictList = array();
		$dict= $this->where('dict_type=0')->select();
		if($dict){
			foreach($dict as $k=>$v){
				$dict_son = $this->where('dict_type='.$v['dict_id'])->select();
				$dictList[$v['dict_id']] = $v;
				foreach($dict_son  as $k2=>$v2){
					$dictList[$v['dict_id']][$v2['dict_id']] = $v2;
				}
			}
		}
		return $dictList;
	}
	
	//根据dict_id获取dict_name
	public function get_dict_name($dict_id){
		return $this->where('dict_id = %d',$dict_id)->getField('dict_name');
	}
}
?>