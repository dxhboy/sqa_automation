<?php
$config = require('db_config.php');
require './db_base.php';

$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);

$name = $json_str[0];
$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();

$array = [
  "name" => $name,
];
$table = 'pmj_name2email';
if ($a->select($array, $table)) {
  die('Select fail');
};

$json_taskinfo = $a->json_data;

exit($json_taskinfo)

?>
