<?php

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

		$helpInfo = Hapyfish2_Island_Cache_UserHelp::getHelpInfo($uid);
		$help = array($helpInfo[1], $helpInfo[2], $helpInfo[3], $helpInfo[4], $helpInfo[5], $helpInfo[6]);

		$actState = Hapyfish2_Island_Bll_Act::get($uid);

		$userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);

		return array(
			'uid' => $userVO['uid'],
			'name' => $user['name'],
			'exp' => $userVO['exp'],
		    'maxExp' => $userVO['next_level_exp'],
			'level' => $userVO['level'],
			'islandLevel' => $userVO['island_level'],
			'praise' => $userVO['praise'],
			'face' => $user['figureurl'],
			'smallFace' => $user['figureurl'],
			'sitLink' => 'http://www.kaixin001.com/home/?uid=' . $user['puid'],
			'coin' => $userVO['coin'],
			'money' => $userVO['gold'],
		    'presentNum' => 0,
		    'help' => $help,
            'actState' => $actState
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

        if (!in_array($help, array('1','2','3','4','5','6')) ) {
            return $result;
        }

        //get user help info
        $userHelpInfo = Hapyfish2_Island_Cache_UserHelp::getHelpInfo($uid);

        if ($userHelpInfo[6] == 1) {
            $result['status'] = 1;
            return $result;
        }

        if ($userHelpInfo[$help] == 1) {
            $result['status'] = 1;
            return $result;
        }

        $helpTotal = $userHelpInfo[1] + $userHelpInfo[2] + $userHelpInfo[3] + $userHelpInfo[4] + $userHelpInfo[5];

        if ($help == 6 && $helpTotal < 5) {
            return $result;
        }

		$userHelpInfo[$help] = 1;
		Hapyfish2_Island_Cache_UserHelp::updateHelp($uid, $userHelpInfo);
		$result['status'] = 1;

        try {
        	if ($help == 6) {
                //add 3000 coin
				Hapyfish2_Island_HFC_User::incUserCoin($uid, 3000);

                //add card 加速卡III(26441)
                Hapyfish2_Island_HFC_Card::addUserCard($uid, 26441, 1);

                //add plant 风车(3931)
                $newPlant = array(
                	'uid' => $uid,
                    'cid' => 3931,
                    'status' => 0,
                    'item_id' => 39,
                    'buy_time' => time(),
                    'item_type' => 31
                );
                Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);

                $result['itemBoxChange'] = true;
                $result['coinChange'] = 3000;
        	}

        } catch (Exception $e) {
			return $result;
        }

        return $result;
	}

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
		$userLevelInfo = array('level' => $user['level'], 'island_level' => $user['island_level']);
		$nextIslandLevel = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($user['level']);
		if ($user['island_level'] < $nextIslandLevel) {
			$islandLevelUp = true;
			$user['island_level'] += 1;
			$userLevelInfo['island_level'] += 1;
		}

		$ok = Hapyfish2_Island_HFC_User::updateUserLevel($uid, $userLevelInfo);
		if ($ok) {
			$now = time();
			Hapyfish2_Island_Bll_LevelUpLog::add($uid, $userLevel, $user['level']);

			if ($islandLevelUp) {
				//update builing and plant coordinate
				Hapyfish2_Island_HFC_Plant::upgradeCoordinate($uid);
				Hapyfish2_Island_HFC_Building::upgradeCoordinate($uid);
			}
			
			//升级送2颗宝石
			$goldInfo = array('gold' => 2, 'type' => 1, 'time' => $now);
			Hapyfish2_Island_Bll_Gold::add($uid, $goldInfo);

			$gift = Hapyfish2_Island_Cache_BasicInfo::getGiftByUserLevel($user['level']);
			if ($gift) {
				$itemType = substr($gift['cid'], -2);
				$type = substr($gift['cid'], -2, 1);
				$giftName = $gift['name'];

				if ($type == 1) {
					$newBackground = array(
						'uid' => $uid,
						'bgid' => $gift['cid'],
						'item_type' => $itemType,
						'buy_time'=> $now
					);

					Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground);
				} else if ($type == 2) {
					$newBuilding = array(
						'uid' => $uid,
						'cid' => $gift['cid'],
						'item_type' => $itemType,
						'buy_time'=> $now,
						'status'=> 0
					);

					Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);
				} else if ($type == 3) {
					$newPlant = array(
						'uid' => $uid,
						'cid' => $gift['cid'],
						'item_type' => $itemType,
						'buy_time'=> $now,
						'status'=> 0
					);

					Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
				} else if ($type == 4) {
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $gift['cid'], 1);
				}

	            $minifeed = array(
	            	'uid' => $uid,
					'template_id' => 8,
					'actor' => $uid,
					'target' => $uid,
					'title' => array('level' => $user['level'], 'giftName' => $giftName),
					'type' => 3,
					'create_time' => $now
	            );
	            Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
			}
		}

		if ($islandLevelUp) {
			$islandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($user['island_level']);
			if ($islandLevelInfo) {
				$achievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
				$achievement['num_15'] = $islandLevelInfo['max_visitor'];
				Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $achievement);
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
            if ($islandLevelUp) {
            	$result['newIslandLevel'] = $user['island_level'];
            	$result['feed'] = Hapyfish2_Island_Bll_Activity::send('ISLAND_LEVEL_UP', $uid);
        	} else {
        		$result['feed'] = Hapyfish2_Island_Bll_Activity::send('USER_LEVEL_UP', $uid, array('level' => $user['level']));
        	}
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
	public static function updateUserTodayInfo($uid)
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
	            $taskId = 3000 + $userTitleInfo['title'];
	            $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskId);
	            if ($taskInfo) {
	            	Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $taskInfo['exp'], $taskInfo['coin']);
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
		} else if ($loginInfo['last_login_time'] < $todayTime && $loginInfo['active_login_count'] > 0) {
			$activeCount = $loginInfo['active_login_count'];
			$newActiveCount = $activeCount + 1;
			if ($activeCount > 5) {
				$activeCount = 5;
			}

			//连续登陆奖励 todo here
		}

		return array('newActiveCount' => $newActiveCount, 'activeCount' => $activeCount);
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
			$result['content'] = '로그인 일수 15일이 되지 않았어요. 15일을 채운 후 다시 하세요.';
			return $result;
		}
		
		//get user star info
		$starResult = Hapyfish2_Island_Cache_UserStar::getStarInfo($uid);
		$starList = $starResult['starList'];
		if ( $starList[$sid] == 2 ) {
			$result['content'] = '같은 별자리는 또 받으실 수 없어요. 다른 별자리로 선택해 주세요.';
			return $result;
		}
		else if ( $starList[$sid] == 0 ) {
			$result['content'] = '이 별자리는 아직 오픈되지 않았어요. 다른 별자리로 선택해 주세요.';
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
}