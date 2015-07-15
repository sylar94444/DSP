<?php
/*
 * ¶Á¡ͳ¼ÆÅ¢
 * */
$adid = $_GET['adid'];
$price = $_GET['p'];
$file = "./count.json";
$count_str = file_get_contents($file);
$count_obj = json_decode($count_str);
if(!is_array($count_obj)) {
    return null;
}

function find_record($count){
    foreach ($count as $value){
        if(($value->adid==$_GET['adid'])&&($value->date==date('y-m-d',time()))){
            $value->nload++;
            $value->price = $_GET['p'];
            $value->cost += $_GET['p'];
            return $value;
        }
    }
    return null;
}
$record = find_record($count_obj);
if(empty($record)){
    $add_str = '{"adid":"'.$_GET['adid'].'","date":"'.date('y-m-d',time()).'","nclick":'.'0'.',"nload":'.'1'.',"price":'.$_GET['p'].',"cost":'.$_GET['p'].'}';
    $add_obj = json_decode($add_str);
    array_push($count_obj,$add_obj);
}

$count_str = json_encode($count_obj);
file_put_contents($file, $count_str,LOCK_EX);
?>
