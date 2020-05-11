<?php


require './libs/class.phpmailer.php'; 
require './libs/class.smtp.php'; 

function SendEmail($tester_name, $email_from, $email_pwd, $email_to, $resultlist, $starttime='', $endtime='', $edition='', $environment='', $project='', $total=0, $pass=0, $fail=0, $abort=0, $cancel=0) {
	/**  
	* 
	* @After running MME or SAEGW scripts, use PHPMailer() to send email
	
	* @return array 返回类型
	*/  
   
	$bodyverdict='';
	if($pass==$total && $pass>0) {
		$Verdict='pass';
		$bodyverdict='<span style="color: green;">Verdict：pass</span>';
	} elseif ($fail>0) {
		$Verdict='fail';
		$bodyverdict='<span style="color: red;">Verdict：fail</span>';
	} elseif ($abort>0) {
		$Verdict='abort';
		$bodyverdict='<span style="color: red;">Verdict：abort</span>';
	} elseif ($cancel>0) {
		$Verdict='cancel';
		$bodyverdict='<span style="color: red;">Verdict：cancel</span>';
	} else {
		$Verdict='fail';
		$bodyverdict='<span style="color: red;">Verdict：fail</span>';
	}
	
	$totalnum=$total-$pass-$fail-$abort-$cancel;
	if($totalnum!=0) {
		$fail=$fail+$totalnum;
		$bodyverdict='<span style="color: red;">Verdict：fail</span>';
		$Verdict='fail';
	}
	
	// $subject.=' Version:'.$json_array->edition.' '.$tester_name[0][0].' '.$Verdict.' Executor:'.$tester_name[0][1].$tester_name[0][2];
	$subject = $project.' Version:'.$edition.' '.$tester_name[0][0].' '.$Verdict.' Executor:'.$tester_name[0][1].$tester_name[0][2].' pass('.$pass.')fail('.$fail.')abort('.$abort.')cancel('.$cancel.')';
	
	
	$mail = new PHPMailer();  
	//是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式 
	$mail->SMTPDebug = 1; 
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
	$mail->Username =$email_from; 
	$mail->Password = $email_pwd; 
	//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱” 
	$mail->From = $email_from; 
	//邮件正文是否以html方式发送  
	$mail->isHTML(true);  
	//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大 
	$emailaddress = explode(',',$email_to); 
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
								<span style="color: blue;">total：'.$total.'</span>
							</div>
							<div style="display:inline-block;text-algin: left;width:49%;">
								start time：<span>'.$starttime.'</span>
							</div>
							<div style="display:inline-block;text-algin: left;width:49%;">
								End time：<span>'.$endtime.'</span>
							</div>
							
							
							<div style="display:inline-block;text-algin: left;width:49%;">
								<span class="testTime" style="text-algin: left;">pass：<span>'.$pass.'</span></span>
							</div>
							<div style="display:inline-block;text-algin: left;width:49%;">
								<span class="testTime" style="text-algin: left;">abort：<span>'.$abort.'</span></span>
							</div>
							<div style="display:inline-block;text-algin: left;width:49%;">
								<span class="testTime" style="text-algin: left;">cancel：<span>'.$cancel.'</span></span>
							</div>
							<div style="display:inline-block;text-algin: left;width:49%;">
								<span class="testTime" style="text-algin: left;">fail：<span>'.$fail.'</span></span>
							</div>
							<div style="display:inline-block;text-algin: left;width:49%;">
								<span class="testTime" style="text-algin: left;">Environment：<span>'.$environment.'</span></span>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
					   <th colspan="1">Case numbe</th>
					   <th colspan="1">Testlink id</th>
					   <th colspan="15">Case name</th>
					   <th colspan="3">Verdict</th>
					   <th colspan="3"> Duration</th>
					   <th colspan="3">Log</th>
					   <th colspan="3">Testplan</th>
					</tr>
					'.$resultlist.'
				</tbody>
			</table>
		</div>';
	
	$status = $mail->send(); 
	return $status;
}


function Get_Email_from_pwd_to($tcl_server_ip, $edition, $environment) {
	/**
	Get the information of three config from TCL config file: $DEFAULT_EMAIL(from), $DEFAULT_EMAIL(to), $DEFAULT_EMAIL(pwd), and return array
	*tcl_server_ip: like "172.0.11.88"
	*edition: like "492" "493" "495"
	*environment: tcl config file name, like "saegw-mzftest.tcf"
	**/
	
	$connection = ssh2_connect ($tcl_server_ip, 22);
	if($connection) {
		if(!ssh2_auth_password($connection, 'root', 'casa')) {
			$loginflag=true;
			$result_dio="Fail to login " .$table[0] ." with root casa. \n";
		}
	} else {
		$loginflag=true;
		$result_dio="Fail connect server " .$table[0].".\n";
	}
	
	if (!$loginflag) {
		$config='cd /public/casa-'.$edition.'/test/Config;cat '.$environment.' | grep "IP_EMAIL";cat '.$environment.' | grep "DEFAULT_EMAIL"';
	
		// file_put_contents("log.txt", "\n config: ".$config, FILE_APPEND);
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
		if ($mailinfo[$i]=="DEFAULT_EMAIL(from)") {
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
	
	$result = array(
		'from' => $from,
		'to' => $to,
		'pwd' => $pwd,
	);
	
	return $result;
	
}

