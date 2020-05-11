<?php
	
	@include_once('config_db.inc.php');
	if( !defined('DB_HOST') ) {
		define('DB_HOST','localhost' );
	}
	$postStr = file_get_contents("php://input"); //获取POST数据
	
	$json_array=json_decode($postStr);

	$stream ="";
	$ziploglist="Initialization";
	$zipName="";
	$souretxtName="";
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
	
	$errMessage="";

	
	$array_test_name=array();
	
	try { 
		$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		mysqli_query($mysqli,"SET NAMES utf8");
		if ($mysqli->connect_error) {
			die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
		$table = explode('s_',$json_array->table);
		$testplan_sql="SELECT testplan_id from builds where id='".$json_array->smalledition."';";
	
		$result =$mysqli->query($testplan_sql);
		$array1=$result->fetch_all();
		$arr1=$array1[0];
		$testplan_id=$arr1[0];
		$projectarry = explode('/',$table[2]);
		$numb=count($projectarry)-1;
	
		$project= substr($projectarry[$numb],0,-5);
	
		unset($projectarry);
		
		// 去掉[]
		$json_array->caselist = strtr($json_array->caselist, '[', ' '); 
		$json_array->caselist = strtr($json_array->caselist, ']', ' '); 
		$test_list=trim($json_array->caselist);

		$test_plan_id = '';
		$test_list_id = '';
		// 登陆连接标识
		$loginflag=true;
		// 获取测试者，版本
		$sql1= "SELECT t.name,u.last,u.first from builds t,users u where t.id=".$json_array->smalledition." and u.id=".$json_array->tester.";";
	
		$result =$mysqli->query($sql1);
		$tester_name=$result->fetch_all();
		
		// 根据testlist查询testplan,确认执行顺序，一般按用例名称排序执行，二按用例指定顺序执行
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

			
			$stream = ssh2_exec($connection, $config);  //执行结果以流的形式返回
			
			
			$dio_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);  //获得标准输入输出留
			$err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);  //获得错误输出留
			stream_set_blocking($err_stream, true);
			stream_set_blocking($dio_stream, true);
			
			$result_err = stream_get_contents($err_stream);
			
			$result_dio = stream_get_contents($dio_stream); //获取流的内容，即命令的返回内容

		}
		// 替换字符串
		$search = array('global',';','set','{','}','"');
		$replace = " ";
		$result_dio = str_replace($search,$replace,$result_dio);
		// 去除多余空格
		$result_dio=preg_replace ( "/\s(?=\s)/","\\1", $result_dio );
		// 去除首尾空格
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
				$to.=$mailinfo[$i]." ";
			}
		}
		
		
		for($m=1;$m<= intval($json_array->looptimes);$m++) {
			try { 
				if($m>1) {
					$result_sql="truncate table  ".$table[1].";";
					
					$result =$mysqli->query($result_sql);
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
				$zipName="";
				$resultstarttime=date('Y-m-d h:i:s', time());
				
				$souretxtName="";
				$ditzipName="";
				$subject="";
				$Verdict="";
				$resultlist="-------------------------------------------------------------------------------------------------\nTest                 Duration  Verdict     Errs    Warns           Type             Bugs  Fixed \n-------------------------------------------------------------------------------------------------";
				
				for($i=0;$i<count($array);$i++)
				{
					
					try { 
						$result_dio ="";
						$result_err ="";
						if($array[$i]) {//如果数据存在输出数据
							$arr=$array[$i];
							
							$test_plan_id=$arr[0];
							
							#筛选test_plan一样的caselist一起执行
							$sql1= "SELECT  parent_id from nodes_hierarchy where node_type_id='4' and id='".$arr[0]."' and id not in (SELECT testplanid from ".$table[1]." where exeflag='1')  ORDER BY parent_id ASC;";
							// file_put_contents("log.txt", "\n$sql1", FILE_APPEND);
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
								// file_put_contents("log.txt", "\n config:".$config, FILE_APPEND);
								$command='cd /public/casa-'.$json_array->edition.'/test;./lte.sh '.$json_array->edition.' /public/casa-'.$json_array->edition.'/test/Config/'.$json_array->environment.' /public/casa-'.$json_array->edition.$table[2].' 1';
							
								$stream = ssh2_exec($connection, $config);  //执行结果以流的形式返回
								$stream = ssh2_exec($connection, $command);  //执行结果以流的形式返回

								
								$dio_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);  //获得标准输入输出留
								$err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);  //获得错误输出留
								stream_set_blocking($err_stream, true);
								stream_set_blocking($dio_stream, true);
								
								$result_err = stream_get_contents($err_stream);
								$result_dio = stream_get_contents($dio_stream); //获取流的内容，即命令的返回内容
							
							}
							
							
							$datetime=date('Y-m-d h:i:s', time()); 
							#保存文件
							$tstable = explode(' ',$datetime);
							$dir = str_replace('-','',$tstable[0]); 
							$file= $test_plan_id;
							$file.='_'.str_replace(':','',$tstable[1]); 
							$number=rand(10,20);
							$file.='_'.$number.'.txt';
							
							$path="./log/".$dir."/".$file;
							
							
							
							$log='./log.php?data='.$dir.'&file='.$file;
							if($ziploglist=="Initialization") {
								$ziploglist=$path;
								$timenum = str_replace(":","",$tstable[1]);
								$zipName="./log/".$dir."/".$project."_".$timenum.".zip";
								$ditzipName="/home/".$project."_".$timenum.".zip";
								$souretxtName="./log/".$dir;
								$subject=$project;
							} else {
								$ziploglist.=",".$path;
							}
							
							
							if(is_dir("./log/".$dir)) {
								if(strlen($result_err)>1) {
									file_put_contents($path, $result_dio, FILE_APPEND);
									file_put_contents($path, $result_err, FILE_APPEND);
								} else {
									file_put_contents($path, $result_dio, FILE_APPEND);
								}
							} else {
								mkdir("./log/".$dir,0777,true);
								if(strlen($result_err)>1) {
									file_put_contents($path, $result_dio, FILE_APPEND);
									file_put_contents($path, $result_err, FILE_APPEND);
								} else {
									file_put_contents($path, $result_dio, FILE_APPEND);
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
							 // #逐行解析日志内容
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
										$resultlist.="\n".$file_arr[$k];
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
							
							$result_sql.=$json_array->smalledition."','1','".$execution_ts."','".$resultsate."','".$testplan_id."','".$test_plan_id."','1','".$platform_id."','2','".$execution_duration."','".$remarks."','".$end_ts."','".$log."','printDocOptions.tpl','".$json_array->tester."','".$m."','".$json_array->edition."','".$json_array->environment."');";
							
							$result =$mysqli->query($result_sql);
							
							$result_sql="INSERT into ".$table[1]." (`testplanid`,`exeflag`) VALUES ('".$test_plan_id."','1');";
							$result =$mysqli->query($result_sql);
							$result_sql="SELECT id from executions where build_id='".$json_array->smalledition."'  and execution_ts='".$execution_ts."' and testplan_id='".$testplan_id."' and  tcversion_id='".$test_plan_id."' and environmental='".$json_array->environment."';";
						
							$result =$mysqli->query($result_sql);
							$executions=$result->fetch_all();
							
							// file_put_contents("log.txt", "\n result_sql:".$result_sql, FILE_APPEND);
						
							$executions_id=$executions[0][0];
							unset($executions);
							// #检查是否所有步骤度执行了
							$steps_sql="SELECT id from tcsteps where parent_id in (SELECT id from nodes_steps where parent_id=".$test_plan_id.") ORDER BY id ASC;";
							
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
						// file_put_contents("log.txt", "\n  end for 22 continue", FILE_APPEND);
						$Failed=$Failed+1;
						$errMessage=$e->getMessage();
						continue;
					} 
					// finally { 
						#continue;
					// }
					// file_put_contents("log.txt", "\n  end for 22", FILE_APPEND);
				}//end for
				
				try { 
			
					// 判断是否需要发邮件，需要就组装发送邮件内容文件
					if($json_array->sentmail=="on") {
						$Endtime=date('Y-m-d h:i:s', time());
						
						if($Passed==$Total && $Passed>0) {
							$Verdict="Passed";
						} elseif ($Failed>0) {
							$Verdict="Failed";
						} elseif ($Aborted>0) {
							$Verdict="Aborted";
						} elseif ($Canceled>0) {
							$Verdict="Canceled";
						} else {
							$Verdict="Failed";
						}
						
						$Totalnum=$Total-$Passed-$Failed-$Aborted-$Canceled;
						if($Totalnum!=0) {
							$Failed=$Failed+$Totalnum;
							$Verdict="Failed";
						}
						
						$souretxtName.="/casamail.txt";
						if($errMessage!="") {
							$resultlist.=$errMessage;
						}
						$resultlist.="\n-------------------------------------------------------------------------------------------------";
						$resultlist.="\n############################################\n#####  Passed:      ".$Passed."\n#####  Aborted:     ".$Aborted;
						$resultlist.="\n#####  Canceled:    ".$Canceled."\n#####  Failed:      ".$Failed."\n#####  ----------------\n#####  Total:       ".$Total;
						$resultlist.="\n#####  start time:  ".$resultstarttime."\n#####  End time:    ".$Endtime."\n#####  Verdict:     ".$Verdict."\n############################################\n".$testlistname;
						if (file_exists($souretxtName)) {
							unlink($souretxtName);
						} 
						file_put_contents($souretxtName, $resultlist, FILE_APPEND);
						
					
						
						// 把压缩包和发邮件的内容发送到发邮件服务器	
						
						if(is_file($zipName)){
							unlink($zipName);

						}
						
						$zipExt=strtolower(pathinfo($zipName,PATHINFO_EXTENSION));
						$zip=new ZipArchive();
						
						if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
							$files = explode(',',$ziploglist);
						
							foreach($files as $file){
							  
							  if(is_file($file)){
								
								$zip->addFile($file);

							  }

							}
							$zip->close();

						}	
						
						
						
						
						// 连接发邮件的服务器
						$connection = ssh2_connect ($IP_EMAIL, 22);
						if($connection) {
							if(!ssh2_auth_password($connection, 'root', 'casa')) {
								$result_dio="Fail to login " .$IP_EMAIL." with root casa. \n";	
							} 
							
						} else {
							
							$result_dio="Fail connect server " .$IP_EMAIL.".\n";
						}	
						
						
						$flag = ssh2_scp_send($connection, $souretxtName, "/home/casamail.txt", 0644);  //默认权限为0644，返回为bool
						
						$flag = ssh2_scp_send($connection, $zipName, $ditzipName, 0644);  //默认权限为0644，返回为bool
						$subject.=' Version:'.$json_array->edition.' '.$tester_name[0][0].' '.$Verdict.' Executor:'.$tester_name[0][1].$tester_name[0][2];

						$mail='mail -s "'.$subject.'" -a '.$ditzipName.' '.$to.' < /home/casamail.txt';
						$stream = ssh2_exec($connection, $mail);  //执行结果以流的形式返回
						

						$delete='rm -f /home/casamail.txt;rm -f '.$ditzipName;
						$stream = ssh2_exec($connection, $delete);  //执行结果以流的形式返回	
						unlink($zipName);

						
					}
					
					
				}   catch (Exception $e) {
					// file_put_contents("log.txt", "\n  end for continue11", FILE_APPEND);
					$Failed=$Failed+1;
					$errMessage=$e->getMessage();
					continue;
				} 
				
			} catch (Exception $e) {
				// file_put_contents("log.txt", "\n  end for continue", FILE_APPEND);
				$Failed=$Failed+1;
				$errMessage=$e->getMessage();
				continue;
			} 
		// file_put_contents("log.txt", "\n  end for", FILE_APPEND);
		} //end for
	
	} catch (Exception $e) {
		// file_put_contents("log.txt", "\n  33333over", FILE_APPEND);
		$mailflag=1;
		$datetime=date('Y-m-d h:i:s', time()); 
		// 保存文件
		$tstable = explode(' ',$datetime);
		$dir = str_replace('-','',$tstable[0]); 
		$file= $test_plan_id;
		$file.='_'.str_replace(':','',$tstable[1]); 
		$number=rand(10,20);
		$file.='_'.$number.'.txt';
		
		$path="./log/".$dir."/".$file;
		$log='./log.php?data='.$dir.'&file='.$file;
		
		if(is_dir("./log/".$dir)) {
			file_put_contents($path, $e->getMessage(), FILE_APPEND);
		} else {
			mkdir("./log/".$dir,0777,true);
			file_put_contents($path, $e->getMessage(), FILE_APPEND);
		}
		$ziploglist=$path;
		$zipName="./log/".$dir."/loginerror.zip";
		$ditzipName="/home/loginerror.zip";
		
		
		$resultlist=$e->getMessage()."\n-------------------------------------------------------------------------------------------------\n############################################\n#####  Passed:      0\n#####  Aborted:     0\n#####  Canceled:    0\n#####  Failed:      0\n#####  ----------------";
		$resultlist.="#####  Total:       0\n#####  start time:  ".$datetime."\n#####  End time:    ".$datetime."\n#####  Verdict:   Canceled\n############################################\n";
		$Verdict="Canceled";
		file_put_contents($souretxtName, $resultlist, FILE_APPEND);
	} finally {
		fclose($stream);
		// file_put_contents("log.txt", "\n  1212over", FILE_APPEND);
		try { 
			// file_put_contents("log.txt", "\n  over".$mailflag, FILE_APPEND);
			if ($mailflag>0) {
				if($json_array->sentmail=="on") {
					
					// 把压缩包和发邮件的内容发送到发邮件服务器	
					
					if(is_file($zipName)){
						unlink($zipName);

					}
					
					$zipExt=strtolower(pathinfo($zipName,PATHINFO_EXTENSION));
					$zip=new ZipArchive();
					
					if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
						$files = explode(',',$ziploglist);
					
						foreach($files as $file){
						  
						  if(is_file($file)){
							
							$zip->addFile($file);

						  }

						}
						$zip->close();

					}	
					
					
					
					
					// 连接发邮件的服务器
					$connection = ssh2_connect ($IP_EMAIL, 22);
					
					if($connection) {
						if(!ssh2_auth_password($connection, 'root', 'casa')) {
							
							$result_dio="Fail to login " .$IP_EMAIL." with root casa. \n";	
						} 
						
					} else {
						
						$result_dio="Fail connect server " .$IP_EMAIL.".\n";
					}	
				
					$flag = ssh2_scp_send($connection, $souretxtName, "/home/casamail.txt", 0644);  //默认权限为0644，返回为bool
					$flag = ssh2_scp_send($connection, $zipName, $ditzipName, 0644);  //默认权限为0644，返回为bool	
					$subject.=' Version:'.$json_array->edition.' '.$tester_name[0][0].' '.$Verdict.' Executor:'.$tester_name[0][1].$tester_name[0][2];
					$mail='mail -s "'.$subject.'" -a '.$ditzipName.' '.$to.' < /home/casamail.txt';
					$stream = ssh2_exec($connection, $mail);  //执行结果以流的形式返回
					

					$delete='rm -f /home/casamail.txt;rm -f '.$ditzipName;
					$stream = ssh2_exec($connection, $delete);  //执行结果以流的形式返回	
					unlink($zipName);
					
					
				}
				
			}
		} finally {
			$mysqli->close();
			$output = array(
				'staes' => "ok"
			);
			// file_put_contents("log.txt", "\n  over", FILE_APPEND);

			$txt =json_encode($output,JSON_UNESCAPED_UNICODE);

			exit($txt);//把结果反馈给客户端
		}
	}
	




?>