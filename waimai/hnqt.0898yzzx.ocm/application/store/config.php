<?php
//配置文件
return [
//    'default_return_type'    => 'json',/*返回json格式数据*/
    'exception_handle'       => '\\util\\exception\\HttpHandle',
    
    //自动登录cookie保存时间
    'keep_login_time'   =>  3600*24*1,
];
