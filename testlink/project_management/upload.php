<?php
    header('Access-Control-Allow-Origin:*');
/*
{
"name":"1.png",//原名称。
"type":"image\/png",//文件的 MIME 类型
"tmp_name":"C:\\Users\\h\\AppData\\Local\\Temp\\phpEAC5.tmp",//临时文件名
"error":0,//错误代码,0成功,1大小超过php.ini限制,2-超过HTML 表单中 MAX_FILE_SIZE 选项指定的值,3; 文件只有部分被上传。 4; 没有文件被上传。5; 上传文件大小为0. 
"size":160330//已上传文件的大小
}
*/

if(!is_dir("./upload")) {
	mkdir("./upload",0777,true);
}

$datetime=date('Y-m-d H:i:s',time()); 
#保存文件
$tstable = explode(' ',$datetime);
$dirtable = explode('-',$tstable[0]);
if(!is_dir("./upload/".$dirtable[0])) {
	mkdir("./upload/".$dirtable[0],0777,true);
}
if(!is_dir("./upload/".$dirtable[0]."/".$dirtable[1])) {
	mkdir("./upload/".$dirtable[0]."/".$dirtable[1],0777,true);
}
if(!is_dir("./upload/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2])) { 
	mkdir("./upload/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2],0777,true);
}

if(strpos($_FILES["file"]["type"],'image') !== false){ 
	if(!is_dir("./upload/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2]."/pic")) { 
		mkdir("./upload/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2]."/pic",0777,true);
	}
	$dir = "upload/".$dirtable[0].'/'.$dirtable[1].'/'.$dirtable[2]."/pic/";
	$filename = str_replace(":","",$tstable[1]);
	if($_FILES["file"]["name"]=="image.png") {
		
		$_FILES["file"]["name"] = str_replace("image",$filename,$_FILES["file"]["name"]);
		
	} else {
		$_FILES["file"]["name"] = str_replace(".","_".$filename.".",$_FILES["file"]["name"]);
	} 
	
}else{
	if(!is_dir("./upload/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2]."/file")) { 
		mkdir("./upload/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2]."/file",0777,true);
	}
	$dir = "upload/".$dirtable[0].'/'.$dirtable[1].'/'.$dirtable[2]."/file/";
}

// 檢查文件是否已存在
if (file_exists($dir.$_FILES["file"]["name"])){
	file_put_contents("log.txt", "\n type:no", FILE_APPEND);
    echo $_FILES["file"]["name"] . "已存在";
}else{
    // 將零時文件保存
    move_uploaded_file($_FILES["file"]["tmp_name"],$dir.$_FILES["file"]["name"]);
	file_put_contents("log.txt", "\n type:yes", FILE_APPEND);
    echo $dir.$_FILES["file"]["name"];
}

?>

