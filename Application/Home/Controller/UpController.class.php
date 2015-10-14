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
	//上传体质信息
	public function index(){
		$ac = I('ac','phydata');
		switch($ac){
			case 'phydata':
				$this->_phydata();
			break;
			case 'phydata2':
				$this->_phydata2();
			break;
			case 'historyPhydata':
				$this->_historyPhydata();
			break;
			default:
				$this->_phydata();
			break;
		}
	}
	//有全国学籍号
	private function _phydata(){
		$targetFolder = '/uploads'; // Relative to the root
		$unique_salt = C('UNIQUE_SALT');
		$verifyToken = md5($unique_salt . $_POST['timestamp']);

		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			$tempFile = $_FILES['file_data']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
				
			// Validate the file type
			$fileTypes = array('xls','xlsx'); // File extensions
			$fileParts = pathinfo($_FILES['file_data']['name']);
				
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				echo '1';
			} else {
				echo 'Invalid file type.';
			}
		}else{
			$this->assign('timestamp',time());
			$this->web_title = '上传学生体质信息(有全国学籍号)';
        	$this->page_template = 'Up:phydata';
    	}
	}
	//上传体质信息，无全国学籍号
	public function phydata2(){
		$this->assign('timestamp',time());
		$this->web_title = '上传学生体质信息(无全国学籍号)';
        $this->page_template = 'Up:phydata';
	}
	//体质数据上报
	public function phydataSubmit(){
		$this->web_title = '学生体质数据上报';
        $this->page_template = 'Up:phydataSubmit';
	}

	//历史数据修改（模板下载）
	public function historyPhyData(){
		$this->web_title = '历史数据修改（模板下载）';
        $this->page_template = 'Up:historyPhyData';
	}
}