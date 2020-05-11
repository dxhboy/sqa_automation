<?php
@include_once('../../config_db.inc.php');
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

$index=1;
for($i=0;$i<count($json_array->subprocedure);$i++) 
{
	$id=$json_array->subprocedure[$i];
	$sql= "UPDATE tcsteps SET step_number='$index' WHERE id ='$id' and parent_id='$json_array->id';";
    $result =$mysqli->query($sql);
	$index++;
	// file_put_contents("test.txt", $sql, FILE_APPEND);
	// file_put_contents("test.txt", "\n", FILE_APPEND);
}
$output = array(
	'smallStepList' => "ok"
);
$mysqli->close();
$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
exit($txt);//把结果反馈给客户端
	


?>