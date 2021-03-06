<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ali-oss-web-upload-helper</title>
</head>
<body>
<ul id="filelist"></ul>
<br />
<div id="container">
    <a id="browse" href="javascript:;">[Browse...]</a>
    <a id="start-upload" href="javascript:;">[Start Upload]</a>
</div>
<br />
<pre id="console"></pre>
</body>
</html>
<script type="text/javascript" src="{{ asset('plupload/plupload.full.min.js') }}"></script>
<script type="text/javascript">

    var getPolicyFrom = '{{ config('aliOss.getPolicyFrom') }}'; // 请求Policy的地址
    var ossDomain = '{{ config('aliOss.callback') }}';                   // OSS外网访问地址

    ossUploader = new OssUploader();
    var uploader = new plupload.Uploader({
        browse_button: 'browse',
        url: ossDomain,
        max_retries: 10,
        init: {
            FilesAdded: function(up, files) {
                var html = '';
                plupload.each(files, function(file) {
                    html += '<li id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></li>';
                });
                document.getElementById('filelist').innerHTML += html;
            },
            BeforeUpload: function(up, file) {
                ossUploader.setUploadPara(uploader, file.name);
            },
            UploadProgress: function(up, file) {
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
            },
            Error: function(up, err) {
                document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
            },
        },
    });
    uploader.init();


    document.getElementById('start-upload').onclick = function() {
        ossUploader.init();
        uploader.start();
    };

    function OssUploader()
    {
        this.getPolicyFrom = getPolicyFrom;
        this.now = '';
        this.body = '';
        this.obj = '';
        this.host = '';
        this.policyBase64 = '';
        this.accessid = '';
        this.signature = '';
        this.expire = 0;
        this.callbackbody = '';
        this.dir = '';

        this.getPolicy = function()
        {
            var xmlhttp = null;
            if (window.XMLHttpRequest)
                xmlhttp=new XMLHttpRequest();
            else if (window.ActiveXObject)
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            if (xmlhttp!=null)
            {
                xmlhttp.open("GET", this.getPolicyFrom, false);
                xmlhttp.send();
                return xmlhttp.responseText;
            }
            else
                alert("Your browser does not support XMLHTTP.");
        }
        this.init = function()
        {
            var rawObj = this.getPolicy();
            this.now = Date.parse(new Date()) / 1000;
            this.body = rawObj;
            this.obj = JSON.parse(rawObj);
            this.host = this.obj.host;
            this.policyBase64 = this.obj.policy;
            this.accessid = this.obj.accessid;
            this.signature = this.obj.signature;
            this.expire = parseInt(this.obj.expire);
            this.callbackbody = this.obj.callback;
            this.dir = this.obj.dir;
        };
        this.setUploadPara = function(up, fileName)
        {
            this.now = Date.parse(new Date()) / 1000;
            if(this.now > this.expire - 3)
                this.init();
            var multipart_params = {
                'key' : this.dir + fileName,
                'policy': this.policyBase64,
                'OSSAccessKeyId': this.accessid,
                'success_action_status' : '200',
                'callback' : this.callbackbody,
                'signature': this.signature,
            };
            up.setOption({
                'url': this.host,
                'multipart_params': multipart_params,
            });
        };
    }
</script>