<?php
return array(
	//缓存
	'DATA_CACHE_TYPE' => 'Memcache', 
	'MEMCACHE_HOST'  => 'tcp://127.0.0.1:11211',
	'DATA_CACHE_TIME'=> '3600',
	'SHOW_PAGE_TRACE'=>false,

	//年级编号和测试项目对应关系
	'GRADE_ITEM'	=> 	array(
		11=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳'),
		12=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳'),
		13=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳','一分钟仰卧起坐'),
		14=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳','一分钟仰卧起坐'),
		15=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳','一分钟仰卧起坐','50米×8往返跑'),
		16=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳','一分钟仰卧起坐','50米×8往返跑'),
		21=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		22=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		23=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		24=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		30=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		31=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		32=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		33=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		34=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		41=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		42=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		43=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		44=>array('身高','体重','肺活量','50米跑','立定跳远','坐位体前屈','800米跑','1000米跑','一分钟仰卧起坐','引体向上'),
		99=>array('身高','体重','肺活量','50米跑','坐位体前屈','一分钟跳绳','一分钟仰卧起坐','50米×8往返跑','立定跳远','800米跑','1000米跑','引体向上'),
	),
	//字段对应关系
	'GRADE_ITEM_FIELD' =>	array(
		11=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts'),
		12=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts'),
		13=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts','ywqz_ytxs'),
		14=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts','ywqz_ytxs'),
		15=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts','ywqz_ytxs','wsmwfp'),
		16=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts','ywqz_ytxs','wsmwfp'),
		21=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		22=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		23=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		24=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		30=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		31=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		32=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		33=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		34=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		41=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		42=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		43=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		44=>array('body_height','body_weight','vital_capacity','wsm','ldty','zwtqq','bbm_nv','yqm_nan','ywqz_ytxs','ytxs_nan'),
		99=>array('body_height','body_weight','vital_capacity','wsm','zwtqq','yfzts','ywqz_ytxs','wsmwfp','ldty','bbm_nv','yqm_nan','ytxs_nan'),
	),
	//字段对应关系
	'ITEM_NO' =>	array('body_height'=>'01','body_weight'=>'02','vital_capacity'=>'04','wsm'=>'05','zwtqq'=>'07','yfzts'=>'18','ywqz_ytxs'=>'12','wsmwfp'=>'09','ldty'=>'06','bbm_nv'=>'10','yqm_nan'=>'11','ytxs_nan'=>'14'
	),
);