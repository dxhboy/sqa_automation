<?php
# read all task list from DB according project id



$config = require('db_config.php');
require './db_base.php';

$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);

$mysqli = new mysqli($config['host'], $config['user'], $config['passwd'], $config['db']);

mysqli_query($mysqli,"SET NAMES utf8");


if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}



//$_POST['group_id'] = "1";

if ($json_str != '') {
	$group_reg1 = "',".$json_str."$'";    // ,3
	$group_reg2 = "'^".$json_str.",'";   // 3,
	$group_reg3 = "',".$json_str.",'";   //  ,3,
	$sql = "select * from ".$config['personnel']." where
			group_id = ".$json_str." or 
			group_id regexp ".$group_reg1." or 
			group_id regexp ".$group_reg2." or 
			group_id regexp ".$group_reg3;
} else {
	$sql = "select * from ".$config['personnel'];
}

//print_r($sql);
$result =$mysqli->query($sql);

$array=$result->fetch_all();

$txt =json_encode($array,JSON_UNESCAPED_UNICODE);


exit($txt);



?>