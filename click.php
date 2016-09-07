<?php

	$ip = $_SERVER["REMOTE_ADDR"];
    $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$referer = $_SERVER['HTTP_REFERER'];
	$ua = $_SERVER['HTTP_USER_AGENT'];
	//var_dump($url, $referer , $ua);
    $dbconnect = mysql_connect('localhost','dsp_admin','dsp_admin') or die('Could not connect: ' . mysql_error());
    mysql_select_db("dsp") or die("Unable to select database!");
    
	$sql = "INSERT INTO `dsp`.`mgtv_click` (`click_time`, `ip`, `url`, `referer`, `ua`) VALUES (CURRENT_TIMESTAMP, '".$ip."','".$url."','".$referer."', '".$ua."')"; 
    mysql_query($sql) or die("Error in insert: ".mysql_error());
    
    mysql_close($dbconnect);

?>
