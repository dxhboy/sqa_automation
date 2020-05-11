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

    if (edition == "" || edition == null || edition == undefined) { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有选择执行版本");
		forbidsubmit("enable");
        return 0
    } 
	// < !--获取版本号--><!--不能用name不然抓不到值-->
    var smalleditionobj = document.getElementById('smalledition'); //定位id
  
    var smalledition = smalleditionobj.value;

    if (smalledition == "" || smalledition == null || smalledition == undefined) { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有选择执行版本");
		forbidsubmit("enable");
        return 0
    } 
	// < !--获取测试人-->
    var testerobj = document.getElementById('tester'); //定位id
    var tester = testerobj.value;

    if (tester == "" || tester == null || tester == undefined) { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有填写测试人");
		forbidsubmit("enable");
        return 0
    } 
	// < !--执行次数-->
    var timesobj = document.getElementById('looptimes'); //定位id
    var looptimes = timesobj.value;
    if (looptimes == "" || looptimes == null || looptimes == undefined) { // 只能用 === 运算来测试某个值是否是未定义的
        looptimes = "1";
    }

    // < !--执行环境--><!--不能用name不然抓不到值-->
    var environmentobj = document.getElementById('environment'); //定位id
    var environment = environmentobj.value;
	var table=obj[environment];
    if (environment == "" || environment == null || environment == undefined) { // 只能用 === 运算来测试某个值是否是未定义的
        alert("没有选择执行环境");
		forbidsubmit("enable");
        return 0
    }

    // < !--发邮件--><!--不能用name不然抓不到值-->
    var sentmailobj = document.getElementById('sentmail'); //定位id
    var sentmail = sentmailobj.value;

    // < !--备注-->
    var remarksobj = document.getElementById('remarks'); //定位id
    var remarks = remarksobj.value;
    var flag=true;
	var l=0;
    for (var i = 0; i < caselist.length; i++) {
		
		console.log(caselist[i]);
		window.parent.frames["main"].document.getElementById(caselist[i]).src='3.gif';
        window.parent.frames["main"].document.getElementById('span'+caselist[i]).innerHTML='执行中';
		window.parent.frames["main"].document.getElementById('span'+caselist[i]).className="colorGreen";
		// flag=true;
    
   
		var jsonStr = '{caselist:"' + caselist[i] + '",table:"' + table + '",edition:"' + edition + '",smalledition:"' + smalledition + '",tester:"' + tester + '",looptimes:"' + looptimes + '",environment:"' + environment + '",sentmail:"' + sentmail + '",remarks:"' + remarks + '"}';
		var jsondata = eval('(' + jsonStr + ')');

		console.log(jsondata);
		var xhr2 = new XMLHttpRequest;
		xhr2.open('post', 'runcaselist.php');
		xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr2.send(JSON.stringify(jsondata));
		console.log("send");
		xhr2.onreadystatechange = function() {
			if (xhr2.readyState == 4 && (xhr2.status == 200 || xhr2.status == 304)) { //响应完成并且响应码为200或304
				var rs = xhr2.responseText;
				if(i==caselist.length-1) {
					forbidsubmit("enable");
				}
				console.log(rs);
				for (var i = 0; i < caselist.length; i++) {
					
					window.parent.frames["main"].document.getElementById(caselist[i]).src='5.gif';
					if(i==caselist.length-1) {
						parent.getcaselist(projectid);
						parent.initialization(projectid);
					}
					flag=false;
					// window.parent.frames["main"].document.getElementById('span'+caselist[i]).innerHTML='执行完成';
					// window.parent.frames["main"].document.getElementById('span'+caselist[i]).className="colorOrange";
				}
			}
		};
		// console.log("tyuuuiyu");
		// console.log(xhr2.readyState);
		// console.log(flag);
		// while(flag) {
			// l++;
			// console.log("l:"+l);
			// console.log(flag);
			// console.log(xhr2.readyState);
			// sleep(1000);
			// if(l>12) {
				// flag=false;
			// }
		// }
	}
	
	// forbidsubmit("enable");
	// parent.getcaselist(projectid);
	// parent.initialization(projectid);
}

function forbidsubmit(type) { 
	if(type=="forbid") {
		document.getElementById("submit").disabled=true;
	} else {
		document.getElementById("submit").disabled=false;
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
	console.log(json);
    xhr1.send(json);

    xhr1.onreadystatechange = function() {
        if (xhr1.readyState == 4 && (xhr1.status == 200 || xhr1.status == 304)) { //响应完成并且响应码为200或304
            var rs = xhr1.responseText;
            // console.log(rs);
			// forbidsubmit("enable");
			
			// for (var i = 0; i < caselist.length; i++) {
				
				// window.parent.frames["main"].document.getElementById(caselist[i]).src='5.gif';
				// parent.getcaselist(projectid);
				// parent.initialization(projectid);
				
				// window.parent.frames["main"].document.getElementById('span'+caselist[i]).innerHTML='执行完成';
				// window.parent.frames["main"].document.getElementById('span'+caselist[i]).className="colorOrange";
			// }
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