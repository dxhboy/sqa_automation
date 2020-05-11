document.write("<script language=javascript src='js/jquery-3.4.1.js'></script>");
document.write("<script language=javascript src='Python_Server_Config.js'></script>");

function Choose_Mode_And_Run() {

	var mode = $('select#mode').val()
	console.log('运行模式:'+mode);
	
	var mycars = ready_runcase(mode);
	
	if (mycars != 0) {
		var contact = mycars[0];
		var looptimes = mycars[1];
		
		casa_Http_Request(contact, looptimes, 1, mode);
	}
}

function ready_runcase(mode) {
	/*
	Get the information from web, store in the array and return
	
	array[0]: Object contact
	array[1]: number looptimes
	*/

	// < !--获取用例列表--><!--获取子页面（兄弟页面）元素-->
	var liobj = window.parent.frames["main"].document.getElementsByName("mychk");
	var objss = JSON.parse(localStorage.getItem('obj'));
	var obj1=objss.environmentalall;
	var obj=obj1[0];
	
	
	var caselist = []; //获取选中的id
	var projectid=localStorage.getItem('projectid');
	var index = 0;
	for (var k in liobj) {
		if (liobj[k].checked) {
			caselist.push(liobj[k].value); 
		}
	}
   
	if (caselist.length == 0) { // 只能用 === 运算来测试某个值是否是未定义的
		alert("没有勾选所执行的用例");
		return 0
	} 
	
	// < !--获取版本号--><!--不能用name不然抓不到值-->
	var editionobj = document.getElementById('edition'); //定位id
  
	var edition = editionobj.value;

	if (edition == "" || edition == null || edition == undefined || edition == "No") { // 只能用 === 运算来测试某个值是否是未定义的
		alert("没有选择执行版本");
		return 0
	} 
	// < !--获取版本号--><!--不能用name不然抓不到值-->
	var smalleditionobj = document.getElementById('smalledition'); //定位id
  
	var smalledition = smalleditionobj.value;
	if (smalledition == "" || smalledition == null || smalledition == undefined || smalledition == "No") { // 只能用 === 运算来测试某个值是否是未定义的
		alert("没有选择执行版本");
		return 0
	} 
	// < !--获取测试人-->
	// var testerobj = document.getElementById('tester'); //定位id
	// var tester = testerobj.value;

	// if (tester == "" || tester == null || tester == undefined || tester == "No") { // 只能用 === 运算来测试某个值是否是未定义的
		// alert("没有选择测试人");
		// forbidsubmit("enable");
		// return 0
	// } 
	// < !--执行次数-->
	var timesobj = document.getElementById('looptimes'); //定位id
	var looptimes = timesobj.value;
	if (looptimes == "" || looptimes == null || looptimes == undefined ) { // 只能用 === 运算来测试某个值是否是未定义的
		looptimes = "1";
	}

	// < !--执行环境--><!--不能用name不然抓不到值-->
	var environmentobj = document.getElementById('environment'); //定位id
	var environment = environmentobj.value;
	var table=obj[environment];
	// console.log("environment:"+environment);
	if (environment == "" || environment == null || environment == undefined || environment == "No") { // 只能用 === 运算来测试某个值是否是未定义的
		alert("没有选择执行环境");
		return 0
	}

	// < !--发邮件--><!--不能用name不然抓不到值-->
	var sentmailobj = document.getElementById('sentmail'); //定位id
	var sentmail = sentmailobj.value;
	
	 // < !--指定执行顺序--><!--不能用name不然抓不到值-->
	var orderobj = document.getElementById('order'); //定位id
	var order = orderobj.value;

	// < !--备注-->
	var remarksobj = document.getElementById('remarks'); //定位id
	var remarks = remarksobj.value;
   
	
	var objs = JSON.parse(localStorage.getItem('obj'));
	var tester=objs.tester;

	if (mode == "mode2") {
		//处理一下测试用例id, 删除拥有同一个testplan的id, 比如[31822, 32374, 32378], (31822和32374拥有拥有同一份testplan),处理后变成[32822, 32378]
		var caselist = Delete_Testcase_with_Same_Testplan(caselist);
		console.log(caselist);
		if (caselist == null) {
			return 0;
		}
	}
	
	var contact = new Object();
	contact.projectid = projectid;
	contact.caselist = caselist;  
	contact.table = table;
	contact.edition = edition;
	contact.smalledition = smalledition;
	contact.tester = tester[0];
	contact.looptimes = 1;
	contact.environment = environment;
	contact.sentmail = sentmail;
	contact.remarks = remarks;
	contact.order = order;
	contact.mode = mode;
	
	if (mode == "mode2") {
		contact.email_flag = false;
		contact.finish_pass_testcase_mode2 = [];
		contact.finish_fail_testcase_mode2 = [];
		contact.finish_stop_testcase_mode2 = [];
		contact.waiting_testcase_mode2 = caselist;
		contact.wait_run_testcase_mode2 = caselist;
		contact.total_num = 0;
		contact.pass_num = 0;
		contact.fail_num = 0;
		contact.cancel_num = 0;
		contact.abort_num = 0;
		contact.all_result_info_for_email = '';
		
		var myDate = new Date();
		contact.starttime = myDate.toLocaleString();
	}
	
	//console.log(contact);
	console.log('开始运行脚本,总共'+looptimes+'次,测试用例列表:');
	console.log(caselist);

	var json_caselist = JSON.stringify(caselist);
	
	if (mode == "mode1" || mode == "mode3") {
		localStorage.setItem('testcase_running',json_caselist);
	}
	
	var mycars = new Array();
	mycars[0] = contact;
	mycars[1] = looptimes;
	
	//清除缓存.防止错误
	localStorage.removeItem('stop_all_case_flag');
	localStorage.removeItem('testcase_running');
	localStorage.removeItem('testcase_waitting');
	localStorage.removeItem('finish_pass_testcase');
	localStorage.removeItem('finish_fail_testcase');
	localStorage.removeItem('finish_stop_testcase');
	
	return mycars
}

function casa_Http_Request(contact, looptimes, i, mode) {
	/*
	 发送http请求到文件或者服务器完成脚本运行
	 
	 contact:携带运行脚本需要的信息,是一个对象,函数ready_runcase生成
	 looptimes:总循环次数,函数ready_runcase生成
	 i:当前循环次数,调用本函数请置1
	 mode:运行模式,"mode1"或者"mode2"
	 
	*/
	if (looptimes > 1) {
		console.log('正在跑第'+i+'次循环......');
	}
	
	if (mode == "mode2") {		
		//console.log(contact);
		var case_id = contact.waiting_testcase_mode2[0];

		contact.waiting_testcase_mode2 = contact.waiting_testcase_mode2.slice(1)
		
		console.log('  正在运行用例id:'+case_id+'......');
		
		var car = new Array();
		car[0] = case_id;
		contact.caselist = car;
		
		json_caselist = JSON.stringify(car);
		localStorage.setItem('testcase_running',json_caselist);
		waiting_json_caselist = JSON.stringify(contact.waiting_testcase_mode2);
		localStorage.setItem('testcase_waitting',waiting_json_caselist);
		
		//等待中的用例显示"等待中"
		for (var j = 0; j < contact.waiting_testcase_mode2.length; j++) {
			if (!window.parent.frames["main"].document.getElementById(contact.waiting_testcase_mode2[j])) {
				console.log('Warnning: '+contact.waiting_testcase_mode2[j]+'转化状态成 "等待中" 异常');
				continue;
			}
			
			window.parent.frames["main"].document.getElementById(contact.waiting_testcase_mode2[j]).src='images/3.gif';
		    window.parent.frames["main"].document.getElementById('span'+contact.waiting_testcase_mode2[j]).innerHTML='等待中';
			window.parent.frames["main"].document.getElementById('span'+contact.waiting_testcase_mode2[j]).className="colorIndigo";
		}
	} else {
		localStorage.setItem('testcase_running',JSON.stringify(contact.caselist));
	}
	
	forbidsubmit("forbid",mode);
	for (var j = 0; j < contact.caselist.length; j++) {
		window.parent.frames["main"].document.getElementById(contact.caselist[j]).src='images/3.gif';
	    window.parent.frames["main"].document.getElementById('span'+contact.caselist[j]).innerHTML='执行中';
		window.parent.frames["main"].document.getElementById('span'+contact.caselist[j]).className="colorGreen";
	}
	
	var prefix = window.parent.frames["main"].document.getElementById('prefix').value; //定位id
	
	var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
	var regExp = /ne-/i;
	if(res = regExp.exec(prefix)){
		console.log("    这是5G项目"+res);
		// httpRequest.open('POST', 'http://172.0.11.192:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
		httpRequest.open('POST', 'http://'+ PYTHON_SERVER_IP +':'+ PYTHON_SERVER_PORT +'/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
	} else {
		console.log("    这是4G项目"+prefix);
		
		if (mode == "mode1" || mode == "mode2") {
			httpRequest.open('post', 'runcaselist.php', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
		} else if (mode == "mode3" || mode == "mode4") {
			httpRequest.open('post', 'runcaselist_debug.php', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
		} 
	
	}
	
	// 设置请求头 注：post方式必须设置请求头（在建立连接后设置请求头）
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	// 发送请求 将json写入send中
	var jsondata = JSON.stringify(contact);
	//console.log(jsondata);
	httpRequest.send(jsondata);
	/**
	 * 获取数据后的处理程序
	 */
	
	httpRequest.onreadystatechange = function () {//请求后的回调接口，可将请求成功后要执行的程序写在其中
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {//验证请求是否发送成功
			var json = httpRequest.responseText;//获取到服务端返回的数据
			//console.log(json);
			
			if (mode == "mode3" || mode == "mode4") {
				console.log(json);
			}
			
			if (localStorage.getItem('stop_all_case_flag')) {
				//在函数casa_Stop_All_Case中设置的变量,跳出循环
				
				localStorage.removeItem('stop_all_case_flag');
				localStorage.removeItem('testcase_running');
				localStorage.removeItem('testcase_waitting');
				localStorage.removeItem('finish_pass_testcase');
				localStorage.removeItem('finish_fail_testcase');
				localStorage.removeItem('finish_stop_testcase');
				
				if (mode == "mode2" && contact.sentmail == "on") {
					//模式二 发邮件
					var myDate = new Date();
					contact.endtime = myDate.toLocaleString();
					Mode2_Sent_Email(contact);
				}
				
				console.log('循环终止');
				forbidsubmit("enable");

				parent.getcaselist(contact.projectid);
				
				if (res = regExp.exec(prefix)){
					console.log('    5G项目不需要初始化');
				} else {
					console.log('    4G项目需要初始化......');
					clearrecords();
				}
				return 0
			}
			
			if (localStorage.getItem('the_testcase_stop')) {
				//只要mode2才能跳过单个用例

				contact.finish_stop_testcase_mode2.push(case_id);
				console.log(contact.finish_stop_testcase_mode2);
				localStorage.setItem('finish_stop_testcase',JSON.stringify(contact.finish_stop_testcase_mode2));
				
				localStorage.removeItem('the_testcase_stop');
			} else {
				if(res = regExp.exec(prefix)){
					//5G 项目
					if (mode == "mode2") {

						var python_result_info_json = json.split('--!!For mode2 split!!--')[1];
						//console.log(python_result_info_json);
						
						var python_result_info = JSON.parse(python_result_info_json);
						
						//console.log(python_result_info);
						
						//处理能html格式的结果,用于发邮件
						var result_info = Handle_For_Email_Mode2(python_result_info);
						
						//处理 python服务器上的邮件信息
						contact.email_flag = true;
						contact.email_from = python_result_info['email_from'];
						contact.email_to = python_result_info['email_to'];
						contact.email_pwd = python_result_info['email_pwd'];
					}
				} else {
					//4G 项目
					var result_info_json = json.split('--!!Split and Get the result information!!--')[1];
					
					try {
						var result_info = JSON.parse(result_info_json);
					} catch(err) {
						console.log("!!!注意: 用例"+case_id+"运行异常，请单独运行查看");
						var result_info = new Array();
						result_info["result"] = "F";
						result_info['result_info'] = "";
					}
				
				}
				//console.log(result_info);
				
				if (mode == "mode2") {
					
					//运行一个用例之后的信息存放,处理,用于完成所有用例之后发邮件
					//还有存放到缓存,每运行完一个用例就刷新一下main.html,根据缓存显示用例状态
					contact.all_result_info_for_email += result_info['result_info'];
					contact.total_num++;
					console.log("此用例脚本运行完成，结果:"+result_info["result"]);
					if (result_info["result"] == "P") {
						contact.pass_num++;
						contact.finish_pass_testcase_mode2.push(case_id);
						//console.log(contact.finish_pass_testcase_mode2);
						localStorage.setItem('finish_pass_testcase',JSON.stringify(contact.finish_pass_testcase_mode2));
					} else {
						contact.fail_num++;
						contact.finish_fail_testcase_mode2.push(case_id);
						//console.log(contact.finish_fail_testcase_mode2);
						localStorage.setItem('finish_fail_testcase',JSON.stringify(contact.finish_fail_testcase_mode2));
					}
				}
				
				
			}
			
			parent.getcaselist(contact.projectid); //刷新main.html
			
			var current_waiting_testcase = contact.waiting_testcase_mode2;
			if (mode == "mode2" && current_waiting_testcase.length > 0) {
				//运行下一个用例

				casa_Http_Request(contact, looptimes, i, "mode2");

			}

			//一个循环运行结束
			if (mode == "mode1" || mode == "mode3" || current_waiting_testcase.length == 0) {
				console.log('finish all')
				
				//一次循环结束清除缓存
				if (mode == "mode2") {
					localStorage.removeItem('testcase_waitting');
					localStorage.removeItem('finish_pass_testcase');
					localStorage.removeItem('finish_fail_testcase');
					localStorage.removeItem('finish_stop_testcase');
				}
				
				forbidsubmit("enable");
				
				if (looptimes > 1) {
					console.log('第'+i+'次循环跑完了');
				}
					
				if(!regExp.exec(prefix)){
					clearrecords();
				}
				
				if (mode == "mode2" && contact.sentmail == "on") {
					//模式二 发邮件
					var myDate = new Date();
					contact.endtime = myDate.toLocaleString();
					Mode2_Sent_Email(contact);
				}
				
				if (i<looptimes) {
					//运行下一次循环
					i++;
					
					if (mode == "mode2") {
						var wait_run = contact.wait_run_testcase_mode2
						contact.waiting_testcase_mode2 = wait_run;
						contact.total_num = 0;
						contact.pass_num = 0;
						contact.fail_num = 0;
						contact.cancel_num = 0;
						contact.abort_num = 0;
						contact.all_result_info_for_email = '';
						contact.finish_pass_testcase_mode2 = [];
						contact.finish_fail_testcase_mode2 = [];
						contact.finish_stop_testcase_mode2 = [];
						
						var myDate = new Date();
						contact.starttime = myDate.toLocaleString();
						
					} 
					
					casa_Http_Request(contact, looptimes, i, mode);
					
				} else {
					localStorage.removeItem('testcase_running');
					return 0
				}	
			}
			
		}
	};
}

function Handle_For_Email_Mode2(python_result) {
	/*
	处理一下 5G python服务器返回的字典, 生成两个属性 result_info 和 result
	*/
	var mycars = new Array();
   
	if (python_result['verdict'] == 'pass') {
		mycars['result'] = 'P' 
	} else {
		mycars['result'] = 'F'
	}
   
	mycars["result_info"] = '\
		<tr>\
			<th colspan="1" style="text-align:center" >\
				1\
			</th>\
			<th colspan="1" style="text-align:center" >'
				+ python_result['case_id'] +
			'</th>\
			<th colspan="15" style="text-align:left">' 
				+ python_result['testcase'] +
			'</th>\
			<th colspan="3" style="text-align:center">\
				<span style="text-align:center;color:green;">'+mycars['result']+'</span>'+
			'</th><th colspan="3" style="text-align:center">\
				<span style="text-align:center;">'+python_result['duration']+'</span>'+
			'</th>\
			<th colspan="3">\
				<a href="'+python_result['logfile']+'">log</a>' +
			'</th>\
			<th colspan="3">\
				<a href="'+python_result['testplan']+'">testplan</a>' +
			'</th>\
		</tr>'
	return mycars
}

function Delete_Testcase_with_Same_Testplan(testcase_list) {
	//console.log('start');
	var finish = false;
	$.ajax({
		async: false,
		type:"POST",
		url:"Delete_Testcase_with_Same_Testplan.php",
		data:JSON.stringify(testcase_list),
		success:function(msg){
			//console.log(msg);
			var result = JSON.parse(msg);
			//console.log(result);
			finish = result;
			return finish;
		},
		error:function(msg){
			console.log('执行 Delete_Testcase_with_Same_Testplan 发生错误');
			console.log('data:'+JSON.stringify(testcase_list));
			console.log(msg);
			alert('执行 Delete_Testcase_with_Same_Testplan 发生错误,请查看console并联系自动化人员:'+msg);
			return null
		}
	})
	
	if (finish) {
		//console.log('end');
		return finish;
	}
}

function Mode2_Sent_Email(contact) {
	//console.log(contact);
	$.ajax({
		type:"POST",
		url:"sent_email_mode2.php",
		data:JSON.stringify(contact),
		success:function(msg){
			if (msg.split('Email result:')[1] == "true") {
				console.log('邮件已发送');
			} else {
				console.log('邮件发送失败');
				console.log(msg);
			}
		},
		error:function(msg){
			console.log('邮件发送失败');
			console.log(msg);
		}
	})
	
	// var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
	
	// httpRequest.open('post', 'sent_email_mode2.php', true)
	// httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	// var jsondata = JSON.stringify(contact);
	// //console.log(jsondata);
	// httpRequest.send(jsondata);
	// httpRequest.onreadystatechange = function () {//请求后的回调接口，可将请求成功后要执行的程序写在其中
	// 	if (httpRequest.readyState == 4 && httpRequest.status == 200) {//验证请求是否发送成功
	// 		console.log(11111111);
	

	// 		var json = httpRequest.responseText;//获取到服务端返回的数据
	// 		console.log(json);
	// 	}
	// }
			
};

function forbidsubmit(type, mode='mode1') { 
	if(type=="forbid") {
		document.getElementById("submit").disabled=true;
		document.getElementById("Pause").value="暂停";
		document.getElementById("Pause").style.display="block";
		document.getElementById("stop").style.display="block";
		document.getElementById("stopall").style.display="block";
		
		if (mode == 'mode1'|| mode == "mode3") {
			document.getElementById("stop").disabled=true;
		} 
		
	} else if  (type=="enable") {
		document.getElementById("submit").disabled=false;
		document.getElementById("Pause").style.display="none";
		document.getElementById("stop").style.display="none";
		document.getElementById("stop").disabled=false;
		document.getElementById("stopall").style.display="none";
		
	}
}

function clearrecords(type) { 
	// < !--执行环境--><!--不能用name不然抓不到值-->
    var environmentobj = document.getElementById('environment'); //定位id
    var environment = environmentobj.value;
	
    if (environment == "" || environment == null || environment == undefined) { // 只能用 === 运算来测试某个值是否是未定义的
        alert("初始化前请选择执行环境");
        return 0
    }
	var objss = JSON.parse(localStorage.getItem('obj'));
	console.log(objss)
	var obj1=objss.environmentalall;
	var obj=obj1[0];
	var table=obj[environment];
	var xhr1 = new XMLHttpRequest;
    xhr1.open('post', 'clearrecords.php');
    xhr1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	var json=JSON.stringify({"table" : table});
	// console.log(json);
    xhr1.send(json);

    xhr1.onreadystatechange = function() {
        if (xhr1.readyState == 4 && (xhr1.status == 200 || xhr1.status == 304)) { //响应完成并且响应码为200或304
            var rs = xhr1.responseText;
            
        }
    };
}

// numberMillis 毫秒
function sleep(numberMillis) {
   var now = new Date();
   var exitTime = now.getTime() + numberMillis;
   while (true) {
      now = new Date();
      if (now.getTime() > exitTime)
      　　return;
      }
   }
   
function Pausecase() { 
	var prefix = window.parent.frames["main"].document.getElementById('prefix').value; //定位id
	var pausename=document.getElementById("Pause").value;
	var operation="";
	var text='';
	var environmentobj = document.getElementById('environment'); //定位id
    var environment = environmentobj.value;
	
	var running_testcase = JSON.parse(localStorage.getItem('testcase_running'));
	if(pausename=="暂停") {
		document.getElementById("Pause").value="恢复";
		operation="Pause";
		// console.log("pausename:"+pausename);
		
		for (var j = 0; j < running_testcase.length; j++) {
		    window.parent.frames["main"].document.getElementById('span'+running_testcase[j]).innerHTML='暂停';
		}
		
	} else {

		for (var j = 0; j < running_testcase.length; j++) {
		    window.parent.frames["main"].document.getElementById('span'+running_testcase[j]).innerHTML='执行中';
		}
		
		text="确定恢复当前测试用例？";
		operation="recover";
		document.getElementById("Pause").value="暂停";
	}
	
	
	// if (confirm(text)) {
	var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
	var regExp = /ne-/i;
	if(res = regExp.exec(prefix)){
		httpRequest.open('POST', 'http://'+ PYTHON_SERVER_IP +':'+ PYTHON_SERVER_PORT +'/pauseandstop', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
		// httpRequest.open('POST', 'http://172.0.11.197:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
	} else {
		
		httpRequest.open('post', 'pauseandstop.php', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
	}
	
	// 设置请求头 注：post方式必须设置请求头（在建立连接后设置请求头）
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	var contact = new Object();
	contact.operation = operation;
	contact.environment = environment;
	// 发送请求 将json写入send中
	var jsondata = JSON.stringify(contact);
	console.log(operation);
	httpRequest.send(jsondata);
	/**
	 * 获取数据后的处理程序
	 */
	httpRequest.onreadystatechange = function () {//请求后的回调接口，可将请求成功后要执行的程序写在其中
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {//验证请求是否发送成功
			var json = httpRequest.responseText;//获取到服务端返回的数据
			
		}
	};
	
}




function casa_StopCase(element) {
	var prefix = window.parent.frames["main"].document.getElementById('prefix').value; //定位id
	var environmentobj = document.getElementById('environment'); //定位id
	var environment = environmentobj.value;

	var objss = JSON.parse(localStorage.getItem('obj'));
	var obj1=objss.environmentalall;
	var obj=obj1[0];
	var table=obj[environment];
	
	
	var operation=element.id;
	console.log(operation);
	if (operation == "stopall") {
		var alert_info = "确定终止所有测试用例？";
	} else if (operation == "stop") {
		var alert_info = "确定停止当前测试用例？";
	} 
	
	if (confirm(alert_info)) {
	    
		var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
		var regExp = /ne-/i;
		if(res = regExp.exec(prefix)){
			httpRequest.open('POST', 'http://'+ PYTHON_SERVER_IP +':'+ PYTHON_SERVER_PORT +'/pauseandstop', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
			// httpRequest.open('POST', 'http://172.0.11.197:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */	
		} else {
			httpRequest.open('post', 'pauseandstop.php', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
		}
		
		// 设置请求头 注：post方式必须设置请求头（在建立连接后设置请求头）
		httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		var contact = new Object();

		contact.environment = environment;
		contact.operation = operation;
		contact.table = table;
		// 发送请求 将json写入send中
		var jsondata = JSON.stringify(contact);
		//console.log('Stop all');
		httpRequest.send(jsondata);
		/**
		 * 获取数据后的处理程序
		 */
		httpRequest.onreadystatechange = function () {//请求后的回调接口，可将请求成功后要执行的程序写在其中
			if (httpRequest.readyState == 4 && httpRequest.status == 200) {//验证请求是否发送成功
				var json = httpRequest.responseText;//获取到服务端返回的数据
				//console.log(json);
			}
		};
		
		if (operation == "stopall") {
			show_testcases_stopping()
			
			console.log('终止所有测试用例脚本');
			localStorage.setItem('stop_all_case_flag', '1'); //本次已经使用了http发送请求,上面关闭了进程,http超时后对这个变量判断,从而跳出循环
		} else if (operation == "stop") {
			show_testcases_stopping(all=false)
			console.log('停止当前测试用例脚本 id:'+ localStorage.getItem('testcase_running'));
			localStorage.setItem('the_testcase_stop', localStorage.getItem('testcase_running'));
		} 
	} 

	
}



function show_testcases_stopping(all=true) {
	//console.log(localStorage);
	var running_testcase = JSON.parse(localStorage.getItem('testcase_running'));
	for (var j = 0; j < running_testcase.length; j++) {
	    window.parent.frames["main"].document.getElementById('span'+running_testcase[j]).innerHTML='正在停止';
		window.parent.frames["main"].document.getElementById('span'+running_testcase[j]).className="colorRed";
	}
	//localStorage.removeItem('testcase_running');
	
	if (all) {
		if (localStorage.getItem('testcase_waitting')) {
			var waiting_testcase = JSON.parse(localStorage.getItem('testcase_waitting'));
			for (var j = 0; j < waiting_testcase.length; j++) {
				window.parent.frames["main"].document.getElementById('span'+waiting_testcase[j]).innerHTML='正在停止';
				window.parent.frames["main"].document.getElementById('span'+waiting_testcase[j]).className="colorRed";
			}
			localStorage.removeItem('waiting_testcase');
		}
	}
}