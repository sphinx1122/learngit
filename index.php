<?php

 $url = 'http://www.zhihu.com/people'; //此处mora-hu代表用户ID
    $ch = curl_init($url); //初始化会话
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_COOKIE, 'keyword=java%20%E5%8F%91%E9%80%81POST%E3%80%81GET%E8%AF%B7%E6%B1%82%E6%97%B6%EF%BC%8C%E8%8E%B7%E5%8F%96%E8%AF%B7%E6%B1%82%E7%9A%84%E5%A4%B4%E4%BF%A1%E6%81%AFSet-Cookie%EF%BC%8C%E8%AF%B7%E6%B1%82%E6%90%BA%E5%B8%A6Cookie%20-%20DOBEONE%E7%8E%89%E8%8B%91%E7%9A%84%E5%8D%9A%E5%AE%A2');  //设置请求COOKIE
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
    $result = curl_exec($ch);
    var_dump($result);
    return $result;  //抓取的结果