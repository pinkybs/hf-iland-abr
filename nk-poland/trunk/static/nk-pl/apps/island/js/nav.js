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

function sendFeed(feedSettings)
{
	 /*try {
	 	if (feedSettings) {
	 		feedSettings = gadgets.json.parse(feedSettings);
	 		if (feedSettings) {
	 			XN.Connect.showFeedDialog(feedSettings);
	 		}
	 	}
	 }catch(e){}*/
	return;
}

function sendUserLevelUpFeed(level)
{
	/*if ( level != 3 && level != 4 && level != 5 ) {
		var title = '在【<a href="http://apps.renren.com/rrisland">快乐岛主</a>】中升到了' + level + '级，赶快去看看吧~';
		var body = '升级了，奖励真不少啊！大家不要落后，一起努力吧！';
		var feedSettings = {
			'template_bundle_id': 1,
	        'template_data' : {
	        	'images': [{'src': 'http://static.hapyfish.com/renren/apps/island/images/feed/user_level_up.gif', 'href': 'http://apps.renren.com/rrisland'}],
	            'title': title,
	            'body' : body
	        },
	        'body_general' : '',
	        'user_message_prompt' : '',
	        'user_message' : '只要好友多就能快升级，大家快点加我吧~'
		};
		
		XN.Connect.showFeedDialog(feedSettings);
	}*/
	return;
}