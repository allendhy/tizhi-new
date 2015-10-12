<?php
namespace Home\Model;
use Think\Model;
class DictGradeModel extends Model {
	public function get_grade_list(){

		$gradeListTmp = $this->order('grade_id')->select();
		foreach($gradeListTmp as $key=>$val){
			$gradeList[$val['grade_id']] = $val['grade_name'];
		}
		unset($gradeListTmp);
		return $gradeList;
	}
}
?>