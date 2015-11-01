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
		$ac = I('ac','');

		if($ac == '下载'){
			if($this->school_code == 0)$this->error('请选择学校!');
			$list = D('StudentScore')->get_up_num_infos($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);

			if(empty($list)) $this->error('数据有误!');

			$this->downUpNumInfos($list);
		}else{
			$this->web_title = '查看受检未检人数';
			$this->page_template = "Show:upNum";

			if($this->school_grade != 0){
				$data = D('StudentScore')->get_up_num($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);
			}else{
				//school_status
				$data = D('SchoolStatus')->get_up_num($this->school_year,$this->town_id,$this->school_code);
			}

			//应受检人数
			$data['s_ysj_cnt'] = $data['s_cnt'] - $data['s_notinschool_cnt'];
			//已受检人数
			$data['s_sj_cnt'] = $data['s_phy_cnt'] - $data['s_phynotinschool_cnt'];
			//未受检人数
			$data['s_wsj_cnt'] = $data['s_ysj_cnt'] - $data['s_sj_cnt'];
			//上传率
			$data['s_scl'] = round($data['s_sj_cnt']/$data['s_ysj_cnt'],4) * 100 . '%';

			$this->assign('sch_status',$data);
		}
	}
	//下载没有受检的人信息
	private function downUpNumInfos($list){

		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.IOFactory");

		$file = './Public/template/stuDataTmp.xls';
		$objReader = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load($file);
		$objActSheet = $objPHPExcel->getActiveSheet();
		//缓存
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
										 ->setLastModifiedBy("Maarten Balliauw")
										 ->setTitle("physicalHealth")
										 ->setSubject("physicalHealth")
										 ->setDescription("physicalHealth")
										 ->setKeywords("physicalHealth")
										 ->setCategory("physicalHealth");

		$rowNum = 2;

		foreach($list as $k=>$row){
			$sex = $row['sex']=='106020'?'2':'1';
			$birthday = date('Y-m-d',strtotime($row['birthday']));
			if(is_object($row['birthday'])){
				$birthdayObj = object2array($row['birthday']);
				$birthday = date('Y-m-d',strtotime($birthdayObj['date']));
			}

			if($row['country_education_id'] == '')$row['country_education_id'] = $row['education_id'];
					
			$objActSheet->setCellValueExplicit('A'.$rowNum, $row['school_grade'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('A'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('B'.$rowNum, $row['class_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('B'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('C'.$rowNum, $row['class_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('D'.$rowNum, $row['country_education_id'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('D'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			$objActSheet->setCellValueExplicit('E'.$rowNum, $row['folk_code'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('E'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('F'.$rowNum, $row['name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('F'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('G'.$rowNum, $sex,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('G'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('H'.$rowNum, $birthday,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('H'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			$objActSheet->setCellValueExplicit('I'.$rowNum, $row['address'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('I'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if($row['is_avoid'] == '是'){
				$row['body_height'] = '免体';
			}else{
				if(floatval($row['body_height'])<=0){$row['body_height']='';}
			}
			$objActSheet->setCellValueExplicit('J'.$rowNum, $row['body_height'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('J'.$rowNum)->getNumberFormat()->setFormatCode("@");
			if(floatval($row['body_weight'])<=0){$row['body_weight']='';}
			$objActSheet->setCellValueExplicit('K'.$rowNum, $row['body_weight'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('K'.$rowNum)->getNumberFormat()->setFormatCode("@");
			if(floatval($row['vital_capacity'])<=0){$row['vital_capacity']='';}
			$objActSheet->setCellValueExplicit('L'.$rowNum, $row['vital_capacity'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('L'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			$objActSheet->setCellValueExplicit('M'.$rowNum, $row['wsm'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('M'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['ldty'])<=0 || $row['school_grade'] < 21){$row['ldty']='';}
			$objActSheet->setCellValueExplicit('N'.$rowNum, $row['ldty'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('N'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			$objActSheet->setCellValueExplicit('O'.$rowNum, $row['zwtqq'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('O'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['wsmwfp'])<=0 || $row['school_grade'] < 15 || $row['school_grade'] > 16){$row['wsmwfp']='';}
			$objActSheet->setCellValueExplicit('P'.$rowNum, $row['wsmwfp'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('P'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['ywqz_ytxs']) >= 0 && $row['school_grade'] >=13 && $row['school_grade'] <= 16){$yfzywqz=$row['ywqz_ytxs'];}else{$yfzywqz = '';}
			$objActSheet->setCellValueExplicit('Q'.$rowNum, $yfzywqz,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('Q'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['yfzts'])<=0 || $row['school_grade'] > 16){$row['yfzts']='';}
			$objActSheet->setCellValueExplicit('R'.$rowNum, $row['yfzts'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('R'.$rowNum)->getNumberFormat()->setFormatCode("@");

			if(floatval($row['bbm_yqm']) > 0 && $row['sex'] == 106020 && $row['school_grade'] > 16 ){$bbm=$row['bbm_yqm'];}else{$bbm='';}
			$objActSheet->setCellValueExplicit('S'.$rowNum, $bbm,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('S'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['bbm_yqm']) > 0 && $row['sex'] == 106010 && $row['school_grade'] > 16 ){$yqm=$row['bbm_yqm'];}else{$yqm='';}
			$objActSheet->setCellValueExplicit('T'.$rowNum, $yqm,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('T'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['ywqz_ytxs']) >= 0 && $row['sex'] == 106020 && $row['school_grade'] > 16 ){$ywqz=$row['ywqz_ytxs'];}else{$ywqz='';}
			$objActSheet->setCellValueExplicit('U'.$rowNum, $ywqz,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('U'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			if(floatval($row['ywqz_ytxs']) >= 0 && $row['sex'] == 106010 && $row['school_grade'] > 16 ){$ytxs=$row['ywqz_ytxs'];}else{$ytxs='';}
			$objActSheet->setCellValueExplicit('V'.$rowNum, $ytxs,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('V'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$rowNum++;
		}

		$fileName = '学生体质数据上传情况';
		$fileName = iconv('utf-8','gb2312',$fileName);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('student');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		// Redirect output to a client’s web browser (Excel5)

		//ob_end_clean ();
		//输出到浏览器
		header('Pragma:public');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Type:application/x-msexecl;name='.$fileName.'.xlsx');
		header('Content-Disposition:inline;filename='.$fileName.'.xlsx');
			
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '.xlsx"');
		} else {
			header('Content-Disposition: attachment; filename="' . $fileName . '.xlsx"');
		}

		// Redirect output to a client’s web browser (Excel5)
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		//PHPExcel::Destroy();
		exit();
	}
	//查看学生体质成绩
	public function phydata(){
		$ac = I('ac','');

		switch($ac){	
			case '查看':
				$this->showPhyInfo('list');
			break;
			case '下载':
				$this->showPhyInfo('down');
			break;
			case 'showDetail':
				$this->showPhyDetail();
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
		$this->assign('dtype','rank');
		switch($ac){	
			case '查看':
				$this->showPhyInfo('rank');
				$this->assign('dtype','rank');
			break;
			default:
				$this->web_title = '测试总成绩排名';
				$this->page_template = "Show:phydata";
			break;
		}
	}

	//查看学生体质成绩
	private function showPhyInfo($dtype='list'){
		if($this->town_id == 0)$this->error('请选择区县！');

		$order = '';

		if($dtype == 'rank')$order = 'total_score DESC';

		$ac = $dtype == 'down' ? 'down' : 'show';

		$phyinfos = D('StudentScore')->get_phyinfos($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num,'school_code',$ac,$order);

	
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

		if($ac == 'down'){
			if($this->school_code == 0)$this->error('请选择学校!');
			if(empty($phyinfos['list']))$this->error('无数据!');
			$this->downPhyInfo($phyinfos['list']);
		}else{
			$this->assign('dtype',$dtype);
			$this->assign('phyinfos',$phyinfos);

			$this->web_title = '查看学生体质数据';
		   	$this->page_template = 'Show:phydata';
		}
	}
	//下载体质数据
	private function downPhyInfo($list){
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.IOFactory");

		$objPHPExcel = new \PHPExcel();

		$objActSheet = $objPHPExcel->getActiveSheet();
		//缓存
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
										 ->setLastModifiedBy("Maarten Balliauw")
										 ->setTitle("physicalHealth")
										 ->setSubject("physicalHealth")
										 ->setDescription("physicalHealth")
										 ->setKeywords("physicalHealth")
										 ->setCategory("physicalHealth");
		$objActSheet->setCellValueExplicit('A1', '全国学籍号',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('B1', '教育ID',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('C1', '所属区县',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('D1', '姓名',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('E1', '学校名称',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('F1', '年级',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('G1', '班级',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('H1', '性别',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('I1', '综合成绩',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('J1', '综合评定',\PHPExcel_Cell_DataType::TYPE_STRING);

		$objActSheet->setCellValueExplicit('K1', '测试成绩',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('L1', '测试成绩评定',\PHPExcel_Cell_DataType::TYPE_STRING);

		$objActSheet->setCellValueExplicit('M1', '附加分',\PHPExcel_Cell_DataType::TYPE_STRING);

		//$objActSheet->getStyle('A1')->getNumberFormat()->setFormatCode("@");
		$rowNum = 2;
		//$objPHPExcel ->getSheet(0)->getProtection()->setSheet(true);
		foreach($list as $k=>$row){
			$objActSheet->setCellValueExplicit('A'.$rowNum, $row['country_education_id'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('A'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			$objActSheet->setCellValueExplicit('B'.$rowNum, $row['education_id'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('B'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('C'.$rowNum, $row['town_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('D'.$rowNum, $row['name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('D'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('E'.$rowNum, $row['school_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('E'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('F'.$rowNum, $row['grade_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('F'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('G'.$rowNum, $row['class_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('G'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('H'.$rowNum, $row['sex'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('H'.$rowNum)->getNumberFormat()->setFormatCode("@");



			$total_score = round($row['total_score'],0);
			$total_score_ori = round($row['total_score_ori'],0);

			$objActSheet->setCellValueExplicit('I'.$rowNum, $total_score,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('I'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('J'.$rowNum, $row['score_level'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('J'.$rowNum)->getNumberFormat()->setFormatCode("@");


			$objActSheet->setCellValueExplicit('K'.$rowNum, $total_score_ori,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('K'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('L'.$rowNum, $row['score_level_ori'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('L'.$rowNum)->getNumberFormat()->setFormatCode("@");
				//}
					
			$objActSheet->setCellValueExplicit('M'.$rowNum, $row['addach_score'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('M'.$rowNum)->getNumberFormat()->setFormatCode("@");
			$rowNum++;
		}

		$fileName = iconv('utf-8','gb2312',$list[0]['school_name'].$this->school_year.'学年成绩数据');
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('studentData');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		//ob_end_clean ();
		//输出到浏览器
		header('Pragma:public');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Type:application/x-msexecl;name='.$fileName.'.xlsx');
		header('Content-Disposition:inline;filename='.$fileName.'.xlsx');
			
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '.xlsx"');
		} else {
			header('Content-Disposition: attachment; filename="' . $fileName . '.xlsx"');
		}

		// Redirect output to a client’s web browser (Excel5)
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		//PHPExcel::Destroy();
		exit();
	}
	//查看学生体质详情
	private function showPhyDetail(){

		$year_score_id = I('id',0);
		$partition_field = I('par',0);

		$phyinfo = D('StudentScore')->get_info($partition_field,$year_score_id);

		if(empty($phyinfo))$this->error('参数错误!找不到学生!');

		$dictList = session('dictList');

		$phyinfo['score_level'] = $dictList['203'][$phyinfo['score_level']]['dict_name'];

		$phyinfo['score_level_ori'] = $dictList['203'][$phyinfo['score_level_ori']]['dict_name'];

		$this->assign('stuScoreInfo',$phyinfo);

		$stuItemScoreList =  D('ItemScore')->get_info_list($partition_field,$year_score_id);

		if(!$stuItemScoreList){$this->error('没有找到该生体质健康成绩信息~');}

		
		//xt,zs,item
		$xt = array();
		$zs = array();
		$item = array();
		foreach($stuItemScoreList as $k=>$v){
			if(intval($v['item_id'])==0||$v['kind_id']=='')continue;
			//各项目评定
			if($v['score_level']){
				$v['score'] = intval($v['score']);
				$v['score_level'] = substr($v['score_level'],0,3) == '205' ? $dictList['205'][$v['score_level']]['dict_name'] :$dictList['203'][$v['score_level']]['dict_name'];
			}else{
				$v['score'] = '未检查';
			}
			$v['exam_result_display']	 = stripslashes($v['exam_result_display']);
			if(in_array($v['kind_id'],array('jn','xt'))){
				array_push($xt,$v);
			}elseif($v['kind_id']=='zs'){
				array_push($zs,$v);
			}else{
				array_push($item,$v);
			}
		}

		$stuItemScoreList  = array();
		$stuItemScoreList['xt'] = $xt;
		$stuItemScoreList['zs'] = $zs;
		$stuItemScoreList['item'] = $item;

		foreach($stuItemScoreList['item'] as $key=>$val){
			if($val['item_id']=='08'&&intval($val['score'])==0)
				$stuItemScoreList['item'][$key]['score']='';
		}

		$this->assign('stuItemScoreList',$stuItemScoreList);

		$this->school_year = intval(substr($partition_field,6));
		//echo $this->school_year;
		//导入时间
		if($this->school_year >= 2014){
			$import_detail_t = 'import_detail_new';
			
		}else{
			$import_detail_t = 'import_detail';
		}
		

		$import_log = D($import_detail_t)->get_detail_info($partition_field,$phyinfo['import_detail_id']);
		
		if(is_object($import_log['import_time'])){
			$impTimeObj = object2array($import_log['import_time']);
			$import_time = date('Y-m-d H:i:s',strtotime($impTimeObj['date']));
		}else{
			$import_time = date('Y-m-d H:i:s',strtotime($import_log['import_time']));
		}
		$this->assign('import_time',$import_time);

		
		//导入历史
		if(!empty($import_log)){
			if(!intval($import_log['vital_capacity']))$import_log['vital_capacity']='';

			if(is_object($import_log['birthday'])){
				$birthdayObj = object2array($import_log['birthday']);
				$import_log['birthday'] = date('Y-m-d',strtotime($birthdayObj['date']));
			}
			$gradeList = session('gradeList');
			$import_log['grade_num'] = $gradeList[$import_log['grade_num']];
			$this->assign('import_detail',$import_log);
			
			//操作人
			$login_name =  D('SysUser')->where('user_id = '.$import_log['user_id'])->getField('login_name');
			if(!$login_name)$login_name = D('School')->alias('s')->join('LEFT JOIN sys_user u ON u.org_schoolcode = s.school_code')->where('s.school_id = '.$import_log['user_id'])->getField('u.login_name');
			//echo M()->getlastsql();
			$this->assign('login_name',$login_name);
		}

		$this->assign('school_year',$this->school_year);
		$this->web_title = '查看学生体质成绩详情';
		$this->page_template = "Show:phyDetail2014";
		
	}
	//查看体质上传情况
	public function phyUpStatus(){
		$ac = I('ac','list');
		
		if(!in_array($ac,array('list','detail')))$ac = 'list';

		$is_error = I('is_error','');
		$this->assign('is_error',$is_error);
		$this->assign('dictList',session('dictList'));

		if($ac == 'list'){
			$this->page_template = "Show:phyUpStatus";

			$logs = D('ImportLog')->getInfos($this->school_year,$this->town_id,$this->school_code,$is_error);
			$this->assign('logs',$logs);
		}else{

			$import_id = I('id',0);
			$this->page_template = "Show:phyUpStatusDetail";

			$detail_tb = $this->school_year >= 2014 ? 'ImportDetailNew' : 'ImportDetail';
			$details = D($detail_tb)->get_details($this->school_year,$this->town_id,$import_id);

			$this->assign('details',$details);
			$this->assign('gradeList',session('gradeList'));
		}

		$this->web_title = '查看学生体质上传情况';
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
		$ac = I('ac','');
		$show_type = I('show_type','');

		if($ac == 'show'){
			//if($this->town_id == 0)$this->error('请选择区县!');

			if(!in_array($show_type,array('age','sex')))$this->error('请选择查看方式!');

			$data = D('StudentScore')->weight_stat($this->school_year,$this->town_id,$this->school_code,$show_type);

			foreach($data as $row){
				if($row['age']<=7){
					$age = 7;
					$weightData['7']['age'] = '7岁及以下';
				}elseif($row['age']>=22){
					$age = 22;
					$weightData['22']['age'] = '22岁及以上';
				}else{
					$age = $row['age'];
					$weightData[$age]['age'] = $age;
				}


				$weightData[$age][$row['sex']]['sex'] = $row['sex'] == '106010' ? '男' : '女'; 

				if (empty($weightData[$age]['106020'])) {
					$weightData[$age]['106020']['sex'] = '女';
				}

				if (empty($weightData[$age]['106010'])) {
					$weightData[$age]['106010']['sex'] = '男';
				}

				$weightData[$age][$row['sex']]['cnt'] = ($weightData[$age][$row['sex']]['cnt'] + $row['cnt'])>0?($weightData[$age][$row['sex']]['cnt'] + $row['cnt']):0;

				$weightData[$age][$row['sex']]['yybl_cnt'] = ($weightData[$age][$row['sex']]['yybl_cnt'] + $row['yybl_cnt'])>0?($weightData[$age][$row['sex']]['yybl_cnt'] + $row['yybl_cnt']):0;
				$weightData[$age][$row['sex']]['yybl_bfb'] = round($weightData[$age][$row['sex']]['yybl_cnt']/$weightData[$age][$row['sex']]['cnt']*100,2).'%';

				$weightData[$age][$row['sex']]['jdtz_cnt'] = ($weightData[$age][$row['sex']]['jdtz_cnt'] + $row['jdtz_cnt'])>0?($weightData[$age][$row['sex']]['jdtz_cnt'] + $row['jdtz_cnt']):0;
				$weightData[$age][$row['sex']]['jdtz_bfb'] = round($weightData[$age][$row['sex']]['jdtz_cnt']/$weightData[$age][$row['sex']]['cnt']*100,2).'%';

				$weightData[$age][$row['sex']]['zc_cnt'] = ($weightData[$age][$row['sex']]['zc_cnt'] + $row['zc_cnt'])>0?($weightData[$age][$row['sex']]['zc_cnt'] + $row['zc_cnt']):0;
				$weightData[$age][$row['sex']]['zc_bfb'] = round($weightData[$age][$row['sex']]['zc_cnt']/$weightData[$age][$row['sex']]['cnt']*100,2).'%';

				$weightData[$age][$row['sex']]['cz_cnt'] = ($weightData[$age][$row['sex']]['cz_cnt'] + $row['cz_cnt'])>0?($weightData[$age][$row['sex']]['cz_cnt'] + $row['cz_cnt']):0;
				$weightData[$age][$row['sex']]['cz_bfb'] = round($weightData[$age][$row['sex']]['cz_cnt']/$weightData[$age][$row['sex']]['cnt']*100,2).'%';

				$weightData[$age][$row['sex']]['fp_cnt'] = ($weightData[$age][$row['sex']]['fp_cnt'] + $row['fp_cnt'])>0?($weightData[$age][$row['sex']]['fp_cnt'] + $row['fp_cnt']):0;
				$weightData[$age][$row['sex']]['fp_bfb'] = round($weightData[$age][$row['sex']]['fp_cnt']/$weightData[$age][$row['sex']]['cnt']*100,2).'%';

				//合计
				$weightData[$age]['heji']['cnt'] = $weightData[$age]['106010']['cnt'] + $weightData[$age]['106020']['cnt'];
				$weightData[$age]['heji']['yybl_cnt'] = $weightData[$age]['106010']['yybl_cnt'] + $weightData[$age]['106020']['yybl_cnt'];
				$weightData[$age]['heji']['yybl_bfb'] = round($weightData[$age]['heji']['yybl_cnt']/$weightData[$age]['heji']['cnt']*100,2).'%';

				$weightData[$age]['heji']['jdtz_cnt'] = $weightData[$age]['106010']['jdtz_cnt'] + $weightData[$age]['106020']['jdtz_cnt'];
				$weightData[$age]['heji']['jdtz_bfb'] = round($weightData[$age]['heji']['jdtz_cnt']/$weightData[$age]['heji']['cnt']*100,2).'%';

				$weightData[$age]['heji']['zc_cnt'] = $weightData[$age]['106010']['zc_cnt'] + $weightData[$age]['106020']['zc_cnt'];
				$weightData[$age]['heji']['zc_bfb'] = round($weightData[$age]['heji']['zc_cnt']/$weightData[$age]['heji']['cnt']*100,2).'%';

				$weightData[$age]['heji']['cz_cnt'] = $weightData[$age]['106010']['cz_cnt'] + $weightData[$age]['106020']['cz_cnt'];
				$weightData[$age]['heji']['cz_bfb'] = round($weightData[$age]['heji']['cz_cnt']/$weightData[$age]['heji']['cnt']*100,2).'%';

				$weightData[$age]['heji']['fp_cnt'] = $weightData[$age]['106010']['fp_cnt'] + $weightData[$age]['106020']['fp_cnt'];
				$weightData[$age]['heji']['fp_bfb'] = round($weightData[$age]['heji']['fp_cnt']/$weightData[$age]['heji']['cnt']*100,2).'%';
			}
			$this->assign('weightData',$weightData);
		}
		$this->assign('show_type',$show_type);
		$this->web_title = '学生身高标准体重统计表';
		$this->page_template = "Show:weightStat";	
	}
	//总体成绩统计表
	public function stat(){
		$ac = I('ac','');
		$show_type = I('show_type','');
		
		if($ac == 'show'){
			//if($this->town_id == 0)$this->error('请选择区县!');

			if(!in_array($show_type,array('age','sex','level','item')))$this->error('请选择查看方式!');

			$data = D('StudentScore')->stat($this->school_year,$this->town_id,$this->school_code,$show_type);

			foreach($data as $k=>$row){
				if($show_type == 'age' || $show_type == 'sex'){
					if($row['age']<=7){
						$age = 7;
						$statData['7']['age'] = '7岁及以下';
					}elseif($row['age']>=22){
						$age = 22;
						$statData['22']['age'] = '22岁及以上';
					}else{
						$age = $row['age'];
						$statData[$age]['age'] = $age;
					}
					$statData[$age][$row['sex']]['sex'] = $row['sex'] == '106010' ? '男' : '女'; 

					if (empty($statData[$age]['106020'])) {
						$statData[$age]['106020']['sex'] = '女';
					}

					if (empty($statData[$age]['106010'])) {
						$statData[$age]['106010']['sex'] = '男';
					}

					$statData[$age][$row['sex']]['cnt'] = ($statData[$age][$row['sex']]['cnt'] + $row['cnt'])>0?($statData[$age][$row['sex']]['cnt'] + $row['cnt']):0;

					$statData[$age][$row['sex']]['bjg_cnt'] = ($statData[$age][$row['sex']]['bjg_cnt'] + $row['bjg_cnt'])>0?($statData[$age][$row['sex']]['bjg_cnt'] + $row['bjg_cnt']):0;
					$statData[$age][$row['sex']]['bjg_bfb'] = round($statData[$age][$row['sex']]['bjg_cnt']/$statData[$age][$row['sex']]['cnt']*100,2).'%';

					$statData[$age][$row['sex']]['jg_cnt'] = ($statData[$age][$row['sex']]['jg_cnt'] + $row['jg_cnt'])>0?($statData[$age][$row['sex']]['jg_cnt'] + $row['jg_cnt']):0;
					$statData[$age][$row['sex']]['jg_bfb'] = round($statData[$age][$row['sex']]['jg_cnt']/$statData[$age][$row['sex']]['cnt']*100,2).'%';

					$statData[$age][$row['sex']]['lh_cnt'] = ($statData[$age][$row['sex']]['lh_cnt'] + $row['lh_cnt'])>0?($statData[$age][$row['sex']]['lh_cnt'] + $row['lh_cnt']):0;
					$statData[$age][$row['sex']]['lh_bfb'] = round($statData[$age][$row['sex']]['lh_cnt']/$statData[$age][$row['sex']]['cnt']*100,2).'%';

					$statData[$age][$row['sex']]['yx_cnt'] = ($statData[$age][$row['sex']]['yx_cnt'] + $row['yx_cnt'])>0?($statData[$age][$row['sex']]['yx_cnt'] + $row['yx_cnt']):0;
					$statData[$age][$row['sex']]['yx_bfb'] = round($statData[$age][$row['sex']]['yx_cnt']/$statData[$age][$row['sex']]['cnt']*100,2).'%';


						//合计
					$statData[$age]['heji']['cnt'] = $statData[$age]['106010']['cnt'] + $statData[$age]['106020']['cnt'];
					$statData[$age]['heji']['bjg_cnt'] = $statData[$age]['106010']['bjg_cnt'] + $statData[$age]['106020']['bjg_cnt'];
					$statData[$age]['heji']['bjg_bfb'] = round($statData[$age]['heji']['bjg_cnt']/$statData[$age]['heji']['cnt']*100,2).'%';

					$statData[$age]['heji']['jg_cnt'] = $statData[$age]['106010']['jg_cnt'] + $statData[$age]['106020']['jg_cnt'];
					$statData[$age]['heji']['jg_bfb'] = round($statData[$age]['heji']['jg_cnt']/$statData[$age]['heji']['cnt']*100,2).'%';

					$statData[$age]['heji']['lh_cnt'] = $statData[$age]['106010']['lh_cnt'] + $statData[$age]['106020']['lh_cnt'];
					$statData[$age]['heji']['lh_bfb'] = round($statData[$age]['heji']['lh_cnt']/$statData[$age]['heji']['cnt']*100,2).'%';

					$statData[$age]['heji']['yx_cnt'] = $statData[$age]['106010']['yx_cnt'] + $statData[$age]['106020']['yx_cnt'];
					$statData[$age]['heji']['yx_bfb'] = round($statData[$age]['heji']['yx_cnt']/$statData[$age]['heji']['cnt']*100,2).'%';
				}else{

					if($show_type == 'item' && $row['itemname'] == 'other')continue;
					
					$row['bjg_bfb'] = round($row['bjg_cnt']/$row['cnt']*100,2).'%';

					$row['jg_bfb'] = round($row['jg_cnt']/$row['cnt']*100,2).'%';

					$row['lh_bfb'] = round($row['lh_cnt']/$row['cnt']*100,2).'%';

					$row['yx_bfb'] = round($row['yx_cnt']/$row['cnt']*100,2).'%';



					$statHeji['cnt'] = $statHeji['cnt'] + $row['cnt'];

					$statHeji['bjg_cnt'] = $statHeji['bjg_cnt'] + $row['bjg_cnt'];

					$statHeji['jg_cnt'] = $statHeji['jg_cnt'] + $row['jg_cnt'];
					
					$statHeji['lh_cnt'] = $statHeji['lh_cnt'] + $row['lh_cnt'];
					
					$statHeji['yx_cnt'] = $statHeji['yx_cnt'] + $row['yx_cnt'];
					

					$statData[] = $row;


				}
			}

			if(!empty($statData) && ($show_type == 'level')){ // OR $show_type == 'item'
				$statHeji['bjg_bfb'] = round($statHeji['bjg_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['jg_bfb'] = round($statHeji['jg_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['lh_bfb'] = round($statHeji['lh_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['yx_bfb'] = round($statHeji['yx_cnt']/$statHeji['cnt']*100,2).'%';

				// if($show_type == 'level'){
					$statHeji['levelname'] = '合计';
				// }

				array_push($statData,$statHeji);
			}
			

			$this->assign('statData',$statData);
		}
		$this->assign('show_type',$show_type);
		$this->web_title = '总体成绩统计表';
		$this->page_template = "Show:stat";	
	}
	//区县成绩统计表
	public function townStat(){
		$ac = I('ac','');
		$show_type = I('show_type','');
		
		if($ac == 'show'){
			//if($this->town_id == 0)$this->error('请选择区县!');

			$data = D('StudentScore')->town_stat($this->school_year,$this->town_id);
			
			foreach($data as $k=>$row){

				$row['bjg_bfb'] = round($row['bjg_cnt']/$row['cnt']*100,2).'%';

				$row['jg_bfb'] = round($row['jg_cnt']/$row['cnt']*100,2).'%';

				$row['lh_bfb'] = round($row['lh_cnt']/$row['cnt']*100,2).'%';

				$row['yx_bfb'] = round($row['yx_cnt']/$row['cnt']*100,2).'%';


				$statHeji['cnt'] = $statHeji['cnt'] + $row['cnt'];

				$statHeji['bjg_cnt'] = $statHeji['bjg_cnt'] + $row['bjg_cnt'];

				$statHeji['jg_cnt'] = $statHeji['jg_cnt'] + $row['jg_cnt'];
					
				$statHeji['lh_cnt'] = $statHeji['lh_cnt'] + $row['lh_cnt'];
					
				$statHeji['yx_cnt'] = $statHeji['yx_cnt'] + $row['yx_cnt'];
					
				$statData[] = $row;

			}

			if(!empty($statData)){
				$statHeji['bjg_bfb'] = round($statHeji['bjg_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['jg_bfb'] = round($statHeji['jg_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['lh_bfb'] = round($statHeji['lh_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['yx_bfb'] = round($statHeji['yx_cnt']/$statHeji['cnt']*100,2).'%';

				$statHeji['town_name'] = '合计';

				array_push($statData,$statHeji);
			}

			$this->assign('statData',$statData);
		}

		$this->web_title = '区县成绩统计表';
		$this->page_template = "Show:townStat";	
	}
	//审核学校上报情况
	public function raterUpStatus(){
		$ac = I('ac','show');

		$deal_status = I('deal_status','');

		$this->assign('deal_status',$deal_status);

		if($ac == 'show'){
			$dictList = session('dictList');

			$deal_status_list = $dictList['206'];

			foreach($deal_status_list as $k=>$row){
				if(intval($k) < 206000) unset($deal_status_list[$k]);
			}

			$this->assign('deal_status_list',$deal_status_list);


			$s_status = D('SchoolStatus')->get_status_list($this->school_year,$this->town_id,$this->school_code,'show',$deal_status);

			foreach($s_status['list'] as $k=>$row){
				$s_status['list'][$k]['s_status_name'] = $deal_status_list[$row['s_status']]['dict_name'];
				$s_status['list'][$k]['sub_time'] = $row['sub_time'] > 0 ? date('Y-m-d H:i:s',$row['sub_time']) : '';
			}
			// print_r($s_status);exit();
			$this->assign('s_status',$s_status);
			$this->web_title = '审核学校上报情况';
			$this->page_template = "Show:raterUpStatus";	

		}elseif($ac == 'check' && IS_AJAX){
			$school_id = I('data');
			$s_status = D('SchoolStatus')->where('school_id = %d',$school_id)->find();

			if(empty($s_status))$this->ajaxReturn(array('errno'=>1,'errtitle'=>'没有这个学校的信息!'));

			if($s_status['s_status'] == '206010' || $s_status['s_status'] == '206040'){
				$this->ajaxReturn(array('errno'=>2,'errtitle'=>'当前状态为未上报,需要学校上报后方可进行审核或者撤销审核操作!'));
			}

			$data['s_status'] = $s_status['s_status'] == '206020' ? 206030 : 206040;
			$data['suh_time'] = time();

			$return = D('SchoolStatus')->where('school_id = %d',$school_id)->save($data);

			if($return == true){
				$this->ajaxReturn(array('errno'=>0,'errtitle'=>'操作成功!'));
			}else{
				$this->ajaxReturn(array('errno'=>5,'errtitle'=>'操作失败!'));
			}
		}
	}
	//查看区县上报情况
	public function townUpStatus(){

		$ac = I('ac','');

		if($ac == 'showStatus'){
			$info = D('SchoolStatus')->get_town_status_list($this->school_year,$this->town_id);

			$this->assign('info',$info);
		}

		$this->web_title = '查看区县上报情况';
		$this->page_template = "Show:townUpStatus";	
	}
	//分城郊区查看成绩统计
	public function suburbStat(){
		$ac = I('ac','');

		if($ac == 'show'){

			$data = D('StudentScore')->suburb_stat($this->school_year);
			
			foreach($data as $k=>$row){

				$row['bjg_bfb'] = round($row['bjg_cnt']/$row['cnt']*100,2).'%';

				$row['jg_bfb'] = round($row['jg_cnt']/$row['cnt']*100,2).'%';

				$row['lh_bfb'] = round($row['lh_cnt']/$row['cnt']*100,2).'%';

				$row['yx_bfb'] = round($row['yx_cnt']/$row['cnt']*100,2).'%';


				$statHeji['cnt'] = $statHeji['cnt'] + $row['cnt'];

				$statHeji['bjg_cnt'] = $statHeji['bjg_cnt'] + $row['bjg_cnt'];

				$statHeji['jg_cnt'] = $statHeji['jg_cnt'] + $row['jg_cnt'];
					
				$statHeji['lh_cnt'] = $statHeji['lh_cnt'] + $row['lh_cnt'];
					
				$statHeji['yx_cnt'] = $statHeji['yx_cnt'] + $row['yx_cnt'];
					
				$statData[] = $row;

			}

			if(!empty($statData)){
				$statHeji['bjg_bfb'] = round($statHeji['bjg_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['jg_bfb'] = round($statHeji['jg_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['lh_bfb'] = round($statHeji['lh_cnt']/$statHeji['cnt']*100,2).'%';
				$statHeji['yx_bfb'] = round($statHeji['yx_cnt']/$statHeji['cnt']*100,2).'%';

				$statHeji['town_name'] = '合计';

				array_push($statData,$statHeji);
			}

			$this->assign('statData',$statData);
		}

		$this->web_title = '分城郊区查看成绩统计';
		$this->page_template = "Show:suburbStat";	
	}
}
?>