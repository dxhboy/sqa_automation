function ajaxFunction()
 {
 var xmlHttp;
 try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
 catch (e)
  {
 // Internet Explorer
  try
   {
   xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
   }
  catch (e)
   {
   try
     {
     xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
     }
   catch (e)
     {
     alert("您的浏览器不支持AJAX！");
     return false;
     }
   }
  }
  return xmlHttp;
}

function paste_img(e) {
    if (e.clipboardData && e.clipboardData.items) {
        var imageContent = e.clipboardData.getData('image/png');
        ele = e.clipboardData.items
        for (var i = 0; i < ele.length; ++i) {
        	//粘贴图片
			<!-- console.log(ele[i].kind); -->
			<!-- console.log(ele[i].type.indexOf('image/')); -->
            if (ele[i].kind == 'file' && ele[i].type.indexOf('image/') !== -1) {
                var blob = ele[i].getAsFile();
                window.URL = window.URL || window.webkitURL;
                var blobUrl = window.URL.createObjectURL(blob);
				<!-- console.log('ok'); -->
                /*
               // 显示到div中，此时是显示的本地图片数据，并没有上传到服务器
                var new_img = document.createElement('img');
                new_img.setAttribute('src', blobUrl);
                new_img.setAttribute('blobdata', blob);// 移动div光标到新元素后面
                insertHtmlAtCaret(new_img);// 直接上传，当然你也可以不在这上传，可以点击按钮在上传
                */
                //粘贴文本
                uploadImg(blob);
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
//聊天内容框 插入文本或者其他元素后，移动置光标到最新处
function insertHtmlAtCaret(childElement) {
    var sel, range;
    if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();

            var el = document.createElement("div");
            el.appendChild(childElement);
            var frag = document.createDocumentFragment(), node, lastNode;
            while ((node = el.firstChild)) {
                lastNode = frag.appendChild(node);
            }

            range.insertNode(frag);
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    }
    else if (document.selection && document.selection.type != "Control") {
        // IE < 9
        //document.selection.createRange().pasteHTML(html);
    }
}
//前端上传方法
function uploadImg(obj) {
    //发送的数据
    var fd = new FormData();
    fd.append("file", obj, "imgtest.png");
    $.ajax({
        type: 'post',
        url: 'up.php',
        data: fd,
		// 当有文件要上传时，此项是必须的，否则后台无法识别文件流的起始位置(详见：#1)
        contentType: false,
        processData: false,// 是否序列化data属性，默认true(注意：false时type必须是post，详见：#2)
        xhr: xhrOnProgress(function(e){// (详见：#3) 
            var percent= parseFloat(e.loaded) / e.total;//计算百分比
            $("#percent").html(parseInt(percent*100));
        }),
        success: function(data) {
             // 显示到div中
            var new_img = document.createElement('img');
            new_img.setAttribute('src', data);
            insertHtmlAtCaret(new_img);// 直接上传，当然你也可以不在这上传，可以点击按钮在上传
            $("#newurl").html(data);
        },error:function(e){
        	alert(e);
        }
    })
}
// 监听上传进度
var xhrOnProgress = function(fun) {
    xhrOnProgress.onprogress = fun; //绑定监听
    return function() {
        //通过$.ajaxSettings.xhr();获得XMLHttpRequest对象
        var xhr = $.ajaxSettings.xhr();
        //判断监听函数是否为函数
        if (typeof xhrOnProgress.onprogress !== 'function')
            return xhr;
        //如果有监听函数并且xhr对象支持绑定时就把监听函数绑定上去
        if (xhrOnProgress.onprogress && xhr.upload) {
            xhr.upload.onprogress = xhrOnProgress.onprogress;
        }
        return xhr;
    }
}