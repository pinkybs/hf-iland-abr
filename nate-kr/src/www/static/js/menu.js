var _Config = {
        userId:"{%$uid%}",
		platformUid:"{%$platformUid%}",
		homeUrl:"{%$hostUrl%}/index",
		staticUrl:"{%$staticUrl%}",
        inviteUrl:"{%$hostUrl%}/invite/top",
        giftUrl:"{%$hostUrl%}/gift/top",
        hf_skey:"{%$hf_skey%}"
        //shareStreamUrl:"",
        //systemNewsUrl:"",
        //invitationUrl:"{%$hostUrl%}/api/sendinvitation"
    };

var HFApp = {
	loadHtml: function(url, pardata, id) {
		if (typeof(pardata)==undefined || pardata==null) {
	        pardata = {};
		}

	    if (_Config.hf_skey != "") {
	    	pardata['hf_skey'] = _Config.hf_skey;
	    }

	    var param = {};
	    param[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
	    param[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
	    param[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(pardata);
	    param[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.NONE;
	    gadgets.io.makeRequest(url, function(response) {
	    	alert('invite page');
	        var data = response.data;
	        $("#" + id).html(data);  
	    }, param);            
    },
    
    invite: function() {
    	HFApp.loadHtml(_Config.inviteUrl, null, 'hf_wrapper');
            //var fid2 = RKApp.getFriends();
            //fids = fid1+fid2;
    }
    
    
}