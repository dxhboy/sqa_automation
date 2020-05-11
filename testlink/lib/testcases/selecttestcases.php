<?php
// @include_once('config_db.inc.php');
// if( !defined('DB_HOST') ) {
	// define('DB_HOST','localhost' );
// }

$mysql_conn = mysqli_connect("localhost","root","casa1234","testlink");
// $mysql_conn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
if (mysqli_connect_errno($mysql_conn)) 
{ 
	echo "连接 MySQL 失败: " . mysqli_connect_error(); 
} 
$sql= "SELECT name from nodes_hierarchy where node_type_id='3';";//sql语句
$result = mysqli_query($mysql_conn,$sql)or die(mysqli_error($mysql_conn));
$num=1;	
while($row = mysqli_fetch_array($result))
  {
	echo "<option value=$num >$row[name]</option>";
   
	$num=$num+1;
	
  }
?>