<?php
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);
	
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

if($json_str=="Initialization") {
	$sql= "SELECT name,filename,exemv,testtable,main from environmental where project in (SELECT min(project) from environmental)  ORDER BY id ASC;";	
	
	
} else {
	$sql= "SELECT name,filename,exemv,testtable,main from environmental where project in (SELECT DISTINCT testproject_id from testplans WHERE id='$json_str') ORDER BY id ASC;";

}

$result =$mysqli->query($sql);

$array=$result->fetch_all();

$environmental = array();
$res['environmental'] = array();
$res['edition'] = array();
$res['tester'] = array();
$res['smalledition'] = array();
$res['environmentalall'] = array();

if(count($array)<1) {
	
	
	$environmental['No'] = 'No data';
	$environmentalall['No'] = 'No data';
} else {
	
	for($i=0;$i<count($array);$i++)
	{
		
		if($array[$i]){//如果数据存在输出数据
			$arr=$array[$i];
			$environmental[$arr[1]] = $arr[0];
			$environmentalall[$arr[1]] = $arr[2].'s_'.$arr[3].'s_'.$arr[4];
		}
	}
}


$sql= "SELECT name from edition  ORDER BY id ASC;";

$result =$mysqli->query($sql);
$array1=$result->fetch_all();

if(count($array1)<1) {
	// $res = array(
	// 'state' => "empty"
	// );
	$edition['No'] = 'No data';
} else {
	
	for($i=0;$i<count($array1);$i++)
	{
		
		if($array1[$i]){//如果数据存在输出数据
			$arr=$array1[$i];
			$edition[$i] = $arr[0];
			
		}
	}
}

$sql= "SELECT id,login from users where id>1 and active=1;";

$result =$mysqli->query($sql);
$array1=$result->fetch_all();

if(count($array1)<1) {
	
	$tester['No'] = 'No data';
} else {
	
	for($i=0;$i<count($array1);$i++)
	{
		
		if($array1[$i]){//如果数据存在输出数据
			$arr=$array1[$i];
			
			$tester[$arr[0]] = $arr[1];
			
		}
	}
}


if($json_str=="Initialization") {
	$sql= "SELECT id,name from builds where testplan_id in (SELECT id from nodes_hierarchy where node_type_id='5' and parent_id in (SELECT min(project) from environmental));";

} else {
	$sql= "SELECT id,name from builds where testplan_id in (SELECT id from nodes_hierarchy where node_type_id='5' and parent_id in (SELECT DISTINCT testproject_id from testplans WHERE id='$json_str'));";
	// $sql= "SELECT id,name from builds where testplan_id ='$json_str';";
	// file_put_contents("log.txt", "sql$sql \n", FILE_APPEND);
}
// $sql= "SELECT id,name from builds where testplan_id ='$json_str';";
// file_put_contents("log.txt", "sql$sql \n", FILE_APPEND);
$result =$mysqli->query($sql);
$array2=$result->fetch_all();
if(count($array2)<1) {
	
	$smalledition['No'] = 'No data';
} else {
	
	for($i=0;$i<count($array2);$i++)
	{
		
		if($array2[$i]){//如果数据存在输出数据
			$arr=$array2[$i];
			$smalledition[$arr[0]] = $arr[1];
			
		}
	}
}
$mysqli->close();	

array_push($res['environmental'],$environmental);
array_push($res['tester'],$tester);
array_push($res['edition'],$edition);
array_push($res['smalledition'],$smalledition);
array_push($res['environmentalall'],$environmentalall);
$txt =json_encode($res,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端



?>