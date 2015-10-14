//主函数
function allCheckForm(oForm)
{
    var els = oForm.elements;
    //遍历所有表元素
    for(var i=0;i<els.length;i++){
        //判断input是否需要验证
		check=els[i].getAttribute("check"); 
		warning=els[i].getAttribute("warning"); 
        if(check){
            //取得验证的正则字符串
            var sReg = check;
            //取得表单的值,用通用取值函数
            var sVal = GetValue(els[i]);
            //字符串->正则表达式,不区分大小写
            var reg = new RegExp(sReg,"i");
			//alert(sVal)
            if(!reg.test(sVal)){
                //验证不通过,弹出提示warning
                layer.alert(warning,{icon:0});
                //该表单元素取得焦点,用通用返回函数 
                GoBack(els[i]);
                return false;
            }
        }
    }
}


//通用取值函数分三类进行取值
//文本输入框,直接取值el.value
//单多选,遍历所有选项取得被选中的个数返回结果"00"表示选中两个
//单多下拉菜单,遍历所有选项取得被选中的个数返回结果"0"表示选中一个
function GetValue(el){
    //取得表单元素的类型
    var sType = el.type;

    switch(sType){
        case "text":
			el.value=Trim(el.value);
        case "hidden":
        case "password":
        case "file":
        case "textarea": 
			el.value=Trim(el.value);
			return el.value;
        case "checkbox":
        case "radio": return GetValueChoose(el);
        case "select-one": 
        case "select-multiple": return GetValueSel(el);
    }
    //取得radio,checkbox的选中数,用"0"来表示选中的个数,我们写正则的时候就可以通过0{1,}来表示选中个数
    function GetValueChoose(el){
        var sValue = "";
        //取得第一个元素的name,搜索这个元素组
        var tmpels = document.getElementsByName(el.name);
        for(var i=0;i<tmpels.length;i++){
            if(tmpels[i].checked){
                sValue += "0";
            }
        }
        return sValue;
    }
    //取得select的选中数,用"0"来表示选中的个数,我们写正则的时候就可以通过0{1,}来表示选中个数
    function GetValueSel(el){
        var sValue = "";
        for(var i=0;i<el.options.length;i++){
            //单选下拉框提示选项设置为value=""
            if(el.options[i].selected && el.options[i].value!=""){
                sValue += "0";
            }
        }
        return sValue;
    }
}

//通用返回函数,验证没通过返回的效果.分三类进行取值
//文本输入框,光标定位在文本输入框的末尾
//单多选,第一选项取得焦点
//单多下拉菜单,取得焦点
function GoBack(el){
    //取得表单元素的类型
    var sType = el.type;
	var sFather=el.name.toLowerCase().substr(0,1);
	var iexit=eval('document.all.'+sFather+'check?1:0');

	if (iexit==1){
		eval('document.all.'+sFather+'check.checked=""');
		show(sFather);
	}	
    switch(sType){
        case "text":
        case "hidden":
        case "password":
        case "file":
        case "textarea": el.focus(); return;//var rng = el.createTextRange(); rng.collapse(false); rng.select();
        case "checkbox":
        case "radio": var els = document.getElementsByName(el.name);els[0].focus(); return;
        case "select-one":
        case "select-multiple":el.focus(); return;
    }
}



function LTrim(str){ 
	var i; 
	for(i=0;i<str.length;i++){ 
		if(str.charAt(i)!=" "&&str.charAt(i)!=" ")break; 
	} 
	str=str.substring(i,str.length); 
	return str; 
} 
function RTrim(str){ 
	var i; 
	for(i=str.length-1;i>=0;i--){ 
		if(str.charAt(i)!=" "&&str.charAt(i)!=" ")break; 
	} 
	str=str.substring(0,i+1); 
	return str; 
} 
function Trim(str){ 
	return LTrim(RTrim(str)); 
}

	function ajaxSelectSchool(select_type,obj){
		school_year = $('#school_year').val();
		town_id = $('#town_id').val();

		school_code = 0;
		school_grade = 0;

		if(select_type == 'grade' || select_type == 'class'){
			school_code = $('#school_code').val();

			if(select_type == 'class')school_grade = $('#school_grade').val();
		}

		
		if(school_year == 0 || school_year == 'undefined'){
			layer.alert('请选择学年！',{icon : 0});
			return;
		}
		$('#' + obj).empty();
					
		$.post("/index.php/Home/Show/stuInfo",{ac : 'ajaxSelect',select_type : select_type, school_year : school_year, town_id : town_id, school_code : school_code, school_grade: school_grade},function(result){
			if(result.errno != 0){
				layer.alert(result.errtitle,{icon : 2});
				return;
			}
			$('#' + obj).append(result.optionstr);
		},'json');
	}


