<?php
namespace Home\Controller;
use Think\Controller;
class ShowController extends PublicController {
	public function _initialize(){
		parent::_initialize();

		//各页面下拉选项列表
		$school_year_options = D('SchoolYear')->getOptions($this->school_year);
		$town_id_options = get_town_options($this->town_id);
		$school_id_options = get_school_options($this->school_year,$this->town_id,$this->school_id);
		
		$this->assign('school_year_options',$school_year_options);
		$this->assign('town_id_options',$town_id_options);
		$this->assign('school_id_options',$school_id_options);

		//判断是否需要为模版年级和班级下拉框赋值
		if(in_array(ACTION_NAME,array('stuInfo')) && !IS_AJAX){

			$school_grade_options = get_grade_options($this->school_year,$this->town_id,$this->school_id,$this->school_grade);
			$class_num_options = get_class_options($this->school_year,$this->town_id,$this->school_id,$this->school_grade,$this->class_num);
			$this->assign('school_grade_options',$school_grade_options);
			$this->assign('class_num_options',$class_num_options);
		}
	}
	//查看学生基础数据
	public function stuInfo(){
		$ac = I('ac','');

		switch($ac){
			case 'showStuInfo':
				$this->showStuInfo();
			break;
			case 'ajaxSelect':
				$this->ajaxSelect();
			break;
			case 'chooseInSchool':
				$this->chooseInSchool();
			break;
			default:
				$this->web_title = '查看学生基础数据';
        		$this->page_template = 'Show:stuInfo';
			break;
		}
	}
	//查看学生基础数据
	private function showStuInfo(){
		if($this->town_id == 0)$this->error('请选择区县！');

		$stuinfos = D('StudentScore')->get_stuinfos($this->school_year,$this->town_id,$this->school_id,$this->school_grade,$this->class_num);

		$gradeListCache = session('gradeList');
		$folkListCache = session('folkList');
		foreach($stuinfos['list'] as $key=>$row){
			$stuinfos['list'][$key]['grade_name'] = $gradeListCache[$row['school_grade']];
			$stuinfos['list'][$key]['folk'] = $folkListCache[$row['folk']];
		}
		$this->assign('stuinfos',$stuinfos);

		$this->web_title = '查看学生基础数据';
	   	$this->page_template = 'Show:stuInfo';

	}
	//ajax返回下拉框options信息
	private function ajaxSelect(){
		$selectType = I('select_type','');
		if($selectType == '' || !in_array($selectType,array('school','grade','class'))) $this->ajaxReturn(array('errno'=>1,'errtitle'=>'参数错误！'));

		$options = '';
		switch($selectType){
			case 'school':
				$options = get_school_options($this->school_year,$this->town_id,$this->school_id);
			break;
			case 'grade':
				$options = get_grade_options($this->school_year,$this->town_id,$this->school_id,$this->school_grade);
			break;
			case 'class':
				$options = get_class_options($this->school_year,$this->town_id,$this->school_id,$this->school_grade,$this->class_num);
			break;
		}
		
		$this->ajaxReturn(array('errno'=>0,'optionstr'=>$options));
	}
	//ajax 设置是否在学
	private function chooseInSchool(){
		$year_score_id = I('id',0);
		$in_school = I('in_school','');

		$return = D('StudentScore')->set_in_school($year_score_id,$in_school);
		$this->ajaxReturn($return);
	}

	//打印登记表
	public function printRegister(){}
}
?>