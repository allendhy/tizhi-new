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
		if(IS_AJAX && $_FILES['file_data']){
			//由于文件上传插件暂时无法解决同步提交学校ID，如果是区县或者市级进行上传，则在选择学校时异步将学校ID保存到SESSION中
			//提交上传文件后,则根据相应权限进行判断学校ID区县ID等信息
			//然后将上传的文件进度返回到浏览器
			//并且将校验进度及校验结果返回到浏览器

			//print_r($_REQUEST);
			//print_r($_FILES);
			sleep(10);
			//exit();
		}

		$this->web_title = '上传学生体质信息';
        $this->page_template = 'Up:phydata';
	}
}