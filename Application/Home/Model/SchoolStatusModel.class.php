<?php
namespace Home\Model;
use Think\Model;
class SchoolStatusModel extends Model {
	//获取一个学校的人数和状态信息
	public function get_status_info_one($school_year,$school_code){
		return $this->alias('ss')->field('s.school_name,s.school_code,ss.*')->join('LEFT JOIN school s ON s.school_id = ss.school_id')->where('s.year_year = %d AND s.school_code = \'%s\'  AND ss.school_id = s.school_id',array($school_year,$school_code))->find();
	}
	//设置上报状态或审核状态
	public function set_status($school_id,$status){
		if($status == 206030 || $status == 206040){
			$time_field = 'suh_time';
		}else{
			$time_field = 'sub_time';
		}

		$where['school_id'] = $school_id;
		$data['s_status'] = $status;
		$data[$time_field] = time();//date('Y-m-d H:i:s');
		return $this->where($where)->save($data);
	}
	//查看受检未检人数
	public function get_up_num($school_year,$town_id,$school_code){

		$where['s.year_year'] = $school_year;
		$where['s.town_id'] = $town_id;
		if($school_code != 0){
			$where['s.school_code'] = $school_code;
		}
		$where['s.is_del'] = 0;

		return $this->alias('ss')->field('sum(ss.s_cnt) s_cnt,sum(ss.s_notinschool_cnt) s_notinschool_cnt,sum(ss.s_noceid_cnt) s_noceid_cnt,sum(ss.s_n2_cnt) s_n2_cnt,sum(ss.s_phy_cnt) s_phy_cnt,sum(ss.s_phynotinschool_cnt) s_phynotinschool_cnt,sum(ss.s_phynoceid_cnt) s_phynoceid_cnt,sum(ss.s_phyn2_cnt) s_phyn2_cnt,sum(ss.s_phyavoid_cnt) s_phyavoid_cnt')->join('LEFT JOIN school s ON s.school_id = ss.school_id')->where($where)->find();
	}

	//学校上传情况列表
	public function get_status_info_list($school_year,$town_id,$school_code,$ac='export'){
		$where['s.year_year'] = $school_year;
		$where['s.town_id'] = $town_id;

		if($school_code != 0){
			$where['s.school_code'] = $school_code;
		}

		$where['s.is_del'] = 0;

		$count = $this->alias('ss')->join('LEFT JOIN school s ON s.school_id = ss.school_id')->where($where)->count();
		//分页
		$page = new \Think\Page($count,C('PAGE_LISTROWS'));

		$limit = $ac == 'show' ? ($page->firstRow . ',' . $page->listRows) : '';

		$list = $this->alias('ss')->field('s.school_id,s.school_name,sum(ss.s_cnt) s_cnt,sum(ss.s_notinschool_cnt) s_notinschool_cnt,sum(ss.s_noceid_cnt) s_noceid_cnt,sum(ss.s_n2_cnt) s_n2_cnt,sum(ss.s_phy_cnt) s_phy_cnt,sum(ss.s_phynotinschool_cnt) s_phynotinschool_cnt,sum(ss.s_phynoceid_cnt) s_phynoceid_cnt,sum(ss.s_phyn2_cnt) s_phyn2_cnt,sum(ss.s_phyavoid_cnt) s_phyavoid_cnt')->join('LEFT JOIN school s ON s.school_id = ss.school_id')->where($where)->group('s.school_id,s.school_name')->limit($limit)->select();

		$show = $page->show();
		
		return array('list'=>$list,'page'=>$show);
	}
}
?>