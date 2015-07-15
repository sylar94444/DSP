<?php

$fp_win = "./win.json";
$win_str = file_get_contents($fp_win);
$win_obj = json_decode($win_str);
if(!is_array($win_obj)) {
    return null;
}

echo "竞价统计：";
echo "<br>";
foreach ($win_obj as $value){
    if(!empty($value)){
        echo "id:  ".$value->adid."    投放日期:  ".$value->date."    竞价成功量（次数）:  ".$value->num."    当天花费（元）:  ".$value->cost/1000;
        echo "<br>";
    }
}

echo "<br>";

$fp_count = "./count.json";
$count_str = file_get_contents($fp_count);
$count_obj = json_decode($count_str);
if(!is_array($count_obj)) {
    return null;
}
echo "广告投放统计：";
echo "<br>";
foreach ($count_obj as $value){
    $ctr = sprintf("%.4f", 100.0*$value->nclick/$value->nload);
    echo "id:  ".$value->adid."    投放日期:  ".$value->date."    点击量（次数）:  ".$value->nclick."    曝光量（次数）:  ".$value->nload."    点击率:  ".$ctr."%"."    累计花费（元）:  ".$value->cost/1000;
	echo "<br>";
}

?>
