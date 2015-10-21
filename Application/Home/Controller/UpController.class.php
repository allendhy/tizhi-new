<?php
namespace Home\Controller;
use Think\Controller;
class UpController extends PublicController {
	public function _initialize(){
		parent::_initialize();

		//各页面下拉选项列表
		$school_year_options = D('SchoolYear')->getOptions($this->school_year);
		$town_id_options = get_town_options($this->town_id);
		$school_code_options = get_school_options($this->school_year,$this->town_id,$this->school_code);
		
		$this->assign('school_year_options',$school_year_options);
		$this->assign('town_id_options',$town_id_options);
		$this->assign('school_code_options',$school_code_options);

	}
	//获取import数据校验状态
	private function check_state($import_id){
		if(!$import_id){$this->ajaxReturn(array('errno'=>1,'errtitle'=>'参数错误'));}

		$importLog = D("ImportLog")->where("import_id = %d",$import_id)->find();
		if(empty($importLog)){
			$this->ajaxReturn(array('errno'=>1,'errtitle'=>'获取文件上传状态失败，请到‘查看学生体质上传情况’处查看上传记录！'));
		}

		$errorList = '';

		$errno = 0;
		$msg = '';

		switch($importLog['deal_status']){
			case '204010':
				$msg = "文件上传中...";
			break;
			case '204020':
				$count = D('import_log')->where('deal_status = 204020 AND is_error = 0')->count();
				if($count > 1){
					$msg = '排在您前边共有'.$count.'份体质数据文件等待校验，请您耐心等候。。。';
				}else{
					$msg = "请稍候片刻，正在校验您上传的文件...";
				}
			break;
			case '204030':
				$msg = "正在执行数据校验，请稍后...";
			break;
			case '204040':
				$msg = "校验完毕";
				if($importLog['is_error']==1){
					$status = "校验完毕，数据有错误";
					if($importLog['year_year'] >= 2014){
						$errorList = D('import_detail_new')->field('detail_id,import_id,error_desc,excel_num,education_id,grade_num,class_num,class_name,country_education_id,name,sex')->where("partition_field = %d AND import_id= %d AND is_error = 1",array($importLogData['partition_field'],$import_id))->select();
					}else{
						$errorList = D('import_detail')->field('detail_id,import_id,error_desc,excel_num,education_id,grade_num,class_num,class_name,student_no,name,sex')->where("partition_field = %d AND import_id = %d AND is_error = 1",array($importLogData['partition_field'],$import_id))->select();
					}
				}
				$errno = 1;
			break;
			case '204050':
				$msg = "正在计算得分，请稍候...";
			break;
			case '204060':
				$msg = "计算得分完毕";
			break;
			default:
				$msg = "正在上传文件...";
			break;
		}

		if(is_array($errorList)){
			$msg .= '';
			foreach($errorList as $row){
				$msg .= "<p>" . '第 ' . $row['excel_num'] . ' 行 '.$row['name'] . ' ' . $row['error_desc'] . '</p>';
			}
		}

		//$msg = date('Y-m-d H:i:s');
		$this->ajaxReturn(array('errno'=>$errno,'errtitle'=>$msg));
	}
	//上传体质信息
	public function index($ac='phydata'){
		$ac = $ac != '' ? $ac : I('ac','phydata');

		$unique_salt = C('UNIQUE_SALT');
		$verifyToken = md5($unique_salt . $_POST['timestamp']);
		//获取校验状态
		if($ac == 'check_state' && IS_AJAX && $import_id = I('id',0)){
			$this->check_state($import_id);
			exit();
		}

		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			//判断来源
			if(!in_array($ac,array('phydata','phydata2','historyPhydata'))){
				$this->ajaxReturn(array('errno'=>99,'errtitle'=>'请求来源错误!'));
			}

			$fileinfo = $this->upload('file_data');
			//print_r($fileinfo);
			if($fileinfo['errno'] != 0){
				$this->ajaxReturn($fileinfo);
			}

			switch($ac){
				case 'phydata':
				case 'phydata2':
					$this->_phydata($fileinfo,$ac);
				break;
				case 'historyPhydata':
					$this->_historyPhydata();
				break;
			}
			//读取文件并处理数据
		}else{

			$school_year_info  = D('SchoolYear')->get_info($this->school_year);

			$this->assign('not_upload_time',$school_year_info['not_upload_time']);

			if(!empty($school_year_info['not_upload_time']) && time() > strtotime($school_year_info['not_upload_time'])){
				$is_upload = 0;
			}

			$this->assign('is_upload',$is_upload);
			$this->assign('ac',$ac);
			$this->assign('timestamp',time());
			if($ac == 'phydata2'){
				$this->web_title = '上传学生体质信息(无全国学籍号)';
			}else{
				$this->web_title = '上传学生体质信息(有全国学籍号)';
			}
			
        	$this->page_template = 'Up:phydata';

    	}
	}
	//有全国学籍号:phydata, 无全国学籍号:phydata2
	private function _phydata($fileinfo,$ac='phydata'){
		//$this->ajaxReturn($fileinfo);

		$this->school_id = D('School')->get_list_by_schoolcode_year($this->school_code,$this->school_year,'one');
		$this->school_id = $this->school_id['school_id'];

		//查看是否截止上报
		$school_year_info  = D('SchoolYear')->get_info($this->school_year);
		if(empty($school_year_info) || $school_year_info['state'] == 207020){
			$this->ajaxReturn(array('errno'=>101,'errtitle'=>'您选择的学年'.$this->school_year.'未开始,无法录入'));
		}
		if(!empty($school_year_info['not_upload_time']) && time() > strtotime($school_year_info['not_upload_time'])){
			$this->ajaxReturn(array('errno'=>102,'errtitle'=>'数据上报截止时间为'.$school_year_info['not_upload_time']));
		}

		//审核状态
		$userinfo = session('userinfo');
		$dictList = session('dictList');
		if($userinfo['org_id'] != 110105 && $userinfo['user_kind'] != '109010'){
			$s_status = D('SchoolStatus')->get_status_info_one($this->school_year,$this->school_code);
			if($s_status['s_status'] == 206020 || $s_status['s_status'] == 206030){
				$this->showAjaxMsg(array('error'=>'您当前上报状态为'.$dictList['206'][$s_status['s_status']]['dict_name'].',如需重新上报或修改请等待区县撤销！'));
			}
		}

		//读取excel文件内容
		$fPath = '/Upload/' . $fileinfo['info']['savepath'] . $fileinfo['info']['savename'];
		
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.IOFactory");

		//$PHPExcel = new \PHPExcel();
		$reader = \PHPExcel_IOFactory::createReader('Excel2007');
		$PHPExcel = \PHPExcel_IOFactory::load($_SERVER['DOCUMENT_ROOT']  . $fPath);
		$reader->setReadDataOnly(true);

		$sheet = $PHPExcel->getSheet(0);//sheet1
		$highestRow = $sheet->getHighestRow();//总行数
		$highestColumn = $sheet->getHighestColumn();//总列数,字母表示

		//判断是否空文件
		if($highestRow <= 1){
			@unlink($_SERVER['DOCUMENT_ROOT'] . $fPath);
			$this->ajaxReturn(array('errno'=>103,'errtitle'=>'您上传的文件没有内容!'));
		}

		$keys = array('grade_num','class_num','class_name','country_education_id','folk_code','name','sex','birthday','address');
		//将导入记录插入到导入历史表
		//上传状态为初始状态204010 上传中
		//is_error 初始状态为正确，待数据校验完毕后更改该列值
		$importLogData = array(
			'user_id'=>$this->school_id,
			'year_year'=>$this->school_year,
			'import_time'=>date('Y-m-d H:i:s'),
			'file_name'=>$fileinfo['info']['name'],
			'file_path'=>$fPath,
			'deal_status'=>'204010',//文件上传状态
			'is_error'=>'0',
			'town_id'=>$this->town_id,
			'partition_field'=>intval($this->town_id.$this->school_year),
		);
		$titContent = array(
			'body_height'=>'身高',
			'body_weight'=>'体重',
			'vital_capacity'=>'肺活量',
			'wsm'=>'50米跑',
			'ldty'=>'立定跳远',
			'zwtqq'=>'坐位体前屈',
			'bbm_nv'=>'800米跑（女）',
			'yqm_nan'=>'1000米跑（男）',
			'ywqz_nv'=>'一分钟仰卧起坐（女）',
			'ytxs_nan'=>'引体向上（男）',
			'wsmwfp'=>'50米×8往返跑',
			'yfzts'=>'一分钟跳绳',
			'ywqz_ytxs'=>'一分钟仰卧起坐'
		);

		$gradeItem = C('GRADE_ITEM_FIELD');

		


				
		//记录错误内容
		$errorLog = '';
		//启动事务
		M()->startTrans();

		$import_id = D('ImportLog')->add($importLogData);
		if(!$import_id){
			//删除文件
			@unlink($_SERVER['DOCUMENT_ROOT'] . $fPath);
			M()->rollback();
			$this->ajaxReturn(array('errno'=>102,'errtitle'=>'保存上传记录失败，请稍候重试！'));
		}

		$titleArr = array();
		//从第九列开始
		$columnNum = 9;
		for($column = 'J';$column <= $highestColumn;$column++){
			$val = $sheet->getCellByColumnAndRow($columnNum,1)->getValue();
			if($val instanceof PHPExcel_RichText) {
				//富文本转换字符串
				$val = $val->__toString();
			}
			$val  =  trim($val);
			if($val == '身高')$titleArr[] = 'body_height';
			elseif($val == '体重')$titleArr[] = 'body_weight';
			elseif($val == '肺活量')$titleArr[] = 'vital_capacity';
			elseif($val == '50米跑')$titleArr[] = 'wsm';
			elseif($val == '坐位体前屈')$titleArr[] = 'zwtqq';
			elseif($val == '一分钟跳绳')$titleArr[] = 'yfzts';
			elseif(strpos($val,'800米跑') !== false)$titleArr[] = 'bbm_nv';
			elseif(strpos($val,'1000米跑')  !== false)$titleArr[] = 'yqm_nan';
			elseif(strpos($val,'一分钟仰卧起坐')  !== false ){					
				$titleArr[] = 'ywqz_nv';
			}
			elseif(strpos($val,'引体向上')  !== false)$titleArr[] = 'ytxs_nan';
			elseif(strpos($val,'立定跳远')  !== false)$titleArr[] = 'ldty';
			elseif(strpos($val,'往返跑')  !== false)$titleArr[] = 'wsmwfp';
					
			$columnNum ++;
		}

		if(empty($titleArr)){
			@unlink($_SERVER['DOCUMENT_ROOT'] . $fPath);
			M()->rollback();
			$this->ajaxReturn(array('errno'=>105,'errtitle'=>'请务必使用本系统或者国家体质检测系统下载的上报模板并不要更改列格式！'));
		}

		//合并表头
		$keys = array_merge($keys,$titleArr);
		$titleCount = count($keys);

		if($ac == 'phydata2'){
			$field = 'education_id';
			$fieldTitle = '教育ID号';
		}else{
			$field = 'country_education_id';
			$fieldTitle = '全国学籍号';
		}

		$phyData = array();

		for($row=2;$row<=$highestRow;$row++){
			for($column = 0;$column < $titleCount;$column++){
				$val = $sheet->getCellByColumnAndRow($column,$row)->getValue();
				if($val instanceof PHPExcel_RichText) {
					//富文本转换字符串
					$val = $val->__toString();
				}
				$val  =  trim($val);
				$phyData[$row][$keys[$column]] = $val;
			}

			if($phyData[$row]['country_education_id'] == '' && $phyData[$row]['name'] == '')break;
					
			$errLogT1 = '第 '.$row.' 行';
			$errLogT2 = '';

			if($phyData[$row]['country_education_id'] == '')$errLogT2 .= $fieldTitle . '不能为空；';
			if($phyData[$row]['name'] == '')$errLogT2 .= '姓名不能为空；';

			$stuinfo = D('StudentScore')->where("partition_field = %d AND school_id= %d AND ".$field." = '%s' AND name = '%s' AND is_del = 0",array($importLogData['partition_field'],$this->school_id,$phyData[$row]['country_education_id'],$phyData[$row]['name']))->find();
			
			if(empty($stuinfo) || $stuinfo['in_school'] == 0){
				if(empty($stuinfo)){
					$errLogT2 .=  " 非当前学校数据或者数据格式错误，请核对学生姓名、".$fieldTitle."是否有误；";
				}elseif($stuinfo['in_school'] == 0){
					$errLogT2 .=  " 当前学生已设置为不在学,如需上报体质信息请设置该生在学状态为'是';";
				}
				
			}else{
				$realGrade = $phyData[$row]['grade_num'];
				if($stuinfo['school_length54'] == 1){
					switch($stuinfo['school_grade']){
						case 21:
							$realGrade = 16;
							break;
						case 22:
							$realGrade = 21;
							break;
						case 23:
							$realGrade = 22;
							break;
						case 24:
							$realGrade = 23;
							break;
						default:
							$realGrade = $stuinfo['school_grade'];
							break;
					}
				}
				if(!in_array($realGrade,array('11','12','13','14','15','16','21','22','23','24','31','32','33','34')))$errLogT2 .= ' 年级编号错误 ;';
						
				if(in_array($realGrade,array('11','12'))){
					$titles = $gradeItem[11];
				}elseif(in_array($realGrade,array('13','14'))){
					$titles = $gradeItem[13];
				}elseif(in_array($realGrade,array('15','16'))){
					$titles = $gradeItem[15];
				}else{
					$titles = $gradeItem[21];
				}
						
				if(!in_array($phyData[$row]['sex'],array(1,2))){
					$errLogT2 .= ' 性别列用数字1和2表示 ;';
				}
				if($phyData[$row]['sex']!= substr($stuinfo['sex'],4,1)){
					$errLogT2 .= ' 学生性别与cmis信息不一致 ;';
				}
						
						
				$titleErr = 0;
				
				foreach($titles as $va){
					$tmpStr 	= $phyData[$row][$va];
					if($phyData[$row]['body_height'] == '免体'){
						break;
					}

					if($realGrade > 16){
						if($tmpStr == '' && in_array($va,array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq'))){
							$titleErr = 1;
						}else{
							if($phyData[$row]['sex'] == 2 && $va == 'bbm_nv' && $tmpStr == ''){
								$titleErr = 1;
							}elseif($phyData[$row]['sex'] == 2 && $va == 'ywqz_nv' && $tmpStr == ''){
								$titleErr = 1;
							}elseif($phyData[$row]['sex'] == 1 && $va == 'yqm_nan' && $tmpStr == ''){
								$titleErr = 1;
							}elseif($phyData[$row]['sex'] == 1 && $va == 'ytxs_nan' && $tmpStr == ''){
								$titleErr = 1;
							}
						}
								
					}else{
						if($va == 'ywqz_ytxs')$tmpStr=$phyData[$row]['ywqz_nv'];							
						$titleErr = $tmpStr == '' ? 1 : 0; 
					}
							
					if($titleErr == 1){
						$errLogT2 .= $titContent[$va] . ' 不能为空！ ;';
						$titleErr = 0;
						continue;
					}
							
					if(!is_numeric($tmpStr) && $tmpStr != ''){
						if(!preg_match("('|’|”|′|\")",$tmpStr)){
							$errLogT2 .= $titContent[$va] . "[".$tmpStr."] 不是有效的体质健康数据，请确认; ";
						}else{
							$tmpStr = str_replace("’",'′',$tmpStr);
							$tmpStr = str_replace("”",'′′',$tmpStr);
							$tmpStr = str_replace("'",'′',$tmpStr);
							$tmpStr = str_replace("''",'′′',$tmpStr);
							$tmpStr = str_replace("\"",'′′',$tmpStr);
						}
					}

					if(!strpos($tmpStr,'′') && !strpos($tmpStr,'′′')){
						$phyData[$row][$va] = floatval($tmpStr);
					}else{
						$tmpFen = strpos($tmpStr,'′') ? intval(substr($tmpStr,0,strpos($tmpStr,'′'))) : 0;
						$tmpMiao = strpos($tmpStr,'′') ? substr(strstr($tmpStr,'′'),3,strlen($tmpStr)-1) : '00' ;
								
						$tmpMiao = strlen($tmpMiao) == 1 ? '0'.$tmpMiao : $tmpMiao;
								
						$end_result = $tmpFen == 0 ? intval($tmpMiao) : floatval($tmpFen.'.'.$tmpMiao);

						if(!$end_result||intval($tmpMiao)>59){
							$errLogT2 .= " 分秒时间格式错误或者秒大于59，请确认; ";
						}else{
							$phyData[$row][$va] = $end_result;
						}
					}
				}
			}
			$errLogT3 = ' <br>';

			if($errLogT2 != '') {$errorLog .= $errLogT1 . $errLogT2 . $errLogT3; continue;}
					
			$data = array(
				'import_id'				=>	$import_id,
				'is_error'				=>	0,
				'error_desc'			=>	'',
				'excel_num'				=>	$row,
				'education_id'			=>	$stuinfo['education_id'],
				'country_education_id'	=>	$stuinfo['country_education_id'],
				'student_no'			=>	$stuinfo['studentno'],
				'grade_num'				=>	str_replace("'","",$phyData[$row]['grade_num']),
				'class_num'				=>	str_replace("'","",$phyData[$row]['class_num']),
				'class_name'			=>	str_replace("'","",$phyData[$row]['class_name']),
				'folk_code'				=>	str_replace("'","",$phyData[$row]['fork_code']),
				'name'					=>	$stuinfo['name'],
				'sex'					=>	$stuinfo['sex'] == '106020' ? '女' : '男',
				'birthday'				=>	date('Y-m-d',strtotime($phyData[$row]['birthday'])),
				'student_source'		=>	$stuinfo['student_source'],
				'address'				=>	str_replace("'","",$phyData[$row]['address']),
				'rewards_code'			=>	0,
				'town_id'				=>	$importLogData['town_id'],
				'partition_field'		=>	$importLogData['partition_field'],
				'year_year'				=>  $importLogData['year_year'],
				'import_time'			=>  date('Y-m-d H:i:s'),
				'is_avoid'				=>	'否'
			);

			if($phyData[$row]['body_height'] === '免体'){
				$data['is_avoid']	=	'是';
			}else{
				$data['body_height']	=	floatval($phyData[$row]['body_height']);
				$data['body_weight']	=	floatval($phyData[$row]['body_weight']);
				$data['vital_capacity']	=	floatval($phyData[$row]['vital_capacity']);

				$data['wsm'] 			= 	floatval($phyData[$row]['wsm']);
				$data['ldty']			= 	floatval($phyData[$row]['ldty']);
				$data['zwtqq'] 			= 	floatval($phyData[$row]['zwtqq']);

				if($phyData[$row]['wsmwfp'] != ''){
					$data['wsmwfp'] = floatval($phyData[$row]['wsmwfp']);
				}
				if($phyData[$row]['yfzts'] != ''){
					$data['yfzts'] = (int)($phyData[$row]['yfzts']);
				}
				if($stuinfo['sex'] == '106020' && $phyData[$row]['bbm_nv']!=''){
					$data['bbm_yqm'] = floatval($phyData[$row]['bbm_nv']);
				}elseif($stuinfo['sex'] == '106010' && $phyData[$row]['yqm_nan']!=''){
					$data['bbm_yqm'] = floatval($phyData[$row]['yqm_nan']);
				}
				if($realGrade < 21){
					if($phyData[$row]['ywqz_ytxs'] != '')
					$data['ywqz_ytxs'] = (int)($phyData[$row]['ywqz_ytxs']);
				}else{
					if($stuinfo['sex'] == '106020' && $phyData[$row]['ywqz_nv'] != ''){
						$data['ywqz_ytxs'] = (int)($phyData[$row]['ywqz_nv']);
					}elseif($stuinfo['sex'] == '106010' && $phyData[$row]['ytxs_nan'] != ''){
						$data['ywqz_ytxs'] = (int)($phyData[$row]['ytxs_nan']);
					}
				}
			}
					
			$birObj = object2array($stuinfo['birthday']);
			$data['birthday'] = date('Y-m-d H:i:s',strtotime($birObj['date']));

			$school_length54 = $stuinfo['school_length54'];

			$detail_id = D('import_detail_new')->add($data);
					
			if(!$detail_id){
				$errLogT2 .= '添加学生记录失败！';
			}

			if($errLogT2 != '') $errorLog .= $errLogT1 . $errLogT2 . $errLogT3;
		}
		if(!empty($errorLog)){
			@unlink($_SERVER['DOCUMENT_ROOT'] . $fPath);
			//回滚
			M()->rollback();
			$this->ajaxReturn(array('errno'=>'109','errtitle'=>$errorLog));
		}else{
			//数据上传成功后修改状态为 待校验：204020
			D('ImportLog')->where('import_id= %d',$import_id)->setField('deal_status','204020');
			//朝阳区学校自动变更为已上报，无需点击上报按钮
			if($userinfo['org_id'] == 110105 || $userinfo['user_kind'] == 109010){
				$data = array('s_status'=>206020,'sub_time'=>time());
				D('SchoolStatus')->where('year_year = %d AND school_id = %d',array($this->school_year,$importLogData['user_id']))->save($data);
			}
			//提交
			M()->commit();
			$this->ajaxReturn(array('errno'=>0,'errtitle'=>'文件上传成功,请等待系统校验...','import_id'=>$import_id));
		}
	}
	//上传体质信息，无全国学籍号
	public function phydata2(){
		$this->index('phydata2');
	}
	//体质数据上报
	public function phydataSubmit(){

		$ac = I('ac','default');

		$sch_status = D('SchoolStatus')->get_status_info_one($this->school_year,$this->school_code);

		$this->assign('sch_status',$sch_status);

		if($ac == 'dataSubmit' && IS_AJAX && IS_POST){
			//提交上报
			if(empty($sch_status))$this->ajaxReturn(array('errno'=>1,'errtitle'=>'学校信息错误!'));

			if($sch_status['s_status'] == 206020 || $sch_status['s_status'] == 206030){

				$this->ajaxReturn(array('errno'=>2,'errtitle'=>'当前状态不允许上报,如需上报请等待区县撤销!'));

			}

			$return = D('SchoolStatus')->set_status($sch_status['school_id'],'206020');

			if($return == true){

				$this->ajaxReturn(array('errno'=>0,'errtitle'=>'操作成功!'));

			}else{
				$this->ajaxReturn(array('errno'=>5,'errtitle'=>'状态操作失败!'));
			}

		}else{

			$this->web_title = '学生体质数据上报';

      	 	$this->page_template = 'Up:phydataSubmit';

		}
	}

	//历史数据修改（模板下载）
	public function historyPhyData(){
		$this->web_title = '历史数据修改（模板下载）';
        $this->page_template = 'Up:historyPhyData';
	}

	//上传文件方法
	private function upload($file_data){
		$userinfo = session('userinfo');
	    $upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   	=     3145728 ;// 设置附件上传大小,3m
	    $upload->exts      	=     array('xls','xlsx');// 设置附件上传类型
	    $upload->rootPath  	=      './Upload/'; // 设置附件上传根目录
	    $upload->savePath  	=	'stuPhyData/' . $userinfo['login_name'] . '/';
		$upload->autoSub 	= true;
		$upload->subName 	= array('date','Ym');
	    // 上传单个文件 
	    $info   =   $upload->uploadOne($_FILES[$file_data]);
	    if(!$info) {// 上传错误提示错误信息
	        return array('errno'=>1,'errtitle'=>$upload->getError());
	    }else{// 上传成功 获取上传文件信息
	        return array('errno'=>0,'info'=>$info);
	    }
	}
}