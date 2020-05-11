<?php
$dirpath=dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config_db.inc.php';

@include_once($dirpath);
// @include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}
session_start();
$tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;	
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$sql= "SELECT t.id,b.name from testplans t,nodes_hierarchy b WHERE t.active='1'  and t.testproject_id=$tproject_id and t.id=b.id ORDER BY id ASC;";
	
$result =$mysqli->query($sql);
$array=$result->fetch_all();

for($i=0;$i<count($array);$i++)
{
	
	if($array[$i]){//如果数据存在输出数据
		
		$arr=$array[$i];
		$res[$arr[0]] = $arr[1];
		
	}
}

$mysqli->close();	

$txt =json_encode($res,JSON_UNESCAPED_UNICODE);

// file_put_contents("log.txt",  $txt."\n", FILE_APPEND);

exit($txt);//把结果反馈给客户端



?>