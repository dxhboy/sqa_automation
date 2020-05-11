<?php
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}
	
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$sql= "SELECT id,name from nodes_hierarchy where node_type_id='1' and id in (SELECT id from testprojects where active='1') ORDER BY id ASC;";

$result =$mysqli->query($sql);

$array=$result->fetch_all();
$res['testproject'] = array();

for($i=0;$i<count($array);$i++)
{
	
	if($array[$i]){//如果数据存在输出数据
		
		$arr=$array[$i];
		$sql= "SELECT t.id,b.name from testplans t,nodes_hierarchy b WHERE t.active='1'  and t.testproject_id='".$arr[0]."' and t.id=b.id ORDER BY id ASC;";
		
		$result =$mysqli->query($sql);
		$array1=$result->fetch_all();
		
		
		$testproject[$arr[0]] = array();
		
		
		
		$records['prname'] = $arr[1];
		
		for($j=0;$j<count($array1);$j++) {
			if($array1[$j]){//如果数据存在输出数据
			
				$arr1=$array1[$j];
				$tsname=str_replace("<p>"," ",$arr1[1]);
				$arr1[1]=str_replace("</p>"," ",$tsname);
				
				
				$records[$arr1[0]] = $arr1[1];
				
				
			}
		}
		
		
	}
	array_push($testproject[$arr[0]],$records);
	unset($records);
}
array_push($res['testproject'],$testproject);
$mysqli->close();	

$txt =json_encode($res,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端



?>