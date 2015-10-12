<?php
namespace Home\Model;
use Think\Model;
class ArticleModel extends Model {
    public function getArticleTitles($can_display = '', $own_part = 0 , $limit = 10 , $ac = 'show'){

        $where = array();

        if($own_part != 0)
            $where['ar.own_part'] = $own_part;
        if($can_display != '')
            $where['ar.can_display'] = $can_display;

		$count = $this->alias('ar')->where($where)->count();
		//分页
		$page = new \Think\Page($count,C('PAGE_LISTROWS'));
		$limit = $ac == 'show' ? ($page->firstRow . ',' . $page->listRows) : '';

        $list = $this->alias('ar')->field('ar.article_id,ar.own_part,ar.article_title,ar.can_display,ar.publish_time,ar.publish_login_name,dc.dict_name')->join('LEFT JOIN sys_dict dc ON dc.dict_id = ar.own_part')->where($where)->order('publish_time DESC')->limit($limit)->select();
        $show = $page->show();
        return array('list'=>$list,'page'=>$show);
    }
    
    public function getArticleOne($article_id){
        return $this->where('article_id = %d',$article_id)->find();
    }
}