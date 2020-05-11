<?php
@include_once('../../config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

// $postStr = file_get_contents("php://input"); //获取POST数据
// $json_array=json_decode($postStr);
// file_put_contents("test.txt", $postStr, FILE_APPEND);
// file_put_contents("test.txt", "\n", FILE_APPEND);	


$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}


$sql= "SELECT t.id,t.name from automation_tcsteps t ORDER BY t.id ASC;";
$result =$mysqli->query($sql);

$array=$result->fetch_all();
$res['options'] = array();
for($i=0;$i<count($array);$i++)
{
	
	if($array[$i]){//如果数据存在输出数据
		$arr=$array[$i];
		$options['label'] = $arr[1];
		$options['value'] = $arr[0];
		array_push($res['options'],$options);
	}
}

$mysqli->close();	
$txt =json_encode($res,JSON_UNESCAPED_UNICODE);
exit($txt);//把结果反馈给客户端



?>