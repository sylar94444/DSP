<?php

/*
 * 全局变量
 * */
$bid_price = 0;
$bid_result = null;

/*
 * 加载bid request 消息
 * */
$req_str = file_get_contents("php://input");
$req_obj = json_decode($req_str);
if(!is_object($req_obj) || !isset($req_obj->id)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * 构造bid response消息主体
 * */
$resp_str = '{
 "id": "",
 "seatbid": [
 {
 "bid": [
 {
 "id": "",
 "impid": "1",
 "price": 0,
 "cmflag": 0,
 "adid": "",
 "nurl": "",
 "crid": "",
 "fmt": 0,
 "cat": [],
 "adomain": [],
 "adm": ""
 }
 ]
 }
 ],
 "bidid": "",
 "cur": "CNY"
}';
$resp_obj = json_decode($resp_str);
if(!is_object($resp_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * 读取投放广告信息
 * */
$ads_str = file_get_contents("./ad.json");
$ads_obj = json_decode($ads_str);
if(!is_array($ads_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * 根据bid request site设置投放策略
 * */
function parse_site($r, $b){
    /*匹配网站内容主题分类*/
    $result = array_intersect($r->sectioncat, $b->sectioncat);
    if(empty($result)){
        return false;
    }
    
    return true;
}

/*
 * 根据bid request device设置投放策略
 * */
function parse_device($r, $b){
    /*匹配网站内容UA*/
    if(!isset($r->ua) || !stristr($r->ua,$b->ua)){
        return false;
    }

    /*匹配js,支持JS的客户端展现广告*/
    if(!isset($r->js) || ($r->js!=$b->js)){
        return false;
    }

    /*匹配devicetype，PC客户端展现广告*/
    if(!isset($r->devicetype) || ($r->devicetype!=$b->devicetype)){
        return false;
    }
    return true;
}

/*
 * 根据bid request imp设置投放策略
 * */
function parse_imp($r, $b){
    foreach ($r as $value){
        switch ($b->type){
            case "banner":
                if(isset($value->banner) && is_object($value->banner)){
                    $banner = $value->banner;
                    /*广告尺寸*/
                    if($banner->w==$b->w && $banner->h==$b->h){
                        $GLOBALS['bid_price'] = $value->bidfloor;
                        return true;
                    }
                }
                break;
            case "video":
                break;
        }  
    }
    return false;
}

/*
 * 遍历广告素材，判断广告位和广告素材是否匹配
 * */
function parse_ad($req, $bid){

    if(!isset($req->site) || !is_object($req->site) || !isset($bid->site) || !is_object($bid->site) || !parse_site($req->site, $bid->site)){
        return null;
    }
   
    if(!isset($req->device) || !is_object($req->device) || !parse_device($req->device, $bid->device)){
        return null;
    }
  
    if(!isset($req->imp) || !is_array($req->imp) || !parse_imp($req->imp, $bid->imp)){
        return null;
    }
   
    return $bid;
}

foreach ($ads_obj as $ad_obj){
    $result = parse_ad($req_obj, $ad_obj);
    if(!empty($result)){
        $GLOBALS['bid_result'] = $result; 
        break;
    }
}
if(empty($bid_result)){
    header('HTTP/1.1 204 No Content');
    return null;    
}

/*
 * 生成response素材广告素材
 * */
function generate_response($resp, $req, $bid){
    $resp->id = (string) $req->id;
    $resp_bid = $resp->seatbid[0]->bid[0];
    $resp_bid->id = $bid->id;
    $resp_bid->cmflag = $bid->cmflag;
    $resp_bid->adid = $bid->adid;
    $resp_bid->nurl = $bid->nurl;
    $resp_bid->crid = $bid->crid;
    $resp_bid->fmt = $bid->fmt;
    $resp_bid->cat = $bid->cat;
    $resp_bid->adomain = $bid->adomain;
    $resp_bid->adm = $bid->adm;
	//生成最终的竞价
    $resp_bid->price = $GLOBALS['bid_price']+rand(1,50)/100;
    if($resp_bid->price>$bid->bidceiling){
        header('HTTP/1.1 204 No Content');
        return null;
    }
	//设置投放时间
	if((date('H')<$bid->dtime[0])||(date('H')>$bid->dtime[1]))
	{
		header('HTTP/1.1 204 No Content');
        return null;
	}
    $resp->bidid = $bid->bidid;
}
generate_response($resp_obj, $req_obj, $bid_result);

/*
 * 读取投放数据报表广告信息,判断是否超出每天设置的总额
 * */
$wins_str = file_get_contents("./win.json");
$wins_obj = json_decode($wins_str);
if(!is_array($wins_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}
foreach ($wins_obj as $win_obj){
	if(($win_obj->adid==$result->adid)&&($win_obj->date==date('y-m-d',time())))
	{
		if($win_obj->cost+$resp_obj->seatbid[0]->bid[0]->price>$bid_result->cost)
		{		
			header('HTTP/1.1 204 No Content');
			return null;
		}
	}
}

/*
 * 构造bid response消息http头
 * */
header('Content-type: application/json');
header('Connection: Keep-Alive');

/*
 * 开始投放。。。
 * */
$resp_str = json_encode($resp_obj);

echo $resp_str;
?>
