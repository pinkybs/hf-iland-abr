function goInvite()
{	
	window.location = 'http://mixitest.hapyfish.com/invite/top';
}

function goPay()
{
	window.location = 'http://mixitest.hapyfish.com/pay/top';
}

function sendFeed(feedSettings)
{
 	if (feedSettings) {
 		feedSettings = gadgets.json.parse(feedSettings);
 		if (feedSettings) {
 		    postActivity(feedSettings.title);
 		}
 	}
}

function sendFeedWithImage(feedSettings)
{
 	if (feedSettings) {
 		feedSettings = gadgets.json.parse(feedSettings);
 		if (feedSettings) {
 		    postActivityWithPic(feedSettings.title, feedSettings.picurl);
 		}
 	}
}

function sendUserLevelUpFeed(level)
{
	var title = 'LV' + level + 'になりました。さっそく見に行こう！';
	//postActivityWithPic(title, 'http://static.hapyfish.com/renren/apps/island/images/feed/user_level_up.gif', 'image/gif');
	postActivity(title);
}