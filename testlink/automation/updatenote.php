<?php
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$postStr = file_get_contents("php://input"); //获取POST数据

$json_array=json_decode($postStr);
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

 $sql= "UPDATE executions SET notes='$json_array->notes' WHERE id ='$json_array->id';";	

 $result =$mysqli->query($sql);

$mysqli->close();	
$output = array(
	'states' => "ok"
);

$txt =json_encode($output,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端



?>