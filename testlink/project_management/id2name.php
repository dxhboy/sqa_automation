<?php
# read all task list from DB according project id

$config = require('db_config.php');
require './db_base.php';

$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);


$id = $json_str[0];
$type = $json_str[1];
//print_r($id);
//print_r($type);

$table = $config[$type];


$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();

$array = [
	"id" => $id,
];


if ($a->select($array, $table)) {
	die('Select fail');
};


$json_taskinfo = $a->json_data;

exit($json_taskinfo)


		



?>