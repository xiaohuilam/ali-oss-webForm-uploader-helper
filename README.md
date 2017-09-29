阿里云OSS表单直传助手
====================
Ali-OSS-webForm-upload-helper
---------------------

阿里云OSS表单直传，官方已经给出了教程，这里只是简化一下使用，供大家参考。

>[服务端签名后直传](https://help.aliyun.com/document_detail/31926.html?spm=5176.doc31923.6.631.ryqFP3)
>
>[服务端签名后直传并设置回调](https://help.aliyun.com/document_detail/31927.html?spm=5176.doc31926.6.632.JDVfto)
>
>以上链接可以下载官方的示例（展示用的语言php，有其他语言的示例下载）

使用方法
-----------------------
### 1、在 php/policy.php 填好你的参数
* 阿里云的 Access Key ID
* 阿里云的 Access Key Secret
* OSS的外网访问地址
* OSS服务器执行回调时发送请求的目标地址
* Policy的有效时间
* 用户上传文件存放的目录
### 2、在index.html 填好你的参数
* 请求Policy的地址
* OSS外网访问地址
### 3、访问index.html即可实现表单直传demo
* 需要在回调时进行的业务处理可以在 php/callback.php中添加
* 前端只是一个小demo，具体可以参照该demo来进行编写

在Laravel中的使用方法
--------------------
### 1、添加服务提供者并注册服务
* 复制 laravel/Providers/AliOssServiceProvider.php 到 app/Provides 目录
* 复制 laravel/Services/AliOssService.php 到 app/Service 目录
* 在 config/app.php 的 providers 数组中添加 App\Providers\aliOssServiceProvider::class,
### 2、添加配置
* 复制 laravel/config/aliOss.php 到 config/aliOss.php
* 设置 config/aliOss.php 中的值
### 3、添加路由
* 复制 laravel/routes/aliOss.php 到 app/routes/aliOss.php 并在 app/routes/api.php 中 require aliOss.php
### 4、添加视图
* 复制 laravel/public/plupload 到 app/public/plupload
* 复制 laravel/resources/views/oss-test.blade.php 到 app/resources/views/oss-test.blade.php
### 5、访问 /api/oss/test即可实现表单直传demo
* 需要在回调的业务处理可以在 app('aliOss')->callback() 中添加闭包，闭包需要接受一个 $fileInfo 数组

---------------------
Being lack of time, I didn't translate this markdown into English. If you need an English version, please mail to 745544921@qq.com, I will assist you!