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


if($json_array->locked==false){
	$locked="false";
} else {
	$locked="true";
}
if($json_array->isDeleted==false){
	$is_deleted="false";
	// $sql= "UPDATE nodes_steps SET name='$json_array->title',automation_type='$json_array->type',locked='$locked',is_delete='$is_deleted' WHERE id ='$json_array->id' ;";
	$sql= "UPDATE nodes_steps SET name='$json_array->title',locked='$locked',is_delete='$is_deleted' WHERE id ='$json_array->id' ;";
} else {
	$sql= "DELETE from nodes_steps WHERE id ='$json_array->id' ;";
	$result =$mysqli->query($sql);
	$sql= "DELETE from tcsteps WHERE parent_id ='$json_array->id' ;";
}

$result =$mysqli->query($sql);

$output = array(
	'smallStepList' => "ok"
);
$mysqli->close();
$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
exit($txt);//把结果反馈给客户端
	


?>