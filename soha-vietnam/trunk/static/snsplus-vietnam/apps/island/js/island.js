var HF = HF || {};

HF.Island = (function(){
	var appid = 610;
	var serviceURL = 'http://pengyou.qq.com/app/third/service.html';

	function showLoginBox(callbackurl) {
		location.href = serviceURL +'#qz_appid='+appid+'&qz_oprtype=login'+(callbackurl ? '&qz_callbackurl='+callbackurl : '');
	}
	
	function toHome() {
		location.href = serviceURL + '#qz_oprtype=tohome';
	}
	
	function inviteFriend(target) {
		if (!target) {
			location.href = serviceURL + '#qz_appid=' + appid + '&qz_oprtype=invite';
		} else {
			var f = document.getElementById(target);
			if (f) {
				f.src = serviceURL + '#qz_appid=' + appid + '&qz_oprtype=invite';
			}
		}
	}
	
	function toFriendHome(openid)
	{
		var url = 'http://appmng.xiaoyou.qq.com/cgi-bin/xyapp/xy_third_jump_HomePage.cgi?openid=' + openid;
		window.open(url);
	}
	
	function toPayment()
	{
		var url = 'http://imgcache.qq.com/qzone/mall/app_pay/pay.html#appId=' + appid;
		window.open(url);
	}
	
	return {
		'showLoginBox' : showLoginBox,
		'toHome': toHome,
		'inviteFriend' : inviteFriend,
		'toFriendHome' : toFriendHome,
		'toPayment' : toPayment
	}
})()
