<?php
namespace Home\Model;
use Think\Model;
class ItemScoreModel extends Model {
	//获取学生单项成绩
	public function get_info_list($partition_field,$year_score_id){
		return $this->field('item_score.score_id,item_score.exam_result_display,item_score.exam_result,item_score.score,item_score.score_level,item_score.addach_score,item.item_name,item.result_scope_display,item_kind.kind_name,item_kind.kind_id,item.item_id')->join('item ON item.item_id = item_score.item_id')->join('item_kind ON item_kind.kind_id = item.kind_id')->where('item_score.year_score_id = %d AND item_score.partition_field=%d',array($year_score_id,$partition_field))->select();
	}
}