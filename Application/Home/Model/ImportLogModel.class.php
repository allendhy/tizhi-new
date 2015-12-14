<?php
namespace Home\Model;
use Think\Model;
class ImportLogModel extends Model {
	public function getInfos($school_year,$town_id,$school_code,$is_error = ''){

                $where = array();

                $where['log.partition_field'] = intval($town_id . $school_year);

                if($school_code != '0')

                $where['s.school_code'] = $school_code;

                if($is_error != ''){
                        $where['log.is_error'] = $is_error;
                }

        	$count = $this->alias('log')->join('LEFT JOIN school s ON s.school_id = log.user_id')->where($where)->count();
        	//分页
        	$page = new \Think\Page($count,C('PAGE_LISTROWS'));
        	$limit = ($page->firstRow . ',' . $page->listRows);

                $list = $this->alias('log')->field('log.*')->join('LEFT JOIN school s ON s.school_id = log.user_id')->where($where)->order('import_time DESC')->limit($limit)->select();
                $show = $page->show();
                return array('list'=>$list,'page'=>$show);
	}
    //学校上传当前学年历史记录查询,补录数据
    public function getHistoryList($school_year,$town_id,$school_code,$examine='',$ac="historyUpStatus"){
        $where = array();
        // $where['log.import_time']

        if($school_code != 0)$where['s.school_code'] = $school_code;

        if($ac == 'historyUpStatus'){
            $startTime = $school_year . '-09-01';
            $endTime = ($school_year + 1) . '-08-31';
            $where['log.import_time'] = array('between',array($startTime,$endTime));
        }elseif ($ac == 'historyPhyData'){
             $where['log.year_year'] = $school_year;
        }

        if($examine == ''){
            $where['log.is_examine'] = array('exp','IS NOT NULL');
        }else
            $where['log.is_examine'] = $examine;

        $count = $this->alias('log')->join('LEFT JOIN school s ON s.school_id = log.user_id')->where($where)->count();
        //分页
        $page = new \Think\Page($count,C('PAGE_LISTROWS'));

        $limit = ($page->firstRow . ',' . $page->listRows);

        $list = $this->alias('log')->field('log.*,sd.dict_name AS deal_status_name')->join('LEFT JOIN school s ON s.school_id = log.user_id')->join('LEFT JOIN sys_dict sd ON sd.dict_id = log.deal_status')->where($where)->order('import_id DESC')->limit($limit)->select();

        $show = $page->show();

        return array('list'=>$list,'page'=>$show);
    }
    //历史数据补录审核操作
    public function checkHistoryData($is_examine,$import_ids){

        M()->startTrans();

        $is_error = 0;

        $do_num = 0;

        foreach($import_ids as $import_id){
            $log = $this->field('year_year,partition_field,is_examine')->where('import_id = %d',$import_id)->find();

            if(empty($log)){
                $is_error = 1;
                break;
            }

            if($log['is_examine'] == $is_examine)continue;

            $detail_model = $log['year_year'] >= 2014 ? 'ImportDetailNew' : 'ImportDetail';

            $return = D($detail_model)->where('partition_field = %d AND import_id = %d',array($log['partition_field'],$import_id))->setField('examine',$is_examine);

            if($return === false){
                $is_error = 1;
                break;
            }

            $return = $this->where('partition_field = %d AND import_id = %d',array($log['partition_field'],$import_id))->setField('is_examine',$is_examine);
 
            if($return === false){
                $is_error = 1;
                break;
            }

            $do_num ++;
        }

        if($is_error == 1){
            M()->rollback();
            return array('errno'=>'1','errtitle'=>'审核未成功！请稍后重试!');
        }
        M()->commit();
        return array('errno'=>0,'errtitle'=>'审核成功！共提交'.count($import_ids) . '条记录，其中操作成功'.$do_num.'条记录！');
    }

}