<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends PublicController {
	public function _initialize(){
		parent::_initialize();

	}
    public function index(){

        header('Location:' . U('Home/Show/stuInfo') .'');
        $show_type = I('ac','showlist');

        $own_part_name = '全部';
        
        $can_display = 1;
        $own_part = I('own_part','');

        if($own_part)$own_part_name = D('SysDict')->get_dict_name($own_part);
        //无效类别ID 
        if($own_part_name == '' || $own_part_name == null)$own_part = '';

        //已选择的文章类别
        $this->assign('small_title',$own_part_name);

        switch($show_type){
            // 文章列表
            case 'showlist':
                $this->article_list($can_display,$own_part);
            break;
            // 文章详情页
            case 'showart':
                $article_id = I('id',0);
                $this->show_article($article_id);
            break;
            default:
                $this->article_list($can_display,$own_part);
            break;
        }
    }

    //文章列表
    private function article_list($can_display,$own_part){
        $articles = D('Article')->getArticleTitles($can_display,$own_part);
        $this->assign('articles',$articles);

        $this->web_title = '最新动态';
        $this->page_template = 'Index:index';
    }
    //文章详情页
    private function show_article($article_id){
        $article = D('Article')->getArticleOne($article_id);
        if(empty($article))$this->error('您要查看的文章不存在！');

        $this->assign('article',$article);
        $this->web_title = '最新动态-文章查看';
        $this->page_template = 'Index:show_article';
    }
    //用户登录
    public function login(){

        $login_name = I('post.login_name','');
        $login_pwd  = I('post.login_pwd','');

        $remember = cookie('login_name');

        //登陆验证
        if(IS_POST && ($login_name && $login_pwd) && IS_AJAX){
            $remember = I('post.remember','off');

            $return = D('SysUser')->login($login_name,$login_pwd);

            if(!$return) $this->ajaxReturn(array('errno'=> 99, 'errtitle' => '网络错误，请重试！'));
            if($return['errno'] != 0) $this->ajaxReturn($return);

            $user_info = $return['data'];

            // 登录成功，数据写入session
            if($remember == 'on'){
                cookie('login_name',base64_encode($user_info['login_name']),3600 * 24 * 7);
            }
            
            session('[regenerate]'); // 重新生成session id

            session('userinfo',$user_info);
            //初始化sys_dict
            $dictList = D('SysDict')->get_sys_dict();
            session('dictList',$dictList);
            //民族
            $folkList = D('DictFolk')->get_folk_list();
            session('folkList',$folkList);
            //年级
            $gradeList = D('DictGrade')->get_grade_list();
            session('gradeList',$gradeList);
            //学年
            $thisYear = D('SchoolYear')->this_year();
            session('thisYear',$thisYear);

            //初始化区县数据
            $townList = array();
            $schoolInfo = array();
            $gradeInfo = array();


            switch($user_info['user_kind']){
                case '109010'://系统用户或者市级用户
                    $townList = D('Town')->get_all();
                break;
                case '109020'://区县级用户
                    $townList = D('Town')->get_all($user_info['org_id']);
                    $schoolInfo = D('School')->get_list_by_town_year($user_info['org_id'],$thisYear['year_year']);
                break;
                case '109030'://学校级用户
                    $townList = D('Town')->get_list_by_schoolcode($user_info['org_schoolcode'],$thisYear['year_year']);

                    $town_id = $townList[0]['town_id'];

                    $schoolInfo = D('School')->get_list_by_schoolcode_year($user_info['org_schoolcode'],$thisYear['year_year']);
                    if(empty($schoolInfo)){
                        session(null);
                        session('[destroy]');
                        //return '没有该学校数据。请完善学校数据或者联系系统管理员';
                         $this->ajaxReturn(array('errno'=> 98, 'errtitle' => '没有该学校数据。请完善学校数据或者联系系统管理员'));
                    }

                    $gradeInfo = D('StudentScore')->get_grades($thisYear,$town_id,$user_info['org_schoolcode']);
                break;
                default:
                break;
            }
            session('townList',$townList);
            session('schoolInfo',$schoolInfo);
            session('gradeInfo',$gradeInfo);
           

            ///初始化用户菜单
            $navList = D('SysFunc')->getFuncByRole($user_info['user_kind']);
            session('navList',$navList);
            //允许当前用户操作的action
            $actionList = D('SysFunc')->getActionList($user_info['user_kind']);
            session('actionList',$actionList);

            //print_r($_SESSION);exit();
            $key = session_name();
            $value = session_id();
            $this->ajaxReturn(array('errno'=>0 , 'errtitle' => '登录成功！', 'url' =>  U('Home/Index/index',array($key=>$value)) ));
           // $this->success('登录成功!',U('Home/Index/index',array($key=>$value)));exit();
        }

    	$this->web_title = '登录系统';
    	$this->page_template = 'Index:login';
        $this->assign('login_name',base64_decode($remember));
    }

    //用户注销
    public function logout(){
        session(null);
        session('[destroy]');
        session('[regenerate]'); // 重新生成session id

        $this->success('退出成功!',U('Home/Index/index'));
    }
}