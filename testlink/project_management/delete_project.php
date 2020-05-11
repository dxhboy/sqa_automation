<?php
# delete project



require_once './task_manage_system.php';


$postStr = file_get_contents("php://input"); //获取POST数据
$json_str=json_decode($postStr);

$a = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();




$condition = [
	"id" => $json_str,
];

if ($a->delete($condition, $config['project'])) {
	$result = "失败";
} else {
	$result = "成功";
}

$condition_task = [
	"project_id" => $json_str,
];

if ($a->delete($condition_task, $config['task'])) {
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
<meta http-equiv = "refresh"   content ="2;  url =<?php echo($url) ?>">    
</head>    
<body>    
删除项目<?php echo($result) ?>，2秒后跳转……   
</body>  
</html>