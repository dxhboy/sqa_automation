<?php
# add project

require './task_manage_system.php';




$b = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);
$b->connect();

if ($_POST['project_id']=="") {
	//添加项目
	if ($b->write_project()) {
		$result = "失败";
	} else {
		$result = "成功";
	}
} else {
	//修改项目
	$condition = [
		"id" => $_POST['project_id'],
	];
	
	$set = [
		"name" => $_POST['project_name'],
		"notes" => $_POST['project_notes'],
		"longtime" => $_POST['longtime'],
		"picpath" => $_POST['picpath'],
		"filepath" => $_POST['filepath'],
	];
	
	if(isset($_POST['project_starttime'])) {
		
		$set["start_time"]=$_POST['project_starttime'];
	}
	if(isset($_POST['project_endtime'])) {
		$set["est_com_time"]=$_POST['project_endtime'];
	}
	if(isset($_POST['project_status'])) {
		$set["status"]=$_POST['project_status'];
	}
	
	if ($b->update($set, $condition, $config['project'])) {
		$result = "失败";
	} else {
		$result = "成功";
	}
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

