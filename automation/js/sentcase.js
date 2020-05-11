function runcase() { 
	forbidsubmit("forbid");
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
		forbidsubmit("enable");
        return 0
    } 
	// < !--获取版本号--><!--不能用name不然抓不到值-->
    var editionobj = document.getElementById('edition'); //定位id
  
    var edition = editionobj.value;

    if (edition == "" || edition == null || edition == undefined || edition == "No") { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有选择执行版本");
		forbidsubmit("enable");
        return 0
    } 
	// < !--获取版本号--><!--不能用name不然抓不到值-->
    var smalleditionobj = document.getElementById('smalledition'); //定位id
  
    var smalledition = smalleditionobj.value;
	// console.log(smalledition);
    if (smalledition == "" || smalledition == null || smalledition == undefined || smalledition == "No") { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有选择执行版本");
		forbidsubmit("enable");
        return 0
    } 
	// < !--获取测试人-->
    var testerobj = document.getElementById('tester'); //定位id
    var tester = testerobj.value;

    if (tester == "" || tester == null || tester == undefined || tester == "No") { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有选择测试人");
		forbidsubmit("enable");
        return 0
    } 
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
		forbidsubmit("enable");
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
   
	var l=0;
    for (var i = 0; i < caselist.length; i++) {
		
		
		window.parent.frames["main"].document.getElementById(caselist[i]).src='images/3.gif';
        window.parent.frames["main"].document.getElementById('span'+caselist[i]).innerHTML='执行中';
		window.parent.frames["main"].document.getElementById('span'+caselist[i]).className="colorGreen";
		
    }
   
	// var jsonStr = '{projectid:"' + projectid + '",caselist:"' + caselist + '",table:"' + table + '",edition:"' + edition + '",smalledition:"' + smalledition + '",tester:"' + tester + '",looptimes:"' + looptimes + '",environment:"' + environment + '",sentmail:"' + sentmail + '",remarks:"' + remarks + '",order:"' + order + '"}';
	// var jsonStr = '{caselist:"' + caselist + '",table:"' + table + '",edition:"' + edition + '",smalledition:"' + smalledition + '",tester:"' + tester + '",looptimes:"' + looptimes + '",environment:"' + environment + '",sentmail:"' + sentmail + '",remarks:"' + remarks + '",order:"' + order + '"}';
	
	// var jsondata = eval('(' + jsonStr + ')');
	// console.log(JSON.stringify(jsondata));
	
	
	
	// var jsonStr = '{caselist:"' + caselist + '",table:"' + table + '",edition:"' + edition + '",smalledition:"' + smalledition + '",tester:"' + tester + '",looptimes:"' + looptimes + '",environment:"' + environment + '",sentmail:"' + sentmail + '",remarks:"' + remarks + '",order:"' + order + '"}';
	// var jsondata = eval('(' + jsonStr + ')');

	// console.log(jsondata);
	// console.log(JSON.stringify(jsondata));
	
	
	var contact = new Object();
	contact.projectid = projectid;
	contact.caselist = caselist;
	contact.table = table;
	contact.edition = edition;
	contact.smalledition = smalledition;
	contact.tester = tester;
	contact.looptimes = looptimes;
	contact.environment = environment;
	contact.sentmail = sentmail;
	contact.remarks = remarks;
	contact.order = order;
	// console.log(contact);
	// console.log("#################");
	
	
	var prefix = window.parent.frames["main"].document.getElementById('prefix').value; //定位id
	
	var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
	var regExp = /ne-/i;
	if(res = regExp.exec(prefix)){
		console.log("找到"+res);
		
		// httpRequest.open('POST', 'http://172.0.11.192:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
		httpRequest.open('POST', 'http://172.0.15.122:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
	} else {
		
		console.log("mei找到"+prefix);
		httpRequest.open('post', 'runcaselist.php', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
	}
	
	// 设置请求头 注：post方式必须设置请求头（在建立连接后设置请求头）
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	// 发送请求 将json写入send中
	var jsondata = JSON.stringify(contact);
	console.log(jsondata);
	httpRequest.send(jsondata);
	/**
	 * 获取数据后的处理程序
	 */
	httpRequest.onreadystatechange = function () {//请求后的回调接口，可将请求成功后要执行的程序写在其中
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {//验证请求是否发送成功
			var json = httpRequest.responseText;//获取到服务端返回的数据
			console.log(json);
			forbidsubmit("enable");
			
			
			parent.getcaselist(projectid);
		}
	};
	
	

}

function forbidsubmit(type) { 
	if(type=="forbid") {
		document.getElementById("submit").disabled=true;
		document.getElementById("Pause").value="暂停";
		document.getElementById("Pause").style.display="block";
		document.getElementById("stop").style.display="block";
		document.getElementById("stopall").style.display="block";
	} else if  (type=="enable") {
		document.getElementById("submit").disabled=false;
		document.getElementById("Pause").style.display="none";
		document.getElementById("stop").style.display="none";
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
	if(pausename=="暂停") {
		text="确定暂停当前测试用例？";
		document.getElementById("Pause").value="恢复";
		operation="Pause";
		var pausename=document.getElementById("Pause").value;
		// console.log("pausename:"+pausename);
		
	} else {
		text="确定恢复当前测试用例？";
		operation="recover";
		document.getElementById("Pause").value="暂停";
	}
	
	
	if (confirm(text)) {
		var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
		var regExp = /ne-/i;
		if(res = regExp.exec(prefix)){
			// console.log("找到"+res);
			
			httpRequest.open('POST', 'http://172.0.15.122:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
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
		console.log("jsondata:"+jsondata);
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
}

function stopcase() { 
	var prefix = window.parent.frames["main"].document.getElementById('prefix').value; //定位id
	var environmentobj = document.getElementById('environment'); //定位id
    var environment = environmentobj.value;
	var operation="stop";
	if (confirm("确定停止当前测试用例？")) {
		var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
		var regExp = /ne-/i;
		if(res = regExp.exec(prefix)){
			httpRequest.open('POST', 'http://172.0.15.122:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
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
}

function stopallcase() { 
	var prefix = window.parent.frames["main"].document.getElementById('prefix').value; //定位id
	var environmentobj = document.getElementById('environment'); //定位id
    var environment = environmentobj.value;
	var operation="stopall";
	if (confirm("确定终止所有测试用例？")) {
        
		var httpRequest = new XMLHttpRequest();//第一步：创建需要的对象
		var regExp = /ne-/i;
		if(res = regExp.exec(prefix)){
			httpRequest.open('POST', 'http://172.0.15.122:5000/', true); //第二步：打开连接/***发送json格式文件必须设置请求头 ；如下 - */
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
}