<?php
# add task


/* $info['name'] = $_POST['task_name'];
$info['notes'] = $_POST['task_notes'];
$info['parent_id'] = $_POST['task_parent_id'];
$info['project_id'] = $_POST['project'];
$info['group_id'] = $_POST['task_group'];
$info['staff_id'] = $_POST['task_person'];
$info['status'] = $_POST['task_status'];
$info['start_time'] = $_POST['task_starttime'];
$info['est_com_time'] = $_POST['task_endtime'];


print_r($info)
 */

require_once './task_manage_system.php';


$a = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();


//print_r($_POST);
//Array ( 
// [project] => 2 
// [task_parent_id] => 21 
// [task_id] => 21 
// [task_name] => MME cli 
// [task_notes] => 说明 
// [task_group] => 2 
// [task_person] => 1 
// [task_status] => start 
// [task_pc] => 50 
// [task_starttime] => 2019-12-25 
// [task_endtime] => 2019-12-26 )

$condition = [
	"id" => $_POST['task_id'],
];

$set = [
	"name" => $_POST['task_name'],
	"notes" => $_POST['task_notes'],
	"group_id" => $_POST['task_group'],
	"staff_id" => $_POST['task_staff'],
	"start_time" => $_POST['task_starttime'],
	"est_com_time" => $_POST['task_endtime'],
	"status" => $_POST['task_status'],
	"taskpicpath" => $_POST['taskpicpath'],
	"taskfilepath" => $_POST['taskfilepath'],
];



if ($a->update($set, $condition, $config['task'])) {
	$result = "失败";
} else {
	$result = "成功";
}


$url = $_SERVER["HTTP_REFERER"];
echo("<hr/>");

?>

<!DOCTYPE html>
<html>    
<head>    
<meta http-equiv = "refresh"   content ="0.1, url =<?php echo($url) ?>">    
</head>    
<body>    

</body>  
</html>