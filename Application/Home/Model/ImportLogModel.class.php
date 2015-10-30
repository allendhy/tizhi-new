<?php
namespace Home\Model;
use Think\Model;
class ImportLogModel extends Model {
	public function getInfos($school_year,$town_id,$school_code,$is_error = ''){

                $where = array();

                $where['log.partition_field'] = intval($town_id . $school_year);

                $where['s.school_code'] = $school_code;

                if($is_error != ''){
                        $where['log.is_error'] = $is_error;
                }

        		$count = $this->alias('log')->join('LEFT JOIN school s ON s.school_id = log.user_id')->where($where)->count();
        		//分页
        		$page = new \Think\Page($count,C('PAGE_LISTROWS'));
        		$limit = ($page->firstRow . ',' . $page->listRows);

                $list = $this->alias('log')->field('log.*')->join('LEFT JOIN school s ON s.school_id = log.user_id')->where($where)->order('import_time DESC')->select();
                $show = $page->show();
                return array('list'=>$list,'page'=>$show);
	}

}