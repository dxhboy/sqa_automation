<?php
@include_once('../../config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$postStr = file_get_contents("php://input"); //获取POST数据
$json_array=json_decode($postStr);
file_put_contents("test.txt", $postStr, FILE_APPEND);
	
session_start();
$currentcaseid = $_SESSION['currentcaseid'];

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$sql= "SELECT id from nodes_hierarchy where parent_id='$currentcaseid';";
$result =$mysqli->query($sql);

$array=$result->fetch_all();
$arr=$array[0];
$parent_id = $arr[0];

$sql= "SELECT MAX(step_number) from nodes_steps where parent_id='$parent_id';";
$result =$mysqli->query($sql);

$array=$result->fetch_all();
$arr=$array[0];
$step_number = $arr[0];
$step_number++;
$sql= "INSERT into nodes_steps (`name`,`parent_id`,`step_number`,`locked`,`is_delete`) VALUES ( 'initi_check','$parent_id','$step_number','false','false');";
$result =$mysqli->query($sql);

$mysqli->close();	
$output = array(
	'smallStepList' => "ok"
);

$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
exit($txt);//把结果反馈给客户端



?>