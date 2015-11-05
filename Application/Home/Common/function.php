<?php
    // --------------------------------------------------
    // 分析返回用户网页浏览器名称
    // --------------------------------------------------
    function getBrowser(){
        $agent=$_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,'MSIE')!==false || strpos($agent,'rv:11.0')) //ie11判断
        //return "ie";
        {
           preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
           $exp[0] = "Internet Explorer";
           $exp[1] = $ie[1];
           return $exp;
        }
        else if(strpos($agent,'Firefox')!==false)
        return array("firefox");
        else if(strpos($agent,'Chrome')!==false)
        return array("chrome");
        else if(strpos($agent,'Opera')!==false)
        return array('opera');
        else if((strpos($agent,'Chrome')==false)&&strpos($agent,'Safari')!==false)
        return array('safari');
        else
        return array('unknown');
    }
    /*
    function getBrowser(){
        $sys = $_SERVER['HTTP_USER_AGENT'];
        print_r($sys);
        if(stripos($sys, "NetCaptor") > 0){
           $exp[0] = "NetCaptor";
           $exp[1] = "";
        }elseif(stripos($sys, "Firefox/") > 0){
           preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
           $exp[0] = "Mozilla Firefox";
           $exp[1] = $b[1];
        }elseif(stripos($sys, "MAXTHON") > 0){
           preg_match("/MAXTHON\s+([^;)]+)+/i", $sys, $b);
           preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
          // $exp = $b[0]." (IE".$ie[1].")";
           $exp[0] = $b[0]." (IE".$ie[1].")";
           $exp[1] = $ie[1];
        }elseif(stripos($sys, "MSIE") > 0){
           preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
           //$exp = "Internet Explorer ".$ie[1];
           $exp[0] = "Internet Explorer";
           $exp[1] = $ie[1];
        }elseif(stripos($sys, "Netscape") > 0){
           $exp[0] = "Netscape";
           $exp[1] = "";
        }elseif(stripos($sys, "Opera") > 0){
           $exp[0] = "Opera";
           $exp[1] = "";
        }elseif(stripos($sys, "Chrome") > 0){
            $exp[0] = "Chrome";
            $exp[1] = "";
        }else{
           $exp = "未知浏览器";
           $exp[1] = "";
        }
        return $exp;
    }*/
    //文件下载,文件流
    function down_file($file_name,$file_path,$file_type = ''){

        header('Pragma:public');
        header('Content-Type:application/x-msexecl;name='.$file_name);
        header('Content-Disposition:inline;filename='.$file_name);

        Header("Accept-Length: ".filesize($_SERVER['DOCUMENT_ROOT'] . $file_path));
            
        $ua = $_SERVER["HTTP_USER_AGENT"];

        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename=' . urlencode($file_name));
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*=utf8\'\'' . $file_name);
        } else if (preg_match("/rv:11.0/", $ua) || preg_match("/Chrome/", $ua)) {
            header('Content-Disposition: attachment; filename=' . iconv('UTF-8','GB2312',$file_name));
        } else {
            header('Content-Disposition: attachment; filename=' . $file_name);
        }

        header('Content-Disposition: attachment; filename=' . $file_name);

        readfile($_SERVER['DOCUMENT_ROOT'] . $file_path);
    }
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
    function get_school_options($school_year,$town_id,$school_code){
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
            $selected = $school_code != 0 && $school_code == $row['school_code'] ? 'selected' : '';
            $option .= "<option value='".$row['school_code']."' ".$selected.">".$row['school_name'] ."</option>";
        }
        return $option;
    }
    //返回年级下拉菜单option
    function get_grade_options($school_year,$town_id,$school_code,$school_grade,$school_type="school_code"){
        $userinfo = session('userinfo');
        if(!$userinfo) return '';

        $option = '<option value="0">--年级--</option>';

        $list = D('StudentScore')->get_grades($school_year,$town_id,$school_code,$school_type);
        foreach($list as $row){
            $selected = $school_grade > 0 && $school_grade == $row['school_grade'] ? 'selected' : '';
            $option .= "<option value='".$row['school_grade']."' ".$selected.">".$row['grade_name'] ."</option>";
        }
        return $option;
    }

    //返回班级下拉菜单option
    function get_class_options($school_year,$town_id,$school_code,$school_grade,$class_num){
        $userinfo = session('userinfo');
        if(!$userinfo) return '';

        $option = '<option value="0">--班级--</option>';

        $list = D('StudentScore')->get_classes($school_year,$town_id,$school_code,$school_grade,'school_code');
        foreach($list as $row){
            $selected = $class_num == $row['class_num'] ? 'selected' : '';
            $option .= "<option value='".$row['class_num']."' ".$selected.">".$row['class_name'] ."</option>";
        }
        return $option;
    }
?>