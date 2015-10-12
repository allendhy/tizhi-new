<?php
namespace Home\Model;
use Think\Model;
class DictFolkModel extends Model {
	public function get_folk_list(){

		$dictList = array();
		$dict= $this->select();
		if($dict){
			foreach($dict as $k=>$v){
				$dictList[$v['folk_id']] = $v['folk_name'];
			}
		}
		return $dictList;
	}
}
?>