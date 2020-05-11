<?php
/* Smarty version 3.1.33, created on 2019-12-13 07:14:07
  from 'F:\wampserver\www\automation\mainPage.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5df33a3fec3a30_93981443',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8ad9c5442300cb928cec4ed5e1f4fa0ebed82ac5' => 
    array (
      0 => 'F:\\wampserver\\www\\automation\\mainPage.tpl',
      1 => 1574758036,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5df33a3fec3a30_93981443 (Smarty_Internal_Template $_smarty_tpl) {
?><!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>CASA LTE-SQA 自动化测试平台</title>
<base href="<?php echo '<%';?>=basePath<?php echo '%>';?>">
</head>
<?php echo '<script'; ?>
 type="text/javascript">
	
	initialization('Initialization');
	<!-- 请求环境数据 -->
	function initialization(id){
		var xhr = new XMLHttpRequest;
		xhr.open('post', 'initialization.php');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(JSON.stringify(id));
		xhr.onreadystatechange = function() {
			if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status ==304)) { //响应完成并且响应码为200或304
				var rst=xhr.responseText;
				
				localStorage.setItem('obj', rst);
				<!-- 根据id获取iframe1这个对象； -->
				var iframe1 = document.getElementById('top');
				<!-- 看到reload就该知道是刷新了这个iframe1了。 -->
				iframe1.contentWindow.location.reload(true);
			}
		};
	}
	projectlist();
	<!-- 请求项目列表 -->
	function projectlist(){
		var projectxhr = new XMLHttpRequest;
		projectxhr.open('post', 'projectlist.php');
		projectxhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		projectxhr.send();
		projectxhr.onreadystatechange = function() {
			if(projectxhr.readyState == 4 && (projectxhr.status == 200 || projectxhr.status ==304)) { //响应完成并且响应码为200或304
				
				var rst=projectxhr.responseText;
				<!-- console.log(rst); -->
				localStorage.setItem('projectobj', rst);
				
			}
		};
	}
	
	getcaselist('Initialization');
	<!-- 请求用例数据 -->
	function getcaselist(id){
		
		
		var xhr1 = new XMLHttpRequest;
		xhr1.open('post', 'caselist.php');
		xhr1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		
		 <!-- var tester=localStorage.getItem('tester'); -->
		
		<!-- console.log("tester"); -->
		<!-- console.log(tester); -->
		
		<!-- console.log(id); -->
		var data = new Array();
		var contact = new Object();
		if (localStorage.getItem('smalleditionobj')) {
			
			contact.smalledition = localStorage.getItem('smalleditionobj');
			
		} else {
			
			contact.smalledition = "Initialization";
			
		}
		
		
		<!-- data[1] = id; -->
		contact.id = id;
		
		//转换json打印
		<!-- var jsondata = JSON.stringify(data); -->
		var jsondata = JSON.stringify(contact);
		
		xhr1.send(jsondata);
		

		xhr1.onreadystatechange = function() {
			if(xhr1.readyState == 4 && (xhr1.status == 200 || xhr1.status ==304)) { //响应完成并且响应码为200或304
				
				var rs=xhr1.responseText;
				
				
				localStorage.setItem('caseobj', rs);
				
				<!-- 根据id获取iframe1这个对象； -->
				var iframe1 = document.getElementById('main');
				<!-- 看到reload就该知道是刷新了这个iframe1了。 -->
				iframe1.contentWindow.location.reload(true);
			}
		};
	}
<?php echo '</script'; ?>
>
<frameset rows="13%,87%,*" border="0.5">
        <frame  src="top.html" id="top" name="top" noresize="noresize">
        <frameset cols="15%,*" border="0.5">
            <frame  src="leftmenu.html" id="leftmenu" name="leftmenu" noresize="noresize">
            <frame  src="main.html" id="main" name="main">
        </frameset>

</frameset>

<body>
</body>
</html>
<?php }
}
