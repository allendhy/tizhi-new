<?php
namespace Home\Model;
use Think\Model;
class TownModel extends Model {
	public function get_all($town_id = 0){
		if($town_id == 0)
			return $this->select();
		else{
			$where['town_id'] = $town_id;
			return $this->where($where)->select();
		}
	}
	//根据学校代码获取区县信息
	public function get_list_by_schoolcode($school_code,$year_year){
		return $this->field('t.*')->alias('t')->join('LEFT JOIN school s ON s.town_id = t.town_id')->where("s.school_code = '%s' AND s.year_year = %d",array($school_code,$year_year))->select();
	}
}
?>