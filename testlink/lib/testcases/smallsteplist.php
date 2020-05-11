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


$bigstepid = $json_array->bigStepId;

$sql= "SELECT a.id,a.name as title,a.is_delete,a.locked from nodes_steps a where  a.id='$bigstepid' ;";
$result =$mysqli->query($sql);
$array=$result->fetch_all();
$arr=$array[0];

$res['id'] = $arr[0];
$res['title'] = $arr[1];
$isDeleted=$arr[2];
if($arr[2]=="true") {
	$res['isDeleted'] = true;
} else {
	$res['isDeleted'] = false;
}
if($arr[3]=="true") {
	$res['locked'] = true;
} else {
	$res['locked'] = false;
}



$res['records'] = array();
$res['subprocedure'] = array();

//查询数据库
$sql= "SELECT t.id,t.actions AS title,t.expected_results AS content,t.spawn_id,t.checked,t.display,t.isDeleted,t.step_number,b.id as automation_type,b.name from tcsteps t,automation_tcsteps b where t.parent_id='$bigstepid' and t.automation_type=b.id ORDER BY step_number ASC ;";
$result =$mysqli->query($sql);
$array=$result->fetch_all();

for($i=0;$i<count($array);$i++)
{
	
	if($array[$i]){//如果数据存在输出数据
		$arr=$array[$i];
		
		$records['id'] = $arr[0];
		
		$records['title'] = $arr[1];
		
		$records['content'] = $arr[2];
		if($arr[4]=="true") {
			
			$records['checked'] =true;
		} else {
			$records['checked'] = false;
		}
		
		if($arr[5]=="true") {
			
			$records['display'] = true;
		} else {
			$records['display'] = false;
		}
		if($arr[6]=="true") {
			$records['isDeleted'] = true;
		} else {
			$records['isDeleted'] = false;
		}

		$records['spawn'] = $arr[3];
		$records['type'] = $arr[8];
		$records['label'] = $arr[9];
		array_push($res['records'],$records);
		array_push($res['subprocedure'],$arr[0]);
	}
}
$output = array(
	'smallStepList' => $res
);
$mysqli->close();
$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
exit($txt);//把结果反馈给客户端
	


?>