<?php
	//Import PHPMailer classes into the global namespace
	
	$path = $_REQUEST["path"];
	$id = $_REQUEST["task_id"];
	$table= $_REQUEST["table"];
	
	
	$config = require('db_config.php');
	require './db_base.php';
	$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
	$a->connect();
	$array = [
	  "id" => $id,
	];
	
	if ($a->select($array, $table)) {
		die('Select fail');
		$message = array('success'=>0,'errors'=>'删除数据库几楼失败');
		
	} else {
		
		$task = json_decode($a->json_data, true);
		
		$stekey="";
		if(array_key_exists('taskpicpath',$task[0])) {
			$where = $task[0]['taskpicpath'];
			
			$stekey="taskpicpath";
		} else {
			$where = $task[0]['picpath'];
			$stekey="picpath";
		}
		
		$pic = explode(',',$where); 
		$newwhere="";
		for($index=0;$index<count($pic);$index++){ 
			$taskpath=trim($pic[$index]);
			
			if(strlen($taskpath)<1 ){
				Continue;
			}
			if($taskpath==trim($path)) {
				Continue;
			} else {
				$newwhere.=",".$pic[$index];
			}
		} 
		$set=[$stekey => $newwhere];
		
		if ($a->update($set,$array, $table)) {
			$message = array('success'=>1,'errors'=>'删除图片或文件成功');
		} else {
			$message = array('success'=>0,'errors'=>'删除数据库几楼失败');
		}
	};
	
	echo  $message;
	/**
	* 删除图片或文件
	* @author huxinde
	* @param string $pic 图片或文件地址
	*/
	function delpic($pic){
		if($pic){
			$url1=getcwd ();
			
			
			$picpatha = explode('/',$pic);
			$url="";
			for($index=0;$index<count($picpatha);$index++){ 
				if($index==0) {
					$url=$url1. DIRECTORY_SEPARATOR .$picpatha[$index];
				} else {
					$url=$url. DIRECTORY_SEPARATOR .$picpatha[$index];
				}
			}	
			
			if(file_exists($url)){
				$res = unlink($url);
				
				if($res){
					
					$message = array('success'=>1,'errors'=>'删除图片成功');
				}else{
					$message = array('success'=>0,'errors'=>'操作失误导致图片或文件无法删除');
				}
			}else{
				$message = array('success'=>404,'errors'=>'无法找到文件或者已经删除');
			}
			
		}else{
			$message = array('success'=>404,'errors'=>'请传送正确图片或文件地址');
		}
		
		return $message;
	}
?>