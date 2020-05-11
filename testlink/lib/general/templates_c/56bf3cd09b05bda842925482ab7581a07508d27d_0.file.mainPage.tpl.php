<?php
/* Smarty version 3.1.33, created on 2020-02-12 15:41:08
  from '/var/www/html/testlink/automation/mainPage.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5e43ac14d548b1_79265980',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '56bf3cd09b05bda842925482ab7581a07508d27d' => 
    array (
      0 => '/var/www/html/testlink/automation/mainPage.tpl',
      1 => 1579591838,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5e43ac14d548b1_79265980 (Smarty_Internal_Template $_smarty_tpl) {
?><!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>CASA LTE-SQA 自动化测试平台</title>
<base href="<?php echo '<%';?>=basePath<?php echo '%>';?>">

<?php echo '<script'; ?>
 type="text/javascript">
	

	projectlist();
	initialization('Initialization');
	
	<!-- 请求环境数据 -->
	function initialization(id){
		var xhr = new XMLHttpRequest;
		var honst = window.location.host;
		
		xhr.open('post', 'http://'+honst+'/testlink/automation/initialization.php');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(JSON.stringify(id));
		xhr.onreadystatechange = function() {
			if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status ==304)) { //响应完成并且响应码为200或304
				var rst=xhr.responseText;
				<!-- console.log(rst); -->
				localStorage.removeItem("obj");
				<!-- console.log("*************tester*****************"); -->
				localStorage.setItem('obj', rst);
				
				<!-- 根据id获取iframe1这个对象； -->
				var iframe1 = document.getElementById('top');
				<!-- 看到reload就该知道是刷新了这个iframe1了。 -->
				iframe1.contentWindow.location.reload(true);
			}
		};
	}
	
	<!-- 请求项目列表 -->
	function projectlist(){
		var projectxhr = new XMLHttpRequest;
		var honst = window.location.host;
		projectxhr.open('post', 'http://'+honst+'/testlink/automation/projectlist.php');
		projectxhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		projectxhr.send();
		
		projectxhr.onreadystatechange = function() {
			if(projectxhr.readyState == 4 && (projectxhr.status == 200 || projectxhr.status ==304)) { //响应完成并且响应码为200或304
				
				var rst=projectxhr.responseText;
				
				localStorage.removeItem("projectobj");
				localStorage.setItem('projectobj',rst);
				<!-- 根据id获取iframe1这个对象； -->
				var iframe1 = document.getElementById('leftmenu');
				<!-- 看到reload就该知道是刷新了这个iframe1了。 -->
				iframe1.contentWindow.location.reload(true);
			}
		};
		
	}
	
	
	<!-- 请求用例数据 -->
	function getcaselist(id){
		
		var honst = window.location.host;
		var xhr1 = new XMLHttpRequest;
		xhr1.open('post', 'http://'+honst+'/testlink/automation/caselist.php');
		xhr1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		
		var data = new Array();
		var contact = new Object();
		if (localStorage.getItem('smalleditionobj')) {
			
			contact.smalledition = localStorage.getItem('smalleditionobj');
			
		} else {
			
			contact.smalledition = "Initialization";
			
		}
		
		
		
		contact.id = id;
		
		//转换json打印
	
		var jsondata = JSON.stringify(contact);
		
		xhr1.send(jsondata);
		<!-- xhr1.send(); -->

		xhr1.onreadystatechange = function() {
			if(xhr1.readyState == 4 && (xhr1.status == 200 || xhr1.status ==304)) { //响应完成并且响应码为200或304
				
				var rs=xhr1.responseText;
				
				localStorage.removeItem("caseobj");
				localStorage.setItem('caseobj', rs);
				<!-- console.log("*************getcaselist1111*****************"); -->
				<!-- console.log(rs); -->
				<!-- 根据id获取iframe1这个对象； -->
				var iframe1 = document.getElementById('main');
				<!-- 看到reload就该知道是刷新了这个iframe1了。 -->
				iframe1.contentWindow.location.reload(true);
			}
		};
	}
<?php echo '</script'; ?>
>
</head>

<frameset rows="12%,88%,*" border="0.5">
        <frame  src=<?php echo $_smarty_tpl->tpl_vars['args']->value->toppath;?>
 id="top" name="top" noresize="noresize">
        <frameset cols="15%,*" border="0.5">
            <frame  src=<?php echo $_smarty_tpl->tpl_vars['args']->value->leftpath;?>
 id="leftmenu" name="leftmenu" noresize="noresize">
            <frame  src=<?php echo $_smarty_tpl->tpl_vars['args']->value->mainpath;?>
 id="main" name="main">
        </frameset>

</frameset>




</html>
<?php }
}
