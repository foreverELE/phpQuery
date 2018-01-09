<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="{{ asset('js/base64.js') }}"></script>
    <script src="{{ asset('js/md5.js') }}"></script>
    <script src="{{ asset('js/sha1.js') }}"></script>
</head>
<body>
    <h3>加密测试页</h3>
</body>
<script>
    var date=new Date();
    var timestamp=Date.parse(date)/1000;
//    document.write(timestamp);
//    document.write('<br />');


    var end_timestamp=timestamp+300;
//    document.write(end_timestamp);

    //base64加密对象
    var base64=new Base64();
    var md5=new Md5();
    var base64=new Base64();
    alert(base64.decode('ftYaCXoLr0IcsMrxcCn6c41sUurf9oNmXJiADMMmZA_dVtRmqH7m2F17'));

</script>
</html>