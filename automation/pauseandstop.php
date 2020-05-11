<?php
	
$postStr = file_get_contents("php://input"); #获取POST数据
$json_array=json_decode($postStr);
$lockfile=str_replace(".tcf",".txt",$json_array->environment);
file_put_contents("./lock/".$lockfile, $json_array->operation);


$output = array(
	'staes' => "ok"
);

$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
exit($txt);#把结果反馈给客户端

?>