<?php




$config = require('db_config.php');
require './db_base.php';

$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);


$id = $json_str[0];
$value = $json_str[1];


$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();


$condition = [
	"id" => $id,
];

if ($value == "finish") {
	$pc = 100;
	date_default_timezone_set('prc');
	$data=date('Y-m-d H:i',time());
	$act_com = $data;
} else {
	$pc = 0;
	$act_com = '';
}

$set = [
	"pc" => $pc,
	"status" => $value,
	"act_com_time" => $act_com,
];



if ($a->update($set, $condition, $config['task'])) {
	$result = "失败";
} else {
	$result = "成功";
}


?>

