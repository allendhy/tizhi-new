<?php
namespace Home\Controller;
use Think\Controller;
class UpController extends PublicController {
	public function _initialize(){
		parent::_initialize();

		//各页面下拉选项列表
		$school_year_options = D('SchoolYear')->getOptions($this->school_year);
		$town_id_options = get_town_options($this->town_id);
		$school_id_options = get_school_options($this->school_year,$this->town_id,$this->school_id);
		
		$this->assign('school_year_options',$school_year_options);
		$this->assign('town_id_options',$town_id_options);
		$this->assign('school_id_options',$school_id_options);
	}

	//上传体质信息
	public function phydata(){
		$this->web_title = '上传学生体质信息';
        $this->page_template = 'Up:phydata';
	}
}