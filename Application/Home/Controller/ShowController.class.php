<?php
namespace Home\Controller;
use Think\Controller;
class ShowController extends PublicController {
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
		if(in_array(ACTION_NAME,array('stuInfo','printRegister','upNum','phydata','phydataRanking')) && !IS_AJAX){
			$school_grade_options = get_grade_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade);
			$class_num_options = get_class_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);
			
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

		$stuinfos = D('StudentScore')->get_stuinfos($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);

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
				$options = get_school_options($this->school_year,$this->town_id,$this->school_code);
			break;
			case 'grade':
				$options = get_grade_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade);
			break;
			case 'class':
				$options = get_class_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);
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
	public function printRegister(){
		$this->web_title = '登记卡打印';
		$this->page_template = "Show:printRegister";
	}
	//查看受检未检人数
	public function upNum(){
		$this->web_title = '查看受检未检人数';
		$this->page_template = "Show:upNum";
	}
	//查看学生体质成绩
	public function phydata(){
		$ac = I('ac','');

		switch($ac){	
			case 'showPhyInfo':
				$this->showPhyInfo();
			break;
			case 'downPhyInfo':
				$this->showPhyInfo('down');
			break;
			default:
				$this->web_title = '查看学生体质成绩';
				$this->page_template = "Show:phydata";
			break;
		}

	}
	//测试总成绩排名
	public function phydataRanking(){
		$ac = I('ac','');
		switch($ac){	
			case 'showPhyInfo':
				$this->showPhyInfo('rank');
			break;
			default:
				$this->web_title = '查看学生体质成绩';
				$this->page_template = "Show:phydata";
			break;
		}
	}

	//查看学生体质成绩
	private function showPhyInfo($dtype='list'){
		if($this->town_id == 0)$this->error('请选择区县！');

		$order = '';
		if($dtype == 'rank')$order = 'total_score DESC';

		$phyinfos = D('StudentScore')->get_phyinfos($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num,'school_code','show',$order);

		$gradeListCache = session('gradeList');
		$folkListCache = session('folkList');
		$dictListCache = session('dictList');

		$p = I('p',1);

		foreach($phyinfos['list'] as $key=>$row){

			$phyinfos['list'][$key]['rank'] = (intval($p) - 1) * C('PAGE_LISTROWS') + $row['row_number'];
			//print_r($phyinfos['list'][$key]);exit();
			$phyinfos['list'][$key]['grade_name'] = $gradeListCache[$row['school_grade']];
			//$stuinfos['list'][$key]['folk'] = $folkListCache[$row['folk']];
			if($row['is_avoid'] == '1'){
				$phyinfos['list'][$key]['score_level'] = '免体';
				$phyinfos['list'][$key]['score_level_ori'] = '免体';
			}else{
				$phyinfos['list'][$key]['score_level'] = $dictListCache['203'][$row['score_level']]['dict_name'];
				$phyinfos['list'][$key]['score_level_ori'] = $dictListCache['203'][$row['score_level_ori']]['dict_name'];
			}
		}
		$this->assign('dtype',$dtype);
		$this->assign('phyinfos',$phyinfos);

		$this->web_title = '查看学生基础数据';
	   	$this->page_template = 'Show:phydata';
	}
	//查看体质上传情况
	public function phyUpStatus(){
		$this->web_title = '查看学生体质上传情况';
		$this->page_template = "Show:phyUpStatus";
	}
	//查看历史修改数据
	public function historyUpStatus(){
		$this->web_title = '查看历史修改数据';
		$this->page_template = "Show:historyUpStatus";
	}
	//历史数据查询--上传记录
	public function historyPhyData(){
		$this->web_title = '历史数据查询';
		$this->page_template = "Show:historyPhyData";
	}
	//学生身高标准体重统计表
	public function weightStat(){
		$this->web_title = '学生身高标准体重统计表';
		$this->page_template = "Show:weightStat";	
	}
	//总体成绩统计表
	public function stat(){
		$this->web_title = '总体成绩统计表';
		$this->page_template = "Show:stat";	
	}
	//区县成绩统计表
	public function townStat(){
		$this->web_title = '区县成绩统计表';
		$this->page_template = "Show:townStat";	
	}
	//审核学校上报情况
	public function raterUpStatus(){
		$this->web_title = '审核学校上报情况';
		$this->page_template = "Show:raterUpStatus";	
	}
	//查看区县上报情况
	public function townUpStatus(){
		$this->web_title = '查看区县上报情况';
		$this->page_template = "Show:townUpStatus";	
	}
	//分城郊区查看成绩统计
	public function suburbStat(){
		$this->web_title = '分城郊区查看成绩统计';
		$this->page_template = "Show:suburbStat";	
	}
}
?>