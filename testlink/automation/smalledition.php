<?php

$dirpath=dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config_db.inc.php';
@include_once($dirpath);
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}
	
$postStr = file_get_contents("php://input"); 

$json_array=json_decode($postStr);
if($json_array->testplanID=="Initialization") {
	session_start();

	$testplanID = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : 0;
} else {
	$testplanID = $json_array->testplanID;
}

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}




$smalledition = array();








$sql= "SELECT id,name from builds where testplan_id=$testplanID and active=1 ORDER BY creation_ts DESC;";


$result =$mysqli->query($sql);
$array2=$result->fetch_all();
if(count($array2)<1) {
	
	$smalledition['No'] = 'No data';
} else {
	
	for($i=0;$i<count($array2);$i++)
	{
		
		if($array2[$i]){//如果数据存在输出数据
			$arr=$array2[$i];
			$smalledition[$arr[0]] = $arr[1];
			
		}
	}
}
$mysqli->close();	



$txt =json_encode($smalledition,JSON_UNESCAPED_UNICODE);
	


exit($txt);//把结果反馈给客户端



?>