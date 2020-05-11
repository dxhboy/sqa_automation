<?php
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}
$postStr = file_get_contents("php://input"); //获取POST数据
$json_array=json_decode($postStr);

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
mysqli_query($mysqli,"SET NAMES utf8");
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
$table = explode('s_',$json_array->table);
	


$createtablesql="Create Table If Not Exists `".$table[1]."`(
`testplanid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`exeflag` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`testplanid`),
KEY `pid_m_nodeorder` (`testplanid`)
)ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;";
	
$result =$mysqli->query($createtablesql);

$sql="truncate table ".$table[1].";";
$result =$mysqli->query($sql);

$mysqli->close();

$output = array(
	'staes' => "ok"
);


$txt =json_encode($output,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端



?>