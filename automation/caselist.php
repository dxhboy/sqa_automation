<?php
	
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$postStr = file_get_contents("php://input"); 

$json_array=json_decode($postStr);

	
$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

mysqli_query($mysqli,"SET NAMES utf8");
try { 
	if ($mysqli->connect_error) {
		die('Connect Error (' . $mysqli->connect_errno . ') '
				. $mysqli->connect_error);
	}
	
	$sql= "SELECT t.prefix from testprojects t LEFT JOIN nodes_hierarchy b on t.id=b.parent_id where b.id= ".$json_array->id.";";
	$result =$mysqli->query($sql);
	$arrayrow=$result->fetch_row();
	$prefix=$arrayrow[0];
	
	if($json_array->smalledition=="Initialization") {
		$sql2= "SELECT max(id) from builds where  testplan_id in (SELECT id from nodes_hierarchy where node_type_id='5' and  parent_id in (SELECT DISTINCT testproject_id from testplans WHERE id='$json_array->id') ORDER BY id DESC);";	
		
	} else {
		$sql2= "SELECT max(id) from builds where  name='".$json_array->smalledition."';";

	}


	$result =$mysqli->query($sql2);
	$arrayrow=$result->fetch_row();
	$build_id=$arrayrow[0];
	
	$sql="SELECT locate('<\/',c.actions) as num from tcsteps c,nodes_steps d where  c.automation_type=6 and c.parent_id=d.id and d.parent_id in (SELECT tcversion_id from testplan_tcversions  where testplan_id=".$json_array->id.");";

	$result =$mysqli->query($sql);
	$array=$result->fetch_all();
	$id=$array[0][0];
	
	// $automatic_sql= "SELECT r.id,r.name,r.tool,e.execution_ts,e.end_ts,e.status,e.log,e.details,e.notes,e.id FROM (SELECT li.id,li.name,ll.tool,li.tcversion_id from 
	// (SELECT c.*,t.id as tcversion_id FROM nodes_hierarchy c INNER JOIN nodes_hierarchy t on c.id=t.parent_id LEFT JOIN testplan_tcversions te on t.id=te.tcversion_id where te.testplan_id=".$json_array->id." ) as li LEFT 
	// JOIN (SELECT d.id as id,lower(replace(SUBSTRING_INDEX(c.actions, ' ', 2),'run ','')) as tool from tcsteps c LEFT JOIN nodes_steps d on c.parent_id=d.id  where  
	// c.automation_type=6 ) as ll ON li.parent_id = ll.id GROUP BY
	// li.id ) as r LEFT JOIN  (SELECT * FROM executions col WHERE col.execution_ts in (SELECT max(execution_ts)FROM executions where 
	// build_id='".$build_id."' and execution_type='2' GROUP BY tcversion_id) order by execution_ts desc) e on e.tcversion_id=r.tcversion_id GROUP BY r.id order by substring_index(r.name,'.',1)+0,substring_index(substring_index(r.name,'.',-2),'.',1)+0,substring_index(r.name,'.',-1)+0 ASC;";
	
	$automatic_sql= "SELECT r.id,r.name,r.tool,e.execution_ts,e.end_ts,e.status,e.log,e.details,e.notes,e.id FROM (SELECT li.id,li.name,ll.tool,li.tcversion_id from 
	(SELECT c.*,t.id as tcversion_id FROM nodes_hierarchy c INNER JOIN nodes_hierarchy t on c.id=t.parent_id LEFT JOIN testplan_tcversions te on t.id=te.tcversion_id where te.testplan_id=".$json_array->id." ) as li LEFT 
	JOIN (SELECT d.id as id,lower(replace(SUBSTRING_INDEX(c.actions, ' ', 2),'run ','')) as tool from tcsteps c LEFT JOIN nodes_steps d on c.parent_id=d.id  where  
	c.automation_type=6 ) as ll ON li.parent_id = ll.id GROUP BY
	li.id ) as r LEFT JOIN  (SELECT t.* FROM (select * from executions order by execution_ts desc limit 10000000000) t  where t.build_id='".$build_id."' and t.execution_type='2'
	GROUP BY t.tcversion_id) e on e.tcversion_id=r.tcversion_id GROUP BY r.id order by substring_index(r.name,'.',1)+0,substring_index(substring_index(r.name,'.',-2),'.',1)+0,substring_index(r.name,'.',-1)+0 ASC;";
	
	// file_put_contents("log.txt", "automatic_sql:\n $automatic_sql \n", FILE_APPEND);
	$result =$mysqli->query($automatic_sql);
	$array=$result->fetch_all();
	$res = array();
	$res['state'] = "empty";
	if(count($array)>0) {
		
		
		$res['state'] = "full";
		$res['prefix'] = $prefix; 
		$res['mainlist'] = array();
		for($i=0;$i<count($array);$i++)
		{
			
			if($array[$i]){//如果数据存在输出数据
				$arr=$array[$i];
				
				$mainlist['Case_numbe'] = $arr[0];
				$mainlist['Case_name'] = $arr[1];
				$mainlist['startime'] = $arr[3];
				$mainlist['endtime'] = $arr[4];
				$mainlist['Test_result'] = $arr[5];
				$mainlist['log'] = $arr[6];
				$mainlist['Detail'] = $arr[7];
				$mainlist['notes'] = $arr[8];
				$mainlist['id'] = $arr[9];
				
				$tool=(string)$arr[2];
				$tool=trim($tool);
				
				if(strlen($tool)>0) {
					$mainlist['tooltype'] = $tool;
				}  else {
					$mainlist['tooltype'] = 'other';
				}			
				array_push($res['mainlist'],$mainlist);

			}
		}
	}

	$mysqli->close();
} finally {

	
	$txt =json_encode($res,JSON_UNESCAPED_UNICODE);
	// file_put_contents("log.txt", "$txt\n", FILE_APPEND);
	exit($txt);//把结果反馈给客户端

}

?>
