<?php

    //主帐号
    $accountSid = 'T00000028145';
    //APISecret
    $APISecret = 'd814b6d0-de8b-11ed-964f-0dceab45f754';
    //sig
    $sig = strtoupper(MD5($accountSid.$APISecret.date('YmdHis')));
    //authorization
    $authorization = base64_encode($accountSid.':'.date('YmdHis'));
    //http标头数据格式 json、xml等
    $type = 'json';
    //http 标头
    $header = array("Accept:application/".$type,"Content-Type:application/".$type.";charset=utf-8", "Authorization:$authorization");
    // $header = array("Accept:application/json", "Content-Type:application/json;charset=utf-8", "Authorization:$authorization");
    $code = rand(100000,999999);        //验证码
    $num = '18777103468';               //测试手机号
    $valittime = '5';                  //验证码有效时间 单位分钟
    $a = sms($sig,$header,$num,$code,$valittime,$type);
    /**
     * 获取短信模板
     */
    function smstemplates($sig,$header,$type){
        $url = 'openapis.7moor.com/v20160818/sms/getSmsTemplate/T00000028145?sig='.$sig;
        $smstemplates = curl_post($url,$header,$type);
        return $smstemplates;
    }
    /**
     * 发送短信
     */
    function sms($sig,$header,$num,$code,$valittime,$type){
        if($num){
            if(!(preg_match('/^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$/',$num))){
                return ['succeed'=>-1,'msg'=>'手机号码不正确'];
            }
        }else{
            return ['succeed'=>-1,'msg'=>'手机号码为空'];
        }
        $smstemplates = smstemplates($sig,$header,$type);         //获取模板信息
        var_dump($smstemplates);die;
        $data =
            [
            'num' => $num,               //手机号
            'templateNum' =>'1',         //模板编号
            'var1' => $code,             //模板中{1}值验证码
            'var2' => $valittime,        //模板中{2}值验证码有效时间
            ];
        $data = json_encode($data);
        $url = 'openapis.7moor.com/v20160818/sms/sendInterfaceTemplateSms/T00000028145?sig='.$sig;
        $sms = curl_post($url,$header,$type,$data);
        var_dump($sms);
        return  $sms;
    }
    /**
     * 发起HTTPS请求
     */
    function curl_post($url,$header,$type,$data = array())
    {
        //初始化curl
        $ch = curl_init();
        //参数设置
        $res = curl_setopt($ch, CURLOPT_URL, $url);                 //需要获取的URL地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);            //检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。2 检查公用名是否存在，并且是否与提供的主机名匹配
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);            //禁用后CURL将终止从服务端进行验证。使用CURLOPT_CAINFO选项设置证书使用CURLOPT_CAPATH选项设置证书目录 如果CURLOPT_SSL_VERIFYPEER(默认值为2)被启用，CURLOPT_SSL_VERIFYHOST需要被设置成TRUE否则设置为FALSE
        curl_setopt($ch, CURLOPT_HEADER, FALSE);                    //启用时会将头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);             //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);               //一个用来设置HTTP头字段的数组
        curl_setopt($ch, CURLOPT_POST, TRUE);                       //启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                //全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data
        $result = curl_exec($ch);
        //连接失败
        if ($result == FALSE) {
            if ($type == 'json') {
                $result = "{\"statusCode\":\"172001\",\"statusMsg\":\"网络错误\"}";
            } else {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
            }
        }

        curl_close($ch);
        return $result;
    }