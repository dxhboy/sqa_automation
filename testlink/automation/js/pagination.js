/**
 * Created by mendez on 16/8/12.
 */

function firstPage(){
    hide();
    currPageNum = 1;
    showCurrPage(currPageNum);
    showTotalPage();
	
	
    for(i = 1; i < pageCount + 1; i++){
		
		if (numCount>=i) {
			blockTable.rows[i].style.display = "";
		}
		
    }

    firstText();
    preText();
	if(pageNum>currPageNum) {
		
		nextLink();
		lastLink();
	} else {
		nextText();
		lastText();
	}
}

function prePage(){
    hide();
    currPageNum--;
    showCurrPage(currPageNum);
    showTotalPage();
	
    var firstR = firstRow(currPageNum);
    var lastR = lastRow(firstR);
    for(i = firstR; i < lastR; i++){
        blockTable.rows[i].style.display = "";
    }

    if(1 == currPageNum){
        firstText();
        preText();
        if(pageNum>currPageNum) {
			
			nextLink();
			lastLink();
		} else {
			nextText();
			lastText();
		}
    }else if(pageNum == currPageNum){
        preLink();
        firstLink();
        nextText();
        lastText();
    }else{
        if(numCount==1) {
			firstText();
			preText();
		} else {
			firstLink();
			preLink();
		}
        nextLink();
        lastLink();
    }

}

function nextPage(){
    hide();
	if (localStorage.getItem('currPageNum') != null) {
		currPageNum=localStorage.getItem('currPageNum', currPageNum);
		if(pageNum<currPageNum) {
			currPageNum=1;
		}
	} else {
		currPageNum=1;
	}
	
	
	if (localStorage.getItem('onload') == 'yes') {
		
		localStorage.setItem('onload', "no");
		
	} else {
		
		if(pageNum!=currPageNum) {
			currPageNum++;
		}
		
	}
  
    showCurrPage(currPageNum);
    showTotalPage();
	
    var firstR = firstRow(currPageNum);
    var lastR = lastRow(firstR);
    for(i = firstR; i < lastR; i ++){
        blockTable.rows[i].style.display = "";
    }

    if(1 == currPageNum){
        firstText();
        preText();
        
		if(pageNum>currPageNum) {
			
			nextLink();
			lastLink();
		} else {
			nextText();
			lastText();
		}
    }else if(pageNum == currPageNum){
        preLink();
        firstLink();
        nextText();
        lastText();
    }else{
        firstLink();
        preLink();
        if(pageNum>currPageNum) {
			
			nextLink();
			lastLink();
		} else {
			nextText();
			lastText();
		}
    }
}

function lastPage(){
    hide();
    currPageNum = pageNum;
    showCurrPage(currPageNum);
    showTotalPage();
	
    var firstR = firstRow(currPageNum);
    for(i = firstR; i < numCount + 1; i++){
        blockTable.rows[i].style.display = "";
    }
	
	
	if(numCount==1) {
		firstText();
		preText();
	} else {
		firstLink();
		preLink();
	}
    nextText();
    lastText();
	
}

// 计算将要显示的页面的首行和尾行
function firstRow(currPageNum){
    return pageCount*(currPageNum - 1) + 1;
}

function lastRow(firstRow){
    var lastRow = firstRow + parseInt(pageCount);
    if(lastRow > numCount + 1){
        lastRow = numCount + 1;
    }
    return lastRow;
}

function showCurrPage(cpn){
	localStorage.setItem('currPageNum', cpn);
    currPageSpan.innerHTML = cpn;
}

function showTotalPage(){
    pageNumSpan.innerHTML = pageNum;
}


//隐藏所有行
function hide(){
	
	totalcase.innerHTML = numCount;
    for(var i = 1; i < numCount + 1; i ++){
        blockTable.rows[i].style.display = "none";
    }
}

//控制首页等功能的显示与不显示
function firstLink(){firstSpan.innerHTML = "<a href='javascript:firstPage();'>首页</a>";}
function firstText(){firstSpan.innerHTML = "首页";}

function preLink(){preSpan.innerHTML = "<a href='javascript:prePage();'>上一页</a>";}
function preText(){preSpan.innerHTML = "上一页";}

function nextLink(){nextSpan.innerHTML = "<a href='javascript:nextPage();'>下一页</a>";}
function nextText(){nextSpan.innerHTML = "下一页";}

function lastLink(){lastSpan.innerHTML = "<a href='javascript:lastPage();'>尾页</a>";}
function lastText(){lastSpan.innerHTML = "尾页";}