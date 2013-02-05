<?php

class Hapyfish2_Island_Bll_Act
{
	public static function get($uid = 0)
	{
		$now = time();
		$actState = array();

		if ($uid > 0) {
			//判断新手引导是否完成
			$userHelp = Hapyfish2_Island_Cache_UserHelp::getHelpInfo($uid);
	        if ( $userHelp['completeCount'] == 8 ) {
                /*//团购活动
                $icon = Hapyfish2_Island_Event_Bll_TeamBuy::checkIcon($uid);

                $teamBuy = array('actName' => 'teamBuy',
                                'module' => 'swf/teamBuy.swf?v=2011061301',
                                'btn' => 'teamBuyActBtn',
                                'state' => $icon);
                $actState['teamBuy'] = $teamBuy;*/
	        }
			//check user level info
			
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			
			if ( $userLevelInfo['level'] >= 5 ) {
				$inviteFlowStep = Hapyfish2_Island_Event_Bll_InviteFlow::getInviteStep($uid);
				if ($inviteFlowStep >= 0 && $inviteFlowStep < 4) {
					$yaoQingHaoYou = array(
						'actName' => 'yaoQingHaoYou',
						'btn' => 'yaoQingHaoYouActBtn',
						'index' => 2,
						'module' => 'swf/yaoQingHaoYou.swf?v=2012011901',
						'state' => 0
					);
					$actState['yaoQingHaoYou'] = $yaoQingHaoYou;
				}
			}
					
			
			if ( $userLevelInfo['level'] >= 6 ) {

				//天气feed
				$flashStrom = array('actName' => 'feedflashstorm',
								'module2' => 'swf/feedflashstorm.swf?v=2011062201',
								'state' => 0);
				$actState['feedflashstorm'] = $flashStrom;


				//好友搜索
				$friendserach = array('actName' => 'friendserach',
		        			   		'module2' => 'swf/friendSearch.swf?v=2011123101',
		        			   		'state' => 0);
		    	$actState['friendserach'] = $friendserach;


				//star info ,累计登录送星座
				$starList = array(
					'actName' => 'dailyGetConstellation',
					'btn' => 'dailyGetConstellationActBtn',
					'module' => 'swf/dailyGetConstellation.swf?v=2011123101',
					'index' => 5,
				);
				$actState['dailyGetConstellation'] = $starList;

				//收集任务
				$timekey = 'time';
			    $time =  Hapyfish2_Island_Event_Bll_Hash::getval ($timekey);
				$time = unserialize ($time);
				$switch = Hapyfish2_Island_Event_Bll_Hash::getswitch($uid);

				$state = 1;
				if($switch) {
					if( $now < $time['end'] && $now >$time['start']) {
						$collectkey = 'collectgift_haveget_' . $uid;
						$collectval = Hapyfish2_Island_Event_Bll_Hash::getval($collectkey);

						if(empty($collectval) ) {
							$state = 0;
						} else {
							$state = 1;
						}
					}
				}
				$collectionTask = array ('actName' => "collectionTask",
								    	'btn' => "collectionTaskActBtn",
								    	'module' => "swf/collectionTask.swf?v=2012011801",
								    	'state' => $state);
			    $actState['collectionTask'] = $collectionTask;

			}

			//元旦活动
		    $newDaysTime = mktime(23, 59, 59, 01, 05, 2012);
		    if ($now <= $newDaysTime) {
				$newDays = array ('actName' => "HappyNewYear",
								'module' => "swf/HappyNewYear.swf?v=2011122901",
								'btn' => 'com.hapyfish.hny.HnyActBtn',
								'index' => 0,
								'state' => 0);
		    	$actState['newDays'] = $newDays;
		    }

			//特卖海星
			$starfishAndExternalMall = array(
						'actName' => 'starfishAndExternalMall',
						'btn' => '',
						'index' => 2,
						'module2' => 'swf/starfishAndExternalMall.swf?v=2011123101',
						'state' => 0);
			$actState['starfishAndExternalMall'] = $starfishAndExternalMall;

			//news，海岛新闻
			
			$newsIcon = array(
						'actName' => 'newsIcon',
        		   		'module2' => 'swf/newsIcon.swf',
        		   		'state' => 0);
			$actState['newsIcon'] = $newsIcon;
			
			//岛屿扩建图标
			$islandGuide = array(
							'actName' => 'upgradeIslandGuide',
							'btn' => '',
							'module2' => 'swf/upgradeIslandGuide.swf',
							'state' => 0);
			$actState['upgradeIslandGuide'] = $islandGuide;


    		/*//一元店
			$onegold = array('actName' => 'oneyuanshop',
							'module2' => 'swf/Oneyuanshop.swf?v=2011082202',
							'btn'	=>	'oneyuanBtn',
							'state' => 0);
			$actState['Oneyuanshop'] = $onegold;*/

/*
    		//捕鱼
			$catchFish = array('actName' => 'CatchFish',
								'module2' => 'swf/CatchFish.swf?v=2011101905',
								'module' => 'swf/CatchFishDM.swf?v=2011101905',
								'btn' => 'Moudle1CatchFishBtn',
								'index' => 12,
								'state' => 0);
			$actState['CatchFish'] = $catchFish;
*/
			// 时间性礼物
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$key = 'event_timegift_' . $uid;
			$val = $cache->get($key);

			if( $val && $val['state'] < 6 ) {
				$sixTimesGift = array(	'actName' => 'sixTimesGift',
										'module2' => 'swf/SixTimesGiftMain.swf?v=2011122801',
										'state' => (int)$val['state'] );
				$actState['sixTimesGift'] = $sixTimesGift;
			}

			$levelBigGift = array(	'actName' => 'levelBigGift',
									'btn' => 'LevelBigGiftActBtn',
									'module' => 'swf/levelBigGift.swf?v=2012011201',
									'module2' => 'swf/levelBigGift.swf?v=2012011201');
			$actState['levelBigGift'] = $levelBigGift;
			
			//排行榜
			$rankList = array(
					'actName' => 'rankList',
					'btn' => '',
					'index' => 2,
					'module2' => 'swf/rankingList.swf?v=2012020301',
					'state' => 0,
				);
			$actState['rankList'] = $rankList;
			
			}


		return $actState;
	}

}