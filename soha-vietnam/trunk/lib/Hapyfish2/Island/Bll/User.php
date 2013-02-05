<?php
require_once(CONFIG_DIR . '/language.php');
class Hapyfish2_Island_Bll_User
{
	public static function getUserGold($uid)
	{
		$rest = Qzone_Rest::getInstance();
		$session_key = Hapyfish2_Island_Cache_CustomData::get($uid, 'skey');
		$rest->setUser($uid, $session_key);
		return $rest->getPayBalance();
	}

	public static function getUserInit($uid)
	{
        //owner platform info
        $user = Hapyfish2_Platform_Bll_User::getUser($uid);

		$userHelp = Hapyfish2_Island_Cache_UserHelp::getHelpInfo($uid);
		$helpList = $userHelp['helpList'];
		if ( $userHelp['completeCount'] == 8 ) {
			$help = array();
			$finishOrder = array();
		}
		else {
			$help = array($helpList[1], $helpList[2], $helpList[3], $helpList[4], $helpList[5], $helpList[6], $helpList[7], $helpList[8]);
			$finishOrder = $userHelp['finishOrder'];
		}
		$actState = Hapyfish2_Island_Bll_Act::get($uid);

		$userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);

		/*if ( $userHelp['completeCount'] != 8 ) {
			$userIslandTip = 0;
		}
		else {
			//get user island tip info
			$userIslandTip = Hapyfish2_Island_Cache_User::getIslandTip($uid);
		}*/
		
		return array(
			'uid' => $userVO['uid'],
			'name' => $user['name'],
			'nick' => $user['nick'],
			'exp' => $userVO['exp'],
		    'maxExp' => $userVO['next_level_exp'],
			'level' => $userVO['level'],
			'islandLevel' => $userVO['island_level'],
			'praise' => $userVO['praise'],
			'face' => $user['figureurl'],
			'smallFace' => $user['figureurl'],
			'sitLink' => '',
			'coin' => $userVO['coin'],
			'money' => $userVO['gold'],
		    'presentNum' => 0,
		    'help' => $help,
		    'helpFinishOrderList' => $finishOrder,
            'actState' => $actState,
		    'miniMapIconState' => 0
		);
	}

	public static function readTitle($uid, $ownerUid)
	{
        $userTitle = Hapyfish2_Island_HFC_User::getUserTitle($ownerUid);
        if ($uid != $ownerUid) {
            $result = array('currentTitle' => $userTitle['title']);
        }
        else {
        	$userTitles = array();
        	if (!empty($userTitle['title_list'])) {
        		$tmp = split(',', $userTitle['title_list']);
        		foreach ($tmp as $id) {
        			$userTitles[] = array('title' => $id);
        		}
        	}

            $result = array('userTitles' => $userTitles, 'currentTitle' => $userTitle['title']);
        }
        return $result;
	}

	public static function changeTitle($uid, $titleId)
	{
    	$result = array('status' => -1);

    	try {
	    	$userTitle = Hapyfish2_Island_HFC_User::getUserTitle($uid);
	    	$titleList = $userTitle['title_list'];
	    	$curTitle = $userTitle['title'];

	    	if (empty($titleList)) {
				$result['content'] = 'serverWord_149';
				return $result;
	    	}

	    	if ($titleId == $curTitle) {
				$result['content'] = 'serverWord_149';
				return $result;
	    	}

	    	$list = split(',', $titleList);

	    	if (!in_array($titleId, $list)) {
				$result['content'] = 'serverWord_149';
				return $result;
	    	}

	    	$userTitle['title'] = $titleId;
	    	Hapyfish2_Island_HFC_User::updateUserTitle($uid, $userTitle);

	        $result['status'] = 1;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('result' => $result);
            return $result;
        }

        return $result;
	}

	public static function changehelp($uid, $help)
	{
        $result = array('status' => -1);

        if (!in_array($help, array('1','2','3','4','5','6','7','8')) ) {
            return $result;
        }
        //get user help info
        $userHelp = Hapyfish2_Island_Cache_UserHelp::getHelpInfo($uid);
        $helpList = $userHelp['helpList'];
        $comCount = $userHelp['completeCount'];
        $finishOrder = $userHelp['finishOrder'];

        if ( $comCount >= 8 || $helpList[$help] == 1 ) {
            $result['status'] = 1;
            return $result;
        }
        if ( $comCount < 7 && $help == 8 ) {
            $result['status'] = 1;
            return $result;
        }
        
        //report tutorial log
		$logger = Hapyfish2_Util_Log::getInstance();
		$userInfo = Hapyfish2_Platform_Cache_User::getUser($uid);
		$joinTime = $userInfo['create_time'];
		$gender = $userInfo['gender'];
		$logger->report('tutorial', array($uid, $help, $joinTime, $gender));

		$helpList[$help] = 1;
		$finishOrder[] = (int)$help;
		Hapyfish2_Island_Cache_UserHelp::updateHelp($uid, $helpList, $finishOrder);
		$result['status'] = 1;

        if ( $help == 8 ) {
        	$nowTime = time();
			$bllCompensation = new Hapyfish2_Island_Bll_Compensation();
			$bllCompensation->setCoin(10000);
			$bllCompensation->setGold(5);
			$bllCompensation->setItem(3931, 1);
			$bllCompensation->setItem(16031, 1);
			$bllCompensation->sendOne($uid, '', false);
			
			$result['coinChange'] = 10000;
			$result['goldChange'] = 5;
			$result['itemBoxChange'] = true;
	        $giftPlantInfo1 = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo(3931);
	        $giftPlantInfo2 = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo(16031);
	        $minifeed = array('uid' => $uid,
	                              'template_id' => 32,
	                              'actor' => $uid,
	                              'target' => $uid,
	                              'title' => array('coin' => 10000, 'item' => '5' . LANG_PLATFORM_BASE_TXT_02. ' ' . $giftPlantInfo1['name']. ' ' . $giftPlantInfo2['name']),
	                              'type' => 3,
	                              'create_time' => $nowTime);
	        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
        }

		switch ( $comCount ) {
			case 1 :
				$addExp = 100;
				break;
			case 2 :
				$addExp = 200;
				break;
			case 3 :
				$addExp = 300;
				break;
			case 4 :
				$addExp = 400;
				break;
			case 5 :
				$addExp = 500;
				break;
			case 6 :
				$addExp = 600;
				break;
			default :
				$addExp = 50;
				break;
		}
		Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
		$result['expChange'] = $addExp;

        try {
	        //check level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        } catch (Exception $e) {
        }

        return $result;
	}

	/**
	 * change user help
	 *
	 * @param integer $uid
	 * @param integer $help
	 * @return array
	 */
    /*public static function getHelpGift($uid, $help)
    {
        $result = array('status' => -1);

        if ( !in_array($help, array('1','2','3','4','5','6','7','8')) ) {
            return $result;
        }

        //get user help info
        $userHelpInfo = Hapyfish2_Island_Cache_UserHelp::getHelpInfo($uid);

        if ( $userHelpInfo[$help] != 1 ) {
            $result['status'] = 1;
            return $result;
        }
        $help1 = $userHelpInfo[1] == 2 ? 1 : 0;
        $help2 = $userHelpInfo[2] == 2 ? 1 : 0;
        $help3 = $userHelpInfo[3] == 2 ? 1 : 0;
        $help4 = $userHelpInfo[4] == 2 ? 1 : 0;
        $help5 = $userHelpInfo[5] == 2 ? 1 : 0;
        $help7 = $userHelpInfo[7] == 2 ? 1 : 0;
        $help8 = $userHelpInfo[8] == 2 ? 1 : 0;

        $helpTotal = $help1 + $help2 + $help3 + $help4 + $help5 + $help7 + $help8;

        try {
			$userHelpInfo[$help] = 2;
			Hapyfish2_Island_Cache_UserHelp::updateHelp($uid, $userHelpInfo);
			$result['status'] = 1;

        	$nowTime = time();
        	$helpTotal = $helpTotal + 1;
	        	if ( $helpTotal == 1 ) {
	        		$coin = 50;
	        		$exp = 50;
	        		$itemData = '7821*1';
	        		$itemId = 7821;
	            }
	        	else if ( $helpTotal == 2 ) {
	        		$coin = 80;
	        		$exp = 100;
	        		$itemData = '6521*1';
	        		$itemId = 6521;
	            }
	        	else if ( $helpTotal == 3 ) {
	        		$coin = 110;
	        		$exp = 200;
	        		$itemData = '6721*1';
	        		$itemId = 6721;
	            }
	        	else if ( $helpTotal == 4 ) {
	        		$coin = 140;
	        		$exp = 300;
	        		$itemData = '6621*1';
	        		$itemId = 6621;
	            }
	        	else if ( $helpTotal == 5 ) {
	        		$coin = 170;
	        		$exp = 400;
	        		$itemData = '6921*1';
	        		$itemId = 6921;
	            }
	        	else if ( $helpTotal == 6 ) {
	        		$coin = 200;
	        		$exp = 500;
	        		$itemData = '7021*1';
	        		$itemId = 7021;
	            }
	        	else if ( $helpTotal == 7 ) {
	        		$coin = 230;
	        		$exp = 600;
	        		$itemData = '7421*1';
	        		$itemId = 7421;
	            }
	            //send gift
	            $pid = Hapyfish2_Island_Bll_GiftPackage::getNewPackageId($uid);
	        	$giftInfo = array('to_uid' => $uid,
	        					  'pid' => $pid,
	        					  'gift_type' => 8,
	        					  'coin' => $coin,
	        					  'exp' => $exp,
	        					  'item_data' => $itemData,
	        					  'send_time' => $nowTime);
		        $dalGift = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
		        $dalGift->insert($uid, $giftInfo);
		        $giftId = $pid;

        	    switch ( $help ) {
	        		case 1 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_20;
	        			break;
	        		case 2 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_21;
	        			break;
	        		case 3 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_22;
	        			break;
	        		case 4 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_23;
	        			break;
	        		case 5 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_24;
	        			break;
	        		case 7 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_25;
	        			break;
	        		case 8 :
	        			$feedType = LANG_PLATFORM_BASE_TXT_26;
	        			break;
	        	}
	        	$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($itemId);

	        $minifeed = array('uid' => $uid,
	                              'template_id' => 33,
	                              'actor' => $uid,
	                              'target' => $uid,
	                              'title' => array('feedType' => $feedType, 'coin' => $coin, 'exp' => $exp, 'item' => $buildingInfo['name']),
	                              'type' => 3,
	                              'create_time' => $nowTime);
	        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);

	        $result['status'] = 1;
	        return array('result' => $result, 'packId' => $giftId);

        } catch (Exception $e) {
            info_log('[changeHelp]:' . $e->getMessage(), 'Hapyfish_Island_Bll_User');
            return $result;
        }
    }*/

	public static function checkLevelUp($uid)
	{
        $levelUp = false;
        $giftName = '';
        $islandLevelUp = false;

        $default = array(
        	'levelUp' => $levelUp,
            'islandLevelUp' => $islandLevelUp,
            'giftName' => $giftName,
        	'feed' => null
        );

		$user = Hapyfish2_Island_HFC_User::getUser($uid, array('exp' => 1, 'level' => 1));
		if (!$user) {
			return $default;
		}

		$userLevel = $user['level'];
		$nextLevelExp = Hapyfish2_Island_Cache_BasicInfo::getUserLevelExp($userLevel + 1);
		if (!$nextLevelExp) {
			return $default;
		}

		if ($user['exp'] < $nextLevelExp) {
			return $default;
		}

		$levelUp = true;
		$user['level'] += 1;
		$userLevelInfo = array('level' => $user['level'], 
							   'island_level' => $user['island_level'], 
							   'island_level_2' => $user['island_level_2'],
							   'island_level_3' => $user['island_level_3'],
							   'island_level_4' => $user['island_level_4']);
		
		$ok = Hapyfish2_Island_HFC_User::updateUserLevel($uid, $userLevelInfo);
		if ($ok) {
			$now = time();
			Hapyfish2_Island_Bll_LevelUpLog::add($uid, $userLevel, $user['level']);

			$gift = Hapyfish2_Island_Cache_BasicInfo::getGiftByUserLevel($user['level']);

			if ($gift) {
				if ( $gift['gold'] > 0 ) {
					//升级送宝石
					$goldInfo = array('gold' => $gift['gold'], 'type' => 1, 'time' => $now);
					Hapyfish2_Island_Bll_Gold::add($uid, $goldInfo);
				}
				
				/*$giftName = $gift['name'];
				$itemDataStr = $gift['cid'] . "*" . '1';
				if ( $gift['item_id'] > 0 ) {
					$itemDataStr = $itemDataStr.','.$gift['item_id'].'*1';
					$giftName = $giftName . ' ' .$gift['item_name'];
				}
				$pid = Hapyfish2_Island_Bll_GiftPackage::getNewPackageId($uid);
				$info = array('to_uid' => $uid,
								'pid' => $pid,
								'gift_type' => 1,
								'item_data'	=> $itemDataStr,
								'send_time' => time());
				//insert gift
	        	$dalGift = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
	        	$packId = $dalGift->insert($uid, $info);*/
				
				$giftName = $gift['name'];
				$bllCompensation = new Hapyfish2_Island_Bll_Compensation();
				$bllCompensation->setItem($gift['cid'], 1);
				if ( $gift['item_id'] > 0 ) {
					$bllCompensation->setItem($gift['item_id'], 1);
					$giftName = $giftName . ' ' .$gift['item_name'];
				}
				$bllCompensation->sendOne($uid, '', false);
				
	            $minifeed = array(
	            	'uid' => $uid,
					'template_id' => 8,
					'actor' => $uid,
					'target' => $uid,
	            	'title' => array('level' => $user['level'], 'giftName' => $giftName.' '.$goldInfo['gold'].LANG_PLATFORM_BASE_TXT_02),
					//'title' => array('level' => $user['level'], 'giftName' => $giftName." 2宝石"),
					'type' => 3,
					'create_time' => $now
	            );
	            Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
			}
		}

        $result = array(
        	'levelUp' => $levelUp,
			'islandLevelUp' => $islandLevelUp,
			'giftName' => $giftName,
        	'feed' => null
        );
        if ($levelUp) {
        	$result['newLevel'] = $user['level'];

            //update achievement task,22
	        try {
	        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByFieldData($uid, 'num_22', $user['level']);

				//task id 3050,task type 22
				Hapyfish2_Island_Bll_Task::checkTask($uid, 3050);
	        } catch (Exception $e) {
	        }

        	$result['feed'] = Hapyfish2_Island_Bll_Activity::send('USER_LEVEL_UP', $uid, array('level' => $user['level']));
        }

        return $result;
	}

	/**
	 * join user
	 *
	 * @param integer $uid
	 * @return boolean
	 */
	public static function joinUser($uid)
	{
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		if (empty($user)) {
			return false;
		}

		$step = 0;
		$today = date('Ymd');
		try {
			$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
			$dalUserSequence = Hapyfish2_Island_Dal_UserSequence::getDefaultInstance();
			$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
			$dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
			$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
			$dalCard = Hapyfish2_Island_Dal_Card::getDefaultInstance();
			$dalCardStatus = Hapyfish2_Island_Dal_CardStatus::getDefaultInstance();
			$dalUserIsland = Hapyfish2_Island_Dal_UserIsland::getDefaultInstance();
			$dalAchievement = Hapyfish2_Island_Dal_Achievement::getDefaultInstance();
			$dalAchievementDaily = Hapyfish2_Island_Dal_AchievementDaily::getDefaultInstance();

			$dalUser->init($uid);
			$step++;
			$dalUserSequence->init($uid);
			$step++;
			$dalBackground->init($uid);
			$step++;
			$dalBuilding->init($uid);
			$step++;
			$dalPlant->init($uid);
			$step++;
			$dalDock->init($uid);
			$step++;
			$dalUserIsland->init($uid);
			$step++;
			$dalCard->init($uid);
			$step++;
			$dalCardStatus->init($uid);
			$step++;
			$dalAchievement->init($uid);
			$step++;
			$dalAchievementDaily->init($uid, $today);
			$step++;
		}
		catch (Exception $e) {
			info_log('[' . $step . ']' . $e->getMessage(), 'island.user.init');
            return false;
		}

		Hapyfish2_Island_Cache_User::setAppUser($uid);

		return true;
	}

	/**
	 * update user today info
	 *
	 * @param integer $uid
	 */
	public static function updateUserTodayInfo($uid, $medalArray)
	{
		$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
		if (!$loginInfo) {
			return;
		}

		$lastLoginTime = $loginInfo['last_login_time'];
		$now = time();
		$todayTime = strtotime(date('Y-m-d', $now));

		$activeCount = -1;
        if ($todayTime > $lastLoginTime) {
        	$userTitleInfo = Hapyfish2_Island_HFC_User::getUserTitle($uid);
            if ($userTitleInfo && $userTitleInfo['title'] > 0) {
				$taskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfoByTitle($userTitleInfo['title']);
	            if ($taskInfo) {
	            	Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $taskInfo['exp'], $taskInfo['coin']);
	            	
	            	//捕鱼达人称号加宝石--
	            	if($userTitleInfo['title'] == 100 && $taskInfo['gold'] > 0) {
	            		Hapyfish2_Island_HFC_User::incUserGold($uid, $taskInfo['gold']);
	            		$template_id = 0;
	            		$feedTitle['title'] = '今日<font color="#993300">'.$taskInfo['name'].'</font>称号使你获得<font color="#FF0000">'.$taskInfo['coin'].'金币</font> <font color="#FF0000">'.$taskInfo['exp'].'经验</font> <font color="#FF0000">'.$taskInfo['gold'].'宝石</font>奖励。';
	            	}else {	            	
		            	if ($taskInfo['coin'] > 0) {
		            		if ($taskInfo['exp'] > 0) {
		            			$template_id = 103;
		            			$feedTitle = array('coin' => $taskInfo['coin'], 'exp' => $taskInfo['exp']);
		            		} else {
		            			$template_id = 101;
		            			$feedTitle = array('coin' => $taskInfo['coin']);
		            		}
		            	} else {
		            		$template_id = 102;
		            		$feedTitle = array('exp' => $taskInfo['exp']);
		            	}
		            	$feedTitle['title'] = Hapyfish2_Island_Cache_BasicInfo::getTitleName($userTitleInfo['title']);
	            	}
                	$feed = array(
                		'uid' => $uid,
						'template_id' => $template_id,
						'actor' => $uid,
						'target' => $uid,
						'title' => $feedTitle,
						'type' => 3,
						'create_time' => $now
                	);
					Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
	            }
	        }

            $activeResult = self::loginActivity($uid, $loginInfo, $todayTime, $now);
            $activeCount = $activeResult['activeCount'];
            $loginInfo['active_login_count'] = $activeResult['newActiveCount'];
            if ($loginInfo['active_login_count'] > $loginInfo['max_active_login_count']) {
            	$loginInfo['max_active_login_count'] = $loginInfo['active_login_count'];
            }
            $loginInfo['last_login_time'] = $now;
            $loginInfo['today_login_count'] = 1;

			if ( $loginInfo['all_login_count'] < 8 ) {
            	$loginInfo['all_login_count'] += 1;
            }

			if ( $loginInfo['star_login_count'] < 15 ) {
            	$loginInfo['star_login_count'] += 1;
            }

            Hapyfish2_Island_HFC_User::updateUserLoginInfo($uid, $loginInfo, true);

            //add log
			$logger = Hapyfish2_Util_Log::getInstance();
			$userInfo = Hapyfish2_Platform_Cache_User::getUser($uid);
			$joinTime = $userInfo['create_time'];
			$gender = $userInfo['gender'];
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$userLevel = $userLevelInfo['level'];
			$logger->report('101', array($uid, $joinTime, $gender, $userLevel));
			Hapyfish2_Island_Bll_SearchFriend::addToFriendSearch($uid);
			
			if(time() <= 1319385599 ){
	            $gained = Hapyfish2_Island_Bll_CompensationEvent::isGained($uid, 3);
	            if (!$gained) {
	            	Hapyfish2_Island_Bll_CompensationEvent::gain($uid, 3);
	            }
            }
            
			if($now <= 1313596799){
				$ok =  Hapyfish2_Island_Event_Bll_Valentine::incRose($uid, 7, 1);
	            if($ok){
	            	$feed = array(
	                		'uid' => $uid,
							'template_id' => 0,
							'actor' => $uid,
							'target' => $uid,
							'title' => array('title' => '每日登陆获得7根鹊羽'),
							'type' => 3,
							'create_time' => $now
	                	);
					Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
	            }        
			}
           
			$ids = array(0, 1, 2);

			foreach ($ids as $id) {
				$key = 'i:u:flashstrom' . $uid . $id;
				$cache = Hapyfish2_Cache_Factory::getMC($uid);
				$cache->set($key, -1);
			}
			/*
			$taskId = 0;
			//check rank is top ten
			if ( $medalArray[0] == 1 || $medalArray[1] == 1 || $medalArray[2] == 1 || $medalArray[3] == 1 ) {
				$taskId = 3092;
				$numField = 'num_38';
			}
			else if ( $medalArray[0] == 2 || $medalArray[1] == 2 || $medalArray[2] == 2 || $medalArray[3] == 2 ) {
				$taskId = 3093;
				$numField = 'num_39';
			}
			else if ( $medalArray[0] == 3 || $medalArray[1] == 3 || $medalArray[2] == 3 || $medalArray[3] == 3 ) {
				$taskId = 3094;
				$numField = 'num_40';
			}
			else if ( $medalArray[0] == 4 || $medalArray[1] == 4 || $medalArray[2] == 4 || $medalArray[3] == 4 ) {
				$taskId = 3095;
				$numField = 'num_41';
			}
        	if ( $taskId > 0 ) {
		        //update achievement task,3092
		        try {
		        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, $numField, 1);
					//task id $taskId,task type $numField
					Hapyfish2_Island_Bll_Task::checkTask($uid, $taskId);
		        } catch (Exception $e) {
		        }
        	}*/
        } else {
        	$loginInfo['last_login_time'] = $now;
        	$loginInfo['today_login_count'] += 1;
        	Hapyfish2_Island_HFC_User::updateUserLoginInfo($uid, $loginInfo);
        }

        $showViewNews = Hapyfish2_Island_Cache_User::showEZine($uid, $todayTime);
        return array('activeCount' => $activeCount, 'showViewNews' => $showViewNews);
	}

    /**
     * init swf list
     *
     * @param integer $uid
     */
	public static function loginActivity($uid, $loginInfo, $todayTime, $now)
	{
		$activeCount = -1;
		$newActiveCount = 1;

		if ($loginInfo['last_login_time'] + 24*3600 < $todayTime) {
			$interval = Hapyfish2_Island_Cache_BasicInfo::getActLoginInterval();
			if ($interval > 0) {
				if ($loginInfo['last_login_time'] + 24*3600 + $interval > $todayTime) {
					$activeCount = $loginInfo['active_login_count'];
					$newActiveCount = $activeCount + 1;
				} else {
					$activeCount = 0;
				}
			} else {
				$activeCount = 0;
			}

			// 每天免费发送3张抽奖卡
			$userCards = Hapyfish2_Island_HFC_Card::getUserCard($uid);
			// update by hudanfeng 
			/*
			if (isset($userCards['55041'])) {
				$userCards['55041']['count']=0;
				Hapyfish2_Island_HFC_Card::updateUserCard($uid, $userCards, true);
			}
			Hapyfish2_Island_HFC_Card::addUserCard($uid, '55041', 3);
			*/

		} else if ($loginInfo['last_login_time'] < $todayTime && $loginInfo['active_login_count'] > 0) {
			$activeCount = $loginInfo['active_login_count'];
			$newActiveCount = $activeCount + 1;
			if ($activeCount > 5) {
				$activeCount = 5;
			}

			//连续登陆奖励 todo here

			// 每天免费发送3张抽奖卡
			$userCards = Hapyfish2_Island_HFC_Card::getUserCard($uid);
			// update by hudanfeng 
			/*
			if (isset($userCards['55041'])) {
				$userCards['55041']['count']=0;
				Hapyfish2_Island_HFC_Card::updateUserCard($uid, $userCards, true);
			}
			Hapyfish2_Island_HFC_Card::addUserCard($uid, '55041', 3);
			*/

		}

		return array('newActiveCount' => $newActiveCount, 'activeCount' => $activeCount);
	}

    /**
     * save photo
     *
     * @param integer $uid
     */
	public static function savePhoto($uid)
	{
		$result = array('status');
        //update achievement task,27
        try {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_27', 1);
			//task id 3071,task type 27
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3071);
			if ( $checkTask['status'] == 1 ) {
				$result['finishTaskId'] = $checkTask['finishTaskId'];
			}
			$result['status'] = 1;
        } catch (Exception $e) {
        }
        return $result;
	}

	/**
     * get star gift
     *
     * @param integer $uid
     * @param integer $sid
     */
	public static function getStarGift($uid, $sid)
	{
		$result = array('status' => -1);
		//1-摩羯,2-水瓶,3-双鱼,4-白羊,5-金牛,6-双子,7-巨蟹,8-狮子,9-处女,10-天秤,11-天蝎,12-射手
		//get user login info, star_login_count
		$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
		$starDays = $loginInfo['star_login_count'];

		if ( $starDays < 15 ) {
			$result['content'] = '您的累计登录时间不足15天，请先满足了再来吧。';
			return $result;
		}

		//get user star info
		$starResult = Hapyfish2_Island_Cache_UserStar::getStarInfo($uid);
		$starList = $starResult['starList'];
		if ( $starList[$sid] == 2 ) {
			$result['content'] = '对不起，不能重复领取星座奖励哦，请换一个再来吧。';
			return $result;
		}
		else if ( $starList[$sid] == 0 ) {
			$result['content'] = '对不起，这个星座还没有开通，请换一个再来吧。';
			return $result;
		}

		$starDb = $starResult['starDb'];
		$starDb[$sid] = 1;
		//update user star info
		Hapyfish2_Island_Cache_UserStar::updateStar($uid, $starDb);

		$starPlant = array('1' => 74632, '2' => 74732, '3' => 75532, '4' => 80432, '5' => 85132, '6' => 85232,
						   '7' => 85332, '8' => 85432, '9' => 85532, '10' => 85632, '11' => 85732, '12' => 85832);

		$plantId = $starPlant[$sid];
        $itemId = substr($plantId, -2, 2);
		$newPlant = array(
			'uid' => $uid,
			'cid' => $plantId,
			'item_id' => $itemId,
			'x' => 0,
			'y' => 0,
			'z' => 0,
			'mirro' => 0,
			'can_find' => 0,
			'level' => 5,
			'status' => 0,
			'buy_time' => time(),
			'item_type' => 32
		);
		Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);

		$starInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($plantId);

		$feed = array('uid' => $uid,
					'template_id' => 107,
					'actor' => $uid,
					'target' => $uid,
					'title' => array('name' => $starInfo['name']),
					'type' => 3,
					'create_time' => time());
		Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);

		//update user login info
		$loginInfo['star_login_count'] = 0;
		Hapyfish2_Island_HFC_User::updateUserLoginInfo($uid, $loginInfo, true);

		$result['status'] = 1;

		return $result;
	}

    /**
     * read star gift
     *
     * @param integer $uid
     */
	public static function readStarGift($uid)
	{
		//get user login info, star_login_count
		$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
		$starDays = $loginInfo['star_login_count'];

		//get user star info
		$starResult = Hapyfish2_Island_Cache_UserStar::getStarInfo($uid);
		$starList = $starResult['starList'];
		$starInfo = array($starList[1], $starList[2], $starList[3], $starList[4], $starList[5], $starList[6],
						  $starList[7], $starList[8], $starList[9], $starList[10], $starList[11], $starList[12]);

		$result = array('days' => $starDays, 'list' => $starInfo);
		return $result;
	}

	/**
	 * upgrade island
	 * @param islandId, upgrade island id
	 * @param islandLevel, user current island level
	 * 
	 */	
	public static function upgradeIsland($uid, $islandId, $islandLevel)
	{
     	$result = array('status' => -1);
     	//check island id
     	if ( !in_array($islandId, array(1,2,3,4)) ) {
            return $result;
     	}
     	
        //get user info
        $user = Hapyfish2_Island_HFC_User::getUserVO($uid);
        if ( $islandId == 2 ) {
        	if ( $user['desertIslandState'] != 1 ) {
            	return $result;
        	}
        }
        else if ( $islandId == 3 ) {
        	if ( $user['hawaiiIslandState'] != 1 ) {
            	return $result;
        	}
        }
        else if ( $islandId == 4 ) {
        	if ( $user['iceLandState'] != 1 ) {
            	return $result;
        	}
        }
        //get user level info
		$userLevelInfo = array('level' => $user['level'], 
							   'island_level' => $user['island_level'], 
							   'island_level_2' => $user['island_level_2'],
							   'island_level_3' => $user['island_level_3'],
							   'island_level_4' => $user['island_level_4']);
		//check island level field
		if ( $islandId == 1 ) {
			$islandLevelField = 'island_level';
		}
		else {
			$islandLevelField = 'island_level_' . $islandId;
		}
		
		if ( $islandLevel != $user[$islandLevelField] ) {
			return $result;
		}
		if ( $islandLevel >= 14 ) {
			$result['content'] = '您的岛屿已经最大了哦。';
			return $result;
		}
		
		//get max island level
		$maxIslandLevel = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($user['level'], $islandId);
		
		//get next island level by user level
		$nextIslandLevel = $user[$islandLevelField] + 1;
		$nextIslandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($nextIslandLevel);
		
		//check level,price is free
		if ($nextIslandLevel <= $maxIslandLevel) {
			$priceType = 1;
		}
		else {
			$priceType = 0;
		}
		
		$user[$islandLevelField] = $nextIslandLevel;
		$userLevelInfo[$islandLevelField] += 1;
		
		//pricetype,1:coin,2:gold
		if ( $priceType == 1 ) {
			/*if ($user['coin'] < $nextIslandLevelInfo['coin']) {
				$result['content'] = 'serverWord_137';
				return $result;
			}*/
			
			//update user level info
			$ok = Hapyfish2_Island_HFC_User::updateUserLevel($uid, $userLevelInfo);
			if ($ok) {
				//update builing and plant coordinate
				Hapyfish2_Island_HFC_Plant::upgradeCoordinate($uid, $islandId);
				Hapyfish2_Island_HFC_Building::upgradeCoordinate($uid, $islandId);
				
				/*$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $nextIslandLevelInfo['coin']);
				
				if ($ok2) {
					Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $nextIslandLevelInfo['coin'], LANG_PLATFORM_BASE_TXT_35, time());			
				}*/
			}
			$result['coinChange'] = 0;
		}
		else {
	        $balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
	        if (!$balanceInfo) {
	        	$result['content'] = 'serverWord_1002';
	        	return $result;
	        }
	        
			$userGold = $balanceInfo['balance'];
			if ($userGold < $nextIslandLevelInfo['gold']) {
				$result['content'] = 'serverWord_140';
				return $result;
			}
			
			$isVip = $balanceInfo['is_vip'];
			$userLevel = $user['level'];
			
			//update user level info
			$ok = Hapyfish2_Island_HFC_User::updateUserLevel($uid, $userLevelInfo);
			if ($ok) {
				//update builing and plant coordinate
				Hapyfish2_Island_HFC_Plant::upgradeCoordinate($uid, $islandId);
				Hapyfish2_Island_HFC_Building::upgradeCoordinate($uid, $islandId);
				
				$goldInfo = array(
	        		'uid' => $uid,
	        		'cost' => $nextIslandLevelInfo['gold'],
	        		'summary' => LANG_PLATFORM_BASE_TXT_35,
	        		'user_level' => $userLevel,
	        		'cid' => $islandLevel.$islandId,
	        		'num' => 1
	        	);
	        	$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
	        	if ($ok2) {
					//report log
					$logger = Hapyfish2_Util_Log::getInstance();
					$logger->report('3001', array($uid, $islandLevel, $islandId, $goldInfo['cost']));
	        	}
			}
			$result['goldChange'] = -$nextIslandLevelInfo['gold'];
		}
		
		//check user achievement,15
		$islandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($user[$islandLevelField]);
		if ($islandLevelInfo) {
			$achievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
			if ( $achievement['num_15'] < $islandLevelInfo['max_visitor'] ) {
				$achievement['num_15'] = $islandLevelInfo['max_visitor'];
				Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $achievement);
		        try {
					//task id 3007,task type 15
					Hapyfish2_Island_Bll_Task::checkTask($uid, 3007);
		        } catch (Exception $e) {
		        }
			}
		}

        $result['status'] = 1;
        $result['islandLevelUp'] = true;
        $result['feed'] = Hapyfish2_Island_Bll_Activity::send('ISLAND_LEVEL_UP', $uid);
        
        return $result;
	}
	
	/**
	 * get user next big gift level
	 * @param uid
	 * 
	 */	
	public static function getUserNextBigGiftLevel($uid)
	{
		$resule = array('status' => 1);
		
     	//get user next big gift level
     	$userNextBigGiftLevel = Hapyfish2_Island_Cache_User::getUserNextBigGiftLevel($uid);
     	
     	return array('resultVo' => $resule, 'level' => $userNextBigGiftLevel);
	}
	
	/**
	 * get level big gift
	 * @param uid
	 * 
	 */
	public static function getLevelBigGift($uid)
	{
     	$result = array('status' => -1);
     	
     	//get user next big gift level
     	$userNextBigGiftLevel = Hapyfish2_Island_Cache_User::getUserNextBigGiftLevel($uid);
     	
     	//get user level info
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        
		//get level gift
		$levelGift = Hapyfish2_Island_Cache_BasicInfo::getStepGiftByUserLevel($userNextBigGiftLevel);
		
		//check user level
        if ( $userLevelInfo['level'] < $userNextBigGiftLevel ) {
        	return array('resultVo' => $result);
        }
		//set gift
		$bllCompensation = new Hapyfish2_Island_Bll_Compensation();
		$bllCompensation->setCoin($levelGift['coin']);
		$bllCompensation->setGold($levelGift['gold']);
		$bllCompensation->setStarFish($levelGift['star']);
		
		$itemId = explode(",", $levelGift['item_id']);
        $itemNum = explode(",", $levelGift['item_num']);
        for($i = 0; $i < sizeof($itemId); $i++) {
			$bllCompensation->setItem($itemId[$i], $itemNum[$i]);
        }
		$bllCompensation->sendOne($uid, '');
		
		//update user next big gift level
		Hapyfish2_Island_Cache_User::updateUserNextBigGiftLevel($uid, $userNextBigGiftLevel+5);
        
		//insert minifeed
		$gift = "";
		if( !empty($itemId) ) {
			for ( $j = 0,$jCount = count($itemId); $j<$jCount; $j++ ) {
				$type = $itemId[$j];
				//get gift name
				$giftType = substr($type, -2, 1);

				switch( $giftType ) {
					case 1:
						$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($type);
					break;
					case 2:
						$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($type);
					break;
					case 3:
						$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($type);
					break;
					case 4:
						$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($type);
					break;
				}

				$gift .= $itemNum[$j].$giftInfo['name'].' ';
			}
		}
		
		if($levelGift['gold']) {
			$template_id = 108;
			$title = array('level' => $userLevelInfo['level'],
							'coin' => $levelGift['coin'],
							'gold' => $levelGift['gold'],
							'gift' => $gift,
							'star' => $levelGift['star']);
		} else {
			$template_id = 104;
			$title = array('level' => $userLevelInfo['level'],
							'coin' => $levelGift['coin'],
							'gift' => $gift,
							'star' => $levelGift['star']);
		}

		$minifeed = array(
						'uid' => $uid,
                        'template_id' => $template_id,
                        'actor' => $uid,
                        'target' => $uid,
                        'title' => $title,
                        'type' => 3,
                        'create_time' => time()
					);

        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		
		$result['status'] = 1;
        $result['coinChange'] = $levelGift['coin'];
        $result['goldChange'] = $levelGift['gold'];
        $result['itemBoxChange'] = true;
        
        return array('resultVo' => $result, 'level' => $userNextBigGiftLevel+5);
	}
	
}