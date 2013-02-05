/**!
 * NateCy Opensocial JavaScript Library For Canvas
 * Version: 1.0.0
 *
 * Copyright (c) 2011 hapyfish.com
 */
 
var natecy = natecy || {};

natecy.getType = function(obj) {
    var type = typeof(obj);
    if (type == 'object' && (obj instanceof Array)) {
       type = 'array';
    }
    
    return type;
}

natecy.hitch = function(thisObject, method) {  
    var fcn;
    if(typeof method == "string") {  
        fcn = thisObject[method];
    } else {  
        fcn = method;
    }  
    
    return function() {  
        return fcn.apply(thisObject, arguments);  
    }  
}

natecy.DateFormat = function(d, fmt)
{
    var o = {
        "M+" : d.getMonth() + 1,
        "d+" : d.getDate(),
        "h+" : d.getHours(),
        "m+" : d.getMinutes(),
        "s+" : d.getSeconds(),
        "q+" : Math.floor((d.getMonth() + 3)/3),
        "S"  : d.getMilliseconds()
    };
    
    if(/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (d.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    
    for(var k in o) {
        if(new RegExp("("+ k +")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        }
    }
    
    return fmt; 
}

natecy.Logger = function(enabled) {
    this.enabled = enabled || true;
    var hasConsole = window.console && console.debug;
    
    this.debug = function(message) {
        if (this.enabled && hasConsole) {
            console.debug(message);
        }
        return this;
    }
    
    this.error = function(message) {
        if (this.enabled && hasConsole) {
            console.error(message);
        }
        return this;
    }
}

natecy.application = natecy.application || {};

natecy.application.newCanvas = function(host, appId, appName, staticUrl) {
    return new natecy.Canvas(host, appId, appName, staticUrl);
}

/**
 * Class Canvas
 */
natecy.Canvas = function(host, appId, appName, staticUrl) {
    this.version = '1.0.0';
    this.setHost(host);
    this.appName_ = appName.toLowerCase();
    this.appSignature_ = '';
    this.logger = new natecy.Logger(false);
    this.size_ = {width: '100%', height: '800'};
    this.appId_ = appId;
    this.ownerId_ = 0;
    this.viewerId_ = 0;
    this.nonce_ = '';
    this.isUpdated_ = false;
    this.viewerFriends_ = [];
    this.viewer_ = [];
    
    this.isGetViewer = true;
    this.isGetOwner = false;
    
    this.runUrl_ = this.host_ + '/index';
    this.staticHost_ = staticUrl;//'http://yh-t1.happyfish001.com/static'
    
    this.natecyHost_ = 'http://appstore.nate.com';
    this.checkUrl_ = this.host_ + '/callback/checkstatus';
    this.loginGameUrl_ = this.host_ + '/index/index';
    this.inviteCallBackUrl_ = this.host_ + '/callback/invite';
    this.activityCallBackUrl_ = this.host_ + '/callback/activity';
    
    this.loadingImgSrc_ = this.staticHost_ + '/img/loader.gif';
    this.containerId_ = 'canvas_container';
    this.childIframeId_ = 'content_iframe';
    
    //this.init_();
}

natecy.Canvas.prototype.getHost = function() {
    return this.host_;
}

natecy.Canvas.prototype.setHost = function(host) {
    if (host.charAt(host.length - 1) == '/') {
        host = host.substr(0, host.length - 1);
    }
    this.host_ = host;
    return this;
}

natecy.Canvas.prototype.getStaticHost = function() {
    return this.staticHost_;
}

natecy.Canvas.prototype.setStaticHost = function(host) {
    this.staticHost_ = host;
    return this;
}

natecy.Canvas.prototype.getAppName = function() {
    return this.appName_;
}

natecy.Canvas.prototype.getAppId = function() {
    return this.appId_;
}

natecy.Canvas.prototype.setAppSignature = function(signature) {
    this.appSignature_ = signature;
    return this;
}

natecy.Canvas.prototype.enableDebug = function(enabled) {
    this.logger.enabled = enabled;
    return this;
}

natecy.Canvas.prototype.getSize = function() {
    return this.size_;
}

natecy.Canvas.prototype.setSize = function(size) {
    this.size_ = size;
    return this;
}

natecy.Canvas.prototype.setHeight = function(height) {
    this.size_.height = '' + height;
    return this;
}

natecy.Canvas.prototype.getOwnerId = function() {
    return this.ownerId_;
}

natecy.Canvas.prototype.getViewerId = function() {
    return this.viewerId_;
}

natecy.Canvas.prototype.isOwner = function() {
    return this.viewerId_ == this.ownerId_;
}

natecy.Canvas.prototype.getNonce = function() {
    return this.nonce_;
}

natecy.Canvas.prototype.setRunUrl = function(url) {
    if (url.indexOf('http://') == -1) {
        url = this.host_ + url;
    }
    this.runUrl_ = url;
    return this;
}

natecy.Canvas.prototype.getNatecyHost = function() {
    return this.natecyHost_;
}

natecy.Canvas.prototype.setLoadingImgSrc = function(src) {
    if (src.indexOf('http://') == -1) {
        src = this.host_ + src;
    }
    this.loadingImgSrc_ = src;
    return this;
}

natecy.Canvas.prototype.getContainerId = function() {
    return this.containerId_;
}

natecy.Canvas.prototype.setContainerId = function(id) {
    this.containerId_ = id;
    return this;
}

natecy.Canvas.prototype.getContainerUrl = function() {
    var href = window.location.href;
    return href.substr(0, href.indexOf('#'));
    //return href;
}

natecy.Canvas.prototype.isRpcMode = function() {
    var rpc_mode = this.getParam('rpc_mode');
    if (rpc_mode) {
        return true;
    }
    
    return false;
}

natecy.Canvas.prototype.getRunAppliUrl = function() {
    return document.referrer;
}

natecy.Canvas.prototype.getJoinUrl = function() {
    return this.natecyHost_ + '/Main/View?apps_no=' + this.appId_;
}

natecy.Canvas.prototype.preLoadImage = function(images) {
    var d = document; 
    if(d.images) {
        if (!d.MM_p) d.MM_p = new Array();
        var i, j = d.MM_p.length, a = images;
        for(i = 0; i < a.length; i++) {
            if (a[i].indexOf("#") != 0) {
                d.MM_p[j] = new Image;
                d.MM_p[j++].src = a[i];
            }
        }
    }
}

/************ ops start 2011-03-09****************/
natecy.Canvas.prototype.start = function() {
    if (this.isRpcMode()) {
		var u = location.href, h = u.substr(u.indexOf('#') + 1).split('&'), t, r;
		try {
            t = h[0] === '..' ? parent.parent : parent.frames[h[0]];
            r = t.gadgets.rpc.receive;
		} catch (e) {
		}
		r && r(h);
    } else {
    	//alert('start');
        //this.preLoadImage([this.loadingImgSrc_]);
        //gadgets.util.registerOnLoadHandler(natecy.hitch(this, this.onload_));
    	$('#hf_wrapper').html('loading...');
    	this.onload_();
    }
}

natecy.Canvas.prototype.onload_ = function() {
    //hidden iframe scroll bar
    if (document.all) { //IE
        document.body.setAttribute('scroll', 'no');
    }
    else { //OTHERS
        document.body.style.overflow = 'hidden';
    }
    //this.createContainerContent();
    //gadgets.window.adjustHeight(this.getSize().height);
    //this.checkStatus();
    this.getData();
}

natecy.Canvas.prototype.getData = function() {

    // for more profile info
    // default only [ID, NAME(nickname), THUMBNAIL_URL]
    var p = {};
    p[opensocial.DataRequest.PeopleRequestFields.PROFILE_DETAILS] = [
       opensocial.Person.Field.ABOUT_ME, 
       opensocial.Person.Field.GENDER, 
       opensocial.Person.Field.AGE, 
       opensocial.Person.Field.DATE_OF_BIRTH,
       opensocial.Person.Field.HAS_APP,
       opensocial.Person.Field.PROFILE_URL
    ];
    
    var opt = {};
    opt[opensocial.DataRequest.PeopleRequestFields.FILTER] = opensocial.DataRequest.FilterType.HAS_APP;
    var req = opensocial.newDataRequest();
    // request for viewer info
    req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.VIEWER, p), 'viewer_data');
    req.send(natecy.hitch(this, this.onLoadData_));
}

/**
 * Parses the response to the data information request
 * @param {Object} response data information that was requested.
 */
natecy.Canvas.prototype.onLoadData_ = function(response) {
    var item;
    //get data from natecy response
    item = response.get('viewer_data');
    if(item.hadError()) {
        this.logger.error("get viewer data error: " + item.getErrorMessage());
        return;
    }
    //viewerInfo
    this.viewer_ = item.getData();
    this.viewerId_ = this.viewer_.getId();
    // if viewer has not joined this app, redirect to app join page      
    var hasApp = this.viewer_.getField(opensocial.Person.Field.HAS_APP);
    /*if( !hasApp ) {
       window.top.location.href = this.getJoinUrl();
       return;
    }*/
//alert('getuserdatadone');
	this.viewerFriends_ = [];
    this.getFriendData(0);
}


natecy.Canvas.prototype.getFriendData = function(num) {
	var limitPerRequest = 20;
	var req = opensocial.newDataRequest();
    var viewer_friends_params = {};
    viewer_friends_params[opensocial.IdSpec.Field.USER_ID] = opensocial.IdSpec.PersonId.VIEWER;
    viewer_friends_params[opensocial.IdSpec.Field.GROUP_ID] = opensocial.IdSpec.GroupId.FRIENDS;
    //viewer_friends_params[opensocial.DataRequest.PeopleRequestFields.MAX] = 500;
    //viewer_friends_params[opensocial.DataRequest.PeopleRequestFields.FILTER] = opensocial.DataRequest.FilterType.HAS_APP;
    var opt = {};
    opt[opensocial.DataRequest.PeopleRequestFields.FIRST] = num*limitPerRequest+0;
    opt[opensocial.DataRequest.PeopleRequestFields.MAX] = limitPerRequest;
    //opt[opensocial.DataRequest.PeopleRequestFields.FILTER] = opensocial.DataRequest.FilterType.HAS_APP;
    var viewer_friends_idspec = opensocial.newIdSpec(viewer_friends_params);
    req.add(req.newFetchPeopleRequest(viewer_friends_idspec,opt), 'viewer_friends_data');
    var self = this;
    req.send(function(dataResponse) {
        if (dataResponse.hadError()) {
            //get friend data error
        	self.logger.error("getFriendData: [" + dataResponse.getErrorCode() + '] ' + dataResponse.getErrorMessage());
        	//window.top.location.href = this.getJoinUrl();
            return;
        } 
        var viewerFriends = dataResponse.get('viewer_friends_data').getData();
        var friends = [];
        friends = self.getFriendIds(viewerFriends);
        self.viewerFriends_ = self.viewerFriends_.concat(friends);
        self.logger.debug('friend cnt:' + friends.length);
        self.logger.debug('all friend cnt:' + viewerFriends.getTotalSize());
        if(viewerFriends.getTotalSize()>((num+1)*limitPerRequest)){
            self.getFriendData(num+1);
        }
        else {
        	if (self.viewer_ && self.viewerFriends_) {
            	var viewerInfo = {
        	      'user': gadgets.json.stringify(self.getUser(self.viewer_)),
        	      'friends': gadgets.json.stringify(self.viewerFriends_)
        	    };
            	//goto login
            	self.loginGame(viewerInfo);
            }
        	else {
        		self.logger.debug("getData: failed");
        		return;
        	}
        }
    });
}

natecy.Canvas.prototype.loginGame = function(viewerInfo) {
	var url = this.loginGameUrl_;
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
    var post_data = viewerInfo;
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var self = this;
    gadgets.io.makeRequest(url, function(response) {
        var data = response.data;
        //begin game
        $('#hf_wrapper').html(data);  
        //$('#hf_script').html('<script type="text/javascript">var aa="in js";alert(aa);</script>');  
        /*if (data && data.status) {
        	alert(data.status);
        }
        else {
        	//login game failed
        	self.logger.error("loginGame: failed!");
        }*/
    }, params);
}

natecy.Canvas.prototype.getFriendIds = function(friends) {
    var data = [];
    var self = this;
    friends.each(function(friend) {
    	var fid = friend.getId();
    	if (fid != self.viewerId_) {
    		data.push(fid);
    	}
    });
    
    return data;
}

natecy.Canvas.prototype.getUser = function(user) {
	//var aryId = user.getId().split(':');
    var data = {
        uid: user.getId(),
        displayName: user.getDisplayName().replace(/&/g, '&amp;'), 
        thumbnailUrl: user.getField(opensocial.Person.Field.THUMBNAIL_URL)
    };
    
    var dateOfBirth = user.getField(opensocial.Person.Field.DATE_OF_BIRTH);
    var gender = user.getField(opensocial.Person.Field.GENDER);
    var profileUrl = user.getField(opensocial.Person.Field.PROFILE_URL);
    //var address = user.getField(opensocial.Person.Field.ADDRESS)[0].getField(opensocial.Address.Field.REGION);
    
    if (dateOfBirth) {
        data['dateOfBirth'] = natecy.DateFormat(dateOfBirth, 'yyyy-MM-dd');
    }
    if (gender) {
        data['gender'] = gender.getDisplayValue(); //gender.getKey();
    }
    //var self = this;
    //self.logger.debug('bb:' + gender.getKey());
    if (profileUrl) {
        data['profileUrl'] = profileUrl;
    }
    return data;
}

natecy.Canvas.prototype.checkStatus = function() {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var post_data = {version: this.version, app_name: this.appName_, app_signature: this.appSignature_, view: "canvas", request_nonce: 1};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.checkUrl_, function(response) {
        var status = response.data;
        if (status) {
          if (status.code == 1) {
              self.nonce_ = status.nonce || status.html;
              if (status.parameters) {
                  self.appId_ = status.parameters.app_id;
                  self.ownerId_ = status.parameters.owner_id;
                  self.viewerId_ = status.parameters.viewer_id;
                  if (self.isOwner()) {
                      self.isGetOwner = false;
                  } 
              }
              if (status.isUpdated) {
                  self.isUpdated_ = (status.isUpdated == 'true');
              }
              
              if (self.isUpdated_) {
                  self.submit_(null, null);
              }
              else {
                  self.getData();
              }
          }
          else {
              var container = document.getElementById(self.getContainerId());
              if (container) {
                  container.innerHTML = status.html;
              }
              if (status.script) {
                  eval(status.script);
              }
          }
        }
        
    }, params);  
}

/**
 * Creates the html for form. The function add the hidden elements 
 * used to pass user and friends information to the application
 */
natecy.Canvas.prototype.createFormHtml = function(url, viewerInfo, ownerInfo) {
    // set accept-charset="utf-8", safari 3.2 has a bug
    // natecy charset=euc-jp, our utf-8
    var formHtml = '<form name="mainform" id="mainform" method="post" accept-charset="utf-8" action="' + url + '">';
        
    formHtml += '<input type="hidden" name="version" value="' + this.version + '"/>';
    formHtml += '<input type="hidden" name="nonce" value="' + this.getNonce() + '"/>';
    formHtml += '<input type="hidden" name="windomain" value="' + this.getParam('parent') + '"/>';
    formHtml += '<input type="hidden" name="top_url" value="' + this.getRunAppliUrl() + '"/>';
    formHtml += '<input type="hidden" name="natecy_platform_api_url" value="' + this.getContainerUrl() + '"/>';
    
    if (viewerInfo) {
        formHtml += '<input type="hidden" name="viewer_info" value=\'' + gadgets.json.stringify(viewerInfo) + '\'/>';
        formHtml += '<input type="hidden" name="viewer_id" value="' + viewerInfo['user']['id'] + '"/>';
    }
    
    if (ownerInfo) {
        formHtml += '<input type="hidden" name="owner_info" value=\'' + gadgets.json.stringify(ownerInfo) + '\'/>';
        formHtml += '<input type="hidden" name="owner_id" value="' + ownerInfo['user']['id'] + '"/>';
    }

    formHtml += '</form>';
    
    return formHtml;
}

/**
 * Gets an iframe body for a given iframe element
 */
natecy.Canvas.prototype.extractIFrameBody = function(iFrameEl) {
    var doc = null;
    if (iFrameEl.contentDocument) { // For NS6 and Mozilla
        doc = iFrameEl.contentDocument; 
    } else if (iFrameEl.contentWindow) { // For IE5.5 and IE6
        doc = iFrameEl.contentWindow.document;
    } else if (iFrameEl.document) { // For IE5
        doc = iFrameEl.document;
    } else {
        return null;
    }
    
    //this is a hack for IE. If the following if loop is removed,
    // the body will be returned as null. We need to write something
    // to the document, here writing the loading image element
    if (doc){
        doc.open();
        doc.write('<div align="center" style="margin-top:200px;"><img src="' + this.loadingImgSrc_ + '" id="loadingImg" /></div>');
        doc.close();
    }
    return doc.body;
}

natecy.Canvas.prototype.submit_ = function(viewerInfo, ownerInfo) {
    //create the frame element dynamically. 
    //Note: This frame element cant be added in the simple 
    //html because the opensocial api load functions stops working
    var frameID = this.childIframeId_;
    var frameSize = this.getSize();
    var frameElem = this.createFrameElement(frameID, frameSize);
    
    //add the frame element to the container div
    var frameContainer = document.getElementById(this.containerId_);
    frameContainer.innerHTML = '';
    frameContainer.appendChild(frameElem);
    var frameObj = document.getElementById(frameID);
    var frameBody = this.extractIFrameBody(frameObj);
    
    var url = this.runUrl_;
    
    // append option other params
    var viewParams = gadgets.views.getParams();

    for (var p in viewParams) {
       if (viewParams.hasOwnProperty(p)) {
           url = this.appendParameterToURL(url, p, viewParams[p]);
       } 
    }
            
    //link frame and form element
    frameBody.innerHTML = this.createFormHtml(url, viewerInfo, ownerInfo);
    
    //submit the form
    var form = frameBody.firstChild;
    
    form.submit();
}
        
natecy.Canvas.prototype.appendParameterToURL = function(url, name, value) {
    if(url.indexOf('?') == -1) {
        return url + '?' + name + '=' + encodeURIComponent(value);
    } else {
        return url + '&' + name + '=' + encodeURIComponent(value);
    }
}
        
/**
 * Creates the frame element dynamically. 
 * Takes as input the frame id, and size object
 * that contains the height and width data
 */
natecy.Canvas.prototype.createFrameElement = function(frameID, size) {
    var frameElem = document.createElement('iframe');
    frameElem.setAttribute('id', frameID);
    frameElem.setAttribute('name', frameID);
    frameElem.frameBorder = 0;             //frameElem.setAttribute('frameBorder', '0'); doesnt work in IE
    frameElem.setAttribute('scrolling', 'no');
    frameElem.setAttribute('align', 'middle');
    frameElem.style.height = size.height;
    frameElem.style.width = size.width;
    //frameElem.style.overflowY = "hidden";
    
    return frameElem;
}
        
/**
 * returns the opensocial container name
 */
natecy.Canvas.prototype.getContainerType = function() {
    var syndParam = this.getParam("container");   
    if (syndParam.indexOf("natecy") != -1){
        syndParam = "natecy";
    }
    return syndParam;
}
        
/**
 * gets the parameters from the container
 */
natecy.Canvas.prototype.getParam = function(key) {
    return _args()[key];
}

natecy.Canvas.prototype.createContainerContent = function() {
    var frameContainer = document.getElementById(this.containerId_);
    if (!frameContainer) {
        var divContainer = document.createElement('div');
        divContainer.setAttribute('id', this.containerId_);
        var divCenter = document.createElement('div');
        divCenter.setAttribute('align', 'center');
        divCenter.style.marginTop = 200;
        
        var loadingImg = document.createElement('img');
        loadingImg.setAttribute('id', 'loadingImg');
        loadingImg.setAttribute('src', this.loadingImgSrc_);
        
        divCenter.appendChild(loadingImg);
        
        divContainer.appendChild(divCenter);
        document.body.appendChild(divContainer);
    }
}

natecy.Canvas.prototype.adjustHeight = function(height) { 
    gadgets.window.adjustHeight(height);
    
    this.size_.height = height;
    var frameElem = document.getElementById(this.childIframeId_);
    if (frameElem) {
        frameElem.style.height = height + 'px';
    }
}


natecy.Canvas.prototype.postActivity = function(title, reciptents) {
    var params = {};
    params[opensocial.Activity.Field.TITLE] = title;
    if (reciptents) {
        var type = natecy.getType(reciptents);
        if(type == "string" || type == "array") {
            if (type == "string") {
                reciptents = reciptents.split(',');
            }
            params[natecy.ActivityField.RECIPIENTS] = reciptents;
        }
    }
    var activity = opensocial.newActivity(params);
    var self = this;
    opensocial.requestCreateActivity(activity, opensocial.CreateActivityPriority.HIGH, function(response) {
        if (response.hadError()) {
            self.logger.error("postActivity: [" + response.getErrorCode() + '] ' + response.getErrorMessage());
            return;
        }
        
        var recipientIds = reciptents ? reciptents.join(',') : '';
        self.activityCallBack(recipientIds);
    });
}

natecy.Canvas.prototype.postActivityWithPic = function(title, picUrl, mimeType, reciptents) {
    var img_params = {};  
    img_params[opensocial.MediaItem.Field.TYPE] = opensocial.MediaItem.Type.IMAGE;
    mimeType = mimeType || 'image/jpeg';
    var img = opensocial.newMediaItem(mimeType, picUrl, img_params);
    
    var params = {};
    params[opensocial.Activity.Field.TITLE] = title;
    params[opensocial.Activity.Field.MEDIA_ITEMS] = [img];
    if (reciptents) {
       var type = natecy.getType(reciptents);
       if(type == "string" || type == "array") {
           if (type == "string") {
               reciptents = reciptents.split(',');
           }
           params[natecy.ActivityField.RECIPIENTS] = reciptents;
       }
    }
    var activity = opensocial.newActivity(params);
    var self = this;
    opensocial.requestCreateActivity(activity, opensocial.CreateActivityPriority.HIGH, function(response) {
        if (response.hadError()) {
            self.logger.error("postActivity: [" + response.getErrorCode() + '] ' + response.getErrorMessage());
            return;
        }
        
        var recipientIds = reciptents ? reciptents.join(',') : '';
        //self.activityCallBack(recipientIds);
    });
}

natecy.Canvas.prototype.activityCallBack = function(recipientIds) {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var post_data = {recipientIds: recipientIds};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.activityCallBackUrl_, function(response) {
        //do something.
        self.logger.debug('activityCallBack: ' + response.text);
    }, params);
}

natecy.Canvas.prototype.invite = function(uids,oid) {
    var recipients = uids || "VIEWER_FRIENDS";
    var reason = opensocial.newMessage('your friend would like you to intall this application.');
    var self = this;
    opensocial.requestShareApp(recipients, reason, function(response) {
        //callback done
        var code = response.getErrorCode();
        if(response.hadError() && code != 200) {
            self.logger.error("requestShareApp: [" + response.getErrorCode() + '] ' + response.getErrorMessage());
            return;
        }
        
        var data = response.getData();
        if (data) {
           var recipientIds = data.recipientIds;
           if (recipientIds && recipientIds.length > 0) {
               self.inviteCallBack(recipientIds.join(','), oid);
           }
        }
    });
}

natecy.Canvas.prototype.inviteCallBack = function(recipientIds, oid) {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var post_data = {recipientIds: recipientIds, oid: oid};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.inviteCallBackUrl_, function(response) {
        //do something.
        if (response.text>=0) {
        	//cm_popup(response.text);
        }
        self.logger.debug('inviteCallBack: ' + response.text);
    }, params);
}
