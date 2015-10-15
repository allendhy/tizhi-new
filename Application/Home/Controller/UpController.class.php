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

		$unique_salt = C('UNIQUE_SALT');
		$verifyToken = md5($unique_salt . $_POST['timestamp']);

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
			$this->assign('ac','phydata');
			$this->assign('timestamp',time());
			$this->web_title = '上传学生体质信息(有全国学籍号)';
        	$this->page_template = 'Up:phydata';
    	}
	}
	//有全国学籍号:phydata, 无全国学籍号:phydata2
	private function _phydata($fileinfo,$ac='phydata'){
		//$this->ajaxReturn($fileinfo);
		sleep(10);
		echo '下载';
	}
	//上传体质信息，无全国学籍号
	public function phydata2(){
		$this->assign('ac','phydata2');
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