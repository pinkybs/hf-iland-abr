if (Hapyfish === undefined) {
	var Hapyfish = {};
}
if (Hapyfish.Kaixin001 === undefined) {
	Hapyfish.Kaixin001 = {};
}

(function() {
    Hapyfish.Kaixin001 = {
        lang:{
        },
        Config:{
            appUrl:"",
            appId:"",
            appKey:"",
            userId:"",
            platformUid:"",
            staticUrl:"",
            shareStreamUrl:"",
            systemNewsUrl:"",
            invitationUrl:"",
            streamUrl:"",
            freegiftLink:"",
            gameMode:""
        },

        init:function(lang, config) {
            var that = this;
            that.Config = $.extend({}, that.Config, config);
            that.lang = $.extend({}, that.lang, lang);
        },

        publishStream:function(option) {
            var that = this;
            var opt = {
                "templateId":0,
                "isShare":false,
                "attachment":{
                    "description":"",
                    "media": [{"src":""}]
                },
                "action":[{"text":""}]
            };
            option = $.extend(true, {}, opt, option);

            var requestUrl = "";
            if (option.isShare) {
                requestUrl = that.Config.shareStreamUrl;
            } else {
                requestUrl = that.Config.streamUrl;
                requestUrl += "?link=" + encodeURI(that.Config.appUrl);
            }
            requestUrl += "&templateId=" + option.templateId;
            requestUrl += "&text=" + encodeURI(option.attachment.description);
            requestUrl += "&linkText=" + encodeURI(option.action[0].text);
            requestUrl += "&pic=" + option.attachment.media[0].src;
            if(option.attachment.media[1]!=null){
                requestUrl += "&pic2=" + option.attachment.media[1].src;
            }
            if(option.attachment.media[2]!=null){
                requestUrl += "&pic3=" + option.attachment.media[2].src;
            }
            requestUrl += "&mode=0";
            $.ajax({
                url:requestUrl,
                success:function(data) {
                    data = eval("(" + data + ")");
                    if (data.result) {
                        that._showKaixinDialog(data.para);
                    } else {
                        alert(Hapyfish.Kaixin001.lang["jsConfig"]["publishStream_erro"]);
                    }
                },
                error:function() {
                    alert(Hapyfish.Kaixin001.lang["jsConfig"]["publishStream_erro"]);
                }
            });


        },
        /*
         * 发送系统消息
         * */
        sendSysNews:function(option) {
            var that = this;
            var opt = {
                "giftId":0, //礼物Id
                "sendflag":1,//发送还是索要
                "attachment":{
                    "description":"描述",
                    "media": [{"src":"","href":""}]
                },
                "action":[{"text":"link","href":""}]
            };
            option = $.extend(true, {}, opt, option);
            //如果是分享类stream，则请求PHP，获取相关链接
            var requestUrl = "";
            requestUrl = that.Config.systemNewsUrl;
            requestUrl += "?link=" + encodeURI(that.Config.appUrl);
            requestUrl += "&templateId=" + option.templateId;
            requestUrl += "&text=" + encodeURI(option.attachment.description);
            requestUrl += "&linkText=" + encodeURI(option.action[0].text);
            requestUrl += "&pic=" + option.attachment.media[0].src;
            requestUrl += "&mode=0";
            requestUrl += "&giftId=" + option.giftId;
            requestUrl += "&sendflag=" + option.sendflag;
            $.ajax({
                url:requestUrl,
                success:function(data) {
                    data = eval("(" + data + ")");
                    if (data.result) {
                        that._showKaixinDialog(data.para);
                    } else {
                        alert(Hapyfish.Kaixin001.lang["jsConfig"]["publishStream_erro"]);
                    }
                },
                error:function() {
                    alert(Hapyfish.Kaixin001.lang["jsConfig"]["publishStream_erro"]);
                }
            });


        },

        sendInvitation:function(option) {
            //站内信形式发送邀请
            //submitInviteForm(option);

            //弹窗或者新窗口形式发送
            var that = this;
            var opt = {
                mode:0,//0为弹出层，1为弹出弹出新窗口
                text:encodeURI("还没有自己的海岛？快来这里经营、装扮你心中的梦想，比比谁最牛！阳光、沙滩，一切都由你来定！")
            };
            option = $.extend({}, opt, option);
            var requestUrl = that.Config.invitationUrl;
            requestUrl += "?mode=" + option.mode;
            requestUrl += "&text=" + option.text;
            $.ajax({
                url:requestUrl,
                success:function(data) {
                    data = eval("(" + data + ")");
                    if (data.result) {
                        that._showKaixinDialog(data.para);

                    } else {
                        alert(Hapyfish.Kaixin001.lang["jsConfig"]["publishStream_erro"]);
                    }
                },
                error:function() {
                    alert(Hapyfish.Kaixin001.lang["jsConfig"]["publishStream_erro"]);
                }
            });


        },
        
        sendFreeGift:function() {
            if (window.top) {
                window.top.location.href = this.Config.freegiftLink;
            } else {
                window.location.href = this.Config.freegiftLink;
            }
        },

        sendRequest:function(option) {
            if (option) {
                option = $.extend(true, this.Config.requestOption, option);
            }
            $("#divFriendSelect").html("<div class='close' onclick='Hapyfish.Kaixin001.closeSendRequest()'></div><iframe style='width:760px;height:600px;' scrolling='no' frameborder='0' src='" + option.sendRequstUrl + "&fId=" + option.friendId + "'></iframe>");
            $("#divFriendSelect").show();

        },
        closeSendRequest:function() {
            $("#divFriendSelect").html("");
            $("#divFriendSelect").hide();
        },

        /*游戏到邀请奖励页面的接口*/
        invitationAward:function(){
           if (window.top) {
                window.top.location.href = this.Config.invitationLink;
            } else {
                window.location.href = this.Config.invitationLink;
            }
        },

        /*隐藏/显示Flash
         @option {
         isShow:false  //是否显示 false :不显示，true,显示 默认不显示
         }
         */
        toggleFlashVisible:function(option) {
            opt = {
                isShow:true
            };
            option = $.extend({}, opt, option);
            var that = this;
            if ($("#flashGame").size() > 0 || $("#flashGameInner").size() > 0) {
                if (that.Config.gameMode.toUpperCase() == "WINDOW" && !option.isShow) {
                    $("#flashGame").addClass("visibility-hide");
                    $("#flashGameInner").addClass("visibility-hide");
                } else {
                    $("#flashGame").removeClass("visibility-hide");
                    $("#flashGameInner").removeClass("visibility-hide");
                }
            }
        },
        
        adjustHeight:function(height) {
            height = height || $("body").outerHeight() + 50;
            if (height < 750) {
                height = 750;
            }
            if ($("#divAdjustIframeHeight").size() == 0) {
                $("body").prepend('<div style="display:none;" id="divAdjustIframeHeight"><iframe src="http://rest.kaixin001.com/api/agent.html#' + height + '" scrolling="yes" height="0px" width="0px"></iframe></div>');
            } else {
                $("#divAdjustIframeHeight").find("iframe").src = "http://rest.kaixin001.com/api/agent.html?num=" + Math.random() + "#" + height;
            }
        },

        ///私有方法，请求开心网，弹出开心网Stream等对话框
        _showKaixinDialog:function(para) {
            if ($("#iframeKaixinDialogy").size() > 0) {
                $("#iframeKaixinDialogy").attr("src", "http://www.kaixin001.com/rest/rest.php?para=" + para + "&num=" + Math.random());
            } else {
                $("body").append('<iframe src="http://www.kaixin001.com/rest/rest.php?para=' + para + '" scrolling="yes" style="display:none" id="iframeKaixinDialogy"></iframe>');
            }
        }

    };
})();
