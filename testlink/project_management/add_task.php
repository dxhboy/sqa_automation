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



/* $info = $a->define_array($config['task']);

if ($a->get_next_id($config['task'])) {
	die('Error:get_next_id');
} else {
	$info['id'] = $a->next_id;
	$info['name'] = $_POST['task_name'];
	$info['notes'] = $_POST['task_notes'];
	$info['parent_id'] = $_POST['task_parent_id'];
	$info['project_id'] = $_POST['project'];
	$info['group_id'] = $_POST['task_group'];
	$info['staff_id'] = $_POST['task_person'];
	$info['status'] = $_POST['task_status'];
	$info['start_time'] = $_POST['task_starttime'];
	$info['est_com_time'] = $_POST['task_endtime'];
	
} */


if ($a->write_task()) {
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
<meta http-equiv = "refresh"   content ="0.1;  url =<?php echo($url) ?>">    
</head>    
<body>    
添加项目<?php echo($result) ?>，1秒后跳转……   
</body>  
</html>