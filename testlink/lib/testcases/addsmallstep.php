<?php
@include_once('../../config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$postStr = file_get_contents("php://input"); //获取POST数据
$json_array=json_decode($postStr);

// $bigstepid = $json_array->bigStepId;
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}


$sql= "SELECT MAX(id) from tcsteps;";
$result =$mysqli->query($sql);

$array=$result->fetch_all();
$arr=$array[0];
$index_id = $arr[0];
$index_id++;

$sql= "INSERT into tcsteps (`id`,`actions`,`active`,`execution_type`,`parent_id`,`step_number`,`spawn_id`,`automation_type`) VALUES ( '$index_id','$json_array->title','1','1','$json_array->bigStepId','$index_id','exp','0');";
$result =$mysqli->query($sql);
	
//查询数据库
$sql= "SELECT MAX(id) from tcsteps ";
$result =$mysqli->query($sql);
$array=$result->fetch_all();

for($i=0;$i<count($array);$i++)
{
	
	if($array[$i]){//如果数据存在输出数据
		$arr=$array[$i];
		$records['smallStepId'] = $arr[0];
		$records['title'] = $json_array->title;

	}
}		
$mysqli->close();	
// $output = array(
	// 'smallStepList' => "ok"
// );

$txt =json_encode($records,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端



?>