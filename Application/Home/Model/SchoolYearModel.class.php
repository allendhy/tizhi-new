<?php
namespace Home\Model;
use Think\Model;
class SchoolYearModel extends Model {
	public function this_year(){
		return $this->where('used_year = 1')->find();
	}
	//返回下拉菜单option填充内容
	public function getOptions($school_year){
		$list = $this->order('year_year DESC')->select();
		$option = '';

		foreach($list as $row){
			$selected = $school_year > 0 && $school_year == $row['year_year'] ? 'selected' : ($row['used_year'] == 1 ? 'selecetd' : '');
			$option .= "<option value='".$row['year_year']."' ".$selected.">".$row['year_name'] . '学年'."</option>";
		}
		return $option;
	}
	public function get_info($school_year){
		return $this->where('year_year = %d',$school_year)->find();
	}
}
?>