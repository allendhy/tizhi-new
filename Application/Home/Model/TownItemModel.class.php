<?php
namespace Home\Model;
use Think\Model;
class TownItemModel extends Model {
    public function get_list($town_id,$ac='show'){

        $where = array();

        if($town_id != 0)
            $where['ti.town_id'] = $town_id;

		$count = $this->alias('ti')->where($where)->count();
		//分页
		$page = new \Think\Page($count,C('PAGE_LISTROWS'));

		$limit = $ac == 'show' ? ($page->firstRow . ',' . $page->listRows) : '';

        $list = $this->alias('ti')->field('ti.*,t.town_name')->join('LEFT JOIN town t ON t.town_id = ti.town_id')->where($where)->order('town_id,school_grade')->limit($limit)->select();

        $show = $page->show();


        $gradeList = session('gradeList');

        foreach(D('Item')->select() as $row){
        	$item[$row['item_id']] = $row;
        }

        foreach($list as $key=>$row){
        	$list[$key]['item_name'] = $item[$row['item_id']]['item_name'];
        	$list[$key]['grade_name'] = $gradeList[$row['school_grade']];
        }
        return array('list'=>$list,'page'=>$show);
    }
}