if (Hapyfish === undefined) {
	var Hapyfish = {};
}
if (Hapyfish.Payment === undefined) {
	Hapyfish.Payment = {};
}

(function() {
    Hapyfish.Payment = {
        lang:{},
        Config:{
            userId:"",
            platformUid:"",
            staticUrl:"",
            payOrderUrl:""
        },

        init:function(lang, config) {
            var that = this;
            that.Config = $.extend({}, that.Config, config);
            that.lang = $.extend({}, that.lang, lang);
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
        
        payOrder:function(type) {
            var that = this;
            var params = {'type':type};
            var url = that.Config.payOrderUrl;
            $.ajax({type:'post',
                url:url,
                data:params,
                dataType:'json',
                success:function(data) {
                    if (data) {
	                    if (data.result) {
	                        that._showKaixinDialog(data.para);
	                    } else {
	                        alert(that.lang.error);
	                    }
                    } else {
                        alert(that.lang.error);
                    }
                },
				error:function() {
					alert(that.lang.error);
				}
            });
        },

        _showKaixinDialog:function(para) {
            if ($("#iframeKaixinDialogy").size() > 0) {
                $("#iframeKaixinDialogy").attr("src", "http://www.kaixin001.com/rest/rest.php?para=" + para + "&num=" + Math.random());
            } else {
                $("body").append('<iframe src="http://www.kaixin001.com/rest/rest.php?para=' + para + '" scrolling="yes" style="display:none" id="iframeKaixinDialogy"></iframe>');
            }
        }

    };
})();
