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

if($json_array->smalledition=="Initialization") {
	$sql2= "SELECT max(id) from builds where  testplan_id in (SELECT id from nodes_hierarchy where node_type_id='5' and  parent_id in (SELECT DISTINCT testproject_id from testplans WHERE id='$json_array->id') ORDER BY id DESC);";	
	
} else {
	$sql2= "SELECT max(id) from builds where  name='".$json_array->smalledition."';";

}


$result =$mysqli->query($sql2);
$arrayrow=$result->fetch_row();
$build_id=$arrayrow[0];

$sql= "SELECT tcversion_id from testplan_tcversions where testplan_id='$json_array->id' ORDER BY id ASC;";
$result =$mysqli->query($sql);
$array=$result->fetch_all();

if(count($array)<1) {
	
	$res = array(
	'state' => "empty"
	);
} else {
	
	$res['state'] = "full";
	$res['mainlist'] = array();
	for($i=0;$i<count($array);$i++)
	{
		
		if($array[$i]){//如果数据存在输出数据
			$arr=$array[$i];
			
			$automatic_sql= "SELECT t.execution_ts,t.end_ts,t.status,t.log,t.details,t.notes,t.id FROM ( SELECT * FROM executions where  build_id='".$build_id."' and tcversion_id ='".$arr[0]."' and execution_type='2' order by id) t order by t.execution_ts DESC;";

			$result =$mysqli->query($automatic_sql);
			$array2=$result->fetch_all();
			
			if(count($array2)>0  ) {
				
				$arr2=$array2[0];
				$mainlist['startime'] = $arr2[0];
				$mainlist['endtime'] = $array2[0][1];
				$mainlist['Test_result'] = $array2[0][2];
				$mainlist['log'] = $array2[0][3];
				$mainlist['Detail'] = $array2[0][4];
				$mainlist['notes'] = $array2[0][5];
				$mainlist['id'] = $array2[0][6];
				
				
			} else {
				$mainlist['startime'] = '';
				$mainlist['endtime'] = '';
				$mainlist['Test_result'] = '';
				$mainlist['log'] = '';
				$mainlist['Detail'] = '';
				$mainlist['notes'] = '';
				$mainlist['id'] = '';
			}
			
			
			
			$sql= "SELECT id,name from nodes_hierarchy where  node_type_id='3' and id in (SELECT parent_id from nodes_hierarchy where id='$arr[0]') ORDER BY id ASC;;";
			
			
			$result =$mysqli->query($sql);
			$array1=$result->fetch_all();
			for($k=0;$k<count($array1);$k++)
			{
				
				if($array1[$k]){//如果数据存在输出数据
					$arr1=$array1[$k];
					
					$mainlist['Case_numbe'] = $arr1[0];
					$mainlist['Case_name'] = $arr1[1];
					
					array_push($res['mainlist'],$mainlist);
					
					
				}
			}
			
		}
	}
}

$mysqli->close();
// 排序
if($res['state'] == "full")	{
	foreach ( $res['mainlist'] as $key => $row ){
	  $id[$key] = $row ['Case_numbe'];
	  $name[$key] = $row ['Case_name'];
	}
	array_multisort($name, SORT_ASC, $id, SORT_ASC, $res['mainlist']);
}

$txt =json_encode($res,JSON_UNESCAPED_UNICODE);

exit($txt);//把结果反馈给客户端



?>
