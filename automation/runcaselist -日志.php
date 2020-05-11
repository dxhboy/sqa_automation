<?php
	
	@include_once('config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}

	$postStr = file_get_contents("php://input"); #获取POST数据
	
	

	$json_array=json_decode($postStr);
	
	$stream ="";
	$ziploglist="Initialization";
	$zipMail="";
	$Mailcontent="";
	
	$subject="";
	$Verdict="";
	$mailflag=0;
	#发邮件结果统计
	$Passed=0;
	$Aborted=0;
	$Canceled=0;
	$Failed=0;
	$Total=0;
	
	$errMessage="";

	
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
		$loginflag=true;
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
	
		$connection = ssh2_connect ($table[0], 22);
		
		if($connection) {
			if(!ssh2_auth_password($connection, 'root', 'casa')) {
				$loginflag=false;
				$result_dio="Fail to login " .$table[0] ." with root casa. \n";
				
			}
			
		} else {
			$loginflag=false;
			$result_dio="Fail connect server " .$table[0].".\n";
			

		}
		
		$result_dio ="";
		$result_err ="";
		#获取发邮件信息
		if ($loginflag) {
			$config='cd /public/casa-'.$json_array->edition.'/test/Config;cat '.$json_array->environment.' | grep "IP_EMAIL";cat '.$json_array->environment.' | grep "DEFAULT_EMAIL"';

			
			$stream = ssh2_exec($connection, $config);  #执行结果以流的形式返回
			file_put_contents("log.txt", "\n config:".$config, FILE_APPEND);
			
			
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
			file_put_contents("log.txt", "\n command:".$result_dio, FILE_APPEND);
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
		$mode="";
		for($i=0;$i<$mailinfonum;$i++){
			if($mailinfo[$i]=="IP_EMAIL") {
				$i=$i+2;
				$IP_EMAIL=$mailinfo[$i];
			} elseif ($mailinfo[$i]=="DEFAULT_EMAIL(mode)") {
				$i=$i+1;
				$mode=$mailinfo[$i];
			} elseif ($mailinfo[$i]=="DEFAULT_EMAIL(to)") {
				$i=$i+1;
				$to.=$mailinfo[$i].",";
			}
		}
		
		
		for($m=1;$m<=intval($json_array->looptimes);$m++) {
			try { 
				if($m>1) {
					$result_sql="truncate table  ".$table[1].";";
					
					$result =$mysqli->query($result_sql);
					#暂停 30 秒
					sleep(30);
				}
				$testlistname="";
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
				
				$subject="";
				$Verdict="";
				$resultlist="-------------------------------------------------------------------------------------------------\nTest                 Duration  Verdict     Errs    Warns           Type             Bugs  Fixed \n-------------------------------------------------------------------------------------------------";
				
				for($i=0;$i<count($array);$i++) {
					$resultsate='f';
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
								$testlistname.=$arrtemp[0]."     ".$arrtemp[1]."\n";
							}
							
							if ($loginflag) {
								$config='cd /public/casa-'.$json_array->edition.'/test/Config;sed -i \'s/\(global[^\n]*CASE_ID[^\n]*;[^\n]*set[^\n]*CASE_ID[^\n]*\){[^\n]*}/\1{'.$test_list_id.'}/g\' ./'.$json_array->environment.';sed -i \'s/\(global[^\n]*TEST_LINK_ID[^\n]*;[^\n]*set[^\n]*TEST_LINK_ID[^\n]*\){[^\n]*}/\1{'.$test_plan_id.'}/g\' ./'.$json_array->environment;
								
								$command='cd /public/casa-'.$json_array->edition.'/test;./lte.sh '.$json_array->edition.' /public/casa-'.$json_array->edition.'/test/Config/'.$json_array->environment.' /public/casa-'.$json_array->edition.$table[2].' 1';
								
								$stream = ssh2_exec($connection, $config);  #执行结果以流的形式返回
								
								
								$stream = ssh2_exec($connection, $command);  #执行结果以流的形式返回
								
								
								$logsizenum=0;
								// while($num <3) { 
									// $stdout_stream = ssh2_exec($connection, "find /public/casa-".$json_array->edition."/test/Log -name '*.exp' -mmin -0.1");
									// sleep(8);
									
									// $log_name=array();
									// $lognum=0;
									// $num =0;
									
									// while($num <8) { 
										// if($line = fgets($stdout_stream)) {
											// flush();
											// $lognum++;
											// $log_name[$lognum]=trim($line);
											// $num =0;
											// break;
										// } else {
											
											// sleep(2);
											// $num++;
										// }
									// }
									// if($lognum>0) {
										// foreach($log_name as $key => $value){
											// $stdout_stream = ssh2_exec($connection, "cat ".$value." | tail -n +10 | head -n 1");
											// sleep(2);
											// $line = fgets($stdout_stream);
											// if(strpos($line,trim($json_array->environment))!== false) {
												// $soureflieName=$value;
											// }
										// }
									// }
									// unset($log_name);
									// if($soureflieName!=""){
										// $num =0;
										// $size='';
										// #限制每个脚本最大20分钟
										// while($num<200) {
											// $stdout_stream = ssh2_exec($connection, "du -h ".$soureflieName);
											// sleep(6);
											// $num++;
											// $line = fgets($stdout_stream);
											
											// $sizearr=preg_split('/\s+/is', trim($line));
											// if($size==$sizearr[0]) {
												// unset($sizearr);
												// #36秒内日志大小无变化判断结束
												// if($num>5) {
													
													// break;
												// } else {
													// continue;
												// }
											// }
											// $size=$sizearr[0];
										// }
										
									// } else {
										// $logsizenum++;
									// }
								// }
							}
							
							// $datetime=date('Y-m-d h:i:s',time()); 
							// $execution_ts=$datetime;
							// $end_ts=$datetime;
							
							// #保存文件
							// $tstable = explode(' ',$datetime);
							// $dirtable = explode('-',$tstable[0]);
							
							// $dir = $dirtable[0].'/'.$dirtable[1].'/'.$dirtable[2];
							
							
							
							// if(!is_dir("./log/".$dirtable[0]) {
								// mkdir("./log/".$dirtable[0],0777,true);
							// }
							// if(!is_dir("./log/".$dirtable[0]."/".$dirtable[1]) {
								// mkdir("./log/".$dirtable[0]."/".$dirtable[1],0777,true);
							// }
							// if(!is_dir("./log/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2]) {
								// mkdir("./log/".$dirtable[0]."/".$dirtable[1]."/".$dirtable[2],0777,true);
							// }
							// $sizearr=explode('Log/', $soureflieName);
							// $file = str_replace("exp","txt",$sizearr[1]);
							// unset($sizearr);
							// $path="./log/".$dir."/".$file;
							// $log='./log.php?data='.$dir.'&file='.$file;
							// ssh2_scp_recv($connection, $soureflieName,$path);
							// if($ziploglist=="Initialization") {
								// $ziploglist=$path;
								// $timenum = str_replace(":","",$tstable[1]);
								// $zipMail="./log/".$dir."/".$project."_".$timenum.".zip";
								
								// $Mailcontent="./log/".$dir;
								
							// } else {
								// $ziploglist.=",".$path;
							// }
							
							// $currentstep="";
							
							// $tsnum=0;
							// $notes="";
							// $passindex=0;
							// $allindex=0;
							// $steppass=array();
							// $stepall=array();
							// $notesflag=true;
							// #发邮件结果统计
							// $resultcount=0;
							
							// $file_arr = file($path);
							// $resultlistnum=0;
							// #逐行解析日志内容
							// for($k=0;$k<count($file_arr);$k++){
								
								// if(strpos($file_arr[$k],"Date:")!== false) {
									// $match='/Date:\s*\d+\W\d+\W\d+\s+\d+:\d+:\d+\s+\w+/s';
									
									// preg_match_all($match,$file_arr[$k],$arr, PREG_PATTERN_ORDER);
									// $timearr=count($arr);
									// if($timearr>0  ) {
										// $tsnum=$tsnum+1;
										
										// if($tsnum==1) {
											// $end=$start=strtotime(substr($arr[0][0],6));
											// $end_ts=$execution_ts=date('Y-m-d H:i:s',$start);
											
											// unset($arr);
										// } elseif ($tsnum==3) {
											// $end=strtotime(substr($arr[0][0],6));
											// $end_ts=date('Y-m-d H:i:s',$end);
											// unset($arr);
										// }
									// } 
									
									
								// } elseif (strpos($file_arr[$k],"#####  Result:")!== false) {
									
									// if(strpos($file_arr[$k],'Result:    PASSED') !==false) {
										// $resultsate='p';
										
									// } else{
										// $resultsate='f';
									// } 
									
								// } elseif (strpos($file_arr[$k],"Execute Step")!== false) {
									
									
									
									// $arr=(explode(":",$file_arr[$k]));
								
									// $currentstep = trim($arr[1]);
									// $stepall[$allindex]=trim($currentstep);
									// $allindex=$allindex+1;
									
									// unset($arr);
								// }  elseif (strpos($file_arr[$k],"Success Step")!== false) {
									
									// $notesflag=true;
									
									// $arr=(explode(":",$file_arr[$k]));
									
									// if ($currentstep==trim($arr[1])) {
									
										// $steppass[$passindex]=trim($currentstep);
										// $passindex=$passindex+1;
									// } 

									// unset($arr);
								// } elseif (strpos($file_arr[$k],"Complete all steps")!== false) {
									// $notesflag=true;
									
								// } elseif (strpos($file_arr[$k],"recovery_conf")!== false) {
									// $notesflag=true;
									
								// }  elseif (strpos($file_arr[$k],"=== Tips ===")!== false) {
									// $notesflag=false;
									// $notes.=$file_arr[$k] ."\n";
									
								// } elseif (strpos($file_arr[$k],"All test cases have been executed")!== false) {
									// $resultcount++;
									
								// } else {
									// if(!$notesflag) {
										
										// $notes.=$file_arr[$k] ."\n";
									// }
									// if($resultcount>0) {
										// $resultcount++;
										
									// }
									// if($resultcount==8) {
										// $resultlist.="\n".$file_arr[$k];
										// $Total++;
									// } elseif ($resultcount==12) {
										// if(preg_match('/\d+/',$file_arr[$k],$arr)){
											// $Passed=$Passed+(int)$arr[0];
										// }
									// } elseif ($resultcount==13) {
										// if(preg_match('/\d+/',$file_arr[$k],$arr)){
											// $Aborted=$Aborted+(int)$arr[0];
										// }
									// } elseif ($resultcount==14) {
										// if(preg_match('/\d+/',$file_arr[$k],$arr)){
											// $Canceled=$Canceled+(int)$arr[0];
										// }
									// } elseif ($resultcount==15) {
										// if(preg_match('/\d+/',$file_arr[$k],$arr)){
											// $Failed=$Failed+(int)$arr[0];
										// }
									// }
								// }
								
							// }
							
							// if(strlen($execution_ts)<4) {
								// $end_ts=$execution_ts=$resultstarttime;
							// }
							// if(strlen($resultsate)<1) {
								// $resultsate='f';
							// }
							
							
							// $remarks=$json_array->remarks;
							// $execution_duration=round(($end-$start)%86400/60,2);
							
							// $platform_sql="SELECT t.platform_id from testplan_tcversions t where t.testplan_id='".$testplan_id."' and t.tcversion_id='".$test_plan_id."';";
							
							// $result =$mysqli->query($platform_sql);
							// $array2=$result->fetch_all();
							// $arr2=$array2[0];
							
							// if(strlen($arr2[0])>0) {
								
								// $platform_id=$arr2[0];
								
							// } else {
								
								// $platform_id='0';
								
							// }
							
							// $result_sql="INSERT into executions (`build_id`,`tester_id`,`execution_ts`,`status`,`testplan_id`,`tcversion_id`,`tcversion_number`,`platform_id`,`execution_type`,`execution_duration`,`notes`,`end_ts`,`log`,`details`,`executor`,`looptimes`,`edition`,`environmental`) VALUES ( '";
							
							// $result_sql.=$json_array->smalledition."','".$json_array->tester."','".$execution_ts."','".$resultsate."','".$testplan_id."','".$test_plan_id."','1','".$platform_id."','2','".$execution_duration."','".$remarks."','".$end_ts."','".$log."','printDocOptions.tpl','".$json_array->tester."','".$m."','".$json_array->edition."','".$json_array->environment."');";
							
							// $result =$mysqli->query($result_sql);
							
							// $result_sql="INSERT into ".$table[1]." (`testplanid`,`exeflag`) VALUES ('".$test_plan_id."','1');";
							// $result =$mysqli->query($result_sql);
							// $result_sql="SELECT id from executions where build_id='".$json_array->smalledition."'  and execution_ts='".$execution_ts."' and testplan_id='".$testplan_id."' and  tcversion_id='".$test_plan_id."' and environmental='".$json_array->environment."';";
						
							// $result =$mysqli->query($result_sql);
							// $executions=$result->fetch_all();
							
							
						
							// $executions_id=$executions[0][0];
							// unset($executions);
							// # #检查是否所有步骤度执行了
							// $steps_sql="SELECT id from tcsteps where parent_id in (SELECT id from nodes_steps where parent_id=".$test_plan_id.") and automation_type !=0 ORDER BY id ASC;";
							
							// $result =$mysqli->query($steps_sql);
							// $steps_arry=$result->fetch_all();
							// $steps=array();
							
							// $stepsindex=0;
							// foreach($steps_arry as $value) {
								// $steps[$stepsindex]=$value[0];
								// $stepsindex=$stepsindex+1;
							// }
							// unset($steps_arry);
							
							// $diffarray=array_diff($stepall,$steppass);
							// $intersectarray=array_intersect($stepall,$steppass);
						
							// foreach($intersectarray as $value) {
							
								// $result_sql="INSERT into execution_tcsteps (`execution_id`,`tcstep_id`,`notes`,`status`) VALUES ('".$executions_id."','".$value."','','p');";
							
								// $result =$mysqli->query($result_sql);
							// }
							
							// foreach($diffarray as $value) {
								// $result_sql="INSERT into execution_tcsteps (`execution_id`,`tcstep_id`,`notes`,`status`) VALUES ('".$executions_id."','".$value."','".$notes."','f');";
							
								// $result =$mysqli->query($result_sql);
							// }
							
							// $diffsteps=array_diff($steps,$stepall);
							// foreach($diffsteps as $value) {
								// $result_sql="INSERT into execution_tcsteps (`execution_id`,`tcstep_id`,`notes`,`status`) VALUES ('".$executions_id."','".$value."','unexecuted','b');";
							
								// $result =$mysqli->query($result_sql);
							// }
						}
					} catch (Exception $e) {
						
						// $Failed=$Failed+1;
						$errMessage=$e->getMessage();
						continue;
					} 
					
				}#end for
				
				try { 
			
					# 判断是否需要发邮件，需要就组装发送邮件内容文件
					// if($json_array->sentmail=="on") {
						// $Endtime=date('Y-m-d h:i:s', time());
						
						// if($Passed==$Total && $Passed>0) {
							// $Verdict="Passed";
						// } elseif ($Failed>0) {
							// $Verdict="Failed";
						// } elseif ($Aborted>0) {
							// $Verdict="Aborted";
						// } elseif ($Canceled>0) {
							// $Verdict="Canceled";
						// } else {
							// $Verdict="Failed";
						// }
						
						// $Totalnum=$Total-$Passed-$Failed-$Aborted-$Canceled;
						// if($Totalnum!=0) {
							// $Failed=$Failed+$Totalnum;
							// $Verdict="Failed";
						// }
						
						// $Mailcontent.="/casamail.txt";
						// if($errMessage!="") {
							// $resultlist.=$errMessage;
						// }
						// $resultlist.="\n-------------------------------------------------------------------------------------------------";
						// $resultlist.="\n############################################\n#####  Passed:      ".$Passed."\n#####  Aborted:     ".$Aborted;
						// $resultlist.="\n#####  Canceled:    ".$Canceled."\n#####  Failed:      ".$Failed."\n#####  ----------------\n#####  Total:       ".$Total;
						// $resultlist.="\n#####  start time:  ".$resultstarttime."\n#####  End time:    ".$Endtime."\n#####  Verdict:     ".$Verdict."\n############################################\n".$testlistname;
						// $subject=$project. "Passed(".$Passed.")Failed(".$Failed.")Canceled(".$Canceled.")Aborted(".$Aborted.")";
						// if (file_exists($Mailcontent)) {
							// unlink($Mailcontent);
						// } 
						// file_put_contents($Mailcontent, $resultlist, FILE_APPEND);
						
					
						
						// # 把压缩包和发邮件的内容发送到发邮件服务器	
						
						// if(is_file($zipMail)){
							// unlink($zipMail);

						// }
						
						// $zipExt=strtolower(pathinfo($zipMail,PATHINFO_EXTENSION));
						// $zip=new ZipArchive();
						
						// if($zip->open($zipMail,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
							// $files = explode(',',$ziploglist);
						
							// foreach($files as $file){
							  
							  // if(is_file($file)){
								
								// $zip->addFile($file);

							  // }

							// }
							// $zip->close();

						// }	
						
						
						
						// if(is_file($zipMail)){
							// unlink($zipMail);

						// }
						# 连接发邮件的服务器
						

						
					}
					
					
				}   catch (Exception $e) {
					
					// $Failed=$Failed+1;
					$errMessage=$e->getMessage();
					continue;
				} 
				
			} catch (Exception $e) {
				
				// $Failed=$Failed+1;
				$errMessage=$e->getMessage();
				continue;
			} 
		
		} #end for
	
	} catch (Exception $e) {
		
		// $mailflag=1;
		// $datetime=date('Y-m-d h:i:s', time()); 
		// # 保存文件
		// $tstable = explode(' ',$datetime);
		// $dir = str_replace('-','',$tstable[0]); 
		// $file= $test_plan_id;
		// $file.='_'.str_replace(':','',$tstable[1]); 
		// $number=rand(10,20);
		// $file.='_'.$number.'.txt';
		
		// $path="./log/".$dir."/".$file;
		// $log='./log.php?data='.$dir.'&file='.$file;
		
		// if(is_dir("./log/".$dir)) {
			// file_put_contents($path, $e->getMessage(), FILE_APPEND);
		// } else {
			// mkdir("./log/".$dir,0777,true);
			// file_put_contents($path, $e->getMessage(), FILE_APPEND);
		// }
		// $ziploglist=$path;
		// $zipMail="./log/".$dir."/loginerror.zip";
		
		
		// $resultlist=$e->getMessage()."\n-------------------------------------------------------------------------------------------------\n############################################\n#####  Passed:      0\n#####  Aborted:     0\n#####  Canceled:    0\n#####  Failed:      0\n#####  ----------------";
		// $resultlist.="#####  Total:       0\n#####  start time:  ".$datetime."\n#####  End time:    ".$datetime."\n#####  Verdict:   Canceled\n############################################\n";
		// $Verdict="Canceled";
		// $subject=$project. "Canceled";
		// file_put_contents($Mailcontent, $resultlist, FILE_APPEND);
	} finally {
		fclose($stream);
		
		try { 
			
			if ($mailflag>0) {
				if($json_array->sentmail=="on") {
					
					# 把压缩包和发邮件的内容发送到发邮件服务器	
					
					// if(is_file($zipMail)){
						// unlink($zipMail);

					// }
					
					// $zipExt=strtolower(pathinfo($zipMail,PATHINFO_EXTENSION));
					// $zip=new ZipArchive();
					
					// if($zip->open($zipMail,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
						// $files = explode(',',$ziploglist);
					
						// foreach($files as $file){
						  
						  // if(is_file($file)){
							
							// $zip->addFile($file);

						  // }

						// }
						// $zip->close();

					// }	
					
					
					
					// if(is_file($zipMail)){
						// unlink($zipMail);

					// }
					
					# 连接发邮件的服务器
					
					
					
				}
				
			}
		} finally {
			$mysqli->close();
			$output = array(
				'staes' => "ok"
			);
			

			$txt =json_encode($output,JSON_UNESCAPED_UNICODE);

			exit($txt);#把结果反馈给客户端
		}
		
		
	}
	




?>