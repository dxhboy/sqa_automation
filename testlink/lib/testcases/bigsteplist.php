<?php
@include_once('../../config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
$output = array();

session_start();
$currentcaseid = $_SESSION['currentcaseid'];
// $currentcaseid = '83';
// if (empty($currentcaseid)) {
    // $output = array('data'=>NULL, 'info'=>'this is null!', 'stats'=>1);
    // exit(json_encode($output));
// }
// if ($user_by == 'bigsteplist') {//调用获取用户信息的接口
//查询数据库
$sql= "SELECT name from nodes_hierarchy where id='$currentcaseid';";//获取对应用例父节点
$result =$mysqli->query($sql);

$array=$result->fetch_all();
$arr=$array[0];
$res['title'] = $arr[0];


$sql= "SELECT t.id,t.name,t.step_number,t.locked from nodes_steps t where t.parent_id in (SELECT id from nodes_hierarchy where parent_id='$currentcaseid') and t.is_delete='false'  ORDER BY step_number ASC ;";//获取对应用例父节点
$result =$mysqli->query($sql);
$array=$result->fetch_all();

$res['records'] = array();
$res['caseId'] = $currentcaseid;
$res['procedure'] = array();

for($i=0;$i<count($array);$i++)
{
	
	if($array[$i]){//如果数据存在输出数据
		$arr=$array[$i];
		
		$sql= "SELECT COUNT(1) as count from tcsteps where parent_id='$arr[0]';";//获取对应用例父节点
		$result =$mysqli->query($sql);
		$countarr=$result->fetch_row();
		
		$records['id'] = $arr[0];
		
		// $records['content'] = $arr[3];
		$records['title'] = $arr[1];
		$records['count'] = $countarr[0];
		
		if($arr[3]=="true") {
			$records['locked'] = true;
		} else {
			$records['locked'] = false;
		}
		array_push($res['records'],$records);
		array_push($res['procedure'],$arr[0]);
	}
}


$mysqli->close();
$txt =json_encode($res,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端
	


?>