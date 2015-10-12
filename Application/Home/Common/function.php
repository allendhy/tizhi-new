<?php
    //数组转换为对象
    function array2object($array) {
        if (is_array($array)) {
            $obj = new StdClass();
            foreach ($array as $key => $val){
                $obj->$key = $val;
            }
        }
        else { $obj = $array; }
        return $obj;
    }
    //对象转换为数组
    function object2array($object) {
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $value = object2array($value);
                $array[$key] = $value;
            }
        }
        else {
            $array = $object;
        }
        return $array;
    }
    //多维数组中查找某个值是否存在
    function deep_in_array($value, $array) {   
        foreach($array as $item) {   
            if(!is_array($item)) {   
                if ($item == $value) {  
                    return true;  
                } else {  
                    continue;   
                }  
            }   
               
            if(in_array($value, $item)) {  
                return true;      
            } else if(deep_in_array($value, $item)) {  
                return true;      
            }  
        }   
        return false;   
    }
    //返回区县下拉菜单option
    function get_town_options($town_id){
        $userinfo = session('userinfo');
        if(!$userinfo) return '';

        if($userinfo['user_kind'] == 109010){
           $option = '<option value="0">--区县--</option>'; 
        }else{
           $option = ''; 
        }

        $list = session('townList');
        foreach($list as $row){
            $selected = $town_id > 0 && $town_id == $row['town_id'] ? 'selected' : '';
            $option .= "<option value='".$row['town_id']."' ".$selected.">".$row['town_name'] ."</option>";
        }
        return $option;
    }
    //返回学校下拉菜单option
    function get_school_options($school_year,$town_id,$school_id){
        $userinfo = session('userinfo');
        if(!$userinfo) return '';

        if($userinfo['user_kind'] == 109030){
            $option = '';
        }else{
           $option = '<option value="0">--学校--</option>'; 
        }
        

        switch($userinfo['user_kind']){
            case '109010':
                $list = D('School')->get_list_by_town_year($town_id,$school_year);
                break;
            default:
                $list = session('schoolInfo');
                break;
        }

        foreach($list as $row){
            $selected = $school_id > 0 && $school_id == $row['school_id'] ? 'selected' : '';
            $option .= "<option value='".$row['school_id']."' ".$selected.">".$row['school_name'] ."</option>";
        }
        return $option;
    }
    //返回年级下拉菜单option
    function get_grade_options($school_year,$town_id,$school_id,$school_grade,$school_type="school_id"){
        $userinfo = session('userinfo');
        if(!$userinfo) return '';

        $option = '<option value="0">--年级--</option>';

        $list = D('StudentScore')->get_grades($school_year,$town_id,$school_id,$school_type);
        foreach($list as $row){
            $selected = $school_grade > 0 && $school_grade == $row['school_grade'] ? 'selected' : '';
            $option .= "<option value='".$row['school_grade']."' ".$selected.">".$row['grade_name'] ."</option>";
        }
        return $option;
    }

    //返回班级下拉菜单option
    function get_class_options($school_year,$town_id,$school_id,$school_grade,$class_num){
        $userinfo = session('userinfo');
        if(!$userinfo) return '';

        $option = '<option value="0">--班级--</option>';

        $list = D('StudentScore')->get_classes($school_year,$town_id,$school_id,$school_grade,'school_id');
        foreach($list as $row){
            $selected = $class_num == $row['class_num'] ? 'selected' : '';
            $option .= "<option value='".$row['class_num']."' ".$selected.">".$row['class_name'] ."</option>";
        }
        return $option;
    }
?>