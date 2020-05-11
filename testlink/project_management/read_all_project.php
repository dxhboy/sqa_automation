<?php
# read project

$config = require('db_config.php');
require './db_base.php';

$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();

$array = [];


if ($a->select($array, $config['project'])) {
	die('Select fail');
};


$json_task = $a->json_data;
	

exit($json_task);
?>