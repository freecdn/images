<?php
header('Access-Control-Allow-Origin:*');
header('Content-type:application/json; charset=utf-8');
error_reporting(0);
$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
if ((($_FILES["file"]["type"] == "image/gif")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/jpg")
        || ($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/x-png")
        || ($_FILES["file"]["type"] == "image/png"))
    && ($_FILES["file"]["size"] < 7*1024*1024)
    && in_array($extension, $allowedExts)) {
    if ($_FILES["file"]["error"] > 0) {
        error("文件错误");
    } else {
        $post_data = [
            "image"=>new \CURLFile(realpath($_FILES['file']['tmp_name'])),
        ];
        $data = Curl_POST("https://graph.baidu.com/upload",$post_data);
        if ($data==""){
            error("上传失败");
        }elseif (json_decode($data)->msg!=="Success"){
            error("上传失败");
        }else{
            $pic = "https://graph.baidu.com/resource/".json_decode($data)->data->sign.".jpg";
            echo json_encode([
                "code"=>1,
                "imgurl"=>$pic
            ]);
        }
    }
}else {
    error("非法的文件格式");
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
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4086.0 Safari/537.36 Edg/83.0.461.1");   // 伪造ua
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
function error($str){
    exit(json_encode([
        "code"=>-1,
        "msg"=>$str
    ],JSON_UNESCAPED_UNICODE));
}
