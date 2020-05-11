<?php
//Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';
$mail = new PHPMailer(true);

$sent_to = $_REQUEST["sent_to"];
$task_info = $_REQUEST["task_info"];
$project_id = $task_info['project_id'];
// read project name
$config = require('db_config.php');
require './db_base.php';
$a = new db_control($config['host'], $config['user'], $config['passwd'], $config['db']);
$a->connect();
$array = [
  "id" => $project_id,
];
if ($a->select($array, $config['project'])) {
  die('Select fail');
} else {
  $project = json_decode($a->json_data, true);
  $project_name = $project[0]['name']; 
};
$mail_subject = '任务提醒 : '.$task_info['name'];
$body_init = <<<data
    <!DOCTYPE html><html lang="en"><head>                                                                      
    <meta charset="UTF-8">                                                  
    <title></title>                                                         
    </head>                                                                     
    <style type="text/css">                                                     
    table.gridtable {                                                           
       font-family: verdana,arial,sans-serif;                                  
       font-size:12px;                                                         
       color:#333333;                                                          
       border-width: 1px;                                                      
       border-color: #666666;                                                  
       border-collapse: collapse;                                              
    }                                                                           
    table.gridtable th {                                                        
       border-width: 1px;                                                      
       padding: 10px;                                                          
       border-style: solid;                                                    
       border-color: #666666;                                                  
       background-color: #dedede;                                              
       text-decoration: none;                                                  
       text-align:left;                                                        
    }                                                                           
    table.gridtable td {                                                        
       border-width: 1px;                                                      
       padding: 10px;                                                          
       border-style: solid;                                                    
       border-color: #666666;                                                  
       background-color: #ffffff;                                              
    }                                                                           
    .total_pass {                                                               
       color:green;                                                            
    }                                                                           
    .total_fail {                                                               
       color:red;                                                              
    }                                                                           
    </style>                                                                    
    <body>                                                                      
    <table class="gridtable"> 
data;
$body_end = <<<data
    </td></tr></table>
    <p>请登录<a href="http://172.0.10.81/testlink/index.php?caller=login&viewer=&type=project_manage_platform">任务管理平台</a>查看此任务</p>
    </br></br>
    <p>Do not reply this email,it is triggered by task_manage_system</br>
    Thanks all and please let me know if you have any question</br>
    xiaojinshui<p></body></html>
data;
$mail_body = ($body_init .
    '<tr><td>任务所属项目</td><td>任务名称</td><td>创建时间</td><td>开始时间</td><td>预期完成时间</td></tr><tr><td>' . 
    $project_name . '</td><td>' . $task_info['name'] . '</td><td>' . $task_info['create_time'] . '</td><td>' . 
    $task_info['start_time'] . '</td><td>' .  $task_info['est_com_time'] . $body_end);

try {
  //Server settings
  $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
  $mail->isSMTP();                                            // Send using SMTP
  $mail->Host       = 'smtp.qiye.163.com';                    // Set the SMTP server to send through
  $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
  $mail->Username   = 'xiaojinshui@casachina.com.cn';                     // SMTP username
  $mail->Password   = 'xjs123456';                               // SMTP password
  $mail->Port       = 25;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

  //Recipients
  $mail->setFrom('xiaojinshui@casachina.com.cn');
  $mail->addAddress($sent_to);     // Add a recipient

  // Attachments
  //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
  //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

  // Content
  $mail->CharSet = 'UTF-8';  
  $mail->isHTML(true);                            // Set email format to HTML
  $mail->Subject = '=?utf-8?B?' . base64_encode($mail_subject) . '?=';
  $mail->Body    = $mail_body; 
  //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

  $mail->send();
  echo 'sentsuccess';
} catch (Exception $e) {
  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
