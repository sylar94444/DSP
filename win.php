<?php
/*
 * ¶Á¡ͳ¼ÆÅ¢
 * */
$file = "./win.json";
$count_str = file_get_contents($file);
$count_obj = json_decode($count_str);
if(!is_array($count_obj)) {
    return null;
}

function find_record($count){
	foreach ($count as $value){
		if(($value->adid==$_GET['adid'])&&($value->date==date('y-m-d',time()))){
			$value->cost += $_GET['p'];
			$value->num += 1;
			return $value;
		}
	}
	return null;
}
$record = find_record($count_obj);
if(empty($record)){
	$add_str = '{"adid":"'.$_GET['adid'].'","date":"'.date('y-m-d',time()).'","num":'.'1'.',"cost":'.$_GET['p'].'}';
	$add_obj = json_decode($add_str);
	array_push($count_obj,$add_obj);
}

$count_str = json_encode($count_obj);
file_put_contents($file, $count_str,LOCK_EX);
?>
