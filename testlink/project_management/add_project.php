<?php
# add project

require './task_manage_system.php';


/* $a = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();

$a->write_project(); */

/* $info = $a->define_array($config['project']);
 */
/* if ($a->get_next_id($config['project'])) {
	die('Error:get_next_id');
} else {
	$info['id'] = $a->next_id;
	$info['name'] = $_POST['project_name'];
	$info['notes'] = $_POST['project_notes'];
	$info['status'] = $_POST['project_status'];
	$info['start_time'] = $_POST['project_starttime'];
	$info['est_com_time'] = $_POST['project_endtime'];
}
$sql = $a->create_sql($info, $config['project']);
if ($a->insert($sql)) {
	die('Error:insert');
} */



$b = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);
$b->connect();
if ($b->write_project()) {
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

</body>  
</html>