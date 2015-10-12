<?php
namespace Home\Model;
use Think\Model;
class SchoolStatusModel extends Model {
	//获取一个学校的人数和状态信息
	public function get_status_info_one($school_year,$school_code){
		return $this->alias('ss')->field('s.school_name,s.school_code,ss.*')->join('LEFT JOIN school s ON s.school_id = ss.school_id')->where('s.year_year = %d AND s.school_code = \'%s\'  AND ss.school_id = s.school_id',array($school_year,$school_code))->find();
	}
}
?>