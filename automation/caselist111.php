<?php
@include_once('config_db.inc.php');
if( !defined('DB_HOST') ) {
	define('DB_HOST','localhost' );
}

$postStr = file_get_contents("php://input"); //获取POST数据

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
	// file_put_contents("log.txt", "$sql\n", FILE_APPEND);
	$result =$mysqli->query($sql);
	$array=$result->fetch_all();
	$id=$array[0][0];
	// file_put_contents("log.txt", "array：$id\n", FILE_APPEND);
	if($id==0){
		// $sql= "SELECT tcversion_id from testplan_tcversions where testplan_id='$json_array->id' ORDER BY id ASC;";
		$sql= "SELECT li.id,li.name,ll.tool from (SELECT t.id,t.name,b.id as parent_id from nodes_hierarchy t,nodes_hierarchy b where  t.node_type_id='3' and t.id=b.parent_id and  b.node_type_id='4' and b.id in (SELECT tcversion_id from testplan_tcversions  where testplan_id=".$json_array->id.")) as li  LEFT JOIN  
		(SELECT d.parent_id as id,lower(replace(SUBSTRING_INDEX(c.actions, ' ', 2),'run ','')) as tool from tcsteps c,nodes_steps d where  c.automation_type=6 and c.parent_id=d.id and d.parent_id in (SELECT tcversion_id from testplan_tcversions  where testplan_id=".$json_array->id.")) as ll ON li.parent_id = ll.id GROUP BY
			li.id order by substring_index(li.name,'.',1)+0,substring_index(substring_index(li.name,'.',-2),'.',1)+0,substring_index(li.name,'.',-1)+0 ASC;";
		// file_put_contents("log.txt", "sql: $sql\n", FILE_APPEND);
	} else {
		$sql= "SELECT li.id,li.name,ll.tool from (SELECT t.id,t.name,b.id as parent_id from nodes_hierarchy t,nodes_hierarchy b where  t.node_type_id='3' and t.id=b.parent_id and  b.node_type_id='4' and b.id in (SELECT tcversion_id from testplan_tcversions  where testplan_id=".$json_array->id.")) as li  LEFT JOIN  
		(SELECT d.parent_id as id,lower(replace(SUBSTRING(c.actions,locate('run',c.actions),locate('<\/',c.actions)-locate('run',c.actions)),'run ','')) as tool from tcsteps c,nodes_steps d where  c.automation_type=6 and c.parent_id=d.id and d.parent_id in (SELECT tcversion_id from testplan_tcversions  where testplan_id=".$json_array->id.")) as ll ON li.parent_id = ll.id GROUP BY
			li.id order by substring_index(li.name,'.',1)+0,substring_index(substring_index(li.name,'.',-2),'.',1)+0,substring_index(li.name,'.',-1)+0 ASC;";
	
		// file_put_contents("log.txt", "sql11: $sql\n", FILE_APPEND);
	}
	 
	$result =$mysqli->query($sql);
	$array=$result->fetch_all();

	if(count($array)<1) {
		
		$res = array(
		'state' => "empty"
		);
	} else {
		
		
		$res['state'] = "full";
		$res['prefix'] = $prefix;
		$res['mainlist'] = array();
		for($i=0;$i<count($array);$i++)
		{
			
			if($array[$i]){//如果数据存在输出数据
				$arr=$array[$i];
				
				$automatic_sql= "SELECT t.execution_ts,t.end_ts,t.status,t.log,t.details,t.notes,t.id FROM ( SELECT * FROM executions where  build_id='".$build_id."' and tcversion_id in (SELECT id from nodes_hierarchy where node_type_id='4' and parent_id=".$arr[0]." ) and execution_type='2' order by id) t order by t.execution_ts DESC;";
				
				$result =$mysqli->query($automatic_sql);
				$array2=$result->fetch_all();
				
				if(count($array2)>0  ) {
					
					
					$mainlist['startime'] = $array2[0][0];
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
				
				$mainlist['Case_numbe'] = $arr[0];
				$mainlist['Case_name'] = $arr[1];
				
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

	if (!array_key_exists('state', $res)) {
		$res = array(
		'state' => "empty"
		);
	}
	$txt =json_encode($res,JSON_UNESCAPED_UNICODE);
	// file_put_contents("log.txt", "$txt\n", FILE_APPEND);
	exit($txt);//把结果反馈给客户端

}

?>
