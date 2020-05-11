<?php
	
	
	@include_once('config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}
	require './libs/class.phpmailer.php'; 
	require './libs/class.smtp.php'; 
	date_default_timezone_set('PRC');//设置邮件发送的时间，如果不设置，则会显示其他区的时间 
	$postStr = file_get_contents("php://input"); #获取POST数据
	$json_array=json_decode($postStr);

	$lockfile=str_replace(".tcf",".txt",$json_array->environment);
	file_put_contents("./lock/".$lockfile,"");
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
		
		

		$test_plan_id = '';
		$test_list_id = '';
		# 登陆连接标识
	
		$loginflag=false;
		# 获取测试者，版本
		$sql1= "SELECT t.name,u.last,u.first from builds t,users u where t.id=".$json_array->smalledition." and u.id=".$json_array->tester.";";
		
		$result =$mysqli->query($sql1);
		$tester_name=$result->fetch_all();
		
		# 根据testlist查询testplan,确认执行顺序，一般按用例名称排序执行，二按用例指定顺序执行
		if($json_array->order=="yes") {
			$sql= "SELECT distinct t.id from nodes_hierarchy t,testplan_tcversions b  where  t.parent_id in (".$test_list.") and t.node_type_id='4' and t.id=b.tcversion_id ORDER BY b.node_order ASC;";
		} else {
			$sql= "SELECT  distinct t.id from nodes_hierarchy t,nodes_hierarchy b where t.node_type_id='4' and t.parent_id in (".$test_list.") and t.parent_id=b.id order by substring_index(b.name,'.',1)+0,substring_index(substring_index(b.name,'.',-2),'.',1)+0,substring_index(b.name,'.',-1)+0 ASC;";
		}
		
		$result =$mysqli->query($sql);
		$array=$result->fetch_all();
		// file_put_contents("log.txt", "\n table:".$table[0], FILE_APPEND);
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
		#获取发邮件信息
		if (!$loginflag) {
			$config='cd /public/casa-'.$json_array->edition.'/test/Config;cat '.$json_array->environment.' | grep "IP_EMAIL";cat '.$json_array->environment.' | grep "DEFAULT_EMAIL"';

			
			$stream = ssh2_exec($connection, $config);  #执行结果以流的形式返回
			
			sleep(1);
			$err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

			
			while($line = fgets($err_stream)) { 
				flush(); 
				
				$result_err.=$line;
				unset($line);
			}
			

			while($line = fgets($stream)) { 
				flush();
				
				$result_dio.=$line;
				unset($line);
			}
			
		}
		# 替换字符串
		$search = array('global',';','set','{','}','"');
		$replace = " ";
		$result_dio = str_replace($search,$replace,$result_dio);
		# 去除多余空格
		$result_dio=preg_replace ( "/\s(?=\s)/","\\1", $result_dio );
		# 去除首尾空格
		$result_dio=trim($result_dio);
		
		$mailinfo = explode(' ',$result_dio);

		$mailinfonum=count($mailinfo);
		$to="";
		$IP_EMAIL="";
		$from="";
		$pwd="";
		for($i=0;$i<$mailinfonum;$i++){
			if($mailinfo[$i]=="IP_EMAIL") {
				$i=$i+2;
				$IP_EMAIL=$mailinfo[$i];
			} elseif ($mailinfo[$i]=="DEFAULT_EMAIL(from)") {
				$i=$i+1;
				$from=$mailinfo[$i];
			} elseif ($mailinfo[$i]=="DEFAULT_EMAIL(to)") {
				$i=$i+1;
				$to.=$mailinfo[$i].",";
			} elseif ($mailinfo[$i]=="DEFAULT_EMAIL(pwd)") {
				$i=$i+1;
				$pwd=$mailinfo[$i];
			}
		}
		// file_put_contents("log.txt", "\n to:".$to, FILE_APPEND);
		
		for($m=1;$m<=intval($json_array->looptimes);$m++) {
			try { 
				if($m>1) {
					$result_sql="truncate table  ".$table[1].";";
					
					$result =$mysqli->query($result_sql);
					#暂停 30 秒
					sleep(30);
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
				$resultstarttime=date('Y-m-d h:i:s', time());
				$soureflieName="";
				$Mailcontent="";
				$ditzipName="";
				$subject="";
				$Verdict="";
				$resultlist="";
				for($i=0;$i<count($array);$i++) {
					$resultsate='f';
					$stopflag=0;
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
								
								$stream = ssh2_exec($connection, $config);  #执行结果以流的形式返回
								$stream = ssh2_exec($connection, $command);  #执行结果以流的形式返回
								
								$log_name=array();
								$lognum=0;
								
								
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
											// file_put_contents("log.txt", "\n soureflieName: ".$soureflieName, FILE_APPEND);
											$lognum =10;
											break;
										}
									}
								}
								unset($log_name);
								
								if($lognum>4) {
									$num=0;
									#30min还未跑完就判定失败
									while(1) {
										
										if(file_exists("./lock/".$lockfile)){
											$file_arr = file("./lock/".$lockfile);
											// $stopflag=0;
											#逐行解析日志内容
											for($f=0;$f<count($file_arr);$f++){
												
												if(strpos($file_arr[$f],"stop")!== false) {
													$stdout_stream = ssh2_exec($connection, "ps -aux | grep lte");
													sleep(5);
													
													while($line = fgets($stdout_stream)) {
														flush();
														#超时退出时若进程还存在就要kill
														if(strpos($line,$json_array->environment)!== false) {
															$sizearr=preg_split('/\s+/is', trim($line));
															
															$stream = ssh2_exec($connection, "kill -9 ".$sizearr[1]);  #执行结果以流的形式返回
															unset($sizearr);
														} 
												
													}
													file_put_contents("./lock/".$lockfile,"");
													$stopflag=1;
													break;
													
												} elseif (strpos($file_arr[$f],"Pause")!== false) {
													$stdout_stream = ssh2_exec($connection, "ps -aux | grep ".$json_array->environment);
													sleep(5);
													
													while($line = fgets($stdout_stream)) {
														flush();
														#超时退出时若进程还存在就要kill
														if(strpos($line,"main.tcl")!== false) {
															
															$sizearr=preg_split('/\s+/is', trim($line));
															
															$stream = ssh2_exec($connection, "kill -stop ".$sizearr[1]);  #执行结果以流的形式返回
															unset($sizearr);
															file_put_contents("./lock/".$lockfile,"");
															break;
															
														} 
												
													}
													$stopflag=2;
													break;
												} elseif (strpos($file_arr[$f],"recover")!== false) {
													$stdout_stream = ssh2_exec($connection, "ps -aux | grep ".$json_array->environment);
													sleep(5);
													
													while($line = fgets($stdout_stream)) {
														flush();
														#超时退出时若进程还存在就要kill
														if(strpos($line,"main.tcl")!== false) {
															
															$sizearr=preg_split('/\s+/is', trim($line));
															$stream = ssh2_exec($connection, "ls");  #执行结果以流的形式返回
															
															$stream = ssh2_exec($connection, "kill -cont ".$sizearr[1]);  #执行结果以流的形式返回
															unset($sizearr);
															file_put_contents("./lock/".$lockfile,"");
															break;
															
														} 
												
													}
													$stopflag=0;
													break;
												} elseif(strpos($file_arr[$f],"stopall")!== false) {
													$stdout_stream = ssh2_exec($connection, "ps -aux | grep lte");
													sleep(5);
													
													while($line = fgets($stdout_stream)) {
														flush();
														#超时退出时若进程还存在就要kill
														if(strpos($line,$json_array->environment)!== false) {
															$sizearr=preg_split('/\s+/is', trim($line));
															
															$stream = ssh2_exec($connection, "kill -9 ".$sizearr[1]);  #执行结果以流的形式返回
															unset($sizearr);
														} 
												
													}
													$stopflag=3;
													file_put_contents("./lock/".$lockfile,"");
													break;
													
												}
											}
											if($stopflag==1) {
												
												break;
											} elseif ($stopflag==2) {
												sleep(15);
												continue;
											} elseif ($stopflag==3) {
												
												break;
											}
										}
										// file_put_contents("log.txt", "\n ok: ", FILE_APPEND);
										$stdout_stream = ssh2_exec($connection, "ps -aux | grep lte");
										sleep(5);
										if($num >55) {
											// file_put_contents("log.txt", "\n num: ".$num, FILE_APPEND);
											while($line = fgets($stdout_stream)) {
												flush();
												#超时退出时若进程还存在就要kill
												if(strpos($line,$json_array->environment)!== false) {
													$sizearr=preg_split('/\s+/is', trim($line));
													
													$stream = ssh2_exec($connection, "kill -9 ".$sizearr[1]);  #执行结果以流的形式返回
													unset($sizearr);
												} 
										
											}
											break;
										}
										sleep(18);
										$num++;
										
										$processflag=true;
										
										while($line = fgets($stdout_stream)) {
											flush();
											// file_put_contents("log.txt", "\n line: ".json_encode($line), FILE_APPEND);
											if(strpos($line,$json_array->environment)!== false) {
												$processflag=false;
											} 
									
										}
										// file_put_contents("log.txt", "\n processflag: ".json_encode($processflag), FILE_APPEND);
										if($processflag) {
											
											break;
										}
									}
								}
								
								
							}
							
							$datetime=date('Y-m-d h:i:s',time()); 
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
											<th colspan="15" style="text-align:left">
											'.$testlistname[$arr1[0]].'
											</th>
											<th colspan="3" style="text-align:center">
												
													<span class="colorRed">'.$str_arr[3].'</span> 
												
											</th>
											<th colspan="3" style="text-align:center">
												
													<span class="colorRed">'.$str_arr[2].'</span> 
												
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
							
							if(strlen($execution_ts)<4) {
								$end_ts=$execution_ts=$resultstarttime;
							}
							if(strlen($resultsate)<1) {
								$resultsate='f';
							}
							
							
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
							
							$result_sql.=$json_array->smalledition."','".$json_array->tester."','".$execution_ts."','".$resultsate."','".$testplan_id."','".$test_plan_id."','1','".$platform_id."','2','".$execution_duration."','".$remarks."','".$end_ts."','".$log."','printDocOptions.tpl','".$json_array->tester."','".$m."','".$json_array->edition."','".$json_array->environment."');";
							
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
				# 判断是否需要发邮件，需要就组装发送邮件内容文件
				if($json_array->sentmail=="on") {
					$Endtime=date('Y-m-d h:i:s', time());
					$bodyverdict='';
					if($Passed==$Total && $Passed>0) {
						$Verdict='Passed';
						$bodyverdict='<span style="color: green;">Verdict：Passed</span>';
					} elseif ($Failed>0) {
						$Verdict='Failed';
						$bodyverdict='<span style="color: red;">Verdict：Failed</span>';
					} elseif ($Aborted>0) {
						$Verdict='Aborted';
						$bodyverdict='<span style="color: red;">Verdict：Aborted</span>';
					} elseif ($Canceled>0) {
						$Verdict='Canceled';
						$bodyverdict='<span style="color: red;">Verdict：Canceled</span>';
					} else {
						$Verdict='Failed';
						$bodyverdict='<span style="color: red;">Verdict：Failed</span>';
					}
					
					$Totalnum=$Total-$Passed-$Failed-$Aborted-$Canceled;
					if($Totalnum!=0) {
						$Failed=$Failed+$Totalnum;
						$bodyverdict='<span style="color: red;">Verdict：Failed</span>';
						$Verdict='Failed';
					}
					
					$subject.=' Version:'.$json_array->edition.' '.$tester_name[0][0].' '.$Verdict.' Executor:'.$tester_name[0][1].$tester_name[0][2];
					
					
					
					$mail = new PHPMailer();  
					//是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式 
					$mail->SMTPDebug = 3; 
					$mail->isSMTP(); 
					$mail->SMTPAuth=true; 
					$mail->Host = 'smtp.qiye.163.com'; 
					$mail->SMTPSecure = 'ssl'; 
					$mail->Port = 465; 
					$mail->Hostname = 'localhost'; 
					//设置发送的邮件的编码 可选GB2312  
					$mail->CharSet = 'UTF-8'; 
					//设置发件人姓名（昵称）可为任意内容，不影响回复
					$mail->FromName = $tester_name[0][1].$tester_name[0][2];
					//smtp登录的账号  
					$mail->Username =$from; 
					$mail->Password = $pwd; 
					//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱” 
					$mail->From = $from; 
					//邮件正文是否以html方式发送  
					$mail->isHTML(true);  
					//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大 
					$emailaddress = explode(',',$to); 
					foreach($emailaddress as $address) {
						$mail->addAddress($address,''); 
					}
					
					$mail->Subject = $subject; 
					
					
					$mail->Body = '<h1 align="center">自动化测试报告</h1>
						<div class="table" align="center">
							<table id="content" class="tables" border="1" cellspacing="0" cellpadding="10">
								<thead>
									<tr>
										<th width="100%" colspan="25">
											<div style="display:inline-block;text-algin: left;width:49%;">
												'.$bodyverdict.' 
											</div>
											
											<div style="display:inline-block;text-algin: left;width:49%;">
												<span style="color: blue;">Total：'.$Total.'</span>
											</div>
											<div style="display:inline-block;text-algin: left;width:49%;">
												start time：<span>'.$resultstarttime.'</span>
											</div>
											<div style="display:inline-block;text-algin: left;width:49%;">
												End time：<span>'.$Endtime.'</span>
											</div>
											
											
											<div style="display:inline-block;text-algin: left;width:49%;">
												<span class="testTime" style="text-algin: left;">Passed：<span>'.$Passed.'</span></span>
											</div>
											<div style="display:inline-block;text-algin: left;width:49%;">
												<span class="testTime" style="text-algin: left;">Aborted：<span>'.$Aborted.'</span></span>
											</div>
											<div style="display:inline-block;text-algin: left;width:49%;">
												<span class="testTime" style="text-algin: left;">Canceled：<span>'.$Canceled.'</span></span>
											</div>
											<div style="display:inline-block;text-algin: left;width:49%;">
												<span class="testTime" style="text-algin: left;">Failed：<span>'.$Failed.'</span></span>
											</div>
											
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
									   <th colspan="1">Case numbe</th>
									   <th colspan="15">Case name</th>
									   <th colspan="3">Verdict</th>
									   <th colspan="3"> Duration</th>
									   <th colspan="3">Detail</th>
									   
									</tr>
									'.$resultlist.'
								</tbody>
							</table>
						</div>';
					
					$status = $mail->send(); 
					
					if(strlen($resultlist)<5) {
						$loginflag=true;
					}
				}
			}
			
			if($stopflag==3) {	
				break;
			}
		} #end for
	
	} catch (Exception $e) {
		$errMessage=$e->getMessage();
		file_put_contents("./log/errorlog.log", "errMessage:".$errMessage."\n ", FILE_APPEND);	
		
	} finally {
		
		fclose($stream);
		$mysqli->close();
		$output = array(
			'staes' => "ok"
		);
		
		$txt =json_encode($output,JSON_UNESCAPED_UNICODE);
		exit($txt);#把结果反馈给客户端
		
		
		
	}
	




?>