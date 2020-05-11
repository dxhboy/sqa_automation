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

$set = [
	"pc" => $value,
];


if ($a->update($set, $condition, $config['task'])) {
	$result = "失败";
} else {
	$result = "成功";
}


?>

