<?php
namespace Home\Model;
use Think\Model;
class SchoolModel extends Model {
	//根据区县ID
	public function get_list_by_town_year($town_id,$year_year){

		//自2015学年后,school表增加year_year字段,之前的学年该字段为空
		if($year_year < 2015){
			//$where['year_year'] = array('exp','is null');
			$where['year_year'] = 2014;
		}else{
			$where['year_year'] = $year_year;
		}
		$where['town_id'] = $town_id;
		//$where['is_del'] = 0;

		return $this->field('school_code,school_name')->where($where)->group('school_code,school_name')->select();
	}
	//根据学校代码
	public function get_list_by_schoolcode_year($school_code,$year_year,$type="list"){

		if($year_year < 2015){
			$where['year_year'] = 2014;
		}else{
			$where['year_year'] = $year_year;
		}
		$where['school_code'] = $school_code;
		
		//$where['is_del'] = 0;

		if($type == 'one'){
			return $this->field('school_id,school_code,school_name,town_id')->where($where)->find();
		}else{
			return $this->field('school_code,school_name,town_id')->where($where)->group('town_id,school_code,school_name')->select();
		}
		
	}

	//根据区县ID获取学校是否参测
	public function get_list_by_jointest($year_year,$town_id,$school_code='',$show_type='',$ac='show'){
		if($year_year < 2015){
			//$where['year_year'] = array('exp','is null');
			$where['year_year'] = 2014;
		}else{
			$where['year_year'] = $year_year;
		}
		if($town_id != 0)
		$where['t.town_id'] = $town_id;

		if($school_code != 0){
			$where['school_code'] = $school_code;
		}

		$where['is_del'] = 0;

		if($show_type !== ''){
			$where['join_test'] = $show_type;
		}

		//分页数据

		$count = $this->alias('s')->join('LEFT JOIN town t ON t.town_id= s.town_id')->where($where)->count();
		//分页
		$page = new \Think\Page($count,C('PAGE_LISTROWS'));

		$limit = $ac == 'show' ? ($page->firstRow . ',' . $page->listRows) : '';

		$list = $this->alias('s')->field('t.town_name,s.school_code,s.school_name,s.join_test')->join('LEFT JOIN town t ON t.town_id= s.town_id')->where($where)->limit($limit)->select();

		$show = $page->show();

		return array('list'=>$list,'page'=>$show);
	}

	//设置学校是否在学
	public function set_in_school($school_code,$join_test){

		$where['school_code'] = $school_code;

		$where['is_del'] = 0;

		$data['join_test'] = $join_test;

		return $this->where($where)->save($data);
	}
}
?>