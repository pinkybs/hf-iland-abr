<?php

require_once(CONFIG_DIR . '/language.php');
class Hapyfish2_Island_Bll_Activity
{
    protected static $template = array
          (
            'USER_JOIN' => array(
            	'id'       		=> 0,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_00,//'阳光？沙滩？美女？帅哥！尽在快乐岛主！赶快加入吧~',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'join.gif'
            ),

            'USER_LEVEL_UP' => array(
            	'id'       		=> 1,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_01,//'{_USER_}的小岛升级了！去游览还能拿到免费礼物哦！快去看看吧！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'user_level_up.gif'
            ),

            'ISLAND_LEVEL_UP' => array(
            	'id'       		=> 2,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_02,//'{_USER_}的小岛在他的努力下又变的更大啦！你们羡慕么~那一起来玩吧！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'island_level_up.gif'
            ),

            'BUILDING_LEVEL_UP' => array(
            	'id'       		=> 3,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_03,//'{_USER_}的建筑又升级了，天啊~~~好漂亮！赶快去凑凑热闹吧！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'building_level_up.gif'
            ),

            'BOAT_LEVEL_UP' => array(
            	'id'       		=> 4,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_04,//'呃~小木筏？豪华游轮？让我们一起游览全世界！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'boat_level_up.gif'
            ),

            'DOCK_EXPANSION' => array(
            	'id'       		=> 5,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_05,//'{_USER_}的船位置增加了，又有机会拉人了~还等什么呢？',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'dock_expand.gif'
            ),

            'BUILDING_MISSION_COMPLETE'  => array(
            	'id'       		=> 6,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_06,//'哇！又一座世界级建筑出现啦！你想知道是什么？去快乐岛主看看吧！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'building_mission_complete.gif'
            ),

            'USER_OBTAIN_TITLE' => array(
            	'id'       		=> 7,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_07,//'{_USER_}居然获得了{*title*}称号，不要嫉妒了，加入快乐岛主一起努力吧！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'get_title.gif'
            ),

            'DAILY_MISSION_COMPLETE' => array(
            	'id'       		=> 8,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_08,//'{_USER_}通过一天的努力，所有日常任务都完成了哦！鼓掌~',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'daily_mission_complete.gif'
            ),

            'GOD_POOR_CARD' => array(
            	'id'       		=> 9,
            	'send'     		=> true,
                'limit'    		=> true,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_09,//'{_USER_}快乐岛主受到了穷神的骚扰，快去用送神卡帮帮他吧！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'god_card.gif'
            ),

            'Dream_Garden_User_Award' => array(
            	'id'       		=> 10,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_10,//'{_USER_}在快乐岛主中领取了限时“惊喜大礼包”价值10RMB，升级更快，更给力“领先”从现在开始！',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'join.gif'
            ),
            'Starfish_word' =>array(
            	'id'			=> 11,
            	'send'			=> true,
            	'limit'			=> false,
            	'text'			=> LANG_PLATFORM_FEED_MSG_11,//'{_USER_}在快乐岛主中兑换了神秘海星礼物哦，你想知道是什么吗？快来看看吧！',
            	'linktext'		=> LANG_PLATFORM_FEED_MSG_19,
            	'image'			=> 'starfish.jpg',
            ),

			'TEAMBUY_FEED' => array(
            	'id'       		=> 12,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_12,
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'teambuy.jpg'
            ),

			'STROM_FEED' => array(
            	'id'       		=> 13,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_13,
                'linktext'		=> LANG_PLATFORM_FEED_MSG_18,
            	'image'    		=> 'flashstrom.jpg'
            ),

            'TAOJISHI_FEED' => array(
				'id'       		=> 14,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_17,
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'taojishifeed.jpg'
            ),
            'QIXI_SHRE' =>array(
            	'id'       		=> 16,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_20,//'阳光！沙滩！尽在快乐岛主！快来加入吧',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'qixi.jpg',
            ),
            'QIXI_WANT' => array(
            	'id'       		=> 17,
            	'send'     		=> true,
                'limit'    		=> false,
            	'text'    		=> LANG_PLATFORM_FEED_MSG_21,//'阳光！沙滩！尽在快乐岛主！快来加入吧',
                'linktext'		=> 'Let\'s Go',
            	'image'    		=> 'qixi.jpg',
            )
        );

    public static function canSend($type, $actor, $time)
    {
        $canSend = true;
    	if (!$time) {
    		$time = time();
    	}

    	$lastSendTime = Hapyfish2_Island_Cache_Activity::getLastSendTime($actor);
    	if ($lastSendTime + 600 > $time) {
    		$canSend = false;
    	}

    	return $canSend;
    }

    public static function setSend($type, $actor, $time)
    {
        if (!$time) {
    		$time = time();
    	}
    	Hapyfish2_Island_Cache_Activity::setLastSendTime($actor, $time);
    }

    public static function send($type, $actor, $data = null, $target = null)
    {
    	if(SEND_ACTIVITY && isset(self::$template[$type])) {
    	    if ($type == 'USER_LEVEL_UP'
	    			|| $type == 'ISLAND_LEVEL_UP'
	    			|| $type == 'BUILDING_LEVEL_UP'
	    			|| $type == 'DOCK_EXPANSION'
	    			|| $type == 'DAILY_MISSION_COMPLETE'
	    			|| $type == 'USER_OBTAIN_TITLE'
	    			|| $type == 'GOD_POOR_CARD'
	    			|| $type == 'Starfish_word'
	    			|| $type == 'STROM_FEED'
	    			|| $type == 'TEAMBUY_FEED'
	    			|| $type == 'TAOJISHI_FEED'
	    			|| $type == 'QIXI_SHRE') {

    			$rowUser = Hapyfish2_Platform_Bll_User::getUser($actor);
    			$data['user'] = $rowUser['name'];
    		}

    		$tpl = self::$template[$type];
    	    $send = $tpl['send'];
    	    $time = time();
            if ($send && $tpl['limit']) {
                $send = self::canSend($type, $actor, $time);
            }

    	    if ($send) {
                $imgUrl = STATIC_HOST . '/apps/island/images/feed/';

                $text = self::buildTemplate($tpl['text'], $data);
                $linktext= $tpl['linktext'];

                $feed = array(
                    'templateId' => $tpl['id'],
                    'img' => $imgUrl . $tpl['image'],
                    'text' => $text,
                    'linktext' => $tpl['linktext']
                );

                if ($tpl['limit']) {
                	self::setSend($type, $actor, $time);
                }

                return json_encode($feed);
            }
    	}

    	return false;
    }

    protected static function buildTemplate($tpl, $json_array)
    {
        if (empty($json_array)) {
        	return $tpl;
        }

    	foreach ($json_array as $k => $v) {
            $keys[] = '{*' . $k . '*}';
            $values[] = $v;
        }

        return str_replace($keys, $values, $tpl);
    }
}