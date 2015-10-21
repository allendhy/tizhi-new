<?php
namespace Home\Model;
use Think\Model;
class StudentScoreModel extends Model {
	//根据schoolcode获取年级信息
	public function get_grades($year_year,$town_id,$school_code,$type="school_code"){
		$gradeListCache = session('gradeList');

		$partition_field = intval($town_id . $year_year);

		$wheresql = $type == 'school_id' ? " s.school_id = %d" : " s.school_code = '%s'";

		$gradelist = $this->alias('sc')->field('sc.school_grade')->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where("sc.partition_field = %d AND ".$wheresql." AND sc.is_del = 0",array($partition_field,$school_code))->group('school_grade')->order('school_grade')->select();
		//AND s.is_del = 0
		foreach($gradelist as $key=>$row){
			$gradelist[$key]['grade_name'] = $gradeListCache[$row['school_grade']];
		}
		return $gradelist;
	}
	//获取班级列表信息
	public function get_classes($year_year,$town_id,$school_code,$school_grade,$type="school_code"){

		$partition_field = intval($town_id . $year_year);

		$wheresql = $type == 'school_id' ? " s.school_id = %d" : " s.school_code = '%s'";

		$wheresql .= ' AND sc.school_grade = %d ';
		//AND s.is_del = 0
		$classlist = $this->alias('sc')->field('sc.class_num,sc.class_name')->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where("sc.partition_field = %d AND ".$wheresql."  AND sc.is_del = 0",array($partition_field,$school_code,$school_grade))->group('class_num,class_name')->select();
		return $classlist;
	}

	//获取学生基本信息列表
	public function get_stuinfos($year_year,$town_id,$school_code,$school_grade,$class_num,$type="school_code",$ac='show'){
        $where = array();

        $partition_field = intval($town_id . $year_year);
        //条件
        $where['partition_field'] = $partition_field;

        if($school_code != 0 && $school_code != ''){
        	if($type == 'school_id')$where['s.school_id'] = $school_code;
        	else $where['s.school_code'] = $school_code;
        }

        if($school_grade != 0) $where['sc.school_grade'] = $school_grade;
        if($class_num != 0) $where['sc.class_num'] = $class_num;

        //$where['s.is_del'] = 0;
        $where['sc.is_del'] = 0;

        //查询
		$count = $this->alias('sc')->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where($where)->count();
		//分页
		$page = new \Think\Page($count,C('PAGE_LISTROWS'));
		$limit = $ac == 'show' ? ($page->firstRow . ',' . $page->listRows) : '';

        $list = $this->alias('sc')->field("sc.year_score_id,sc.town_id,sc.school_id,s.school_code,s.school_name,sc.school_grade,sc.class_num,sc.class_name,sc.name,case sc.sex when 106020 then '女' when 106010 then '男' else '未知' end sex,sc.folk,sc.education_id,sc.country_education_id,sc.student_source,sc.in_school")->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where($where)->limit($limit)->select();
        $show = $page->show();
        return array('list'=>$list,'page'=>$show);
	}

	//获取指定学校年级班级信息
	public function get_grade_class_infos($year_year,$town_id,$school_code){

		$partition_field = intval($town_id . $year_year);

		return $this->field('school_grade,class_num,class_name')->where('partition_field = %d  AND school_code = %s AND is_del = 0 AND in_school = 1 AND class_num IS NOT NULL',array($partition_field,$school_code))->group('school_grade,class_num,class_name')->order('school_grade,class_num')->select();
	}
	//下载学生模板,指定字段学生信息
	public function down_template($year_year,$town_id,$school_code,$school_grade = 0,$class_num = 0,$country_education_id_exp = 'IS NOT NULL'){
		$partition_field = intval($town_id . $year_year);
		$where = array(
			'partition_field' => $partition_field,
			's.school_code' =>	$school_code,
			'sc.is_del' => 0,
			'in_school' => 1,
			'country_education_id' => array('EXP', $country_education_id_exp),
			);
		if($school_grade > 0){
			$where['school_grade'] = $school_grade;
		}
		if($class_num != '' && $class_num > 0){
			$where['class_num'] = $class_num;
		}

		return $this->alias('sc')->field('school_grade,class_num,class_name,country_education_id,education_id,folk,name,(case sex when 106020 then 2 else 1 end) AS sex,birthday,student_source,idcardno,sc.address')->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where($where)->order('school_grade,class_num')->select();
	}
	//获取是否有54制学生,如果有，返回54制学生的人数
	public function get_school_length54_count($year_year,$town_id,$school_code,$school_grade = 0,$class_num = ''){
		$partition_field = intval($town_id . $year_year);
		$where = array(
			'partition_field' => $partition_field,
			'school_code' =>	$school_code,
			'is_del' => 0,
			'in_school' => 1,
			'school_length54' => 1,
			//'country_education_id' => array('EXP', 'IS NOT NULL'),
			);
		if($this->school_grade > 0){
			$where['school_grade'] = $this->school_grade;
		}
		if($this->class_num != '' && $this->class_num > 0){
			$where['class_num'] = $this->class_num;
		}

		return $this->where($where)->count();
	}
	//标识学生是否参测
	public function set_in_school($year_score_id,$in_school){
		$where['year_score_id'] = intval($year_score_id);
		$data['in_school'] = in_array($in_school,array(0,1)) ? $in_school : 1;

		$stu_info = $this->field('year_score_id,in_school,year_year,school_id')->where($where)->find();

		if(empty($stu_info))return array('errno'=>1 , 'errtitle'=>'参数错误，没有这个学生的信息！');
		if($stu_info['in_school'] == $in_school)return array('errno'=>2 , 'errtitle'=>'状态无改变！');

		//启动事务
		$this->startTrans();

		$return = $this->where($where)->setField($data);
		//修改相对应学校人数状态
		if($return == true){
			//+---------
			$return = M()->execute("update school_status  set 
			-- 不在学
			s_notinschool_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id AND is_del = 0 AND in_school = 0) ,
			-- 无全国学籍号
			s_noceid_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id AND is_del = 0  and country_education_id is null) ,
			-- 无全国学籍号且不在学
			s_n2_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id AND is_del = 0  and in_school = 0 and  country_education_id is null) ,
			-- 体质不在学
			s_phynotinschool_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id  AND is_del = 0 AND is_check = 1 and in_school = 0) ,
			-- 体质无全国学籍号
			s_phynoceid_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id AND is_del = 0 and is_check = 1 and country_education_id is null) ,
			-- 体质无全国学籍号且不在学
			s_phyn2_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id AND is_del = 0 and is_check = 1 and in_school = 0 and  country_education_id is null) ,
			-- 免体
			s_phyavoid_cnt = (select count(year_score_id) from student_score 
			where partition_field = convert(int,CONVERT(varchar(20), s.town_id, 0) + CONVERT(varchar(20), %d, 0))
			and school_id = s.school_id AND is_del = 0 AND is_check = 1  AND in_school=1 AND country_education_id is not null AND is_avoid = 1) 
			from school s,school_status sst
			where s.school_id = sst.school_id
			AND s.year_year = %d
			AND sst.school_id = %d",array($stu_info['year_year'],$stu_info['year_year'],$stu_info['year_year'],$stu_info['year_year'],$stu_info['year_year'],$stu_info['year_year'],$stu_info['year_year'],$stu_info['year_year'],$stu_info['school_id']));
		}

		if($return == true){
			$this->commit();
			return array('errno'=>0,'errtitle'=>'操作成功！');
		}else{
			$this->rollback();
			return array('errno'=>3,'errtitle'=>'状态设置失败！');
		}
		
	}

	//返回体质信息列表
	public function get_phyinfos($year_year,$town_id,$school_code,$school_grade,$class_num,$type="school_code",$ac='show',$order = ''){
        $where = array();

        $partition_field = intval($town_id . $year_year);
        //条件
        $where['partition_field'] = $partition_field;

        if($school_code != 0 && $school_code != ''){
        	if($type == 'school_id')$where['s.school_id'] = $school_code;
        	else $where['s.school_code'] = $school_code;
        }

        if($school_grade != 0) $where['sc.school_grade'] = $school_grade;
        if($class_num != 0) $where['sc.class_num'] = $class_num;

       // $where['s.is_del'] = 0;
        $where['sc.is_del'] = 0;
        $where['sc.is_check'] = 1;

        //查询
		$count = $this->alias('sc')->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where($where)->count();
		//分页
		$page = new \Think\Page($count,C('PAGE_LISTROWS'));
		$limit = $ac == 'show' ? ($page->firstRow . ',' . $page->listRows) : '';

        $list = $this->alias('sc')->field("t.town_name,sc.year_score_id,sc.town_id,sc.school_id,s.school_code,s.school_name,sc.school_grade,sc.class_num,sc.class_name,sc.name,case sc.sex when 106020 then '女' when 106010 then '男' else '未知' end sex,sc.folk,sc.education_id,sc.country_education_id,sc.student_source,sc.in_school,sc.is_avoid,sc.total_score,sc.score_level,sc.total_score_ori,sc.score_level_ori,sc.addach_score")->join('LEFT JOIN school s ON s.school_id = sc.school_id')->join('LEFT JOIN town t ON t.town_id = s.town_id')->where($where)->order($order)->limit($limit)->select();
        $show = $page->show();
        return array('list'=>$list,'page'=>$show);
	}

	//返回受检未检人数
	public function get_up_num($year_year,$town_id,$school_code,$school_grade,$class_num){

		$where['sc.partition_field'] = intval($town_id . $year_year);
		$where['s.school_code'] = $school_code;

		if($school_grade != 0){
			$where['sc.school_grade'] = $school_grade;
		}

		if($class_num != 0){
			$where['sc.class_num'] = $class_num;
		}

		$where['sc.is_del'] = 0;

		return $this->alias('sc')->field(' SUM(1) AS s_cnt,SUM(CASE sc.in_school WHEN 0 THEN 1 ELSE 0 END) AS s_notinschool_cnt,SUM(CASE WHEN sc.country_education_id is null THEN 1 ELSE 0 END) AS s_noceid_cnt,SUM(CASE WHEN sc.in_school = 0 AND sc.country_education_id is null THEN 1 ELSE 0 END) AS s_n2_cnt,SUM(CASE sc.is_check WHEN 1 THEN 1 ELSE 0 END ) AS s_phy_cnt,SUM(CASE WHEN sc.is_check = 1 AND sc.in_school = 0 THEN 1 ELSE 0 END) AS s_phynotinschool_cnt,SUM(CASE WHEN sc.is_check = 1 AND sc.country_education_id is null THEN 1 ELSE 0 END) AS s_phynoceid_cnt,SUM(CASE WHEN sc.is_check = 1 AND sc.country_education_id is null AND sc.in_school = 0 THEN 1 ELSE 0 END ) AS s_phyn2_cnt,SUM(CASE WHEN sc.is_check = 1 and sc.is_avoid = 1 THEN 1 ELSE 0 END) AS s_phyavoid_cnt')->join('LEFT JOIN school s ON s.school_id = sc.school_id')->where($where)->find();
	}
}
?>