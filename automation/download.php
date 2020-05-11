<?php

$down = $_GET['f'];   //获取文件参数
$filename = $down; //获取文件名称
$dir ="dist/";  //相对于网站根目录的下载目录路径
$down_host = $_SERVER['HTTP_HOST'].'/'; //当前域名


//判断如果文件存在,则跳转到下载路径
if(file_exists(__DIR__.'/'.$filename)){
    // header('location:http://'.$down_host.$dir.$filename);
	
	
	header( "Content-Disposition:  attachment;  filename=".$filename); //告诉浏览器通过附件形式来处理文件
	header('Content-Length: ' . filesize($filename)); //下载文件大小
	readfile($filename);  //读取文件内容

}else{
    header('HTTP/1.1 404 Not Found');
}
