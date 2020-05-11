<?php
# read all task list from DB according project id

$config = require('db_config.php');
require './db_base.php';

$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);

$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();

$array = [];


if ($a->select($array, $config['groups'])) {
	die('Select fail');
};


$json_task = $a->json_data;
	

exit($json_task);

?>