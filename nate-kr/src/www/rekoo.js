var _currentTab = "myfarm";
var _sessionName = "";
var _sessionValue = "";
var _viewer_id = "";
var _uname ="";
var _flashVersion = "0.06";
var fids = ""

var BASE_URL  = "http://cwanimal.rekoo.net";
var MEDIA_URL = "http://cwanimal.rekoo.net/static";

function loadHtml(url, params, id) {
    if (typeof(params)==undefined ||  params==null)
        params = {}
        
    if (_sessionName!="")
        params[_sessionName] = _sessionValue;  
 
    var pobj = {};
    pobj[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    pobj[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
    pobj[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(params);
    pobj[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.NONE;
    gadgets.io.makeRequest(url, function(response) {
        var data = response.data;
        jQuery("#" + id).html(data);  
    }, pobj);              
}

var RKApp = {
    
    init: function() {
        
        
          
            RKApp.getFriends(0);
            //var fid2 = RKApp.getFriends();
            //fids = fid1+fid2;
                      

       
    },
    
    loginCyword:function(){
        //从opensocial平台获取用户信息和好友列表        
        var req = opensocial.newDataRequest();
                
        
        var viewer_params = {};
        viewer_params[opensocial.DataRequest.PeopleRequestFields.PROFILE_DETAILS] = [
            opensocial.Person.Field.THUMBNAIL_URL,
            opensocial.Person.Field.DATE_OF_BIRTH,
            opensocial.Person.Field.GENDER,
            opensocial.Person.Field.HAS_APP
        ];

        req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.VIEWER, viewer_params), 'viewer');
        
        req.send(function(dataResponse){
            if (dataResponse.hadError()) {
                //获取数据错误，通常是用户尚未安装，提示用户安装
                top.location = "http://appstore.nate.com/Main/View?apps_no=121";
                return;
            } 

            var viewer = dataResponse.get('viewer').getData();
            var uid = viewer.getId();
            _viewer_id = uid;
            //var name = viewer.getDisplayName();
            var gender = viewer.getField(opensocial.Person.Field.GENDER).getKey();
            var hasApp = viewer.getField(opensocial.Person.Field.HAS_APP);
            var birth = viewer.getField(opensocial.Person.Field.DATE_OF_BIRTH);
            var thumbnailUrl = viewer.getField(opensocial.Person.Field.THUMBNAIL_URL);
                        //调用Rekoo Farm接口完成登录    
            var url = BASE_URL + "/embed_swf/";
            var params = {};
            params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
            params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
            var post_data = {
                    viewer_id : uid,
                    sex : gender,
                    name : name,
                    photo : thumbnailUrl.replace(/=/g,'|||'),
                    photo_big: thumbnailUrl.replace(/=/g,'|||'),
                    bdate : birth,
                    friends : fids 
                };
            params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
            params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
            gadgets.io.makeRequest(url, function(response) {
                var data = response.data;
                
                _sessionName = data.session_name;
                _sessionValue = data.session_value;
                _uname = data.uname;
                //初始化UI
                RKApp.initCavas();
            }, params);
             });
    },
    
    getFriends:function(num){
        var req = opensocial.newDataRequest();
        var viewer_friends_params = {};
        viewer_friends_params[opensocial.IdSpec.Field.USER_ID] = opensocial.IdSpec.PersonId.VIEWER;
        viewer_friends_params[opensocial.IdSpec.Field.GROUP_ID] = opensocial.IdSpec.GroupId.FRIENDS;
        //viewer_friends_params[opensocial.DataRequest.PeopleRequestFields.MAX] = 500;
        //viewer_friends_params[opensocial.DataRequest.PeopleRequestFields.FILTER] = opensocial.DataRequest.FilterType.HAS_APP;
        var params = {};
        params[opensocial.DataRequest.PeopleRequestFields.FIRST] = num*20+1;
        params[opensocial.DataRequest.PeopleRequestFields.MAX] = 20;
        params[opensocial.DataRequest.PeopleRequestFields.FILTER] = opensocial.DataRequest.FilterType.HAS_APP;
        var viewer_friends_idspec = opensocial.newIdSpec(viewer_friends_params);
        req.add(req.newFetchPeopleRequest(viewer_friends_idspec,params), 'viewerFriends');
        var viewer_friends_idspec = opensocial.newIdSpec(viewer_friends_params);
        req.send(function(dataResponse){
            if (dataResponse.hadError()) {
                //获取数据错误，通常是用户尚未安装，提示用户安装
                top.location = "http://appstore.nate.com/Main/View?apps_no=121";
                return;
            } 
            var viewerFriends = dataResponse.get('viewerFriends').getData();
            
            viewerFriends.each(function(friend) {
              fids += friend.getId() + ",";
            });
            if(viewerFriends.getTotalSize()>((num+1)*20)){
                RKApp.getFriends(num+1);
            }else{
                RKApp.loginCyword();
            }
            } );
    },
    
    initCavas: function(){
        RKApp._initNotice();
        RKApp._initTopBar();
        RKApp.doMyFarm();
    },
    
    _initNotice:function(){
        var url = BASE_URL + "/notices/";
        loadHtml(url, {}, "rk_notices");
    },
    
    _initTopBar:function(){
        var hc = "<img src='"+MEDIA_URL+"/images/logo_beta.gif' style='float:left'/><ul>"; 
        
        if(_viewer_id == "62119946"){
            _topbar_def = _topbar_def_1;
        }else{
            _topbar_def = _topbar_def_1;
        }
        
        for (t in _topbar_def) {
            var tobj = _topbar_def[t];
            
            /*
            if (tobj.name=="invite")
                hc += "<li style='margin-top:0'>";
            else
                hc += "<li>";*/
            if (tobj.name=="addkb"){
                hc += "<li style='margin-top:0px'>";
           } else{
                hc += "<li>";}
            var tid = "rk_topbar_" + tobj.name;
            if (tobj.name==_currentTab)
                hc += "<img src='"+MEDIA_URL+ tobj.on + "' id='" +tid+ "' style='cursor:pointer' /></li>";
            else
                hc += "<img src='"+MEDIA_URL+ tobj.off + "' id='" +tid+ "' style='cursor:pointer' /></li>";    
        }
        
        hc += "</ul>"
        
        jQuery("#rk_topbar").html(hc);
        
        for (t in _topbar_def) {
            var tobj = _topbar_def[t];
            var tid = "rk_topbar_" + tobj.name;
            jQuery("#"+tid).click(tobj.click);
        }
        jQuery("#rk_banner").html("<a href=\"http://appstore.nate.com/Main/View?apps_no=1065\" target=\"_blank\"><img id =\"bannerimg\" src=\"http://cwanimal.rekoo.com/static/images/banner_leyuan.jpg\" /></a>");
        
    },
    
    doMyFarm:function() {   //我的牧场
        _currentTab = "myfarm";
        RKApp.setTabOn(_currentTab);  
        if(_uname != null && _uname.length != 0 && _uname != 'None'){
            jQuery("#rk_main").html("<div id='rk_swf'></div>");
            
            var swf_url = MEDIA_URL + "/images/Main.swf?v=" + _flashVersion;
            var flashvars = {
                    session_name : _sessionName,
                    session_value : _sessionValue,
                    uid : _viewer_id,
                    userLocaleChain : "ko_KR",
                    app_url : BASE_URL + "/",
                    self_url : MEDIA_URL + "/images/"
                };
            var flashparams = {
                    wmode : "Opaque",
                    allowscriptaccess : "always"
                }; 
            swfobject.embedSWF(swf_url, "rk_swf", "760", "590", "9.0.0", null, flashvars, flashparams);
    }else{
        var url = BASE_URL + "/nickname/";
        loadHtml(url, {}, "rk_main");
    }
        gadgets.window.adjustHeight(800); 
    },
    
    doNickName: function(){
	    var kan_keys="!0@0#0$0%0^0&0*0(0)0-0_0+0=0|0?0>0<0/0{0}0[0]0.0:0\"0——0~0`0;0\\0'";
		var kan_sp=kan_keys.split("0")
		var newname = document.getElementById('nname').value
		var pin=0;
		for (i=0;i<kan_sp.length;i++){
			if(newname.indexOf(kan_sp[i])>=0){ 
				pin=1;
				break;
		}
		}
		if(pin == 1){
			alert("애칭에서 특이수자 포함해서는 안됩니다");
			return false;
    	}
    	
        if(newname.replace(/ /g,'').length>8){
        alert("게임의 이름은 2문자 이상 8문자 이하여야 합니다");
        return false;
        }
        if(newname.replace(/ /g,'').length<2){
        alert("게임의 이름은 2문자 이상 8문자 이하여야 합니다");
        return false;
        }
        var params = {};
        var tet = {}
        params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
        params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.Text;
        tet[_sessionName] = _sessionValue;  
        tet['nname']=newname;
        params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(tet);
        params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.NONE;
        gadgets.io.makeRequest(BASE_URL + '/changenickname/', RKApp.init, params);
    },
    
    
    
    
    doForum: function() {
        window.open("http://cyworld.com");
    },
    doAddKB: function(){
        _currentTab = "addkb";
        RKApp.setTabOn(_currentTab); 
        RKApp.setMainOnLoading(); 
        var url = BASE_URL + "/addkb/";
        loadHtml(url, {}, "rk_main");
        gadgets.window.adjustHeight(2000);

    },
    
    doHelp: function() {
        _currentTab = "help";
        RKApp.setTabOn(_currentTab); 
        RKApp.setMainOnLoading(); 
        
        var url = BASE_URL + "/help/";
        loadHtml(url, {}, "rk_main");
        gadgets.window.adjustHeight(2000);
    },
    
    doConfig: function() {
    	  _currentTab = "config";
        RKApp.setTabOn(_currentTab); 
        RKApp.setMainOnLoading(); 
        var url = BASE_URL + "/user_settings/";    	
        loadHtml(url, {}, "rk_main");
        gadgets.window.adjustHeight(800);
    },
    
    doInvite: function() {
        _currentTab = "invite";
          RKApp.setTabOn(_currentTab);
          RKApp.setMainOnLoading();
          var url = BASE_URL + "/invite_install/";
          loadHtml(url, {}, "rk_main");
          gadgets.window.adjustHeight(800);
  
    },
    
    _doInvite: function() {
            var dataRequest = opensocial.newDataRequest();
            var params = {};
            var uid;
			var str = '';
			var friend_noapp = new Array();
            //params[opensocial.DataRequest.PeopleRequestFields.FILTER] = opensocial.DataRequest.FilterType.HAS_APP;
            params[opensocial.DataRequest.PeopleRequestFields.MAX] = 50;
			params[opensocial.DataRequest.PeopleRequestFields.PROFILE_DETAILS] = [opensocial.Person.Field.ID, opensocial.Person.Field.HAS_APP];
            var idspec = opensocial.newIdSpec();
            idspec.setField(opensocial.IdSpec.Field.USER_ID, opensocial.IdSpec.PersonId.VIEWER);
            idspec.setField(opensocial.IdSpec.Field.GROUP_ID, opensocial.IdSpec.GroupId.FRIENDS);


            var request = dataRequest.newFetchPeopleRequest(idspec, params);
            var getResponse = function(response)
            {
               var hadError = response.hadError();
                var resultData;
                
                if(hadError)
                {
                   //出错了
                   alert("에러입니다.");
                    }
                else
                {
                    resultData = response.get("dataObj").getData().asArray();
                    for(var i=0, l=resultData.length,r=0; i<l; i++)
                    {
                        if(!resultData[i].getField(opensocial.Person.Field.HAS_APP)){
                            friend_noapp[r]= resultData[i].getField(opensocial.Person.Field.ID) ;
                            //friend_noapp[r]=62275099;
                            if(r == 0){
                            str =resultData[i].getDisplayName();
                            }else{
                            str +=","+resultData[i].getDisplayName();
                            }
                        r++;
                        }
                    } 
            if(str=='')
            {
                alert('일촌친구가 없거나, 일촌친구가 모두 햇빛목장을 추가하였습니다.친구를 더욱 추가하세요.');
                return;
            }
            opensocial.requestShareApp( friend_noapp,null, function(response) {
              if( response.hadError() ){
                  alert("서버 사용량이 큽니다, 잠시후 다시 시도해주세요.");
              }else{
                if(confirm(str.replace(/\w{1}$/,"")+"에게 초대 메일을 보냅니다,확인합니까?")){  
                    setCookie('loginFeedSent', 'true', 60 * 60 * 24*2);
                    var url = BASE_URL + "/install_callback/";
                    loadHtml(url, {'ids':friend_noapp}, "rk_main");  
                    alert("일촌추천이 성공되 었습니다.");
                    }   
                }
			} );    
               //处理回调问题
            }
        };

            dataRequest.add(request, "dataObj");
            dataRequest.send(getResponse);
    },
    
    setMainOnLoading: function() {
        var hc = "<div height='400' style='margin-top:35px;margin-left:auto;margin-right:auto;text-align:center'>" +
            "<img src='"+MEDIA_URL+"/images/loading.gif' /></div>"
        jQuery("#rk_main").html(hc);
    },
        
    setTabOn: function(tabName) {   //设置tab的图片为on状态
        for (t in _topbar_def) {
            var tobj = _topbar_def[t];
            var tid = "rk_topbar_" + tobj.name;
            if (tobj.name == tabName)
                jQuery("#"+tid).attr("src", MEDIA_URL + tobj.on);
            else
                jQuery("#"+tid).attr("src", MEDIA_URL + tobj.off); 
        }        
    }
    
}

var _topbar_def = [];
var _topbar_def_1 = [
    {
        name : "myfarm",
        on  : "/images/myfarm2.png",
        off : "/images/myfarm1.png",
        click : RKApp.doMyFarm
    },
    
    {
        name : "addkb",
        on  : "/images/chongzhi_on.png",
        off : "/images/chongzhi_off.png",
        click : RKApp.doAddKB
    },    
    
    {
        name : "invite",
        on  : "/images/invite2.png",
        off : "/images/invite1.png",
        click : RKApp.doInvite
    },
     
    {
        name : "help",
        on  : "/images/help2.png",
        off : "/images/help1.png",
        click : RKApp.doHelp
    }             
];
var _topbar_def_2 = [
    {
        name : "myfarm",
        on  : "/images/myfarm2.png",
        off : "/images/myfarm1.png",
        click : RKApp.doMyFarm
    },
    
    {
        name : "invite",
        on  : "/images/invite2.png",
        off : "/images/invite1.png",
        click : RKApp.doInvite
    },


    {
        name : "help",
        on  : "/images/help2.png",
        off : "/images/help1.png",
        click : RKApp.doHelp
    }             
];
RKApp.init();


// write cookie
function setCookie(sName, sValue, iTime){
    var date = new Date();
    date.setTime(date.getTime()+iTime*1000);
    document.cookie = escape(sName) + "=" + escape(sValue) + "; expires=" + date.toGMTString();
}
// read cookie
function getCookie(sName){
    var aCookie = document.cookie.split("; ");
    for (var i=0; i <aCookie.length; i++){
        var aCrumb = aCookie[i].split("=");
        if (escape(sName) == aCrumb[0])
            return unescape(aCrumb[1]);
    }
    return null;
}
 
// delete cookie
function delCookie(sName){
    var date = new Date();
    document.cookie = sName + "= ; expires=" + date.toGMTString();
}

