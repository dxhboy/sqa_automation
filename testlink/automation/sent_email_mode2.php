<?php
	
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}
require './libs/sent_email.php';

$postStr = file_get_contents("php://input"); #获取POST数据
$json_array=json_decode($postStr);



# 获取测试者，版本
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
mysqli_query($mysqli,"SET NAMES utf8");
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
}
session_start();
$tester = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;		
$sql1= "SELECT t.name,u.last,u.first from builds t,users u where t.id=".$json_array->smalledition." and u.id=".$tester.";";
$result =$mysqli->query($sql1);
$tester_name=$result->fetch_all();

#
if ($json_array->email_flag) {
	$from = $json_array->email_from;
	$pwd = $json_array->email_pwd;
	$to = $json_array->email_to;
} else {
	$table = explode('s_',$json_array->table);
	$email_info = Get_Email_from_pwd_to($table[0], $json_array->edition, $json_array->environment);
	$from = $email_info["from"];
	$pwd = $email_info["pwd"];
	$to = $email_info["to"];
}
	
# 发邮件
if($json_array->sentmail=="on") {
	
	$status = SendEmail(
		$tester_name=$tester_name, 
		$email_from=$from, 
		$email_pwd=$pwd, 
		$email_to=$to, 
		$resultlist=$json_array->all_result_info_for_email,
		$starttime=$json_array->starttime,
		$endtime=$json_array->endtime, 
		$edition=$json_array->edition,
		$environment=$json_array->environment, 
		$project=$_SESSION['testprojectName'],
		$total=$json_array->total_num, 
		$pass=$json_array->pass_num, 
		$fail=$json_array->fail_num, 
		$abort=$json_array->abort_num, 
		$cancel=$json_array->cancel_num
	);
}

echo('Email result:');
$txt =json_encode($status,JSON_UNESCAPED_UNICODE);
exit($txt);

