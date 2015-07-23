<?php                                                                                 
$data_string = '{
    "id": "BkAgV42eo27cSSj7bck",
    "tmax": 100,
    "site": {
        "id": "6",
        "domain": "http://www.douban.com",
        "sectioncat": [
            "A08B03C00v2012.1","A10B04C00v2012.1"
        ],
        "page": "http://115.236.76.101/xunda-test.html",
        "ref": "http://115.236.76.101/xunda-test.html",
        "publisher": {
            "id": "113"
        },
        "allyessitetype": "M01N05v2012.1",
        "allyespageform": "R10102v2012.1"
    },
    "device": {
        "dnt": 0,
        "ua": "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 Safari/537.36",
        "ip": "111.202.8.150",
        "language": "zh",
        "js": 1,
        "devicetype": 1
    },
    "user": {
        "id": "JFp30oUZEF0xE5q5AoTn7bcS",
        "cver": 2
    },
    "imp": [
        {
            "id": "1",
            "banner": {
                "w": 300,
                "h": 250,
                "pos": 1,
                "allyesadformat": [
                    0
                ],
                "allyesadform": "102"
            },
            "tagid": "113-51",
            "bidfloor": 0.6,
            "bidfloorcur": "CNY"
        }
    ],
    "cur": [
        "CNY"
    ],
    "bcat": [
        "X02Y09Z00v2012.1",
        "X03Y02Z00v2012.1",
        "X04Y02Z00v2012.1",
        "X04Y03Z00v2012.1",
        "X04Y99Z00v2012.1",
        "X07Y01Z00v2012.1",
        "X07Y02Z00v2012.1",
        "X07Y03Z00v2012.1",
        "X07Y04Z00v2012.1",
        "X07Y99Z00v2012.1",
        "X11Y03Z00v2012.1",
        "X11Y04Z00v2012.1",
        "X12Y02Z00v2012.1",
        "X15Y01Z00v2012.1",
        "X18Y01Z00v2012.1",
        "X19Y03Z00v2012.1"
    ],
    "badv": [
        "17u.cn",
        "airchina.com",
        "china-sss.com",
        "cncn.com",
        "csair.com",
        "kaixin.com",
        "kaixin001.com",
        "lvmama.com",
        "qq.com",
        "renren.com",
        "sh.tuniu.com",
        "sh.uzai.com",
        "shenzhenair.com",
        "springtour.com",
        "tencent.com",
        "weibo.com"
    ]
}';


$ch = curl_init('http://www.xuanruixinxi.com/xd/bid.php');                                                                      
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