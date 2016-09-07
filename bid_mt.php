<?php

/*
 * 加载bid request 消息
 * */
$req_str = file_get_contents("php://input");
$req_obj = json_decode($req_str);
if(!is_object($req_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * 判断请求消息类型
 * */
switch ($req_obj->request_type){
    case 0:
		//正常返回响应报文
        break;
    case 1:
		//测试请求，正常返回响应报文，不会收到结果
        break;
    case 2:
		//心跳测试，返回空响应报文
		header('HTTP/1.1 204 No Content');
		return null;
        break;
	default:
		break;
}  

/*
 * 读取ad.json投放广告策略
 * */
$ads_str = file_get_contents("./ad_mt.json");
$ads_obj = json_decode($ads_str);
if(!is_array($ads_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * 匹配request消息和投放策略中的device
 * */
function parse_device($r, $b){
    /*匹配UA*/
    if(!isset($r->ua)){
        return false;
    }
	foreach ($b->ua as $value){
		if(stristr($r->ua,$value)){
			continue;
		}
	}

    /*匹配OS*/
    if(!isset($r->os)){
        return false;
    }
	foreach ($b->os as $value){
		if(stristr($r->os,$value)){
			continue;
		}
	}

    /*匹配devicetype，PC客户端展现广告*/
    if(!isset($r->connectiontype) || !in_array($r->connectiontype, $b->connectiontype)){
        return false;
    }
    return true;
}

/*
 * 匹配request消息和投放策略中的video
 * */
function parse_video($r, $b){
    
	/*匹配video ID分类列表 */
/*
	$result = array_intersect($r->item_ids, $b->item_ids);
    if(empty($result)){
        return false;
    }
*/	
    return true;
}

/*
 * 根据bid request imp设置投放策略
 * */
function parse_imp($r, $b){
    foreach ($r as $value){
        if($value->width!=$b->width || $value->height!=$b->height ){      
            continue;
        }
		$b->min_cpm_price = $value->min_cpm_price+$b->bidceiling;
		if($b->min_cpm_price>$b->range[1]||$b->min_cpm_price<$b->range[0]){      
            continue;
        }
		if(!isset($value->location) || !in_array($value->location, $b->location)){
			continue;
		}
		if(!isset($value->ctype) || !in_array($b->ctype, $value->ctype)){
			continue;
		}
		//视屏类型的广告
		if($b->ctype==2){
			if($value->playtime!=$b->playtime){      
				continue;
			}
			if(!isset($value->order) || !in_array($value->order, $b->order)){
				continue;
			}
		}
		return true;
    }
    return false;
}

/*
 * 遍历广告素材，判断广告位和广告素材是否匹配
 * */
function parse_ad($req, $bid){

   	if(!parse_device($req->device, $bid->device)){
		return null;
	}
	
   	if(!parse_video($req->video, $bid->video)){
		return null;
	}
	
    if(!parse_imp($req->imp, $bid->imp)){
        return null;
    }
	
    return $bid;
}

/*
 * 全局变量
 * */
$bid_result = null;

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
function generate_response($req, $bid){
	
	$bid->resp->version = $req->version;
	$bid->resp->bid = $req->bid;
	$bid->resp->ads[0]->price = $bid->imp->min_cpm_price;
	$bid->resp->ads[0]->duration = $bid->imp->playtime;
	$bid->resp->ads[0]->ctype = $bid->imp->ctype;
	$bid->resp->ads[0]->width = $bid->imp->width;
	$bid->resp->ads[0]->height = $bid->imp->height;
}
generate_response($req_obj, $bid_result);

/*
 * 构造bid response消息http头
 * */
header('Content-type: application/json');
header('Connection: Keep-Alive');

/*
 * 开始投放。。。
 * */
$resp_str = json_encode($bid_result->resp);

echo $resp_str;
?>
