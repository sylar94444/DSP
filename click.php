<?php

$adid = $_GET['adid'];
$file = "./count.json";
$count_str = file_get_contents($file);
$count_obj = json_decode($count_str);
if(!is_array($count_obj)) {
    header('Location: '.base64_decode($_GET['url']));
    return null;
}

foreach ($count_obj as $value){
    if($value->adid==$adid){
        $value->nclick++;
    }
}
$count_str = json_encode($count_obj);
file_put_contents($file, $count_str);

header('Location: '.base64_decode($_GET['url']));

?>
