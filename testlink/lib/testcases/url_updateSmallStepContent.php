<?php
@include_once('../../config_db.inc.php');
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

$expected_results = str_replace("'","\'",$json_array->content);

$sql= "UPDATE tcsteps SET actions='$json_array->title',expected_results='$expected_results',spawn_id='$json_array->spawn',automation_type='$json_array->type' WHERE id ='$json_array->smallStepId' and parent_id='$json_array->bigStepId';";
	
$result =$mysqli->query($sql);
	
$output = array(
	'smallStepList' => "ok"
);
$mysqli->close();	
$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
exit($txt);//把结果反馈给客户端



?>