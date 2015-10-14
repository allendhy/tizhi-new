<?php
namespace Home\Controller;
use Think\Controller;
class ExportController extends PublicController {
	public function _initialize(){
		parent::_initialize();

		//各页面下拉选项列表
		$school_year_options = D('SchoolYear')->getOptions($this->school_year);
		$town_id_options = get_town_options($this->town_id);
		$school_code_options = get_school_options($this->school_year,$this->town_id,$this->school_code);

		$this->assign('school_year_options',$school_year_options);
		$this->assign('town_id_options',$town_id_options);
		$this->assign('school_code_options',$school_code_options);

		//判断是否需要为模版年级和班级下拉框赋值
		if(in_array(ACTION_NAME,array('stuInfo','printRegister','upNum','phydata')) && !IS_AJAX){
			$school_grade_options = get_grade_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade);
			$class_num_options = get_class_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);
			$this->assign('school_grade_options',$school_grade_options);
			$this->assign('class_num_options',$class_num_options);
		}
	}

	//导出学校上传情况
	public function schoolUpStatus(){
		$this->web_title = '导出学校上传情况';
	   	$this->page_template = 'Export:schoolUpStatus';
	}
}