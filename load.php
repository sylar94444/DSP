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

foreach ($count_obj as $value){
    if($value->adid==$adid){
        $value->nload++;
        $value->price = $price;
        $value->cost += $price;
    }
}
$count_str = json_encode($count_obj);
file_put_contents($file, $count_str);
?>
