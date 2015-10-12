<?php
namespace Home\Controller;
use Think\Controller;
class PublicController extends Controller {
	//模板名称
	protected $web_title = '';
	protected $page_template = '';
	//操作名
	private $action_name = '';
	private $actions = array(
				'MODULE_NAME' => MODULE_NAME,
				'CONTROLLER_NAME' => CONTROLLER_NAME,
				'ACTION_NAME' => ACTION_NAME
			);

	//查找学生信息相关条件
	protected $school_year 	= 0;
	protected $town_id			= 0;
	protected $school_id		= 0;
	protected $school_code 	= '';
	protected $school_grade	= 0;
	protected $class_num		= '';

	public function _initialize(){

		//当前操作名
		$this->action_name = implode('/',$this->actions);
		//用户菜单
		$navList = array();
		$userinfo = session('userinfo');

		if(empty($userinfo) && $this->action_name != 'Home/Index/login'){
			header("Location: " . U('Home/Index/login'));
			exit();
		}
		//判断用户是否具有执行当前操作的权限
		$action_whitelist = session('actionList');
		$action_whitelist[] = 'Home/Index/login';

		if(!in_array($this->action_name,$action_whitelist)){
			$this->error('您无权执行当前操作！');
		}
		//登录用户单位
		$this->assign('login_unit',$userinfo['input_unit']);
		$this->assign('userinfo',$userinfo);
		//用户菜单
		$navList = session('navList');
		$this->assign('navList',$navList);

		//检索学生信息相关字段赋值
		$school_year = I('school_year',0);
		if($school_year == 0){
			$this_year_info = session('thisYear');
			$this->school_year = $this_year_info['year_year'];
		}else $this->school_year = $school_year;

		$this->school_id = I('school_id',0);

		switch($userinfo['user_kind']){
			case 109010:
				$this->town_id = I('town_id',0);
				$this->school_code = I('school_code','');

			break;

			case 109020:
				$this->town_id = $userinfo['org_id'];
				$this->school_code = I('school_code','');

			break;

			case 109030:
				$townlist = session('townList');
				$this->town_id = $townlist[0]['town_id'];
				$this->school_code = $userinfo['org_schoolcode'];
				
				if(!$this->school_id){
					$schInfo = D('School')->get_list_by_schoolcode_year($this->school_code,$this->school_year,'one');

					$this->school_id = $schInfo['school_id'];
				}
				
			break;

			default:
			break;
		}
		

		$this->school_grade = I('school_grade',0);
		$this->class_num = I('class_num','');
	}

    public function _empty(){
    	//404 page not found
    	header('Location: /404.html');
    }
    public function __destruct(){
    	$this->assign('action_name',$this->action_name);
		if($this->page_template != ''){
	    	//模板变量赋值
	    	$this->assign('web_title',$this->web_title);
	    	$this->display($this->page_template);
    	}
    }
}