<?php
return
[
    'accessKey' => '',
    // 账户的AccessKeyId

    'accessSecret' => '',
    // 账户的AccessSecret

    'ossDomain' => 'https://muyuchengfeng.oss-cn-qingdao.aliyuncs.com',
    // OSS的外网访问地址

    'getPolicyFrom' => 'https://moodrain.cn/api/oss/policy',
    // 请求Policy的地址

    'callback' => 'https://moodrain.cn/oss/api/callback',
    // OSS服务器执行回调时发送请求的目标地址

    'expire' => 30,
    // Policy的有效时长

];