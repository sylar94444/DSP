<?php

//set cookie
if(isset($_COOKIE["YDZCMID"]))
{
    echo "clean cookie";
    setcookie("YDZCMID","1", time()-1);
    return;
}
else
{
    echo "add cookie";
    //生成随机数cookie
    $rand = md5(time().mt_rand(0,1000));
    //种植cookie
    setcookie("YDZCMID",$rand, time()+3600*24);
    
    //将该cookie存到数据库
    $query = sprintf("INSERT INTO `cm`.`seeds` (`cookie`, `time`, `domain`) VALUES ('%s', CURRENT_TIMESTAMP, '%s');",
        $rand,
        $_SERVER['SERVER_NAME']);
    
    //插入数据库
    $dbconnect = mysql_connect('www.yundouzi.com:3306','cm_admin','cm_admin') or die('Could not connect: ' . mysql_error());
    mysql_select_db("cm") or die("Unable to select database!");
    $result = mysql_query($query) or die("Error in query: $query. ".mysql_error());
    mysql_free_result($result);
    mysql_close($dbconnect);
    
    //放置一个像素点
    $page = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Insert title here</title></head><body><img src=""  height="1px" width="1px"/></body></html>';
    echo $page;
}

?>