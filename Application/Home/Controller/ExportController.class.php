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
		$ac = I('ac','show');

		if($ac == 'export' && IS_POST){
			
			$data = D('SchoolStatus')->get_status_info_list($this->school_year,$this->town_id,$this->school_code);
			if(empty($data['list']))$this->error('数据为空');

			$this->exportSchoolUpStatus($data['list']);

		}else{
			$data = D('SchoolStatus')->get_status_info_list($this->school_year,$this->town_id,$this->school_code,'list');
			$this->web_title = '导出学校上传情况';
	   		$this->page_template = 'Export:schoolUpStatus';
		}

	}
	//
	private function exportSchoolUpStatus($list){

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
		$objProps->setTitle("学校上传情况");
		$objProps->setSubject("学校上传情况");
		$objProps->setDescription("国家学生体质健康标准测试数据管理与报送系统体质检测数据上报模板");
		$objProps->setKeywords("数据模板");
		$objProps->setCategory("国家学生体质健康标准测试数据管理与报送系统");


		$objActSheet->setCellValueExplicit('A1', '学校名称',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('B1', '学籍总人数',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('C1', '标记不在学人数',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('D1', '免体人数',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('E1', '受检人数',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('F1', '受检率（%）',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('G1', '未检人数',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('H1', '未检人数率（%）',\PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('I1', '无全国学籍号人数',\PHPExcel_Cell_DataType::TYPE_STRING);
		$rowNum = 2;

		foreach($list as $k=>$v){
			//总人数
			$totalStu = $v['s_cnt'];
			$totalStu = $totalStu>0?$totalStu:0;
			//标记不在学人数
			$totalNotInSchool = $v['s_notinschool_cnt'];
			$totalNotInSchool = $totalNotInSchool>0?$totalNotInSchool:0;
			//免体人数
			$totalAvoid = $v['s_phyavoid_cnt'];
			$totalAvoid = $totalAvoid>0?$totalAvoid:0;
			//应受检人数
			$totalStu2 = $v['s_cnt'] - $v['s_notinschool_cnt'];
			//已受检人数
			$totalUp = $v['s_phy_cnt'] - $v['s_phynotinschool_cnt'];
			$totalUp = $totalUp>0?$totalUp:0;

			//$v['s_noceid_cnt'] = $v['s_noceid_cnt'] - $v['s_n2_cnt'];
			//未受检人数
			$noTotalUp = $totalStu2-$totalUp;
			//上传率
			$percentage = $totalUp/($totalStu2)*100;
			$percentage = round($percentage,2);
			$percentage = $percentage.'%';
			//未上传率
			$noPercentage = $noTotalUp/($totalStu2)*100;
			$noPercentage = round($noPercentage,2);
			$noPercentage = $noPercentage.'%';


			//写入excel
			$objActSheet->setCellValueExplicit('A'.$rowNum, $v['school_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('A'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('B'.$rowNum, $totalStu,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('B'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('C'.$rowNum, $totalNotInSchool,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('C'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('D'.$rowNum, $totalAvoid,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('D'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('E'.$rowNum, $totalUp,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('E'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('F'.$rowNum, $percentage,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('F'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('G'.$rowNum, $noTotalUp,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('G'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$objActSheet->setCellValueExplicit('H'.$rowNum, $noPercentage,\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('H'.$rowNum)->getNumberFormat()->setFormatCode("@");
					
			$objActSheet->setCellValueExplicit('I'.$rowNum, $v['s_noceid_cnt'],\PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->getStyle('I'.$rowNum)->getNumberFormat()->setFormatCode("@");

			$rowNum++;
		}


		$fileName = '学校上传体质数据情况统计表';
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
}