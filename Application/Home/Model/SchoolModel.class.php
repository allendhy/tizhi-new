<?php
namespace Home\Model;
use Think\Model;
class SchoolModel extends Model {
	//根据区县ID
	public function get_list_by_town_year($town_id,$year_year){
		return $this->field('school_id,school_code,school_name')->where('town_id = %d AND year_year = %d',array($town_id,$year_year))->select();
	}
	//根据学校代码
	public function get_list_by_schoolcode_year($school_code,$year_year,$type="list"){
		if($type == 'one'){
			return $this->field('school_id,school_code,school_name,town_id')->where("school_code = '%s' AND year_year = %d",array($school_code,$year_year))->find();
		}else{
			return $this->field('school_id,school_code,school_name,town_id')->where("school_code = '%s' AND year_year = %d",array($school_code,$year_year))->select();
		}
		
	}
}
?>