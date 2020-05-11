<?php

$config = require('db_config.php');
require './db_base.php';

$postStr = file_get_contents("php://input"); //获取POST数据
$json_array=json_decode($postStr);

$mysqli = new mysqli($config['host'], $config['user'], $config['passwd'], $config['db']);

mysqli_query($mysqli,"SET NAMES utf8");


if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

//$sql = "select m.parent_id,m.id,m.task,m.progress from (select t.staff_id,b.name as staff,a.id as projectid,a.`name` as project,t.`name` as task,t.parent_id as tasktitle,t.id,t.status,t.pc as progress,t.parent_id from pmj_task t LEFT JOIN pmj_personnel b on b.id=t.staff_id LEFT JOIN pmj_project a on a.id=t.project_id LEFT JOIN pmj_task r on t.parent_id=r.id where r.parent_id is not null ORDER BY  r.project_id ) m  where m.staff is not null;";

$sql = vsprintf("
	SELECT t.id, 
			t.name, 
			t.parent_id, 
			t.project_id, 
			proj.name as 'proj_name', 
			pers.name as 'person_name', 
			t.pc
	FROM %s t
	INNER JOIN %s pers 
	ON t.staff_id = pers.id
	INNER JOIN %s proj 
	ON t.project_id = proj.id
	ORDER BY staff_id
	", array($config['task'], $config['personnel'], $config['project']));

$result =$mysqli->query($sql);

$array1=$result->fetch_all();
$subtask = array();

for($k=0;$k<count($array1);$k++) {
	$arr1=$array1[$k];
	
	$person_name = $arr1[5];
	$proj_name = $arr1[4];
	$proj_id = $arr1[3];
	
	$task_info = array();
	$task_info['name'] = $arr1[1]."(完成".$arr1[6]."%)";
	$task_info['id'] = $arr1[0];
	$task_info['parent_id'] = $arr1[2];
	$task_info['proj_id'] = $proj_id;
	

	//项目id == $json_array->projectid，当前选择的项目
	if ($proj_id == $json_array->projectid) {
		$proj_name = 'current_'.$proj_name.'_'.$proj_id;
	} else {
		$proj_name = $proj_name.'_'.$proj_id;
	}
	
	if(!array_key_exists($person_name,$subtask)){
		$subtask[$person_name] = array();
		$subtask[$person_name][$proj_name] = array();
		array_push($subtask[$person_name][$proj_name], $task_info);
	} else {
		if(!array_key_exists($proj_name,$subtask[$person_name])){
			$subtask[$person_name][$proj_name] = array();
			array_push($subtask[$person_name][$proj_name], $task_info);
		} else {
			array_push($subtask[$person_name][$proj_name], $task_info);
		}
	}
}

$txt =json_encode($subtask,JSON_UNESCAPED_UNICODE);

echo("xxx For split a string xxx");

exit($txt);//把结果反馈给客户端

?>

