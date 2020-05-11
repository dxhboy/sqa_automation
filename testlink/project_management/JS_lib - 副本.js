
document.write("<script language=javascript src='./jquery-3.4.1.js'></script>");

function Run_Php_File(http_type="post", post_info, php_file, locStorVal, del_extra_str=true) {
	//console.log(post_info);
	var xhr = new XMLHttpRequest;
	xhr.open(http_type, php_file, false);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send(JSON.stringify(post_info));
	//console.log('222222222222222############');
	
	var rst=xhr.responseText;
	//console.log(rst);
	//delete extra string of rst
	if (del_extra_str) {
		var reg1=new RegExp("^[^\\[]+");
		var rst=rst.replace(reg1,"");
		var reg2=new RegExp("[^\\]]+$");
		var rst=rst.replace(reg2,"");
	}
	//console.log(rst);
	localStorage.setItem(locStorVal, rst);
	//console.log(44444444444444444444444);
	return rst
	
// 	xhr.onreadystatechange = function() {
// 		console.log('111111##########')
// 		if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status ==304)) { 
// 			var rst=xhr.responseText;
// 			console.log(rst);
// 			//delete extra string of rst
// 			var reg1=new RegExp("^[^\\[]+");
// 			var rst=rst.replace(reg1,"");
// 			var reg2=new RegExp("[^\\]]+$");
// 			var rst=rst.replace(reg2,"");
// 
// 			localStorage.setItem(locStorVal, rst);
// 			return rst
// 		}
// 	}

};



function getQueryVariable(variable) {
	/*
	Get the url parameter
	*/
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i=0;i<vars.length;i++) {
		   var pair = vars[i].split("=");
		   if(pair[0] == variable){return pair[1];}
   }
   return(false);
};

function id2name(id, type) {
	/*方法说明
	 *@method id2name : check DB and get the name from id
	 *@for 
	 *@param{参数类型}参数名 参数说明
	 * @id(int or string) 
	 * @type(string) : from file "db_config.php", table key value
	 *@return {string} name value
	*/
	if (id == null) {
		return ""
	}
	//console.log(id);
   
	var mycars=new Array();
	mycars[0]=id;
	mycars[1]=type;
	//console.log(mycars);
	Run_Php_File(http_type="post", post_info=mycars, php_file="id2name.php", locStorVal="json_name");
	//console.log(localStorage.getItem('json_name'));
	var myarray = JSON.parse(localStorage.getItem('json_name'))[0];
	//console.log(myarray);
	if (!myarray) {
		return
	}
	
	name = myarray.name;
	return name;
}

function name2email(name) {
	/*方法说明
	 *@method name2name : check DB and get the name from name
	 *@for 
	 *@param{参数类型}参数名 参数说明
	 * @name(int or string) 
	 * @type(string) : from file "db_config.php", table key value
	 *@return {string} name value
	*/
	if (name == null) {
		return ""
	}
	//console.log(name);
   
	var mycars=new Array();
	mycars[0]=name;
	//console.log(mycars);
	Run_Php_File(http_type="post", post_info=mycars, php_file="name2email.php", locStorVal="json_name");
	//console.log(localStorage.getItem('json_name'));
	var myarray = JSON.parse(localStorage.getItem('json_name'))[0];
	//console.log(myarray);
	if (!myarray) {
		return
	}
	
	email = myarray.email;
	return email;
}

function showTask(task_arrays) {

	/*
	write the html info in UL(name="task_list"):
	<ul name="task_list">
		<li id="1" selected="" >任务1</li>
		<ul parent_id="1"></ul>
		<li id="2" selected="">任务2</li>
		<ul parent_id="2">
			<li id="6" selected="">任务2.1</li>
			<ul parent_id="6">
				<li id="7" selected="1">任务2.1.1</li>
				<ul parent_id="7"></ul>
		<li id="3" selected="">任务3</li>
		<ul parent_id="3"></ul>
	</ul>
	
	Result browser show:
	任务1
	任务2
		任务2.1
			任务2.1.1
	任务3
	*/
    var root_ulobj = document.getElementsByName('task_list')[0];
    root_ulobj.innerHTML = "";
   
    //console.log(task_arrays);
	/*
	task_arrays.sort(function(a,b){
		return a.id - b.id
	})
	*/

	// designer:xiaojinshui data:20/03/04
	// resort task_arrays
	task_arrays = sortalltask(task_arrays);
	
	//console.log(task_arrays);
	if (task_arrays.length == 0) {
		return;
	}
	
	task_id_pc_origin = new Array();
	for(index=0; index < task_arrays.length;index++){
		//console.log(index);
		//console.log(task_arrays[index]);
		
		var task_id = task_arrays[index].id;
		var task_name = task_arrays[index].name;
		var task_parent_id = task_arrays[index].parent_id;

		task_id_pc_origin[task_id] = task_arrays[index].pc;
		
		//console.log(task_pc);
		//console.log(task_parent_id);
		//console.log(task_parent_id);
		if (!task_parent_id) {
			var root_ulobj = document.getElementsByName('task_list')[0];
			//console.log(root_ulobj);
			add_html = '<li style="list-style:none;" id="'+task_id+'"><span class="showOrHide"><img src="./images/hide.png" width="15px" height="15px" ></span> <span id="'+task_id+'" onmousedown="whichButton(event, this)">'+task_name+'</span></li>';
			add_html += '<ul style="display:none" parent_id ="'+ task_id +'">'
			root_ulobj.innerHTML += add_html;
			//console.log(root_ulobj);

		} else {
			var ulobjs = document.getElementsByTagName('ul');
			//console.log(ulobjs);
			for (var i in ulobjs) {
				ulobj = ulobjs[i];
				if(ulobj instanceof HTMLElement){
					//console.log(ulobj);
					//console.log(ulobj.getAttribute('parent_id'));
					if (ulobj.getAttribute('parent_id') == task_parent_id) {
						add_html = '<li style="list-style:none;" id="'+task_id+'"><span class="showOrHide"><img src="./images/hide.png" width="15px" height="15px"></span> <span id="'+task_id+'" onmousedown="whichButton(event, this)">'+task_name+'</span></li>';
						add_html += '<ul style="display:none" parent_id ="'+ task_id +'">'
						ulobj.innerHTML += add_html;
						
					}
				}
			}
		}
	}
	
	//add task progress finish/unfinish after last li task
	/*
	任务1(未完成)
	任务2
		任务2.1
			任务2.1.1(未完成)
	任务3(完成)
	*/
	var select_html_1 = '\
	<select style="width:78px;" id="'
	
	var select_html_2 = '" onchange="update_Task_Status(this)">\
		<option value="start" selected>未完成</option>\
		<option value="finish">完成</option>\
	</select>';
   
	last_li_list = casa_UL_Get_Last_LI_Generations(root_ulobj);
	//console.log(last_li_list);
	
	//console.log(last_li_list);
	for (var index=0; index < last_li_list.length;index++) {
		//last_li_list[index].setAttribute('last_li', '1');
		last_li = last_li_list[index];

		var task_id = last_li.getAttribute("id");
		var add_html = select_html_1+task_id+select_html_2;
		last_li.innerHTML += add_html;
		
		var task_status = task_id2status(task_arrays,task_id);
		last_li.removeAttribute("pc");
		last_li.setAttribute("last", "1");
		//console.log(last_li);
		//console.log(task_status);

		if (task_status == "finish") {
			last_li.setAttribute("pc", "100");
		} else {
			last_li.setAttribute("pc", "0");
		}
		
		select_option(last_li, task_status);
		//console.log(last_li.childNodes[0]);
		last_li.childNodes[0].childNodes[0].setAttribute('src', '');
	}
	
	//add task progress rate according the last li task 
	/*
	任务1(未完成)
	任务2(50%)
		任务2.1(50%)
			任务2.1.1(未完成)
			任务2.1.2(完成)
	任务3(完成)
	*/
	//modify attribute "pc" for all li
	/*	
	思路:
	1. 用while创建一个大循环, 进入大循环, 
	  1.1 reverse遍历处理每一个li, 计算这个li下的子li的进度平均值作为本li的进度值
      1.2 如果子li还没有进度值就先跳过
	2. 无限循环这个大循环,直到所有根li 都有进度值
	3. 因为本次设计最多三层任务,所以最好加上一个保护defence_count,防止无限循环
	*/
	all_li_objs = casa_getAllElementsByTag(root_ulobj, 'LI');
	//console.log(all_li_objs);
	all_li_objs = all_li_objs.reverse();
	
	root_li_objs = casa_getElementsByTag(root_ulobj, 'LI');
	//console.log(root_li_objs);
	root_li_objs = casa_Element_Reverse_Filter(root_li_objs, "class", "showOrHide");
	//console.log(root_li_objs);

	var defence_count = 0
	while (true) {
		defence_count++;
		//console.log(all_li_objs);
		for (var index=0; index < all_li_objs.length;index++) {
			var li_obj = all_li_objs[index];
			//console.log(li_obj);
			var pc = li_obj.getAttribute("pc");
			//console.log(pc);
			
			ul_obj = li_obj.nextSibling;
			li_list = casa_getElementsByTag(ul_obj, "LI");
			li_list = casa_Element_Reverse_Filter(li_list, "class", "showOrHide");

			var pc_sum = 0;
			count = li_list.length;
			//console.log(li_list);
			// judge if all the li has attribute "pc"
			for (var j=0; j < li_list.length;j++) {
				if (li_list[j].getAttribute("pc")) {
					var has_pc = true;
					pc_sum += parseInt(li_list[j].getAttribute("pc"));
				} else {
					var has_pc = false;
				}
			}
			//console.log(has_pc);
			if (has_pc) {
				//console.log(pc_sum);
				parent_pc = Math.round(pc_sum/count);
				li_obj.setAttribute("pc", parent_pc);
			}
			var has_pc = false;
		}

		var keep_flag = false;
		//when all root li has attribute, break the while
		for (var i=0; i < root_li_objs.length;i++) {
			var root_li = root_li_objs[i];
			//console.log(root_li);
			if (root_li_objs[i].getAttribute("pc")) {
				a = 1;
				delete a;
			} else {
				var keep_flag = true;
			}
		}
		if (!keep_flag) {
			break;
		}
		
		//Prevent infinite loop
		if (defence_count>=10) {
			break;
		}
	}
	
	var task_id_pc_now = new Array();
	// show the pc for all li except last li
	for (var index=0; index < all_li_objs.length;index++) {
		var li = all_li_objs[index];
		
		//console.log(all_li_objs[index]);
		if (li.getAttribute("last")) {
			continue
		} else {
			task_id_pc_now[li.getAttribute("id")] = li.getAttribute("pc");
			pc = li.getAttribute("pc");
			li.innerHTML += "  (" + pc + "%)";
		}
	}
	
	//########### write the task pc to DATABASE
	// get the task id and pc now: task_id_pc_now
	//console.log(task_id_pc_now);
	// compare with the origin: task_id_pc_origin
	//console.log(task_id_pc_origin);
	// if pc changes, update it
	var reg=/^[0-9]+.?[0-9]*$/;
	for (var id in task_id_pc_now) {
		if (reg.test(id)) {
			if (task_id_pc_now[id] != task_id_pc_origin[id]) {
				//console.log(id);
				//console.log(task_id_pc_now[id]);
				//console.log(task_id_pc_origin[id]);
				update_Task_Pc(id, task_id_pc_now[id]);
			}
		}
	}
	
	//############### All the task is hide, now display some  ###################
	//Ability to reproduce task display after refreshing the page
	var json_ul_display = localStorage.getItem('ul_display');
	//console.log(json_ul_display);
	if (json_ul_display) {
		var ul_display = JSON.parse(json_ul_display);
		//console.log(ul_display);
		
		for (var j=0; j<ul_display.length; j++) {
			var parent_id = ul_display[j];
			var choose =  'ul[parent_id="'+parent_id+'"]'
			//console.log(choose);
			$(choose).show();
		}
	}
	
}


function detect_Chick_showOrHide_Span() {
	var span_list = $("span.showOrHide");
	//console.log(span_list);
	for(var i=0;i<span_list.length;i++) {
		//console.log(span_list[i]);
		if (span_list[i].childNodes[0].getAttribute('src')) {
			span_list[i].onclick=function() 
			{
				//console.log(this);
				var li = this.parentNode;
				//console.log(this.parentNode);
				var task_id = li.id;
				//console.log(task_id);
				showOrHide_ul_task(this, task_id);
			}
		}
	}
}

function showOrHide_ul_task(img_span_ele, parent_task_id) {
	var choose = 'ul[parent_id="'+parent_task_id+'"]';
	var display =$(choose).css('display');
	//console.log(img_span_ele);
	img_ele = casa_getAllElementsByTag(img_span_ele, "IMG")[0];
	
	if(display == 'none'){
		$(choose).show();
		//console.log(img_ele);
		img_ele.setAttribute("src", "./images/show.png");
	} else {
		$(choose).hide();
		img_ele.setAttribute("src", "./images/hide.png");
	}
	
	restore_task_ul_showOrHide();
}

function task_id2status(task_arrays,id_value) {
	
	for (var index=0; index < task_arrays.length;index++) {
		if (task_arrays[index].id == id_value) {
			return task_arrays[index].status
		}
	}
}

function update_Task_Pc(id, pc) {
	var mycars=new Array();
	mycars[0]=id;
	mycars[1]=pc;
	
	Run_Php_File(http_type="post", post_info=mycars, php_file="update_task_pc.php", locStorVal="anyname");
}

function update_Task_Status(select_obj) {
	//console.log(select_obj.id);
	//console.log(select_obj.value);
	var mycars=new Array();
	mycars[0]=select_obj.id;
	mycars[1]=select_obj.value;
	
	Run_Php_File(http_type="post", post_info=mycars, php_file="update_task_status.php", locStorVal="anyname");
	location.reload();
	
}

function restore_task_ul_showOrHide() {
	var cars = new Array();
	var ul_list = document.getElementsByTagName("ul");
	//console.log(ul_list);
	for (var i=0; i<ul_list.length; i++) {
		var ul = ul_list[i];
		var parent_id = ul.getAttribute('parent_id');
		var display = ul.style.display;
		if (!display && parent_id) {
			cars.push(parent_id);
		}
	}
	//console.log(cars);
	//console.log(JSON.stringify(cars));
	localStorage.setItem('ul_display', JSON.stringify(cars));
}

function select_option(html_obj, value) {
	
	obj_list = html_obj.getElementsByTagName('OPTION');
	//console.log(obj_list);
	for (var index in obj_list) {
		obj = obj_list[index];
		if(obj instanceof HTMLElement){
			//console.log(obj);
			obj.removeAttribute('selected');
		}
	} 
	for (var index in obj_list) {
		obj = obj_list[index];
		if(obj instanceof HTMLElement){
			if (obj.getAttribute('value') == value) {
				obj.setAttribute('selected', "");
			}
		}
	}
}


function casa_getElementByAttri(obj, attribute, value) {
	obj_arrays = obj.childNodes;
	//console.log(obj_arrays);
	for (var index in obj_arrays) {
		obj = obj_arrays[index];
		if(obj instanceof HTMLElement){
			//console.log(obj);
			if (obj.getAttribute(attribute) == value) {
				return obj
			}
		}
	}
}

function casa_Element_Filter(element_list, attri, value) {
	/*
	remove the element that "< attri=value >", return the leftover
	*/
    //console.log(element_list);
    result = [];
	for (var index=0; index<element_list.length; index++) {
		ele = element_list[index];
		//console.log(ele);
		if (ele.getAttribute(attri) == value) {
			result.push(ele);
		} else {
			continue;
		}
	}
	return result
}

function casa_Element_Reverse_Filter(element_list, attri, value) {
	/*
	remove the element that "< attri=value >", return the leftover
	*/
    //console.log(element_list);
    result = [];
	for (var index=0; index<element_list.length; index++) {
		ele = element_list[index];
		//console.log(ele);
		if (ele.getAttribute(attri) == value) {
			continue;
		} else {
			result.push(ele);
		}
	}
	return result
}

function casa_getElementsByAttri(obj, attribute, value) {
	var result = [];
	
	obj_arrays = obj.childNodes;
	//console.log(obj_arrays);
	for (var index in obj_arrays) {
		obj = obj_arrays[index];
		if(obj instanceof HTMLElement){
			//console.log(obj);
			if (obj.getAttribute(attribute) == value) {
				result.push(obj);
			}
		}
	}
	return result
}

function casa_getElementsByTag(obj, value) {
	var result = [];
	
	obj_arrays = obj.childNodes;
	//console.log(obj_arrays);
	for (var index in obj_arrays) {
		obj = obj_arrays[index];
		if(obj instanceof HTMLElement){
			//console.log(obj);
			//console.log(obj.nodeName);
			if (obj.nodeName == value) {
				result.push(obj);
			}
		}
	}
	return result
}

function casa_getAllElementsByTag(obj, value, result=[]) {
	/*
	You should only declare arguments "obj" and "value"
	For example: casa_getAllElementsByTag(root_ul_obj, "LI")
	
	root_ul_obj =
		<ul name="task_list">
			<li id="1" selected="" >任务1 (50%)</li>
			<ul parent_id="1"></ul>
			<li id="2" selected="">任务2 (50%)</li>
			<ul parent_id="2">
				<li id="6" selected="">任务2.1</li>
				<ul parent_id="6">
					<li id="7" selected="1">任务2.1.1</li>
					<ul parent_id="7"></ul>
					<li id="13" selected="1">任务2.1.2</li>
					<ul parent_id="7"></ul>
				</ul>
			</ul>
			<li id="3" selected="">任务3</li>
			<ul parent_id="3"></ul>
		</ul>
	
	return: [li#1, li#2, li#6, li#7, li#13, li#3]
	
	*/
	var obj_arrays = obj.childNodes;
	//console.log(obj_arrays);
	if (obj_arrays.length == 0) {
		return result
	}
	//console.log(obj_arrays);
	for (var index=0; index < obj_arrays.length;index++) {
		obj1 = obj_arrays[index];
		if(obj1 instanceof HTMLElement){
			//console.log(obj);
			//console.log(obj.nodeName);
			if (obj1.nodeName == value) {
				result.push(obj1);
			} else {
				result.push.apply(casa_getAllElementsByTag(obj1, value, result=result));
				//console.log(result);
				//console.log(111);
			}
		}
	}
	return result
}

function casa_UL_Get_Last_SPAN_Generations(root_ul_obj, root=true) {	
	/*
	root_ul_obj = 
		<ul name="task_list">
			<li id="1" selected="" >任务1 (50%)</li>
			<ul parent_id="1"></ul>
			<li id="2" selected="">任务2 (50%)</li>
			<ul parent_id="2">
				<li id="6" selected="">任务2.1</li>
				<ul parent_id="6">
					<li id="7" selected="1">任务2.1.1</li>
					<ul parent_id="7"></ul>
					<li id="13" selected="1">任务2.1.2</li>
					<ul parent_id="7"></ul>
				</ul>
			</ul>
			<li id="3" selected="">任务3</li>
			<ul parent_id="3"></ul>
		</ul>
	
	return: [li#1, li#7, li#13, li#3]
	*/
	
	if (root) {
		result=[];
	}
	
	var obj = root_ul_obj;
	//console.log(obj);
	
	var li_list = casa_getElementsByTag(obj, "SPAN");
	//console.log(li_list);
	
	if (li_list.length==0) {
		//console.log(obj);
		//console.log(obj.previousSibling);
		result.push(obj.previousSibling);
		//console.log(result);

	} else {
		//console.log(li_list);
		//console.log(li_list.length);
		for (var index=0; index < li_list.length;index++) {
			var ul_obj = li_list[index].nextSibling;
			casa_UL_Get_Last_SPAN_Generations(ul_obj, root=false);
		}
	}
	
	return result;
}

function casa_UL_Get_Last_LI_Generations(root_ul_obj, root=true) {	
	/*
	root_ul_obj = 
		<ul name="task_list">
			<li id="1" selected="" >任务1 (50%)</li>
			<ul parent_id="1"></ul>
			<li id="2" selected="">任务2 (50%)</li>
			<ul parent_id="2">
				<li id="6" selected="">任务2.1</li>
				<ul parent_id="6">
					<li id="7" selected="1">任务2.1.1</li>
					<ul parent_id="7"></ul>
					<li id="13" selected="1">任务2.1.2</li>
					<ul parent_id="7"></ul>
				</ul>
			</ul>
			<li id="3" selected="">任务3</li>
			<ul parent_id="3"></ul>
		</ul>
	
	return: [li#1, li#7, li#13, li#3]
	*/
	
	if (root) {
		result=[];
	}
	
	var obj = root_ul_obj;
	//console.log(obj);
	
	var li_list = casa_getElementsByTag(obj, "LI");
	//console.log(li_list);
	
	if (li_list.length==0) {
		//console.log(obj);
		//console.log(obj.previousSibling);
		result.push(obj.previousSibling);
		//console.log(result);

	} else {
		//console.log(li_list);
		//console.log(li_list.length);
		for (var index=0; index < li_list.length;index++) {
			var ul_obj = li_list[index].nextSibling;
			casa_UL_Get_Last_LI_Generations(ul_obj, root=false);
		}
	}
	
	return result;
}


function casa_getElementById(obj, id) {
	return casa_getElementByAttri(obj, 'id', id);
}

String.prototype.trim=function(){
　　return this.replace(/(^\s*)|(\s*$)/g, "");
}

Array.prototype.list_contains = function (obj) { 
	/*
	  Example:
	  var obj = "ma";
	  var list = ["ma","zhen"];
	  list.list_contains(obj);  >>>> true
	*/
	var index = this.length; 
	while (index--) { 
		 if (this[index] === obj) { 
			 return true; 
		 } 
	} 
	return false; 
}

Array.prototype.array_contains = function (attr, obj) { 
	/*
	  Example:
	  var obj = "ma";
	  var attr = "name";
	  var array = [{"id":1, "name":"zhen"}, {"id":2, "name":"ma"}];
	  array.array_contains(attr, obj);  >>>> true
	*/
	var list = [];
	for (var index in this) {
		list[index] = this[index][attr];
	}
	
	var index = list.length; 
	while (index--) { 
		 if (list[index] === obj) { 
			 return true; 
		 } 
	} 
	return false; 
}

function check_endtime_bigger_starttime (starttime, endtime) {
	/*
	  var start = "2019-12-02";
	  var end = "2019-12-03";  
	  check_endtime_bigger_starttime(start, end); >>> true
	*/
   start = starttime.replace(/-/g,'/');
   var start_timestamp = new Date(start).getTime();
   end = endtime.replace(/-/g,'/');
   var end_timestamp = new Date(end).getTime();
   
   return parseInt(end_timestamp)>=parseInt(start_timestamp)
   
}

function Longtime_Onchange(obj) {
	//console.log(obj);
	var value = obj.value;
	//console.log(value);
	if (value == "yes") {
		$("#project_status")[0].setAttribute("disabled", "disabled");
		$("#project_starttime")[0].setAttribute("disabled", "disabled");
		$("#project_endtime")[0].setAttribute("disabled", "disabled");
	} else {
		$("#project_status")[0].removeAttribute("disabled");
		$("#project_starttime")[0].removeAttribute("disabled");
		$("#project_endtime")[0].removeAttribute("disabled");
	}
}

function get_task_info(task_id){
	//console.log('####################');
	Run_Php_File(http_type="post", post_info=task_id, php_file="read_task_info.php", locStorVal="taskinfoobj")
}

function get_task(project_id){
	//console.log('####################');
	Run_Php_File(http_type="post", post_info=project_id, php_file="read_task.php", locStorVal="taskobj")
}

function reset_task_li_selected() {
	var aObj=document.getElementsByTagName('span');
	for(var i=0;i<aObj.length;i++)
	{
		aObj[i].setAttribute('selected','');
		aObj[i].style.backgroundColor = 'white';
		aObj[i].style.color = 'black';
	}
}

function detect_click_task(){
	var aObj=document.getElementsByTagName('span');
	//console.log(aObj);
	for(var i=0;i<aObj.length;i++)
	{	
		if (!aObj[i].getAttribute('id')) {
			//console.log(aObj[i]);
			continue;
		}
		
		aObj[i].onclick=function()
		{
			if(this.getAttribute('id'))
			{
				selected_id = this.getAttribute('id');
				//console.log(this);
				reset_task_li_selected();
				this.setAttribute('selected','1');
				this.style.color = 'white';
				this.style.backgroundColor = 'gray';
				
				if (selected_id != "root") {
					load_Task_Info(selected_id);
					
					//console.log(window.location.href);
					if (window.location.href.lastIndexOf('modify_task') != "-1") {
						block_submit("modify_button");
					}
				}
				if (window.location.href.lastIndexOf('modify_task') != "-1") {
					open_submit("modify_button");
				}
				
				localStorage.setItem('task_id_selected',selected_id);
				
				//console.log(this.innerHTML);
				task_name_selected = this.innerHTML.replace(/<select.+/,'');
				//console.log(task_name_selected);
				
				localStorage.setItem('task_name_selected',task_name_selected);
				// console.log(this);
				// console.log(this.innerHTML);
// 					console.log(this.getAttribute('selected'));
// 					console.log(this.getAttribute('id'));
				
				load_Task_Info(selected_id);
				//clear in on work field
				if (window.location.href.lastIndexOf('modify_task') != "-1") {
					reset_work_field()
				}
			}
			return false;
		}
		aObj[i].onmouseover = function(){
			if(this.style.backgroundColor != 'gray'){
				this.style.backgroundColor = 'yellow';
			}
		}
		aObj[i].onmouseout = function(){
			if(this.style.backgroundColor != 'gray'){
				this.style.backgroundColor = 'white';
			}
		}
	}
}


function load_Task_Info(task_id) {

	//get info from DB
	get_task_info(task_id);

	var task_info_array = JSON.parse(localStorage.getItem('taskinfoobj'))[0];
	// console.log(task_info_array);
	var group_name = id2name(task_info_array.group_id, "groups");
	//console.log(group_name);
	var staff_name = id2name(task_info_array.staff_id, "personnel");
	task_info_array['group'] = group_name;
	task_info_array['staff'] = staff_name;

	//load and show task info
	var task_info_obj = document.getElementsByName('task_info')[0];
	td_list = task_info_obj.getElementsByTagName('TD');

	//console.log(task_info_obj.getElementsByTagName('TD'));

	
	//reset all info
	for (var index in td_list) {
		
		if(td_list[index] instanceof HTMLElement){
			name = td_list[index].getAttribute('name');

			if (name == 'notes') {
				//console.log(td_list[index].childNodes);
				
				td_list[index].childNodes[1].innerHTML = "";
			} else {
				if (name != 'null') {
					td_list[index].innerHTML = "";
				}
				
			}
		}
	}
	
	
	for (var index in td_list) {
		if(td_list[index] instanceof HTMLElement){
			//console.log(td_list[index]);
			//console.log(td_list[index].name);
			name = td_list[index].getAttribute('name');
			if (name != 'null') {
				
				if (name == 'notes') {
					//console.log(task_info_array[name]);
					td_list[index].childNodes[1].innerHTML = task_info_array[name];
				} else if (name == 'taskpicpath') {
					
					if(task_info_array[name]!=null){
						var temp=task_info_array[name].split(",");
						var taskpicdata=''; 
						
						for(j = 0,len=temp.length; j < len; j++) {
							taskpicdata=taskpicdata+"<img src='"+temp[j]+"'>";
						}
						
						document.getElementById("taskpicpath").innerHTML=taskpicdata;
					}
				} else if (name == 'taskfilepath') {
					
					if(task_info_array[name]!=null){
						var temp=task_info_array[name].split(",");
						var taskfiledata=''; 
						
						for(j = 0,len=temp.length; j < len; j++) {
							var tempname=temp[j].split("file/");
							taskfiledata=taskfiledata+"<a href='"+temp[j]+"'>"+tempname[1]+"</a>&emsp;&emsp;&emsp;";
						}
						
						document.getElementById("taskfilepath").innerHTML=taskfiledata;
					}
				} else {
					td_list[index].innerHTML = task_info_array[name];
					if (name=="pc") {
						td_list[index].innerHTML
					}
				}
			}
		}
	}
	if(document.getElementById("show_img")) 
	{ 
		var div = document.getElementById("show_img"); 
		div.innerHTML = "";	
		if(task_info_array['taskpicpath']!=null){
			var temp=task_info_array["taskpicpath"].split(",");

			for(j = 0,len=temp.length; j<len; j++) {
				var tempname=temp[j].split("file/");
				var divson = document.createElement("div");
				divson.setAttribute("class", "tr");
				var inndata="<div class='td'><img class='img' src="+temp[j]+"/><img style=\"border:none;cursor: pointer; height: 6%;width: 3%;\" title=\"删除图片\" onclick=\"deletepic('"+temp[j].trim().toString()+"','"+task_id+"');\"src=\"images/delete.jpg\"/></div>";
				divson.innerHTML = inndata;	
				div.appendChild(divson);  				
			}
			
			
		}
		
	}
}

function Replace_Work_Field(replace_url) {
	/*For now, only use this in "modify_task_html"*/
	/*
	Replace the html element <frameset id="work_field"> of file "index.html"
	
	Note:
		1. Only used by the file called by index.html
		2. When use this, Back button on webpage is invalid
	
	*/
	var all_ele = window.parent.document.getElementById('all');
	var work_ele = window.parent.document.getElementById('work_field');
	
	var new_work_ele = document.createElement("frame");
	new_work_ele.setAttribute('src', replace_url);
	new_work_ele.setAttribute('id', 'work_field');
	
	all_ele.replaceChild(new_work_ele, work_ele);
	
}


function get_persons(group_id){
		//console.log(group_id);
	Run_Php_File(http_type="post", post_info=group_id, php_file="read_persons.php", locStorVal="personobj");
	//console.log('group_id');
}
	
function loadPerson(){
	
	//##############get the selected groups id
	var oOpt = document.getElementsByName('task_group');
	//console.log(oOpt);
	var group_id = oOpt[0].value;
	//console.log(group_id);
	
	//#############get all person list from DB according prject id
	get_persons(group_id);
	
	
	//############delete old person list
	var groups_obj = document.getElementsByName('task_staff')[0];
	groups_obj.innerHTML = '<option value="" selected="">---------</option>';
	//console.log(groups_obj.innerHTML);
	
	
	var oS1=document.getElementsByName('task_staff');
	//console.log(localStorage.getItem('personobj'));

	var person_arrays = JSON.parse(localStorage.getItem('personobj'));
	
	//console.log(person_arrays);
	for(var index=0; index<person_arrays.length; index++){
		var person_id = person_arrays[index][0];
		var person_name = person_arrays[index][1];
		add_html = '<option value="'+person_id+'">'+person_name+'</option>';
		//console.log(add_html);
		oS1[0].innerHTML+=add_html;
	}
}

function get_groups(){
	Run_Php_File(http_type="post", post_info="", php_file="read_groups.php", locStorVal="groupobj");
}

function loadGroup(){
	var oS1=document.getElementsByName('task_group');
	//console.log(localStorage.getItem('groupobj'));
	var group_arrays = JSON.parse(localStorage.getItem('groupobj'));
	//console.log(group_arrays);
	for(var index in group_arrays){
		var group_id = group_arrays[index].id;
		var group_name = group_arrays[index].name;
		add_html = '<option value="'+group_id+'">'+group_name+'</option>';
		//console.log(add_html);
		oS1[0].innerHTML+=add_html;
	}
}

function whichButton(event, click_obj) {
	var menu = document.getElementById("taskmenu");
	event.preventDefault();
	var btnNum = event.button;
	if (btnNum == 2) {
		var taskmenu = $('#taskmenu');
		taskmenu.empty();
		taskmenu.attr('selected_id', click_obj.id);
		var moveroot = '<ul><li><i class="fa fa-allow-right" id="click_obj_name" name="' + click_obj.innerHTML + '"></i><p>移动到</p></li>' +'<li><i class="fa fa-allow-right"></i><a id="taskmenu_idnull" name="null" href="" onclick="commit_modify_task_parent_id(this.name, this.id)">根目录</a></li></ul>'
		taskmenu.append(moveroot);
		taskmenu.append(getalltask());
		var e = event;
		e.preventDefault();
		let scrollTop =
			document.documentElement.scrollTop ||
			document.body.scrollTop; // 获取垂直滚动条位置
		let scrollLeft =
			document.documentElement.scrollLeft ||
			document.body.scrollLeft; // 获取水平滚动条位置
		menu.style.display = "block";
		menu.style.left = e.clientX + scrollLeft + "px";
		menu.style.top = e.clientY + scrollTop + "px";
	} else if (btnNum == 0) {
		menu.style.display = "none";
	} else if (btnNum == 1) {
		console.log("您点击了鼠标中键！");
	} else {
		console.log(
			"您点击了" + btnNum + "号键，我不能确定它的名称。"
		);
	}
}

function getalltask(){
	function get_all_task_arrays(){
		var oOpt = document.getElementsByName('project');
		var project_id = oOpt[0].value;
		if (!project_id) {
			return;
		}
		get_task(project_id);
		var task_arrays = JSON.parse(localStorage.getItem('taskobj'));
		return task_arrays;
	}
	
	function use_parent_id_get_task(sub_id, task_arrays) {
		var new_task_arrays = new Array();
		var j = 0;
		for (var i=0; i<task_arrays.length; i++) {
			if (!sub_id) {
				if (!task_arrays[i].parent_id) {
					new_task_arrays[j] = task_arrays[i];
					j = j + 1;
				}
			} else {
				if (task_arrays[i].parent_id == sub_id) {
					new_task_arrays[j] = task_arrays[i];
					j = j + 1;
				}
			}
		}
		return new_task_arrays
	}
	
	function create_menu_str(sub_str, parent_id, task_arrays) {
		var sub_array = use_parent_id_get_task(parent_id, task_arrays);
		if (sub_array.length == 0) {
			return sub_str;
		}
		sub_str += '<ul>\n';
		for (var i=0; i<sub_array.length; i++) {
			sub_str +=  ('<li><i class="fa fa-allow-right"></i><a id="taskmenu_id' + 
				sub_array[i].id + '" name="' + sub_array[i].id + 
				'" href="" onclick="commit_modify_task_parent_id(this.name, this.id)">' + 
				sub_array[i].name + '</a>\n');
			
			sub_parent_id = sub_array[i].id;
			sub_str = create_menu_str(sub_str, sub_parent_id, task_arrays);
			sub_str += '</li>\n';
		}
		sub_str += '</ul>\n'; 
		return sub_str;
	}
	task_arrays = get_all_task_arrays();
	var a = create_menu_str(sub_str='', parent_id=null, task_arrays=task_arrays)
	return a;
}

function sortalltask(task_arrays){
	//designer: xiaojinshui
	function get_all_task_arrays(){
		var oOpt = document.getElementsByName('project');
		var project_id = oOpt[0].value;
		if (!project_id) {
			return;
		}
		get_task(project_id);
		var task_arrays = JSON.parse(localStorage.getItem('taskobj'));
		return task_arrays;
	}
	
	function use_parent_id_get_task(sub_id, task_arrays) {
		var new_task_arrays = new Array();
		var j = 0;
		for (var i=0; i<task_arrays.length; i++) {
			if (!sub_id) {
				if (!task_arrays[i].parent_id) {
					new_task_arrays[j] = task_arrays[i];
					j = j + 1;
				}
			} else {
				if (task_arrays[i].parent_id == sub_id) {
					new_task_arrays[j] = task_arrays[i];
					j = j + 1;
				}
			}
		}
		return new_task_arrays
	}

	function push_into_task_array(parent_id, task_arrays) {
		var sub_array = use_parent_id_get_task(parent_id, task_arrays);
		if (sub_array.length == 0) {
			return
		}
		for (var i=0; i<sub_array.length; i++) {
			sort_task_array.push(sub_array[i]); // push into sort_task_array 
			var sub_parent_id = sub_array[i].id; // get sub_parent_id
			push_into_task_array(sub_parent_id, task_arrays);
		}
		return
	}

	//task_arrays = get_all_task_arrays();
	var sort_task_array = new Array();
	push_into_task_array(parent_id=null, task_arrays=task_arrays)
	return sort_task_array;
}

function commit_modify_task_parent_id(pid, name) {
	name = "#" + name;
	var pid_text = $(name).text();
	var taskmenu = $('#taskmenu');
	var init_name = $('#click_obj_name').attr('name');
	if (taskmenu.attr('selected_id') == pid) {
		alert('不能移动到自身中');
	} else {
		if(confirm('你真的确定将\n  任务: "' +init_name + '"\n移动到\n  任务: "' + pid_text + '"\n吗?')) {
			var post_data = {
				selected_id: taskmenu.attr('selected_id'),
				parent_id: pid,
			};
			$.ajax({
				type: 'POST',
				url: './modify_task_parent_id.php',
				data: post_data,
				success: function(msg) {
					if (msg.search(/fail reset parent_id/i) != -1) {
						alert('不能移动当前任务到其子任务节点中');
					} else {
						console.log('移动成功');
						window.location.reload();
					}
				}
			});
		}
	}
}

function send_task_email(){
    /*
    * message:' 操作成功',    //提示信息
    * duration:'5000',       //显示时间（默认：5s）
    * type:'info',           //显示类型，包括4种：success.error,info,warning 默认info
    * showClose:false,       //显示关闭按钮（默认：否）
    * center:true,           //页面竖直居中（默认：否）
    * onClose:function,      //点击关闭回调函数
    */ 
    var task_id = localStorage.getItem('task_id_selected');
    //console.log(task_id);
    get_task_info(task_id);
    var task_info_array = JSON.parse(localStorage.getItem('taskinfoobj'))[0];
    if (task_info_array.staff_id == null) {
        $.message({
            type:'warning',
            message:'<div style="color:#333;font-weight:bold;font-size:16px;">请先分配任务所属人员<div><span style="color:lightgrey;font-size:small;">3秒后自动关闭</span>',
            duration:3000,
            center:true
        });
        return;
    }
    var staff_name = id2name(task_info_array.staff_id, "personnel");
    var email = name2email(name);
    if (email == null) {
        $.message({
            type:'warning',
            message:'<div style="color:#333;font-weight:bold;font-size:16px;">邮箱地址不存在,请联系管理人员添加邮箱地址<div><span style="color:lightgrey;font-size:small;">3秒后自动关闭</span>',
            duration:3000,
            center:true
        });
        return;   
    } else {
        console.log(email);
    }
    var post_data = {
        sent_to: email,
        task_info: task_info_array,
    };
    $.ajax({
        type: 'POST',
        url: './send_task_email.php',
        data: post_data,
        success: function(msg) {
            if (msg.search(/sentsuccess/i) == -1) {
                $.message({
                    type:'error',
                    message:'<div style="color:#333;font-weight:bold;font-size:16px;">邮件发送失败<div><span style="color:lightgrey;font-size:small;">3秒后自动关闭</span>',
                    duration:3000,
                    center:true
                });
            } else {
                $.message({
                    type:'success',
                    message:'<div style="color:#333;font-weight:bold;font-size:16px;">邮件发送成功<div><span style="color:lightgrey;font-size:small;">3秒后自动关闭</span>',
                    duration:3000,
                    center:true
                });
            }
        }
    });
}
function deletepic(path,task_id) {
	var post_data = {
        path: path,
        task_id: task_id,
		table: "pmj_task",
    };
	console.log(post_data);
    $.ajax({
        type: 'POST',
        url: './delet_img.php',
        data: post_data,
        // success: function(msg) {
			// console.log(msg);
            // if (msg.search(/success/i) == -1) {
                // $.message({
                    // type:'error',
                    // message:'<div style="color:#333;font-weight:bold;font-size:16px;">删除图片失败<div><span style="color:lightgrey;font-size:small;">3秒后自动关闭</span>',
                    // duration:3000,
                    // center:true
                // });
            // } else {
                // $.message({
                    // type:'success',
                    // message:'<div style="color:#333;font-weight:bold;font-size:16px;">删除图片成功<div><span style="color:lightgrey;font-size:small;">3秒后自动关闭</span>',
                    // duration:3000,
                    // center:true
                // });
            // }
        // }
    });
	
}