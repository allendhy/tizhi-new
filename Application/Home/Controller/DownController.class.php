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

				if($this->school_grade > 0 && in_array(intval($this->school_grade),array(11,12,13,14,15,16,21,22,23,24,31,32,33,34,41,42,43,44))){

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
			
			$fileName = iconv('utf-8','gb2312',$fileName);
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
			//} else if (preg_match("/Firefox/", $ua)) {
			//	header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.xlsx"');
			//} else if (preg_match("/rv:11.0/", $ua) || preg_match("/Chrome/", $ua)) {
			//	header('Content-Disposition: attachment; filename="' . iconv('UTF-8','GB2312',$fileName) . '.xlsx"');
			} else {
				header('Content-Disposition: attachment; filename="' . $fileName . '.xlsx"');
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
		//下载学校上传的文件 
		if($ac == 'import_log'){
			$import_id = I('id',0);

			$loginfo = D('ImportLog')->where('import_id = %d',$import_id)->find();
			if(empty($loginfo))$this->error('导入信息为空!');
			//下载文件
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
