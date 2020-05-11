<?php
	
	
	@include_once('config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}
	require './libs/sent_email.php';
	// require './libs/class.phpmailer.php';
	// require './libs/class.smtp.php'; 
	
	date_default_timezone_set('PRC');//设置邮件发送的时间，如果不设置，则会显示其他区的时间 
	$postStr = file_get_contents("php://input"); #获取POST数据
	$json_array=json_decode($postStr);

	$lockfile=str_replace(".tcf",".txt",$json_array->environment);
	
	$logfile=str_replace(".tcf",date('Y-m-d_H-i-s',time()).".log",$json_array->environment);
	
	global $log_path_file;
	$log_path_file="./runcaselist_log/".$logfile;
	
	file_put_contents($log_path_file, '');
	
	function file_log($text="", $add_enter=true) {
		if (is_array($text) || is_object($text)) {
			$text =json_encode($text,JSON_UNESCAPED_UNICODE);
		}
		
		if ($add_enter) {
			$text="\n".$text;
		}
		
		global $log_path_file;
		file_put_contents($log_path_file, $text, FILE_APPEND | LOCK_EX);
		echo($text);
		//file_put_contents("runcaselist_log/saegsdfsdfw-mzftest2020-03-10_16-50-39.log.log",$text, FILE_APPEND | LOCK_EX);
	}
	
	file_put_contents("./lock/".$lockfile,""); //清空web 操作记录文件，后面脚本运行过程再检测
	
	$stream ="";
	$ziploglist="Initialization";
	$zipMail="";
	$Mailcontent="";
	$ditzipName="";
	$subject="";
	$Verdict="";
	$mailflag=0;
	#发邮件结果统计
	$Passed=0;
	$Aborted=0;
	$Canceled=0;
	$Failed=0;
	$Total=0;
	$stopflag=0;
	
	$errMessage="";
	$url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
	$url=str_replace("runcaselist.php","",$url);
	$testlistname=array();
	$array_test_name=array();
	session_start();
	// $tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	$tester = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	try { 
		$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		mysqli_query($mysqli,"SET NAMES utf8");
		if ($mysqli->connect_error) {
			die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
		$table = explode('s_',$json_array->table);
		
		$testplan_id=$json_array->projectid;
		
		
		$projectarry = explode('/',$table[2]);
		$numb=count($projectarry)-1;
	
		$project= substr($projectarry[$numb],0,-5);
	
		unset($projectarry);
		
		$test_list="";
		# 去掉[]
		foreach($json_array->caselist as $value){
			$test_list.=$value.",";
		}
		$test_list.="0";
		
		file_log($test_list);

		$test_plan_id = '';
		$test_list_id = '';
		# 登陆连接标识
	
		$loginflag=false;
		# 获取测试者，版本
		$sql1= "SELECT t.name,u.last,u.first from builds t,users u where t.id=".$json_array->smalledition." and u.id=".$tester.";";

		$result =$mysqli->query($sql1);
		$tester_name=$result->fetch_all();
		
		# 根据testlist查询testplan,确认执行顺序，一般按用例名称排序执行，二按用例指定顺序执行
		if($json_array->order=="yes") {
			$sql= "SELECT distinct t.id from nodes_hierarchy t,testplan_tcversions b  where  t.parent_id in (".$test_list.") and t.node_type_id='4' and t.id=b.tcversion_id ORDER BY b.node_order ASC;";
		} else {
			$sql= "SELECT  distinct t.id from nodes_hierarchy t,nodes_hierarchy b where t.node_type_id='4' and t.parent_id in (".$test_list.") and t.parent_id=b.id order by substring_index(b.name,'.',1)+0,substring_index(substring_index(b.name,'.',-2),'.',1)+0,substring_index(b.name,'.',-1)+0 ASC;";
		}
		file_log($sql);
		
		$result =$mysqli->query($sql);
		$array=$result->fetch_all(); //testplan id array, such as [["31748"],["31823"],["31825"]]，（模式二只有一个testplan id）
		// $txt =json_encode($array,JSON_UNESCAPED_UNICODE);
		// exit($txt);
		// file_put_contents("log.txt", "\n table:".$table[0], FILE_APPEND);
		
		file_log($array);
		
		$connection = ssh2_connect ($table[0], 22);
		
		if($connection) {
			if(!ssh2_auth_password($connection, 'root', 'casa')) {
				
				$loginflag=true;
				$result_dio="Fail to login " .$table[0] ." with root casa. \n";
				
			}
			
		} else {
			$loginflag=true;
			$result_dio="Fail connect server " .$table[0].".\n";
			

		}
		$connection12 = ssh2_connect ($table[0], 22);
		
		if($connection12) {
			if(!ssh2_auth_password($connection12, 'root', 'casa')) {
				
				$result_dio="Fail to login " .$table[0] ." with root casa. \n";
				
			}
			
		} else {
			
			$result_dio="Fail connect server " .$table[0].".\n";
			

		}
		
		$result_dio ="";
		$result_err ="";


		$to="";
		$from="";
		$pwd="";
		
		#模式一  获取发邮件信息
		#模式二  不在这里获取
		if ($json_array->mode == "mode1"|| $json_array->mode == "mode3") {
			$email_info = Get_Email_from_pwd_to($table[0], $json_array->edition, $json_array->environment);
			$from .= $email_info["from"];
			$pwd .= $email_info["pwd"];
			$to .= $email_info["to"];
		}
		
		
		
		for($m=1;$m<=intval($json_array->looptimes);$m++) {
			try { 
				if($m>1) {
					$result_sql="truncate table  ".$table[1].";";
					
					$result =$mysqli->query($result_sql);
					#暂停 30 秒  不知道为什么要停30s，先改成10s吧
					sleep(10);
				}
				
				#发邮件结果统计
				$Passed=0;
				$Aborted=0;
				$Canceled=0;
				$Failed=0;
				$Total=0;
				
				$errMessage="";
				$ziploglist="Initialization";
				$zipMail="";
				$resultstarttime=date('Y-m-d H:i:s', time());
				$soureflieName="";
				$Mailcontent="";
				$ditzipName="";
				$subject="";
				$Verdict="";
				$resultlist="";
				
				$stopflag='';
				//遍历测试用例testplan id，每个测试用例一个循环,（模式二只遍历一次）
				
				for($i=0;$i<count($array);$i++) {
					$resultsate='f';
					if ($stopflag == "stopall") {
						//模式一 来自web界面的终止命令
						file_log('Stopall,break!');
						break;
					}
					file_log('######################### Run the testplan id:'.$array[$i][0]);
					try { 
						$result_dio ="";
						$result_err ="";
						if($array[$i]) {
							#如果数据存在输出数据
							$arr=$array[$i];
							
							$test_plan_id=$arr[0];
							
							#筛选test_plan一样的caselist一起执行
							$sql1= "SELECT  parent_id from nodes_hierarchy where node_type_id='4' and id='".$arr[0]."' and id not in (SELECT testplanid from ".$table[1]." where exeflag='1')  ORDER BY parent_id ASC;";
							
							$result =$mysqli->query($sql1);
							$array_test_list=$result->fetch_all();
							$tempnum=count($array_test_list);
								
							if($tempnum<1) {
								Continue;
								
							} 
							
							
							$temp='';
							$testlist='(';
							for($j=0;$j<$tempnum;$j++) {
								$arrtemp=$array_test_list[$j];
								
								if($j==0) {
									$temp=$arrtemp[0];
									$testlist.=$arrtemp[0];
								} else {
									$temp.='_'.$arrtemp[0];
									$testlist.=",".$arrtemp[0];
								}
							}
						
							$test_list_id=$temp;
							file_log('                             对应testlist id 是'.$test_list_id);
							
							#筛选test_plan一样的caselist一起执行
							$sql1= "SELECT  id,name from nodes_hierarchy where node_type_id='3' and id in ".$testlist.");";
							
							$result =$mysqli->query($sql1);
							$array_test_name=$result->fetch_all();
							for($j=0;$j<count($array_test_name);$j++) {
								$arrtemp=$array_test_name[$j];
								$testlistname[trim($arrtemp[0])]=trim($arrtemp[1]);
								
							}
							
							
							if(!$connection||$loginflag) {
								
								$connection = ssh2_connect ($table[0], 22);
								
								if(ssh2_auth_password($connection, 'root', 'casa')) {
									$loginflag=false;
									
								} else {
									
									$loginflag=true;
									$result_dio="Fail to login " .$table[0] ." with root casa. \n";
									
								}
								
							} 
							if (!$loginflag) {
								
								$config='cd /public/casa-'.$json_array->edition.'/test/Config;sed -i \'s/\(global[^\n]*CASE_ID[^\n]*;[^\n]*set[^\n]*CASE_ID[^\n]*\){[^\n]*}/\1{'.$test_list_id.'}/g\' ./'.$json_array->environment.';sed -i \'s/\(global[^\n]*TEST_LINK_ID[^\n]*;[^\n]*set[^\n]*TEST_LINK_ID[^\n]*\){[^\n]*}/\1{'.$test_plan_id.'}/g\' ./'.$json_array->environment;
								
								$command='cd /public/casa-'.$json_array->edition.'/test;./lte.sh '.$json_array->edition.' /public/casa-'.$json_array->edition.'/test/Config/'.$json_array->environment.' /public/casa-'.$json_array->edition.$table[2].' 1';
								// file_put_contents("log.txt", "\n command: ".$command, FILE_APPEND);
								file_log($config);
								file_log($command);
								$stream = ssh2_exec($connection, $config);  #执行结果以流的形式返回
								$stream = ssh2_exec($connection, $command);  #执行结果以流的形式返回
								
								$log_name=array();
								$lognum=0;
								
								
								//check the log and make sure the script is running
								while($lognum <5) { 
									$stdout_stream = ssh2_exec($connection, "find /public/casa-".$json_array->edition."/test/Log -name '*.exp' -mmin -0.1");

									sleep(8);
									$lognum++;
									$num=0;
									
									while($line = fgets($stdout_stream)) {
										flush();
										$log_name[$num]=trim($line);
										
										$num++;

									} 
									foreach($log_name as $key => $value){
										$stdout_stream = ssh2_exec($connection, "cat ".$value." | tail -n +10 | head -n 1");
										sleep(2);
										
										$line = fgets($stdout_stream);
										if(strpos($line,trim($json_array->environment))!== false) {
											$soureflieName=$value;
											$lognum =10;
											break;
										}
									}
								}
								
								
								
								unset($log_name);
								if($lognum>4) {
									$num=0;  //循环次数
									$stopflag='';
									//进入脚本运行阶段，进入一个循环，每次循环检测web操作，进程，sleep 1s
									file_log('Listen for script to run...');
									while(1) {
										file_log('.', $add_enter=false);
										// echo($stopflag);
										// echo('!!');
										#查看lock文件，获取web 操作指示
										if(file_exists("./lock/".$lockfile)){
											$file_arr = file("./lock/".$lockfile);
											//$stopflag='';
											file_put_contents("./lock/".$lockfile,"");
											for($f=0;$f<count($file_arr);$f++){

												if(strpos($file_arr[$f],"stopall")!== false) {
													$command = "kill -9 $(ps -ef | grep '".$json_array->environment."'|grep -v grep|awk '{print $2}')";
													$stdout_stream = ssh2_exec($connection, $command);
													echo('The TCL script has been stopped by web "Automation"');
													$stopflag='stopall';
												} elseif (strpos($file_arr[$f],"Pause")!== false) {
											
													$command = "kill -stop $(ps -ef | grep '".$json_array->environment."'|grep -v grep|awk '{print $2}')";
													$stdout_stream = ssh2_exec($connection, $command);
													echo('The TCL script has been paused by web "Automation"');
											
													$stopflag="Pause";
													
												} elseif (strpos($file_arr[$f],"recover")!== false) {
													$command = "kill -cont $(ps -ef | grep '".$json_array->environment."'|grep -v grep|awk '{print $2}')";
													$stdout_stream = ssh2_exec($connection, $command);
													echo('The TCL script has been recovered by web "Automation"');
													$stopflag='recover';
													
												} elseif (strpos($file_arr[$f],"stop")!== false) {
													$command = "kill -9 $(ps -ef | grep '".$json_array->environment."'|grep -v grep|awk '{print $2}')";
													$stdout_stream = ssh2_exec($connection, $command);
													echo('The testcase has been stopped by web "Automation"');
													$stopflag='stop';
												}	
											}
											if ($stopflag=="Pause") {
												sleep(1);
												continue;
											} elseif ($stopflag=='stopall' || $stopflag=='stop') {
												
												
												if ($json_array->mode == "mode1" || $json_array->mode == "mode3") {
													//mode1 要进行结果统计
													break;
												}
												
												if ($json_array->mode == "mode2") {
													echo('--!!Split and Get the result information!!--');
													$output = array(
														'staes' => "ok",
														'result_info' => "",
														'result' => '',
														'emailfrom' => $from,
														'emailpwd' => $pwd,
														'emailto' => $to,
														'testername' => $tester_name,
													);
													$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
													exit($txt);
												}
											}
										}
										$stopflag == '';
										
										$stdout_stream = ssh2_exec($connection, "ps -aux | grep lte");
										sleep(10);  //设置太小，用例多的话会导致运行一半 php运行一半突然没了，未解
										
										//超时处理，，每次循环运行10s，半小时超时
										if($num >180) {
											// file_put_contents("log.txt", "\n num: ".$num, FILE_APPEND);
											file_log('!!!Listen for script timeout（30 minutes）');
											while($line = fgets($stdout_stream)) {
												flush();
												#超时退出时若进程还存在就要kill
												if(strpos($line,$json_array->environment)!== false) {
													file_log("!!!Script still exists, kill it and run next script");	
													
													$sizearr=preg_split('/\s+/is', trim($line));
													
													$stream = ssh2_exec($connection, "kill -9 ".$sizearr[1]);  #执行结果以流的形式返回
													unset($sizearr);
													
													file_log($line);
													file_log("kill -9 ".$sizearr[1]);	
												} else {
													file_log("进程已不存在，结束这个用例脚本");
													file_log("!!!Script no exists, run next script");	
												}
												break;
											}
											break;
										}
										
										$num++;  
										
										//检查进程是否还存在，不存在就说明脚本运行结束，退出循环
										$processflag=true;
										while($line = fgets($stdout_stream)) {
											flush();
											if(strpos($line,$json_array->environment)!== false) {
												$processflag=false;
											} 
									
										}
										if($processflag) {
											file_log('Finish script');
											break;
										}
									}
								}
							}
							
							
							//脚本运行结束，处理日志文件，以及从日志中获取运行结果
							file_log('Start handle log file, and get the result from it');
							$datetime=date('Y-m-d H:i:s',time()); 
							$execution_ts=$datetime;
							$end_ts=$datetime;
							
							#保存文件
							$tstable = explode(' ',$datetime);
							$dirtable = explode('-',$tstable[0]);
							
							$dir = $dirtable[0].'/'.$dirtable[1].'/'.$dirtable[2];
							
							
							$subject=$project;
							if(!is_dir("./log/".$dirtable[0])) {
								mkdir("./log/".$dirtable[0],0777,true);
							}
							if(!is_dir("./log/".$dirtable[0]."/".$dirtable[1])) {
								mkdir("./log/".$dirtable[0]."/".$dirtable[1],0777,true);
							}
							if(!is_dir("./log/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2])) { 
								mkdir("./log/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2],0777,true);
							}
							
							$sizearr=explode('Log/', $soureflieName);
							
							$file = str_replace("exp","txt",$sizearr[1]);
							
							unset($sizearr);
							$path="./log/".$dir."/".$file;
							$log='./log.php?data='.$dir.'&file='.$file;
							$emaillog=$url.'log.php?data='.$dir.'&file='.$file;
							
							if ($loginflag) {
								$file= $project;
								$file.='_'.str_replace(':','',$tstable[1]); 
								$number=rand(10,20);
								$file.='_'.$number.'.txt';
								$path="./log/".$dir."/".$file;
								$log='./log.php?data='.$dir.'&file='.$file;
								$emaillog=$url.'log.php?data='.$dir.'&file='.$file;
								
								$errorlog=$result_dio.'
=== ===================================
=== Welcome to CASA MEC x86 Platform
=== ===================================
=== 
CLI services started!
=== CASA-MOBILE>
=== CASA-MOBILE>en
=== Password: 
=== CASA-MOBILE#
=== CASA-MOBILE#show task crash
=== 
Number  Task             Crash-Time                Exit-Code     Exit-Reason     Core-file
=== ==========================================================================================
=== Total 0 crash records
=== 
CASA-MOBILE#
=== CASA-MOBILE#
================================================================================


#####  Result:    FAILED
#####
#####  Last Step: 6: 5.get excel case info
#####  Date:      '.$execution_ts.'
#####  Log:       '.$path.'
#####  Warnings:  0
#####  Errors:    0
#####  SubTest Duration: 00:03

##########################################
All test cases have been executed
##########################################

											  SAEGW
-------------------------------------------------------------------------------------------------
	  Test           Duration    Verdict    Errs    Warns    Type               Bugs    Fixed   
-------------------------------------------------------------------------------------------------
 1    '.$testlistname[0].'    00:03       Fail        0       0      Expect No Error                    
-------------------------------------------------------------------------------------------------

############################################
#####  Passed:    0
#####  Aborted:   0
#####  Canceled:  0
#####  Failed:    1
#####  ----------------
#####  Total:     1
#####  Duration:  00:03
#####
#####  Date:      '.$execution_ts.'
#####  Log:       '.$path.'
#####  Verdict:   FAILED
############################################';
								file_put_contents($path,$errorlog, FILE_APPEND);
							} else {
								// file_put_contents("log.txt", "\n soureflieName: ".$soureflieName." path: ".$path, FILE_APPEND);
								if($connection12) {
									sleep(10);
									ssh2_scp_recv($connection12,$soureflieName,$path);
								}
							}
							
							
							$currentstep="";
							
							$tsnum=0;
							$notes="";
							$passindex=0;
							$allindex=0;
							$steppass=array();
							$stepall=array();
							$notesflag=true;
							#发邮件结果统计
							$resultcount=0;
							
							$file_arr = file($path);
							$resultlistnum=0;
							#逐行解析日志内容
							for($k=0;$k<count($file_arr);$k++){
								
								if(strpos($file_arr[$k],"Date:")!== false) {
									$match='/Date:\s*\d+\W\d+\W\d+\s+\d+:\d+:\d+\s+\w+/s';
									
									preg_match_all($match,$file_arr[$k],$arr, PREG_PATTERN_ORDER);
									$timearr=count($arr);
									if($timearr>0  ) {
										$tsnum=$tsnum+1;
										
										if($tsnum==1) {
											$end=$start=strtotime(substr($arr[0][0],6));
											$end_ts=$execution_ts=date('Y-m-d H:i:s',$start);
											
											unset($arr);
										} elseif ($tsnum==3) {
											$end=strtotime(substr($arr[0][0],6));
											$end_ts=date('Y-m-d H:i:s',$end);
											unset($arr);
										}
									} 
									
									
								} elseif (strpos($file_arr[$k],"#####  Result:")!== false) {
									
									if(strpos($file_arr[$k],'Result:    PASSED') !==false) {
										$resultsate='p';
										
									} else{
										$resultsate='f';
									} 
									
								} elseif (strpos($file_arr[$k],"Execute Step")!== false) {
									$arr=(explode(":",$file_arr[$k]));
									$currentstep = trim($arr[1]);
									$stepall[$allindex]=trim($currentstep);
									$allindex=$allindex+1;
									
									unset($arr);
								}  elseif (strpos($file_arr[$k],"Success Step")!== false) {
									
									$notesflag=true;
									
									$arr=(explode(":",$file_arr[$k]));
									
									if ($currentstep==trim($arr[1])) {
									
										$steppass[$passindex]=trim($currentstep);
										$passindex=$passindex+1;
									} 

									unset($arr);
								} elseif (strpos($file_arr[$k],"Complete all steps")!== false) {
									$notesflag=true;
									
								} elseif (strpos($file_arr[$k],"recovery_conf")!== false) {
									$notesflag=true;
									
								}  elseif (strpos($file_arr[$k],"=== Tips ===")!== false) {
									$notesflag=false;
									$notes.=$file_arr[$k] ."\n";
									
								} elseif (strpos($file_arr[$k],"All test cases have been executed")!== false) {
									$resultcount++;
									
								} else {
									if(!$notesflag) {
										
										$notes.=$file_arr[$k] ."\n";
									}
									if($resultcount>0) {
										$resultcount++;
										
									}
									if($resultcount==8) {
										
										
										$str=preg_replace ( "/\s(?=\s)/","\\1", $file_arr[$k]);
										$str=trim($str);
										$str_arr=explode(" ",$str);
										$arr1=explode("_",$str_arr[1]);
										
										$resultlist.='<tr>
											<th colspan="1" style="text-align:center" >
											'.$str_arr[0].'
											</th>
											<th colspan="1" style="text-align:center" >
											'.$str_arr[1].'
											</th>
											<th colspan="15" style="text-align:left">
											'.$testlistname[$arr1[0]];
										if($str_arr[3]=='P') {	
											$resultlist.='</th>
											<th colspan="3" style="text-align:center">
												
													<span style="text-align:center;color:green;">Pass</span> 
												
											</th>';
										} else {
											$resultlist.='</th>
											<th colspan="3" style="text-align:center">
												
													<span style="text-align:center;color:red;">'.$str_arr[3].'</span> 
												
											</th>';
										}
										$resultlist.='<th colspan="3" style="text-align:center">
												
													<span style="text-align:center;">'.$str_arr[2].'</span> 
												
											</th>
											
											<th colspan="3">
												<a href="'.$emaillog.'&title='.$testlistname[$arr1[0]].'">详情</a>
												
											</th>
										</tr>';
										
										$Total++;
									} elseif ($resultcount==12) {
										if(preg_match('/\d+/',$file_arr[$k],$arr)){
											$Passed=$Passed+(int)$arr[0];
										}
									} elseif ($resultcount==13) {
										if(preg_match('/\d+/',$file_arr[$k],$arr)){
											$Aborted=$Aborted+(int)$arr[0];
										}
									} elseif ($resultcount==14) {
										if(preg_match('/\d+/',$file_arr[$k],$arr)){
											$Canceled=$Canceled+(int)$arr[0];
										}
									} elseif ($resultcount==15) {
										if(preg_match('/\d+/',$file_arr[$k],$arr)){
											$Failed=$Failed+(int)$arr[0];
										}
									}
								}
								
							}
							
							file_log('Get the result:'.$testlistname[$arr1[0]].'  Result: '.$str_arr[3]);
							
							if(strlen($execution_ts)<4) {
								$end_ts=$execution_ts=$resultstarttime;
							}
							if(strlen($resultsate)<1) {
								$resultsate='f';
							}
							
							file_log('Record the result to DB');
							
							$remarks=$json_array->remarks;
							$execution_duration=round(($end-$start)%86400/60,2);
							
							$platform_sql="SELECT t.platform_id from testplan_tcversions t where t.testplan_id='".$testplan_id."' and t.tcversion_id='".$test_plan_id."';";
							
							$result =$mysqli->query($platform_sql);
							$array2=$result->fetch_all();
							$arr2=$array2[0];
							
							if(strlen($arr2[0])>0) {
								
								$platform_id=$arr2[0];
								
							} else {
								
								$platform_id='0';
								
							}
							
							$result_sql="INSERT into executions (`build_id`,`tester_id`,`execution_ts`,`status`,`testplan_id`,`tcversion_id`,`tcversion_number`,`platform_id`,`execution_type`,`execution_duration`,`notes`,`end_ts`,`log`,`details`,`executor`,`looptimes`,`edition`,`environmental`) VALUES ( '";
							
							$result_sql.=$json_array->smalledition."','".$tester."','".$execution_ts."','".$resultsate."','".$testplan_id."','".$test_plan_id."','1','".$platform_id."','2','".$execution_duration."','".$remarks."','".$end_ts."','".$log."','printDocOptions.tpl','".$tester."','".$m."','".$json_array->edition."','".$json_array->environment."');";
							// file_put_contents("log.txt", "\n result_sql: ".$result_sql, FILE_APPEND);
							$result =$mysqli->query($result_sql);
							
							$result_sql="INSERT into ".$table[1]." (`testplanid`,`exeflag`) VALUES ('".$test_plan_id."','1');";
							$result =$mysqli->query($result_sql);
							$result_sql="SELECT id from executions where build_id='".$json_array->smalledition."'  and execution_ts='".$execution_ts."' and testplan_id='".$testplan_id."' and  tcversion_id='".$test_plan_id."' and environmental='".$json_array->environment."';";
						
							$result =$mysqli->query($result_sql);
							$executions=$result->fetch_all();
							
							
						
							$executions_id=$executions[0][0];
							unset($executions);
							# #检查是否所有步骤度执行了
							$steps_sql="SELECT id from tcsteps where parent_id in (SELECT id from nodes_steps where parent_id=".$test_plan_id.") and automation_type !=0 ORDER BY id ASC;";
							
							$result =$mysqli->query($steps_sql);
							$steps_arry=$result->fetch_all();
							$steps=array();
							
							$stepsindex=0;
							foreach($steps_arry as $value) {
								$steps[$stepsindex]=$value[0];
								$stepsindex=$stepsindex+1;
							}
							unset($steps_arry);
							
							$diffarray=array_diff($stepall,$steppass);
							$intersectarray=array_intersect($stepall,$steppass);
						
							foreach($intersectarray as $value) {
							
								$result_sql="INSERT into execution_tcsteps (`execution_id`,`tcstep_id`,`notes`,`status`) VALUES ('".$executions_id."','".$value."','','p');";
							
								$result =$mysqli->query($result_sql);
							}
							
							foreach($diffarray as $value) {
								$result_sql="INSERT into execution_tcsteps (`execution_id`,`tcstep_id`,`notes`,`status`) VALUES ('".$executions_id."','".$value."','".$notes."','f');";
							
								$result =$mysqli->query($result_sql);
							}
							
							$diffsteps=array_diff($steps,$stepall);
							foreach($diffsteps as $value) {
								$result_sql="INSERT into execution_tcsteps (`execution_id`,`tcstep_id`,`notes`,`status`) VALUES ('".$executions_id."','".$value."','unexecuted','b');";
							
								$result =$mysqli->query($result_sql);
							}
							
							file_log('Finish record the result to DB');
						}
					} catch (Exception $e) {
						
						$Failed=$Failed+1;
						$errMessage=$e->getMessage();
						
						continue;
					} 
					if($stopflag==3) {
						
						break;
					}
				}#end for
				
				
				
			} catch (Exception $e) {
				
				$Failed=$Failed+1;
				$errMessage=$e->getMessage();
				
				continue;
			} finally {
				
				#模式一 在这里发邮件
				#模式二 不在这里发邮件，在sentcase.js 文件调用 sent_email_mode2.php 发邮件
				if ($json_array->mode == "mode1"|| $json_array->mode == "mode3") {
					$Endtime=date('Y-m-d H:i:s', time());
					
					# 判断是否需要发邮件，需要就组装发送邮件内容文件
					if($json_array->sentmail=="on") {
						$status = SendEmail(
							$tester_name=$tester_name, 
							$email_from=$from, 
							$email_pwd=$pwd, 
							$email_to=$to, 
							$resultlist=$resultlist, 
							$starttime=$resultstarttime, 
							$endtime=$Endtime, 
							$edition=$json_array->edition,
							$environment=$json_array->environment, 
							$project=$project,
							$total=$Total, 
							$pass=$Passed, 
							$fail=$Failed, 
							$abort=$Aborted, 
							$cancel=$Canceled
							);
						
						if(strlen($resultlist)<5) {
							$loginflag=true;
						}
					}
				}
				
				file_log('Send email');

			}
			
			if($stopflag==3) {	
				break;
			}
		} #end for
	
	} catch (Exception $e) {
		$errMessage=$e->getMessage();
		file_put_contents("./log/errorlog.log", "errMessage:".$errMessage."\n ", FILE_APPEND);	
		
	} finally {
		file_log('--!!Split and Get the result information!!--');
		fclose($myfile);
		fclose($stream);
		$mysqli->close();
			
		if ($json_array->mode == "mode2") {
			$output = array(
				'staes' => "ok",
				'result_info' => $resultlist,
				'result' => $str_arr[3],
				'emailfrom' => $from,
				'emailpwd' => $pwd,
				'emailto' => $to,
				'testername' => $tester_name,
			);
		} else {
			$output = array(
				'staes' => "ok",
			);
		}
		
		$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
		exit($txt);#把结果反馈给客户端
		
		
		
	}
	




?>