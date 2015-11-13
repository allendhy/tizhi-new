<?php
namespace Home\Controller;
use Think\Controller;
class DownController extends PublicController {
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
		if(in_array(ACTION_NAME,array('template','receipt')) && !IS_AJAX){
			$school_grade_options = get_grade_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade,'school_code');
			$class_num_options = get_class_options($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);
			$this->assign('school_grade_options',$school_grade_options);
			$this->assign('class_num_options',$class_num_options);
		}
		
	}
	//下载数据模版
	public function template(){

		$sch_status = D('SchoolStatus')->get_status_info_one($this->school_year,$this->school_code);

		$this->assign('sch_status',$sch_status);

		$dType = I('dType','');

		if(IS_POST && $dType){

			if($this->school_code == '')$this->error('请选择学校！');
			
			$gradeItem = C('GRADE_ITEM');
						
			if(!in_array($dType,array('d1','d2','d3','d4','d5'))){
				$dType = 'd3';
			}
			
			//加载phpexcel类库

			import("Org.Util.PHPExcel");
			import("Org.Util.PHPExcel.IOFactory");

			$objPHPExcel = new \PHPExcel();

			//得到当前的活动表
			$objActSheet = $objPHPExcel->getActiveSheet();
			//缓存   一个存储方式
			$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
			$cacheSettings = array();  
			\PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
			//设置文档基本属性
			$objProps = $objPHPExcel->getProperties();
			$objProps->setCreator("国家学生体质健康");
			$objProps->setLastModifiedBy("国家学生体质健康");
			$objProps->setTitle("体质数据上报模板");
			$objProps->setSubject("体质数据上报模板");
			$objProps->setDescription("国家学生体质健康标准测试数据管理与报送系统体质检测数据上报模板");
			$objProps->setKeywords("数据模板");
			$objProps->setCategory("国家学生体质健康标准测试数据管理与报送系统");
			//学校信息			

			$schInfo = D('School')->get_list_by_schoolcode_year($this->school_code,$this->school_year,'one');
						
			$fileName = $schInfo['school_name'];

			//写入表头和内容
			//2014-10-08
			$rowNum = 2;
			switch($dType){
				//年级基本信息模板
				case 'd1':
				//++++++++++++++++++++++++++
				$fileName .= '年级基本信息模板';
				//表头
				$objActSheet->setCellValueExplicit('A1', '年级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('A1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('B1', '班级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('B1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('C1', '班级名称',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('C1')->getNumberFormat()->setFormatCode("@");
				
				$data = D('StudentScore')->get_grade_class_infos($this->school_year,$schInfo['town_id'],$schInfo['school_id']);
				
				if(empty($data))$this->error('学生数据为空！');
				foreach($data as $k=>$row){
					$objActSheet->setCellValue('A'.$rowNum, intval($row['school_grade']));
					$objActSheet->setCellValueExplicit('B'.$rowNum, intval($row['school_grade'].$row['class_num']),\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('B'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('C'.$rowNum, $row['class_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$rowNum++;
				}
				//++++++++++++++++++++++++++
				break;
				//学生基本信息模板
				case 'd2':
				//++++++++++++++++++++++++++
				$fileName .= '学生基本信息模板';
				//表头
				$objActSheet->setCellValueExplicit('A1', '班级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('A1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('B1', '班级名称',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('B1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('C1', '学籍号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('C1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('D1', '民族代码',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('D1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('E1', '姓名',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('E1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('F1', '性别',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('F1')->getNumberFormat()->setFormatCode("@");
				//$objActSheet->setCellValue('G1', '出生日期');
				$objActSheet->setCellValue('G1', '出生日期');
				$objActSheet->getStyle( 'G1' )->getNumberFormat()->setFormatCode('yyyy/m/d');
				$objActSheet->setCellValueExplicit('H1', '学生来源',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('H1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('I1', '身份证号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('I1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('J1', '家庭住址',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('J1')->getNumberFormat()->setFormatCode("@");
				


				$data = D('StudentScore')->down_template($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num);

				if(empty($data))$this->error('当前学校学生数据为空或者您所选择的学生没有全国学籍号！');
				foreach($data as $k=>$row){

					// if(is_object($row['birthday'])){
					// 	$birthdayObj = object2array($row['birthday']);
					// 	//$birthday = date('Y/m/d',strtotime($birthdayObj['date']));
					// 	//excel时间格式
					// 	$birthday = intval(strtotime($birthdayObj['date'])/86400 + 25569 + 1);
					// }
					$row['birthday'] =  intval(strtotime($row['birthday'])/86400 + 25569 + 1);
					$objActSheet->setCellValueExplicit('A'.$rowNum, intval($row['school_grade'].$row['class_num']),\PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objActSheet->setCellValueExplicit('B'.$rowNum, $row['class_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('B'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('C'.$rowNum, $row['country_education_id'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('D'.$rowNum, $row['folk'],\PHPExcel_Cell_DataType::TYPE_NUMERIC);

					$objActSheet->setCellValueExplicit('E'.$rowNum, $row['name'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('E'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('F'.$rowNum, $row['sex'],\PHPExcel_Cell_DataType::TYPE_NUMERIC);

					//日期格式
					$objActSheet->setCellValueExplicit('G'.$rowNum, $row['birthday'],\PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objActSheet->getStyle( 'G'.$rowNum )->getNumberFormat()->setFormatCode('yyyy/m/d');

					$objActSheet->setCellValueExplicit('H'.$rowNum, $row['student_source'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('H'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('I'.$rowNum, $row['idcardno'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('I'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('J'.$rowNum, $row['address'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('J'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$rowNum++;
				}
				//++++++++++++++++++++++++++
				break;
				//学校学生体侧模板
				case 'd3':
				case 'd5':
				//$this->error('因缺失教育部学籍号信息，暂不提供下载');
				//++++++++++++++++++++++++++
				$fileName .= '体测模板';
				$objActSheet->setCellValueExplicit('A1', '年级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('A1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('B1', '班级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('B1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('C1', '班级名称',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('C1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('D1', '学籍号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('D1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('E1', '民族代码',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('E1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('F1', '姓名',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('F1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('G1', '性别',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('G1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValue('H1', '出生日期');
				$objActSheet->getStyle( 'H1' )->getNumberFormat()->setFormatCode('yyyy/m/d');
				$objActSheet->setCellValueExplicit('I1', '家庭住址',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('K1')->getNumberFormat()->setFormatCode("@");
				

				//根据年级判断当前学校都包含哪些学段

				$school_length54_count = D('StudentScore')->get_school_length54_count($this->school_year,$this->town_id,$this->school_code,$this->school_grade);

				$grade = $this->school_grade;
				/*
				echo "grade 		 = " . $grade . '<br />';
				echo "length54_count = " . $school_length54_count . '<br />';
				echo M()->getlastsql();

				exit();
				*/
				if($this->school_grade > 0){

					if(in_array($this->school_grade,array(21,22,23,24)) && $school_length54_count > 0){
						switch($this->school_grade){
							case 21:
								$grade = 16;
								break;
							case 24:
								$grade = 23;
								break;
							default:
								$grade = $grade - 1;
								break;
						}
					}
				}else{
					
					$data = D('StudentScore')->get_grades($this->school_year,$this->town_id,$this->school_code);
					$school_length54_count = D('StudentScore')->get_school_length54_count($this->school_year,$this->town_id,$this->school_code);

					foreach($data as $val){
						if(in_array($val['school_grade'],array(21,22,23,24)) && $school_length54_count > 0){
							switch($val['school_grade']){
								case 21:
									$val['school_grade'] = 16;
									break;
								case 24:
									$val['school_grade'] = 23;
									break;
								default:
									$val['school_grade'] = $val['school_grade'] - 1;
									break;
							}
						}
						if($val['school_grade'] >= 11 && $val['school_grade'] <=16){
							$grades['xx'] = 1;
						}elseif($val['school_grade'] >= 21 && $val['school_grade'] <=44){
							$grades['zx'] = 1;
						}
					}
					if(isset($grades['xx']) && isset($grades['zx'])){
						$grade = 99;
					}elseif(isset($grades['xx']) && !isset($grades['zx'])){
						$grade = 16;
					}elseif(!isset($grades['xx']) && isset($grades['zx'])){
						$grade = 21;
					}
				}
				
				$n = 0;
				for($i = 'j';$i < 'z';$i++){
					$objActSheet->setCellValueExplicit($i.'1', $gradeItem[$grade][$n],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle($i.'1')->getNumberFormat()->setFormatCode("@");
					if($n == count($gradeItem[$grade]) - 1)break;
					$n++;
				}
				
				if($dType == 'd3'){
					$country_education_id_exp = 'IS NOT NULL';
				}else{
					$country_education_id_exp = 'IS NULL';
				}

				$data = D('StudentScore')->down_template($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num,$country_education_id_exp);

				if(empty($data))$this->error('没有符合要求的数据');
				// if($dType == 'd3'){
				// 	if(empty($data))$this->error('当前学校学生数据为空或者当前学校所有学生没有全国学籍号');
				// }
				
				foreach($data as $k=>$row){
				
					if($dType == 'd5')$row['country_education_id'] = $row['education_id'];
					
					// if(is_object($row['birthday'])){
					// 	$birthdayObj = object2array($row['birthday']);
					// 	//$birthday = date('Y/m/d',strtotime($birthdayObj['date']));
					// 	$birthday = intval(strtotime($birthdayObj['date'])/86400 + 25569 + 1);
					// }
					$row['birthday'] =  intval(strtotime($row['birthday'])/86400 + 25569 + 1);

					$objActSheet->setCellValueExplicit('A'.$rowNum, intval($row['school_grade']),\PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objActSheet->setCellValueExplicit('B'.$rowNum, intval($row['school_grade'].$row['class_num']),\PHPExcel_Cell_DataType::TYPE_NUMERIC);

					$objActSheet->setCellValueExplicit('C'.$rowNum, $row['class_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('D'.$rowNum, $row['country_education_id'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('D'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('E'.$rowNum, $row['folk'],\PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objActSheet->getStyle('E'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('F'.$rowNum, $row['name'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('F'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$objActSheet->setCellValueExplicit('G'.$rowNum, $row['sex'],\PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objActSheet->getStyle('G'.$rowNum)->getNumberFormat()->setFormatCode("@");
					//日期格式
					$objActSheet->setCellValueExplicit('H'.$rowNum, $row['birthday'],\PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objActSheet->getStyle( 'H'.$rowNum )->getNumberFormat()->setFormatCode('yyyy/m/d');

					$objActSheet->setCellValueExplicit('I'.$rowNum, $row['address'],\PHPExcel_Cell_DataType::TYPE_STRING);
					$objActSheet->getStyle('I'.$rowNum)->getNumberFormat()->setFormatCode("@");
					$rowNum++;
				}
				//++++++++++++++++++++++++++
				break;
				//测试信息模板
				case 'd4':
				//++++++++++++++++++++++++++
				$fileName .= '测试信息模板';
				//表头
				$objActSheet->setCellValueExplicit('A1', '年级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('A1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('B1', '班级编号',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('B1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('C1', '班级名称',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('C1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('D1', '项目名称',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('D1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('E1', '测试老师',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('E1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('F1', '测试时间',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('F1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('G1', '测试地点',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('G1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('H1', '测试器材',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('H1')->getNumberFormat()->setFormatCode("@");
				$objActSheet->setCellValueExplicit('I1', '测试方法（手工/仪器）',\PHPExcel_Cell_DataType::TYPE_STRING);
				$objActSheet->getStyle('I1')->getNumberFormat()->setFormatCode("@");
				
				$data = D('StudentScore')->get_grade_class_infos($this->school_year,$this->town_id,$this->school_code);
				if(empty($data))$this->error('当前学校学生数据为空或者当前学校所有学生没有全国学籍号');
				
				foreach($data as $k=>$row){
					$g = $gradeItem[$row['school_grade']];
					foreach($g as $val2){
						$objActSheet->setCellValue('A'.$rowNum, intval($row['school_grade']));
						$objActSheet->setCellValueExplicit('B'.$rowNum, intval($row['school_grade'].$row['class_num']),\PHPExcel_Cell_DataType::TYPE_STRING);
						$objActSheet->getStyle('B'.$rowNum)->getNumberFormat()->setFormatCode("@");
						$objActSheet->setCellValueExplicit('C'.$rowNum, $row['class_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
						$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");
						$objActSheet->setCellValueExplicit('D'.$rowNum, $val2,\PHPExcel_Cell_DataType::TYPE_STRING);
						$objActSheet->getStyle('D'.$rowNum)->getNumberFormat()->setFormatCode("@");
						$rowNum++;
					}
					
				}
				//++++++++++++++++++++++++++
				break;
			}
			
			$fileName = iconv('utf-8','gbk',$fileName);
			// Rename sheet
			$objPHPExcel->getActiveSheet()->setTitle('studentData');
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			ob_end_clean ();
			//输出到浏览器
			
			header('Pragma:public');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Type:application/x-msexecl;name='.$fileName.'.xlsx');
			header('Content-Disposition:inline;filename='.$fileName.'.xlsx');


			$ua = isset ( $_SERVER ["HTTP_USER_AGENT"] ) ? $_SERVER ["HTTP_USER_AGENT"] : '';  

			$file_name = $fileName . '.xlsx';

			if (preg_match ( "/MSIE/", $ua )) {  
				$file_name = rawurlencode ( $file_name );  
				header ( 'Content-Disposition: attachment; filename="' . $file_name . '"' );  
			} else if (preg_match ( "/Firefox/", $ua )) {  
				header ( 'Content-Disposition: attachment; filename*="utf8' . $file_name . '"' );  
			} elseif (stripos ( $ua, 'rv:' ) > 0 && stripos ( $ua, 'Gecko' ) > 0) {  
				$file_name = rawurlencode ( $file_name );  
				header ( 'Content-Disposition: attachment; filename="' . $file_name . '"' );  
			} else {  
				header ( 'Content-Disposition: attachment; filename="' . $file_name . '"' );  
			}
			// Redirect output to a client’s web browser (Excel5)
			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			//PHPExcel::Destroy();
			exit();
		}

		$this->web_title = '下载数据模版';
        $this->page_template = 'Down:template';
	}

	//下载学生体质成绩回执单
	public function receipt(){
		
		$ac = I('ac','phydata');

		if($ac == 'down' && IS_POST && $this->class_num != '0'){

			$stuScoreList = D('StudentScore')->get_phyinfos($this->school_year,$this->town_id,$this->school_code,$this->school_grade,$this->class_num,'school_code','down');

			$stuScoreList = $stuScoreList['list'];

			if(!$stuScoreList){$this->error('无体质数据');}

			$gradeList = session('gradeList');

			$dictList = session('dictList');

			//导入时间
			if($this->school_year >= 2014){
				$import_detail_t = 'import_detail_new';
				
			}else{
				$import_detail_t = 'import_detail';
			}

			if($stuScoreList){
				foreach($stuScoreList as $k=>$v){
					$stuScoreList[$k]['grade_name'] = $gradeList[$v['school_grade']];

					if($v['is_avoid']==1){
						$stuScoreList[$k]['total_score'] = '免体';
						$stuScoreList[$k]['score_level'] = '免体';
						$stuScoreList[$k]['total_score_ori'] = '免体';
						$stuScoreList[$k]['score_level_ori'] = '免体';
					}else{
						$stuScoreList[$k]['score_level'] = $dictList['203'][$v['score_level']]['dict_name'];
						$stuScoreList[$k]['score_level_ori'] = $dictList['203'][$v['score_level_ori']]['dict_name'];
						$stuScoreList[$k]['total_score'] = round($v['total_score']);
						$stuScoreList[$k]['total_score_ori'] = round($v['total_score_ori']);

						$stuScoreList[$k]['stuItemScoreList'] =  D('ItemScore')->get_info_list($v['partition_field'],$v['year_score_id']);

						if(!$stuScoreList[$k]['stuItemScoreList']){$this->error('没有找到该生体质健康成绩信息~');}
						//xt,zs,item
						$xt = array();
						$zs = array();
						$item = array();
						foreach($stuScoreList[$k]['stuItemScoreList'] as $k2=>$v2){
							if(intval($v2['item_id'])==0||$v2['kind_id']=='')continue;
							//各项目评定
							if($v2['score_level']){
								
								$v2['score_level'] = substr($v2['score_level'],0,3) == '205' ? $dictList['205'][$v2['score_level']]['dict_name'] :$dictList['203'][$v2['score_level']]['dict_name'];

							}else{
								$v2['score'] = '未检查';
							}

							if(in_array($v2['kind_id'],array('jn','xt'))){
								array_push($xt,$v2);
							}elseif($v2['kind_id']=='zs'){
								array_push($zs,$v2);
							}else{
								array_push($item,$v2);
							}
						}
						$stuScoreList[$k]['stuItemScoreList']  = array();
						$stuScoreList[$k]['stuItemScoreList']['xt'] = $xt;
						$stuScoreList[$k]['stuItemScoreList']['zs'] = $zs;
						$stuScoreList[$k]['stuItemScoreList']['item'] = $item;

						foreach($stuScoreList[$k]['stuItemScoreList']['item'] as $key=>$val){
							if($val['item_id']=='08'&&intval($val['score'])==0)
								$stuScoreList[$k]['stuItemScoreList']['item'][$key]['score']='';
						}
					}
					//导入时间

					$import_log = D($import_detail_t)->get_detail_info($v['partition_field'],$v['import_detail_id']);

					if(is_object($import_log['import_time'])){
						$impTimeObj = object2array($import_log['import_time']);
						$import_time = date('Y-m-d H:i:s',strtotime($impTimeObj['date']));
					}else{
						$import_time = date('Y-m-d H:i:s',strtotime($import_log['import_time']));
					}

					$stuScoreList[$k]['import_time'] = $import_time;
						
					if($import_log){
						//操作人
						$login_name =  D('SysUser')->where('user_id = '.$import_log['user_id'])->getField('login_name');
						if(!$login_name)$login_name = D('School')->alias('s')->join('LEFT JOIN sys_user u ON u.org_schoolcode = s.school_code')->where('s.school_id = '.$import_log['user_id'])->getField('u.login_name');
						$stuScoreList[$k]['login_name'] = $login_name;
					}
				}
			}

			
			//输出文件
			$filename = $stuScoreList[0]['town_name'].'_'.$stuScoreList[0]['school_name'].'_'.$stuScoreList[0]['grade_name'].'_'.$stuScoreList[0]['class_name'].'体质数据';

			$filename = urlencode($filename);

			header("Content-type: application/octet-stream; ");
			header("Content-Disposition:   attachment;   filename=".$filename.".html");

			$html = @file_get_contents ($_SERVER['DOCUMENT_ROOT'] . '/Public/template/printHeader.html');

			foreach($stuScoreList as $key=>$val){
				if($key%6==0)$html .= '<h2 style="margin-top:20px">体质数据打印单</h2>';
				$html .= '
					<table width="100%" cellpadding="5" cellspacing="1" border="0" bgcolor="#8ACBEE" class="tableStyle" style="margin-top:10px;">
					  <tr>
						<td width="7%"  height="25" align="right" bgcolor="#A6D8F1">姓名：</td>
						<td width="10%" bgcolor="#D0EBF6">'.$val['name'].'</td>
						<td width="10%" align="right" bgcolor="#A6D8F1">综合成绩：</td>
						<td width="5%" bgcolor="#D0EBF6">'.$val['total_score'].'</td>
						<td width="10%"  align="right" bgcolor="#A6D8F1">综合评定：</td>
						<td width="7%" bgcolor="#D0EBF6">'.$val['score_level'].'</td>

						<td width="10%" align="right" bgcolor="#A6D8F1">测试成绩：</td>
						<td width="5%" bgcolor="#D0EBF6">'.$val['total_score_ori'].'</td>
						<td width="10%"  align="right" bgcolor="#A6D8F1">测试成绩评定：</td>
						<td width="7%" bgcolor="#D0EBF6">'.$val['score_level_ori'].'</td>
						<td width="10%" align="right" bgcolor="#A6D8F1">附加分数：</td>
						<td width="5%" bgcolor="#D0EBF6">'.$val['addach_score'].'</td>
					  </tr>
					</table>';
					if($val['stuItemScoreList']['xt']){
						$html .= '<table width="100%" cellpadding="5" cellspacing="1" border="0" bgcolor="#8ACBEE" class="tableStyle"><tr>';
						foreach($val['stuItemScoreList']['xt'] as $xt){
							$html .= '
							<td  align="right" bgcolor="#A6D8F1">'.$xt['item_name'].'：</td>
							<td  bgcolor="#D0EBF6">';
							if($xt['exam_result']>0)$html .= $xt['exam_result_display'];
							if($xt['score']>0)$html .= '('.$xt['score'].' ' .$xt['score_level'].')';
							//else $html .= '未检查';

							$html .= '</td>';
						}
						$html .= '</tr></table>';
					}

					if($val['stuItemScoreList']['zs']){
					  $html .= '<table width="100%" cellpadding="5" cellspacing="1" border="0" bgcolor="#8ACBEE" class="tableStyle"><tr>';
						foreach($val['stuItemScoreList']['zs'] as $zs){
							$html .= '<td  align="right" bgcolor="#A6D8F1">'.$zs['item_name'].'：</td>
						<td  bgcolor="#D0EBF6">'.$zs['exam_result'];
						if($zs['score']>0)$html .= '('.$zs['score'].' ' .$zs['score_level'].')';
						$html .= '</td>';
						}
						$html .= '</tr></table>';
					}

				  if($val['stuItemScoreList']['item']){
				   $html .= '<table width="100%" cellpadding="5" cellspacing="1" border="0" bgcolor="#8ACBEE" class="tableStyle"><tr>';
					foreach($val['stuItemScoreList']['item'] as $item){
						$html .= '<td  align="right" bgcolor="#A6D8F1">'.$item['item_name'].'：</td><td  bgcolor="#D0EBF6">'.$item['exam_result_display'];
						$html .= '('.$item['score'].' ' .$item['score_level'].')';
						$html .= '</td>';
					}
					$html .= '</tr></table>';

				  }
				  $html .= '<table width="100%" cellpadding="5" cellspacing="1" border="0" bgcolor="#8ACBEE" class="tableStyle"><tr bgcolor="#A6D8F1"><td >导入时间：'.$val['import_time'].'</td><td>操作人：'.$val['login_name'].'</td></tr><tr><td style="line-height:32px;">家长签字：____________________</td><td>签收日期：</td></tr></table><br /><hr><br />';
				  if($key%6==5)$html .= '<div style="page-break-before:always"></div>';

			}
			$html .= "</body></html>";
			echo $html;
			fclose($html);
			exit;

		//下载学校上传的文件 
		}elseif($ac == 'import_log'){
			$import_id = I('id',0);

			$loginfo = D('ImportLog')->where('import_id = %d',$import_id)->find();
			if(empty($loginfo))$this->error('导入信息为空!');

			//下载文件
			//clearstatcache();//清除文件状态缓存
			//if(is_file($_SERVER['DOCUMENT_ROOT'] . $file_path) == false)$this->error('文件不存在!');

			//echo $loginfo['file_name'];exit();
			down_file($loginfo['file_name'],$loginfo['file_path'],'application/vnd.ms-excel');
		}else{
			$this->web_title = '下载学生体质成绩回执单';
			$this->page_template = "Down:receipt";
		}
	}
	//导出学生体质信息
	public function phydata(){

		$this->web_title = '导出学生体质信息';
		$this->page_template = "Down:phydata";

	}
}
