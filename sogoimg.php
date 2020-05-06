<?php
header('Access-Control-Allow-Origin:*');
header('Content-type:application/json; charset=utf-8');
error_reporting(0);
$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
//文件格式及大小做一下限制限制
if ((($_FILES["file"]["type"] == "image/gif")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/jpg")
        || ($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/x-png")
        || ($_FILES["file"]["type"] == "image/png"))
    && ($_FILES["file"]["size"] < 10*1024*1024)
    && in_array($extension, $allowedExts)) {
    $ImageCachePath='images/sougou/';//临时缓存路径
    //判断目录存在否，存在给出提示，不存在则创建目录
    if (!is_dir($ImageCachePath)){
        $res = mkdir($ImageCachePath, 0777, true);
    }
    //因为不支持直接使用临时文件上传 所以要先保存一下
    move_uploaded_file($_FILES["file"]["tmp_name"], $ImageCachePath . $_FILES["file"]["name"]);
    //文件储存位置
    $files = $ImageCachePath . $_FILES["file"]["name"];
    $post_data = [
        "pic_path"=>new CURLFile(realpath($files))
    ];
    $str=urldecode(Curl_POST("http://pic.sogou.com/ris_upload",$post_data));
    unlink($files); //使用完销毁一下文件
    $imgurl =  str_replace("http","https",GetBetween($str,".com/ris?query=","&oname="));
    if ($imgurl==1 || $imgurl==""){
        exit(json_encode([
            "code"=>-1,
            "msg"=>"上传错误"
        ],JSON_UNESCAPED_UNICODE));
    }else{
        exit(json_encode([
            "code"=>1,
            "imgurl"=>$imgurl
        ],JSON_UNESCAPED_UNICODE));
    }
    }else {
    error("非法的文件格式");
}
function GetBetween($content, $start, $end)
{
    $r = explode($start, $content);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}
function randIp()
{
    return mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);
}
function Curl_POST($url,$post_data){
    $header=[
        'X-FORWARDED-FOR:'.randIp(),
        'CLIENT-IP:'.randIp()
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36");   // 伪造ua
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
    curl_exec($ch);
    $data = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    return $data;
}
