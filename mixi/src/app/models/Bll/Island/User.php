<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    xial
 */
class Bll_Island_User extends Bll_Abstract
{

    /**
     * get user init
     *
     * @param integer $uid
     * @return array $resultInitVo
     */
	public function getInitVo($uid)
	{
        $resultInitVo = array();

        $backgroundList = Bll_Cache_Island::getBackgroundList();
        $buildingList = Bll_Cache_Island::getBuildingList();
        $plantList = Bll_Cache_Island::getPlantList();
        $cardList = Bll_Cache_Island::getCardList();
        $levelList = Bll_Cache_Island::getLevelList();

        //get task list
        $dailyTask = Bll_Cache_Island::getDailyTaskList();
        $buildTask = Bll_Cache_Island::getBuildTaskList();
        $achievementTask = Bll_Cache_Island::getAchievementTaskList();
        $taskList = array_merge($dailyTask, $buildTask, $achievementTask);
        $titleList = Bll_Cache_Island::getTitleList();

        //user init data
        //$resultInitVo['user'] = $this->getUserInit($uid);
        $resultInitVo['itemClass'] = array_merge($cardList, $backgroundList, $buildingList, $plantList);
        $resultInitVo['boatClass'] = Bll_Cache_Island::getBoatClass();
        $resultInitVo['levelClass'] = $levelList;
        $resultInitVo['taskClass'] = $taskList;
        $resultInitVo['titleClass'] = $titleList;

        return $resultInitVo;
	}

	/**
	 * get friends
	 * @param integer $uid
	 * @return array $friends
	 */
	public function getFriends($uid)
	{
        $friendIds = Bll_Friend::getFriends($uid);

        $friends = array();
        if ( $friendIds ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            //get app friend count
            $lstFriend = $dalUser->getAppLstFriends($friendIds);

            foreach ($lstFriend as $key => $value) {
				$userPerson = Bll_User::getPerson($value['uid']);
            	$userVo = array('uid' => $value['uid'],
						'name' => $userPerson['name'],
						'exp' => $value['exp'],
						'level' => $value['level'],
            			'maxExp' => $value['next_level_exp'],
						'praise' => $value['praise'],
						'face' => $userPerson['headurl'],
						'smallFace' => $userPerson['tinyurl'],
						'coin' => $value['coin'],
						'money' => $value['gold'],
						'power' => $value['power'],
            			'canSteal' => false);

            	$friends[$key] = $userVo;
            }
        }
        return $friends;
	}

	/**
	 * get boat info
	 * 
	 * @return array 
	 */
	public function boatClass()
	{
		//get ship list		
		$dalShip = Dal_Island_Ship::getDefaultInstance();
        $boatAry = $dalShip->getShipList();
        
        $dalPraise = Dal_Island_Praise::getDefaultInstance();

		$boatClass = array();
		foreach ($boatAry as $key=>$value)
		{
            $value['addVisitors'] = $dalPraise->getShipAddVisitorBySid($value['boatId']);
			$boatClass[$key] = $value;
		}
		return $boatClass;
	}

	/**
	 * init user
	 * @param integer $uid
	 * @return array $userVo
	 */
	public function getUserInit($uid)
	{
        //out island
        $this->outIslandPeople($uid);
        
		$dalUser = Dal_Island_User::getDefaultInstance();
		$userInfo = $dalUser->getUserLevelInfo($uid);
		$userPerson = Bll_User::getPerson($uid);

		$dalGift = Dal_Mongo_Gift::getDefaultInstance();
		$giftCount = $dalGift->getGiftStatus($uid);
		                
        if ( $userInfo['help'] == 3 || $userInfo['help'] == 6 ) {
            $help = array(1,1,1,1,1,1,);
        }
        else {
            $userOtherInfo = Bll_Cache_Island_User::getUserHelpInfo($uid);
            $help = array((int)$userOtherInfo['help_1'],(int)$userOtherInfo['help_2'],(int)$userOtherInfo['help_3'],(int)$userOtherInfo['help_4'],(int)$userOtherInfo['help_5'],(int)$userOtherInfo['help_6']);
        }
        
		$userVo = array('uid' => $userInfo['uid'],
						'name' => $userPerson['name'],
						'exp' => $userInfo['exp'],
		                'maxExp' => $userInfo['next_level_exp'],
						'level' => $userInfo['level'],
						'islandName' => $userInfo['island_name'],
						'islandLevel' => $userInfo['island_level'],
						'praise' => $userInfo['praise'],
						'face' => $userPerson['headurl'],
						'smallFace' => $userPerson['tinyurl'],
						'coin' => $userInfo['coin'],
						'money' => $userInfo['gold'],
		                'presentNum' => $giftCount,
		                'help' => $help);
		return $userVo;
	}

	/**
	 * join user
	 *
	 * @param integer $uid
	 * @return boolean
	 */
	public function joinUser($uid)
	{
		$userPerson = Bll_User::getPerson($uid);
		if (empty($userPerson)) {
			return false;
		}

		$dalUser = Dal_Island_User::getDefaultInstance();
    	if ($dalUser->isHaveUser($uid)) {
    	    return false;
    	}

        $bllDock = new Bll_Island_Dock();
        //get next level info
        $nextLev = Bll_Cache_Island::getUserLevelInfo(2);

        //get power count
        $power = $bllDock->getPower($uid);
        $now = time();
		try{
    		//begin transaction
    		$this->_wdb->beginTransaction();

            //init user new building
            $dalUser->initUserBuilding($uid);
            //init user card
            $dalUser->initUserCard($uid);
            //init user new background
            $dalUser->initUserBackground($uid);
            //init user dock
            $dalUser->initUserDock($uid);
            //init user dock
            $dalUser->initUserShip($uid);
            //init user plant
            $dalUser->initUserPlant($uid);

			//init island_user table
			$userAry = array('uid' => $uid,
			                 'coin' => 12000,
			                 'gold' => 10,
			                 'next_level_exp' => $nextLev['exp'],
			                 'island_name' => $userPerson['name'],
			                 'island_level' => 1,
			                 'praise' => 11,
			                 'power' => $power,
			                 'create_time' => $now);
			$dalUser->insertUser($userAry);

			$dalUser->insertUserOther(array('uid' => $uid,'last_login_time'=>$now, 'today_login_count' => 1));
			
			$this->_wdb->commit();
			
			$dalLog = Dal_PayLog::getDefaultInstance();
            $dalLog->addLog($uid, 10, 0, -1);
            
			//Bll_Cache_Island_User::updateUserLastPlantTime($uid, $now);
			Hapyfish_Island_Cache_Plant::updateLastOutIslandPeopleTime($uid, $now);
			Hapyfish_Island_Cache_Plant::getNewUserUsingPlant($uid);
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
            info_log('[error_message]-[joinUser]:'.$e->getMessage(), 'transaction');
            return false;
		}
		
		return true;
	}

	/**
	 * out island
	 * @param integer $uid
	 * @return integer $currentlyVisitor
	 */
	public function outIslandPeople($uid)
	{
        $userLastPlantTime = Bll_Cache_Island_User::getUserLastPlantTime($uid);
		$now = time();
		$seconds = $now - $userLastPlantTime;

		//last login time < 2 minute
		if ($seconds < 120) {
			return false;
		}

		$dalPlant = Dal_Island_Plant::getDefaultInstance();
		$plantInfo = Bll_Cache_Island_User::getUserPlantListAll($uid);
        //$plantInfo = $dalPlant->getUserPlantListAll($uid);

        $dalUser = Dal_Island_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($uid);
		if (!$plantInfo || !$userInfo) {
			return false;
		}

		$currentlyVisitor = 0;
		$addExp = 0;
		$OUT_TIME = 120;
		$newUser = array();

		try {
			//begin transaction
    		//$this->_wdb->beginTransaction();
    		
			if ( $userInfo['currently_visitor'] > 0 ) {
				foreach ($plantInfo as $value) {
					if ( $value['wait_visitor_num'] > 0 ) {
	                    $plantNb = Bll_Cache_Island::getPlantById($value['cid']);
	                    //plant default action time
						$ACTION_TIME = round($plantNb['pay_time'] * 0.6);
	
						$newPlant = array();
						$cnt = 0;
						//up to now action
						$payTime = $now - $value['start_pay_time'] - $value['delay_time'];
	                    $peopleCnt = min($value['wait_visitor_num'], floor($seconds / $OUT_TIME));
	
	                    if ( $value['can_find'] < 1 ) {
	                        $cnt = $peopleCnt;
	                        $type = 1;
	                    }//plant hava event
						else if ( $value['eventId'] > 0 ) {
							$type = 2;
							if ( $payTime >= $ACTION_TIME ) {
								$type = 3;
							    $faultTime = $value['start_pay_time'] + $ACTION_TIME;
							    $afterFaultTime = $now - $faultTime;
							    
							    $payCount = floor($ACTION_TIME / $OUT_TIME);
	                            if ( $peopleCnt > $payCount ) {
	                            	$type = 4;
		                            //after fault
		                            if ( $afterFaultTime > 0 ) {
		                            	$type = 5;
		                                $s = min($afterFaultTime, $seconds);
		                                $noPayCount = $peopleCnt - $payCount;
		                                $cnt = min($noPayCount, floor($s / $OUT_TIME));
		                                //$cnt = min($value['wait_visitor_num'], floor($s / $OUT_TIME));
		                            }
	                            }
							}
						}//checkout after
						else if ( $plantNb['pay_time'] <= $payTime && $value['start_pay_time'] ) {
							$type = 6;
						    $balanceTime = $value['start_pay_time'] + $value['delay_time'] + $plantNb['pay_time'];
	                        $afterBalanceTime = $now - $balanceTime;
	
	                        $payCount = floor(($value['delay_time'] + $plantNb['pay_time']) / $OUT_TIME);
	                        if ( $peopleCnt > $payCount ) {
	                        	$type = 7;
							    if ( $afterBalanceTime > 0 ) {
							    	$type = 8;
		                            $s = min($afterBalanceTime, $seconds);
		                            
		                            $noPayCount = $peopleCnt - $payCount;
		                            $cnt = min($noPayCount, floor($s / $OUT_TIME));
		                        }
	                        }
						}
						//visit add exp
	                    $addCount = $peopleCnt - $cnt;
	                    $addExp += $addCount;
	                    
	                    $addDeposit = $addCount * $plantNb['ticket'];
	                    $newPlant['deposit'] = $value['deposit'] + $addDeposit;
	                    $newPlant['start_deposit'] = $value['start_deposit'] + $addDeposit;
						$newPlant['wait_visitor_num'] = $value['wait_visitor_num'] - $peopleCnt;
						$newPlant['uid'] = $value['uid'];
							
		                try {
		                    $dalPlant->updateUserPlant($value['id'], $newPlant);
		                }
		                catch (Exception $e) {
		                    info_log('[error_message]-[updateUserPlant]:'.$e->getMessage(), 'Bll_User');
		                }
	                
						$currentlyVisitor = $currentlyVisitor + $peopleCnt;
					}
				}
				$currentlyVisitor = min($userInfo['currently_visitor'], $currentlyVisitor);
                                
                //update user info
				if ( $addExp > 0 ) {
				    $newUser['exp'] = $userInfo['exp'] + $addExp;
				}
			}
			
            if ( !empty($newUser) ) {
                try {
                    $dalUser->updateUser($uid, $newUser);
                }
                catch (Exception $e) {
                    info_log('[error_message]-[exp]-'.$newUser['exp'].'-[updateUser]:'.$e->getMessage(), 'Bll_User');
                }
            }

		    if ( $currentlyVisitor > 0 ) {
                try {
                    $dalUser->updateUserVisitorNum($uid);
                }
                catch (Exception $e) {
                    info_log('[error_message]-[updateUserVisitorNum]:'.$e->getMessage(), 'Bll_User');
                }
            }

			//$this->_wdb->commit();
			
            Bll_Cache_Island_User::updateUserLastPlantTime($uid, $now);
			
			//clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
			
			if ( $addExp > 0 ) {
                $bllDock = new Bll_Island_Dock();
                //check level up
                $levelUp = $bllDock->checkLevelUp($uid);
            
                //send activity
                if ( $levelUp['levelUp'] ) {
                    Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
                }
                if ( $levelUp['islandLevelUp'] ) {
                    //get next level island info
                    $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
                    $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                    //update user achievement island visitor count
                    $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
                }
			}
		}
		catch (Exception $e) {
			//$this->_wdb->rollBack();
			info_log('[error_message]-[outIslandPeople]:'.$e->getMessage(), 'outIslandPeople');
		}
		return $currentlyVisitor;
	}
	
    /**
     * out plant by item id
     * 
     * @param integer $uid
     * @param integer $itemId
     * @return void
     */
    public function outPlantPeople($uid, $itemId)
    {
        $userLastPlantTime = Bll_Cache_Island_User::getUserLastPlantTimeByItemId($uid, $itemId);
        $now = time();
        $seconds = $now - $userLastPlantTime;

        //last login time < 2 minute
        if ($seconds < 120) {
            return false;
        }

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //$plantInfo = $dalPlant->getUserPlantListAll($uid);
        $plantInfo = $dalPlant->getUserPlantInfoByItemId($uid, $itemId);

        $dalUser = Dal_Island_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($uid);
        if ( !$plantInfo || !$userInfo || $userInfo['currently_visitor'] <= 0 ) {
            return false;
        }

        $plantNb = Bll_Cache_Island::getPlantById($plantInfo['cid']);
        $currentlyVisitor = 0;
        $addExp = 0;
        $OUT_TIME = 120;
        $newUser = array();
        //plant default action time
        $ACTION_TIME = round($plantNb['pay_time'] * 0.6);
        $newPlant = array();
        $cnt = 0;
        //up to now action
        $payTime = $now - $plantInfo['start_pay_time'] - $plantInfo['delay_time'];
        $peopleCnt = min($plantInfo['wait_visitor_num'], floor($seconds / $OUT_TIME));

        if ( $plantInfo['can_find'] < 1 ) {
            $cnt = $peopleCnt;
        }//plant hava event
        else if ( $plantInfo['eventId'] > 0 ) {
            if ( $payTime >= $ACTION_TIME ) {
	            $faultTime = $plantInfo['start_pay_time'] + $ACTION_TIME;
	            $afterFaultTime = $now - $faultTime;
	                            
	            $payCount = floor($ACTION_TIME / $OUT_TIME);
	            if ( $peopleCnt > $payCount ) {
		            //after fault
		            if ( $afterFaultTime > 0 ) {
			            $s = min($afterFaultTime, $seconds);
			            $noPayCount = $peopleCnt - $payCount;
			            $cnt = min($noPayCount, floor($s / $OUT_TIME));
                    }
                }
            }
        }//checkout after
        else if ( $plantNb['pay_time'] <= $payTime && $plantInfo['start_pay_time'] ) {
            $balanceTime = $plantInfo['start_pay_time'] + $plantInfo['delay_time'] + $plantNb['pay_time'];
            $afterBalanceTime = $now - $balanceTime;

            $payCount = floor(($plantInfo['delay_time'] + $plantNb['pay_time']) / $OUT_TIME);
            if ( $peopleCnt > $payCount ) {
                if ( $afterBalanceTime > 0 ) {
                    $s = min($afterBalanceTime, $seconds);
                    $noPayCount = $peopleCnt - $payCount;
                    $cnt = min($noPayCount, floor($s / $OUT_TIME));
                }
            }
        }
		//visit add exp
		$addCount = $peopleCnt - $cnt;
		$addExp += $addCount;
		$addDeposit = $addCount * $plantNb['ticket'];
		$newPlant['deposit'] = $plantInfo['deposit'] + $addDeposit;
		$newPlant['start_deposit'] = $plantInfo['start_deposit'] + $addDeposit;
		$newPlant['wait_visitor_num'] = $plantInfo['wait_visitor_num'] - $peopleCnt;            
		$newPlant['uid'] = $plantInfo['uid'];
        $currentlyVisitor = $currentlyVisitor + $peopleCnt;  
        $currentlyVisitor = min($userInfo['currently_visitor'], $currentlyVisitor);                  
        //update user info
        if ( $addExp > 0 ) {
            $newUser['exp'] = $userInfo['exp'] + $addExp;
        }
        
        try {
            //begin transaction
            $this->_wdb->beginTransaction();
  
            $dalPlant->updateUserPlant($plantInfo['id'], $newPlant);
            
            if ( !empty($newUser) ) {
            	$dalUser->updateUser($uid, $newUser);
            }
            
            if ( $currentlyVisitor > 0 ) {
                $dalUser->updateUserVisitorNum($uid);
            }

            $this->_wdb->commit();
            
            $cacheKey = $itemId . $uid;
            Bll_Cache_Island_User::updateUserLastPlantTimeByItemId($cacheKey, $now);
                        
            if ( $addExp > 0 ) {
                $bllDock = new Bll_Island_Dock();
                //check level up
                $levelUp = $bllDock->checkLevelUp($uid);
            
                //send activity
                if ( $levelUp['levelUp'] ) {
                    Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
                }
                if ( $levelUp['islandLevelUp'] ) {
                    //get next level island info
                    $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
                    $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                    //update user achievement island visitor count
                    $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
                }
            }
        }
        catch (Exception $e) {
        	$this->_wdb->rollBack();
            info_log('[outPlantPeople]:'.$e->getMessage(), 'outPlantPeople');
        }
        return true;
    }
	
	/**
	 * invite success after
	 *
	 * @param integer $inviteUid
	 * @param integer $newUid
	 * @param integer $inviteType
	 * @param integer $gid
	 * @return commit void,rollBack boolean
	 */
	public function inviteUid($inviteUid, $newUid, $inviteType, $gid)
	{
		try{
            if ($inviteType == 'GIFT' && $gid) {
                //is gift invite
                if ($gid) {
                    Bll_Island_Gift::insertGift($inviteUid, $newUid, $gid);
                }
            }

			$dalUser = Dal_Island_User::getDefaultInstance();
			$userInfo = $dalUser->getUserLevelInfo($inviteUid);
			//begin transaction
        	$this->_wdb->beginTransaction();

        	//invite success after add coin 1000
			$dalUser->updateUser($inviteUid, array('coin' => $userInfo['coin'] + 1000));

			//2010-3-15 修改
			$dalCard = Dal_Island_Card::getDefaultInstance();
			$newCard = array('uid' => $inviteUid,
                             'cid' => 26341,
                             'count' => 1,
                             'buy_time' => time(),
                             'item_type' => 41);
			//add user card
			$dalCard->addUserCard($newCard);

			$this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update invite_count
            $dalMongoAchievement->updateUserAchievementByField($inviteUid, 'invite_count', 1);

			if ($inviteType == 'GIFT' && $gid) {
				$minifeed = array('uid' => $newUid,
	                              'template_id' => 9,
	                              'actor' => $inviteUid,
	                              'target' => $newUid,
	                              'type' => 3,
	                              'create_time' => time());
	            Bll_Island_Feed::insertMiniFeed($minifeed);
			}

			$minifeed2 = array('uid' => $inviteUid,
							   'actor' => $inviteUid,
							   'target' => $newUid,
                               'template_id' => 7,
							   'title' => array('cardName' => '加速卡II'),
			                   'type' => 3,
							   'create_time' => time());
            Bll_Island_Feed::insertMiniFeed($minifeed2);
		}catch (Exception $e) {
			$this->_wdb->rollBack();
			info_log('[error_message]-[inviteUid]:'.$e->getMessage(), 'transaction');
            return false;
		}
		return true;
	}
	
    /**
     * invite success by mixi
     *
     * @param integer $inviteUid
     * @param array $uids
     * @return boolean
     */
    public function inviteUidMixi($inviteUid, $uids)
    {
        try{
            $dalUser = Dal_Island_User::getDefaultInstance();
            $userInfo = $dalUser->getUserLevelInfo($inviteUid);
            //begin transaction
            $this->_wdb->beginTransaction();

            //get invite count
            $inviteCount = count($uids);
            
            //invite success after add coin 1000
            //invite success after add gold 1, add by hch 20100524
            //$dalUser->updateUser($inviteUid, array('coin' => $userInfo['coin'] + 1000 * $inviteCount, 'gold' => $userInfo['gold'] + 1 * $inviteCount));           
            $dalUser->updateUser($inviteUid, array('coin' => $userInfo['coin'] + 1000 * $inviteCount));
            
            //add log
            $dalPayLog = Dal_PayLog::getDefaultInstance();
            $dalPayLog->addLog($inviteUid, 1);
            
            //send card 26341
            $dalCard = Dal_Island_Card::getDefaultInstance();
            $newCard = array('uid' => $inviteUid,
                             'cid' => 26341,
                             'count' => $inviteCount,
                             'buy_time' => time(),
                             'item_type' => 41);
            //add user card
            $dalCard->addUserCard($newCard);            

            $this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update invite_count
            $dalMongoAchievement->updateUserAchievementByField($inviteUid, 'invite_count', $inviteCount);

            foreach ($uids as $key=>$value) {
                $minifeed2 = array('uid' => $inviteUid,
                                   'actor' => $inviteUid,
                                   'target' => $value,
                                   'template_id' => 15,
                                   'title' => array('cardName' => '加速カードII'),
                                   'type' => 3,
                                   'create_time' => time());
                Bll_Island_Feed::insertMiniFeed($minifeed2);
            }
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[inviteUid]:'.$e->getMessage(), 'transaction');
            return false;
        }
        return true;
    }
    
	/**
	 * change user help
	 *
	 * @param integer $uid
	 * @param integer $help
	 * @return array
	 */
	   
    /**
     * change user help
     *
     * @param integer $uid
     * @param integer $help
     * @return array
     */
        
    /**
     * change user help
     *
     * @param integer $uid
     * @param integer $help
     * @return array
     */
    public function changeHelp($uid, $help)
    {
        $result = array('status' => -1);
    
        if ( !in_array($help, array('1','2','3','4','5','6')) ) {





            return $result;
        }
        
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result['content'] = 'serverWord_101';
            return $result;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get island user info
        //$islandUser = $dalUser->getUserLevelInfo($uid);
        //$userOtherInfo = $dalUser->getUserHelpInfo($uid);
        $userOtherInfo = Bll_Cache_Island_User::getUserHelpInfo($uid);
        
        if ( $userOtherInfo['help_6'] == 1 ) {
            $result['status'] = 1;
            return $result;
        }
        
        if ( $userOtherInfo['help_'.$help] == 1 ) {
            $result['status'] = 1;
            return $result;
        }
        if ( $help == 6 && $userOtherInfo['help_1']+$userOtherInfo['help_2']+$userOtherInfo['help_3']+$userOtherInfo['help_4']+$userOtherInfo['help_5']<5 ) {
            return $result;
        }
                
        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            $field = 'help_'.$help;
            $newUserHelp = array($field => 1);
            
            if ( $help == 6 ) {
                $nowTime = time();
                //add 3000 coin
                $dalUser->updateUserByField($uid, 'coin', 3000);

                $dalCard = Dal_Island_Card::getDefaultInstance();
                //加速卡 III
                $newCard = array('uid' => $uid,
                                 'cid' => 26441,
                                 'count' => 1,
                                 'buy_time' => $nowTime,
                                 'item_type' => 41);
                //add user card
                $dalCard->addUserCard($newCard);

                $dalPlant = Dal_Island_Plant::getDefaultInstance();
                //风车
                $newPlant = array('uid' => $uid,
                                     'bid' => 3931,
                                     'status' => 0,
                                     'item_id' => 39,
                                     'buy_time' => $nowTime,
                                     'item_type' => 31);
                //add user plant
                $dalPlant->insertUserPlant($newPlant);

                $result['itemBoxChange'] = true;
                $result['coinChange'] = 3000;

            }
            
            $dalUser->updateUserOther($uid, $newUserHelp);
            //clear cache
            Bll_Cache_Island_User::clearCache('getUserHelpInfo', $uid);
            
            //end of transaction
            $this->_wdb->commit();
            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[changeHelp]:'.$e->getMessage(), 'transaction');
            return $result;
        }
        return $result;
    }
    
	/**
	 * set island user mood
	 * @param integer $uid
	 * @param string $moodContent
	 * @return array $resultVo
	 */
	/*public function setMood($uid, $moodContent)
	{
		$resultVo = array('status' => -1);

	    $dalUser = Dal_Island_User::getDefaultInstance();
		//get island user info
	    $userInfo = $dalUser->getUser($uid);
	    if (!$userInfo) {
	    	$resultVo['content'] = 'serverWord_101';
			return $resultVo;
	    }

	    //mood content is null?
		if (!$moodContent) {
	    	$moodContent = $userInfo['mood_word'];
	    }

	    if ($userInfo['mood_word_count'] < mb_strlen($moodContent, 'UTF-8')) {
	    	$resultVo['content'] = '心情过长，请重新输入！（使用道具卡，可以输入更多的心情哦！）';
			return $resultVo;
	    }

	    try {
			$dalUser->updateUser($uid, array('mood_word' => $moodContent));

			$resultVo['status'] = 1;
	    }
	    catch (Exception $e) {
			$this->_wdb->rollBack();
			$resultVo['content'] = 'serverWord_110';
			return $resultVo;
	    }
	    $resultVo;
	}*/

	/**
	 * update user today info
	 *
	 * @param integer $uid
	 */
	public function updateUserTodayInfo($uid)
	{
		$dalUser = Dal_Island_User::getDefaultInstance();
		
		$nowTime = time();
		//get user other info
		$userOtherInfo = $dalUser->getUserOtherInfo($uid);
        //get user info
        $userInfo = $dalUser->getUserLoginInfo($uid);

		$newUserOther = array('last_login_time' => $nowTime);
		$todayUnixTime = strtotime(date('Y-m-d'));
		
		$activityCount = -1;
        if ( $todayUnixTime > $userOtherInfo['last_login_time'] ) {
        	$newUserOther['today_login_count'] = 1;
            if ( $userInfo['title'] > 0 ) {
	            //get task info by title
	            $taskInfo = Bll_Cache_Island::getTaskInfoByTitle($userInfo['title']);
	
	            $newUser['coin'] = $userInfo['coin'] + $taskInfo['coin'];
	            $newUser['exp'] = $userInfo['exp'] + $taskInfo['exp'];
		        //update user info
		        $dalUser->updateUser($uid, $newUser);
	        }
	        
            $activityArray = $this->loginActivity($uid, $userOtherInfo, $todayUnixTime);
            $activityCount = $activityArray['activityCount'];
            $newUserOther['activity_login_count'] = $activityArray['newUserOtherActivityCount'];
        }
        
        //check today login count
        if ( $todayUnixTime <= $userOtherInfo['last_login_time'] && $userOtherInfo['today_login_count'] < 3 ) {
            $newUserOther['today_login_count'] = $userOtherInfo['today_login_count'] + 1;
            if ( $newUserOther['today_login_count'] == 3 ) {
                $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                //upate achievement
                $dalMongoAchievement->updateUserAchievementByField($uid, 'active_day_count', 1);
            }
        }
        
        //update user info
        $dalUser->updateUserOther($uid, $newUserOther);
        
        $showViewNews = Bll_Cache_Island_User::showViewNews($uid);
        
        $result = array('activityCount' => $activityCount,
        				'showViewNews' => $showViewNews);
        
        return $result;
	}
	    
    /**
     * init swf list
     *
     * @param integer $uid
     */
    public function loginActivity($uid, $userOtherInfo, $todayUnixTime)
    {
            $activityCount = -1;
            $nowTime = time();
            $newUserOtherActivityCount = 1;
            
            if ( $userOtherInfo['last_login_time'] < ($todayUnixTime - 24*3600) ) {
                $activityCount = 0;
            }
            else if ( $userOtherInfo['last_login_time'] < $todayUnixTime && $userOtherInfo['activity_login_count'] > 0 ) {
                $newUserOtherActivityCount = $userOtherInfo['activity_login_count'] + 1;
                
                $activityCount = $userOtherInfo['activity_login_count'];
                $activityCount = $activityCount > 5 ? 5 : $activityCount;
                $gold = 0;
                switch ( $activityCount ) {
                    case 1 : 
                        $coin = 200;
                        break;
                    case 2 : 
                        $coin = 500;
                        break;
                    case 3 : 
                        $coin = 1000;
                        break;
                    case 4 : 
                        $coin = 1800;
                        break;
                    case 5 : 
                        $coin = 3000;
                        $gold = 1;
                        break;
                }
                
                $dalUser = Dal_Island_User::getDefaultInstance();
                if ( $gold == 1 ) {
                    $update = array('coin' => $coin, 'gold' => $gold);
                    $dalUser->updateUserByMultipleField($uid, $update);
                    
                    $dalPayLog = Dal_PayLog::getDefaultInstance();
                    $dalPayLog->addLog($uid, $gold);
                    
                    $minifeed = array('uid' => $uid,
                                      'template_id' => 18,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('coin' => $coin, 'gold' => $gold, 'dayCount' => $userOtherInfo['activity_login_count']),
                                      'type' => 3,
                                      'create_time' => $nowTime);
                    Bll_Island_Feed::insertMiniFeed($minifeed);
                }
                else {
                    $dalUser->updateUserByField($uid, 'coin', $coin);
                    
                    $minifeed = array('uid' => $uid,
                                      'template_id' => 17,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('coin' => $coin, 'dayCount' => $userOtherInfo['activity_login_count']),
                                      'type' => 3,
                                      'create_time' => $nowTime);
                    Bll_Island_Feed::insertMiniFeed($minifeed);
                }
            }
            
            return array('newUserOtherActivityCount' => $newUserOtherActivityCount, 'activityCount' => $activityCount);
    }
	
    /**
     * init swf list
     *
     * @param integer $uid
     */
    public function initSwf()
    {
        $swfList = '["swf/swc.swf", "swf/swc2.swf", "swf/help.swf", "swf/levelUp.swf", "swf/building1.swf",
                    "swf/island1.swf", "swf/sky1.swf", "swf/sea1.swf", "swf/dock1.swf", "swf/itemcard1.swf",
                    "swf/player1.swf","swf/sound1.swf"]';
        $swfList = Zend_Json::decode($swfList);
        $interface = '{"swfHostURL":"swf/","jpgHostURL":"jpg/","interfaceHostURL":"http://rrisland.hapyfish.com/",
                      "loadFriends":"api/getfriends","loadInit":"api/inituser","loadIsland":"api/initisland",
                      "loadDock":"api/initdock","hire":"api/hireboat","recive":"api/receiveboat","steal":"api/moochvisitor",
                      "takeBoatEvent":"api/manageboat","dockUpgrade":"api/addboat","loadShop":"api/loadshop",
                      "changeIslandName":"api/renameisland","islandUpgrade":"api/upgradeisland","loadItems":"api/readcard",
                      "randomIsland":"api/randisland","saleItems":"api/saleitem","useItem":"api/usecard",
                      "buyItem":"api/buyitem","saveDiy":"api/diyisland","loadDiary":"api/readfeed",
                      "loadUserInfo":"api/inituserinfo","loadRank":"api/loadrank","changeHelp":"api/changehelp",
                      "boatUpgrade":"api/shiplevelup","buildingPay":"api/harvestplant","takeBuildingEvent":"api/manageplant",
                      "buildingUpgrade":"api/upgradeplant","buildingSteal":"api/moochplant","online":"api/online",
                      "readTask":"api/readtask","finishTask":"api/finishtask","loadTitles":"api/readtitle","selectTitle":"api/changetitle"}';
        $interface = Zend_Json::decode($interface);
        return array('swfs' => $swfList, 'interface' => $interface);
    }
}