<?php
require './task_manage_system.php';
$obj = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);
$id = $_POST['selected_id'];
$parent_id  = $_POST['parent_id'];
$all_pid = array();

function get_all_parent_id($parent_id) {
	global $obj;
	global $all_pid;
	if (!$obj->select('', '', 'select parent_id from pmj_task where id='.$parent_id)) {
		$a = json_decode($obj->json_data, true);
		$sub_parent_id = $a[0]['parent_id'];
		if (!$sub_parent_id) {
			array_push($all_pid, "null");
		} elseif ($sub_parent_id == "") {
			array_push($all_pid, "null");
		} else {
			array_push($all_pid, $sub_parent_id);
			get_all_parent_id($sub_parent_id);
		}
	}
	return;
}
# get all_pid array
get_all_parent_id($parent_id);

if (array_key_exists($id, array_flip($all_pid))) {
	echo "fail reset parent_id";
} else {
	$sql = "update pmj_task set parent_id=".$parent_id." where id=".$id;
	$obj->exec_sql($sql);
}
?>
