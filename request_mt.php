<?php                                                                                 
$data_string = '{"version":3,"bid":"9e55e7a5714045279e627c9b0bd69cae","request_type":0,"imp":[{"space_id":"101331","width":480,"height":270,"min_cpm_price":149,"player_id":4388,"location":4,"ctype":[1],"playtime":25,"order":3}],"device":{"duid":"EAFF463B-906F-F1DF-2A59-E4F98B0788E7","os":"ios","sw":1080,"sh":1920,"ip":"39.128.79.14","ua":"ios","connectiontype":0,"type":1,"version":"pcweb_WIN 21,0,0,197","screen_orientation":0},"video":{"video_id":717333,"video_name":"............... ...43...","collection_id":54686,"collection_name":"...............","item_ids":"30,36,37,40","item_names":"null,null,null,null","area_id":1,"year":2014,"duration":2291,"type":1}}';


$ch = curl_init('http://dsp.yundouzi.com/bid_mt.php');                                                                      
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
);                                                                                                                   

$result = curl_exec($ch);
$b = curl_multi_getcontent($ch);
print_r($result);

?>