function goInvite()
{	
	window.location = 'http://island.hapyfish.com/invite/top';
}

function goPay()
{
	window.location = 'http://island.hapyfish.com/pay/top';
}

var OnLine = -1;
var islandLoader = null;

function thisMovie(movieName)
{
	if (document[movieName]) {
		return document[movieName];
	}
	if (navigator.appName.indexOf("Microsoft") != -1) {
		if (window[movieName].length != undefined) {
	    	return window[movieName][1];
	    } else {
	    	return window[movieName];
	    }
	}else{
	    if(document[movieName].length != undefined){
	        return document[movieName][1];
	    }
	    return document[movieName];
	}
}

function getFlash()
{
	if (islandLoader == null) {
		islandLoader = thisMovie('islandLoader');
	}
	
	return islandLoader;
}

function debug(info)
{
	if(window.console) {
		console.debug(info);
	}
}

function isOnline(name)
{
	OnLine = -1;
	var url = 'http://amos.im.alisoft.com/userstatus3.aw?uid=' + name + '&site=cntaobao&charset=utf-8';
	jQuery.getScript(url, function() {
		debug('ok');
		if (OnLine == 1) {
			var flash = getFlash();
			if (flash) {
				flash.setWangOnline();
			}
		}
	}
	);
}

function sendMessage(talkId, userId)
{
	talkId = 'cntaobao' + talkId;
	userId = 'cntaobao' + userId;
	var p = "loginid=" + getSiteLoginId(talkId);
	sendClientMsg( getSite(userId), getSiteLoginId(userId), getSite(talkId), getSiteLoginId(talkId), OnLine, p);
}


if (typeof APP_DOMAIN=="undefined") {
	APP_DOMAIN = null;
} 

function getDomain()
{
	if (!APP_DOMAIN) {
		var search = window.location.search;
		var domain = 'taobao.com';
		if (search && search.length > 1) {
			var ret = {},seg = search.replace(/^\?/,'').split('&'),len = seg.length, i = 0, s;
	        for (;i<len;i++) {
	            if (!seg[i]) { continue; }
	            s = seg[i].split('=');
	            ret[s[0]] = s[1];
	        }
	        
	        if(ret.domain) {
	        	domain = ret.domain;
	        }
		}
		
		APP_DOMAIN = domain;
	}
	
	return APP_DOMAIN;
}
