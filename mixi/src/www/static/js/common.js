function cm_popup(money) 
{
	var staticUrl = 'http://cloudfront.hapyfish.com/apps/island/';
	var divContainer = document.getElementById('canvas_container');
	divContainer.style.position = 'relative';
	
	var divPop = document.createElement('div');
	divPop.setAttribute('id', 'div_cm_popup');
    //divPop.setAttribute('align', 'center');    	
    divPop.style.width = 437;
    divPop.style.height = 276;
    divPop.style.position = 'absolute';
    divPop.style.left = 182;
    divPop.style.top = 204;

    var strHtml = '<div style="width:437px; height:278px; background-image:url(' + staticUrl + 'images/jianglishu.jpg);">';
    if (money > 0) {
    	strHtml += '<div style="width:350px; height:100px; padding:120px 0 0 140px;font-family:MS Gothic;font-size:8pt;">' + money + 'コインゲットしました！</div>';
    }
    else {
    	strHtml += '<div style="width:350px; height:100px; padding:120px 0 0 120px;font-family:MS Gothic;font-size:8pt; ">招待特典のコインをもらえるのは、<br />マイミク1人につき1日1回までですよ！</div>';
    }    
    strHtml += '<div style="width:130px; height:40px; padding:18px 0 0 160px;"><img src="' + staticUrl + 'images/gb.gif" width="129" height="31" border="0" usemap="#Map3" />';
    strHtml += '<map name="Map3" id="Map3"><area shape="rect" coords="2,1,126,29" href="javascript:void(0)" onclick="closeme();return false;" /></map></div>';        
    divPop.innerHTML = strHtml;    
    divContainer.appendChild(divPop);	     
}

function closeme() 
{
	var divPop = document.getElementById('div_cm_popup');
	divPop.innerHTML = '';
	divPop.style.display = 'none';
	var divContainer = document.getElementById('canvas_container');
	divContainer.removeChild(divPop);
}