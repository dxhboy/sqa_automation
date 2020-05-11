<?php
	
	@include_once('config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}

	$postStr = file_get_contents("php://input"); #获取POST数据
	$json_array=json_decode($postStr);
	
	
	$envir = $json_array->environment;
	$table = $json_array->table;
	$server_ip = explode('s_', $table)[0];
	

		
	$command = "kill -9 $(ps -ef | grep '".$envir."'|grep -v grep|awk '{print $2}')";
	$connection = ssh2_connect($server_ip, 22);


	if(ssh2_auth_password($connection, 'root', 'casa')) {
		$stream = ssh2_exec($connection, $command);
		
		exit('stop all case success');

	} else {
		$result="Fail to login " .$server_ip ." with root casa. \n";
		exit($result);
	}
		


	




?>