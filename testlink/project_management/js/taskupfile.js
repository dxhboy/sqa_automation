var drop = document.getElementById('messages');
var upload_file = document.getElementById('upload-file');
var destination = document.getElementById('messages');

// 进入元素触发
drop.addEventListener("dragenter", function (e) {
	e.preventDefault();
	console.log('进入', e);
})
// 离开元素触发
drop.addEventListener("dragleave", function (e) {
	e.preventDefault();
	console.log('离开', e);
})
// 元素上移动触发
drop.addEventListener("dragover", function (e) {
	e.preventDefault();
})

// 放置图片在元素上触发
drop.addEventListener("drop", function (event) {
	var e = event || window.event;
	e.preventDefault();//阻止默认事件,这是必须的,
	var files = e.dataTransfer.files;
	// console.log(files);
	fileFn(files);
})
// input选择图片触发
upload_file.addEventListener('change', function () {
	// console.log(this.files);
	fileFn(this.files);
});

var isempty = false;//是否清空已选择文件
var fileArr = [];//存储选中的文件数据

function fileFn(f) {
	if (isempty) {
		fileArr = [];
	}
	for (var i = 0; i < f.length; i++) {
		fileArr.push(f[i]);
	}
	destination.innerHTML = '';
	
	for (var x = 0, xlen = fileArr.length; x < xlen; x++) {
		file = fileArr[x];
		// console.log(file.name);
		var name=file.name;
		if (file.type.indexOf('image') != -1) {
			var reader = new FileReader();
			reader.onload = function (e) {
				var img = new Image();
				img.src = e.target.result;
				destination.appendChild(img);
			};
			reader.readAsDataURL(file);//读取文件
			upload(file,'image',name);
		} else {
			upload(file,'file',name);
		}
		
		
		
	}
	
}

function upload(file,type,name) {
	xhr = new XMLHttpRequest();
	
	xhr.open("post", "upload.php", true);
	
	// xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	
	xhr.onload=function(data){
		if(xhr.readyState==4&&xhr.status==200){
			console.log(xhr.responseText);
		}
		
	};
	var fd = new FormData();
	fd.append('file', file);
	xhr.send(fd);
	xhr.onreadystatechange = function () {//请求后的回调接口，可将请求成功后要执行的程序写在其中
		if (xhr.readyState == 4 && xhr.status == 200) {//验证请求是否发送成功
			var data = xhr.responseText;//获取到服务端返回的数据
			// 显示到div中
			if(type=="image") {
				var htmldata=$("#taskpicpath").val();
				
				if(data.indexOf("已存在") != -1){
					alert("上传图片已存在请检查！");
					return 0
				}
				if(htmldata.length==0) {
					htmldata=data;
				} else if(htmldata==null) {
					htmldata=data;
				} else if(htmldata=='null') {
					htmldata=data;
				} else {
					
					htmldata=htmldata+','+data;
				}
				
				$("#taskpicpath").val(htmldata);
			} else {
				var htmldata=$("#taskfilepath").val();
				
				if(data.indexOf("已存在") != -1){
					alert("上传文件已存在请检查！");
					return 0
				}
				
				if(htmldata.length==0) {
					htmldata=data;
				} else if(htmldata==null) {
					htmldata=data;
				} else if(htmldata=='null') {
					htmldata=data;
				} else {
					
					htmldata=htmldata+','+data;
				}
				
				$("#taskfilepath").val(htmldata);
			}
			var nametext=document.getElementById("nametext").innerText;	
			
			if(nametext.length==0) {
				nametext=name;
			} else {
				nametext=nametext+','+name;
			}
			document.getElementById("nametext").innerText=nametext;
		}
	};
	
}

function paste_img(e) {
    if (e.clipboardData && e.clipboardData.items) {
        var imageContent = e.clipboardData.getData('image/png');
        ele = e.clipboardData.items
        for (var i = 0; i < ele.length; ++i) {
        	//粘贴图片
		
            if (ele[i].kind == 'file' && ele[i].type.indexOf('image/') !== -1) {
                var blob = ele[i].getAsFile();
                window.URL = window.URL || window.webkitURL;
                var blobUrl = window.URL.createObjectURL(blob);
				
                /*
               // 显示到div中，此时是显示的本地图片数据，并没有上传到服务器
                var new_img = document.createElement('img');
                new_img.setAttribute('src', blobUrl);
                new_img.setAttribute('blobdata', blob);// 移动div光标到新元素后面
                insertHtmlAtCaret(new_img);// 直接上传，当然你也可以不在这上传，可以点击按钮在上传
                */
                //粘贴文本
				var reader = new FileReader();
				reader.onload = function () {
					var img = new Image();
					img.src = blobUrl;
					destination.appendChild(img);
				};
				reader.readAsDataURL(blob);//读取文件
                upload(blob);
            } else{
				//console.log('没有图片');
            	alert('没有图片');
            }
        }
    }
    else {
        alert('不支持的浏览器');
    }
}
