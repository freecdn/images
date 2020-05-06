<?php
header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
$file = $_FILES['file'];
if (is_uploaded_file($file['tmp_name'])){
    $arr = pathinfo($file['name']);
    $ext_suffix = $arr['extension'];
    $allow_suffix = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
    if(!in_array($ext_suffix, $allow_suffix)){
        imgurl(['code'=> -1,'imgurl'=> '上传格式不支持']);
    }
    $new_filename = time().rand(100,1000).'.'.$ext_suffix;
    if (move_uploaded_file($file['tmp_name'], $new_filename)){
        $data = upload('https://kfupload.alibaba.com/mupload',$new_filename);
        $pattern = '/"url":"(.*?)"/';
        preg_match($pattern, $data, $match);
        @unlink($new_filename);
        if($match && $match[1]!=''){
            imgurl(['code'=> 1,'imgurl'=> $match[1]]);
        }else{
            imgurl(['code'=> -1,'msg'=> '上传失败']);
        }
    }else{
        imgurl(['code'=> -1,'msg'=> '上传数据错误']);
    }

}else{
    imgurl(['code'=> -1,'msg'=> '上传数据错误']);
}



function upload($url,$file) {
    return get_url($url,[
        'scene' => 'aeMessageCenterV2ImageRule',
        'name' =>$file,
        'file' => new \CURLFile(realpath($file))
    ]);
}


function get_url($url,$post){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if($post){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
    }
    if(curl_exec($ch) === false){
      echo 'Curl error: ' . curl_error($ch);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function imgurl($data){
    exit(json_encode($data));
}
