<?php
	
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}
require './libs/sent_email.php';

$postStr = file_get_contents("php://input"); #获取POST数据

$caselist=str_replace("[","(",$postStr);
$caselist=str_replace("]",")",$caselist);

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
mysqli_query($mysqli,"SET NAMES utf8");
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
}

$sql= "SELECT parent_id,id from nodes_hierarchy where parent_id in ".$caselist;
$result =$mysqli->query($sql);

$all_testcase_list=$result->fetch_all();


$all_testcase_array = array();
foreach($all_testcase_list as $list) {
	$all_testcase_array[$list[0]] = $list[1];
}

$testcase_array = array_unique($all_testcase_array);

$result = array();
foreach($testcase_array as $key => $value){
  array_push($result, $key);
}


$txt =json_encode($result,JSON_UNESCAPED_UNICODE);
exit($txt);

