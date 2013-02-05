<?php

class Hapyfish_Island_Bll_User
{
	
	/**
	 * init user
	 * @param integer $uid
	 * @return array $userVo
	 */
	public static function getUserInit($uid)
	{
        //out island
        self::outIslandPeople($uid);
        
		$dalUser = Dal_Island_User::getDefaultInstance();
		$userInfo = $dalUser->getUserLevelInfo($uid);
		$userPerson = Bll_User::getPerson($uid);
		
        $casino = array('actName' => 'zhuanpan', 
        				'btn' => 'zhuanpanActBtn', 
        				'module' => 'swf/v01/zhuanpan.swf',
        				'state' => 0);
        $actState = array('zhuanpan' => $casino); 
        
        $lv = array('actName' => 'lv', 
        				'btn' => 'lvActBtn', 
        				'module' => 'swf/v01/lv.swf',
        				'state' => 0);
        $hasChange = Bll_Cache_Casino_Casino::hasChange($uid);
        if ( $hasChange ) {
        	$lv['state'] = 1;
        }
        $actState['lv'] = $lv;
        
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
		                'presentNum' => 0,
		                'help' => $help,
                        'actState' => $actState);
		
		return $userVo;
	}
	
	/**
	 * join user
	 *
	 * @param integer $uid
	 * @return boolean
	 */
	public static function joinUser($uid)
	{
		$userPerson = Bll_User::getPerson($uid);
		if (empty($userPerson)) {
			return false;
		}

		$dalUser = Dal_Island_User::getDefaultInstance();
    	if ($dalUser->isHaveUser($uid)) {
    	    return false;
    	}

        //get next level info
        $nextLev = Hapyfish_Island_Cache_User::getUserLevelInfoByLevel(2);

        //get power count
        $power = self::getPower($uid);
        $now = time();
        $db = $dalUser->getWriter();
		try{
    		//begin transaction
    		$db->beginTransaction();

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
			                 'coin' => 20000,
			                 'gold' => 10,
			                 'next_level_exp' => $nextLev['exp'],
			                 'island_name' => $userPerson['name'],
			                 'island_level' => 1,
			                 'praise' => 11,
			                 'power' => $power,
			                 'create_time' => $now);
			$dalUser->insertUser($userAry);

			$dalUser->insertUserOther(array('uid' => $uid,'last_login_time'=>$now, 'today_login_count' => 1));
			
			$db->commit();
			
			Hapyfish_Island_Cache_Plant::updateLastOutIslandPeopleTime($uid, $now);
			Hapyfish_Island_Cache_Plant::getNewUserUsingPlant($uid);
		}
		catch (Exception $e) {
			$db->rollBack();
            info_log('[error_message]-[joinUser]:'.$e->getMessage(), 'transaction');
            return false;
		}
		
		return true;
	}
	
    /**
     * get power count
     *
     * @param integer $uid
     * @return integer
     */
    public static function getPower($uid)
    {
        $friendIds = Bll_Friend::getFriends($uid);

        if ( $friendIds ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            //get app friend count
            $friendCount = $dalUser->getAppFriendCount($friendIds);
        }
        else {
            $friendCount = 0;
        }

        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
        //update user achievement friend count
        $userAchievementFriendCount = $dalMongoAchievement->getUserAchievementByField($uid, 'num_16');
        if ( $userAchievementFriendCount < $friendCount ) {
            $dalMongoAchievement->updateUserAchievement($uid, array('num_16' => $friendCount));
        }

        return $friendCount;
    }

	/**
	 * out island
	 * @param integer $uid
	 * @return integer $currentlyVisitor
	 */
	public static function outIslandPeople($uid)
	{
        $lastOutIslandPeopleTime = Hapyfish_Island_Cache_Plant::getLastOutIslandPeopleTime($uid);
		$now = time();
		$seconds = $now - $lastOutIslandPeopleTime;
		
	    //< 2 minutes
        if ($seconds < 120) {
            return false;
        }
		
		$canDo = Hapyfish_Island_Cache_Plant::canOutIslandPeople($uid);
		if (!$canDo) {
			return false;
		}

		$plantInfo  = Hapyfish_Island_Cache_Plant::getUserPlantList($uid);
		if (!$plantInfo) {
			return false;
		}
		
		$currently_visitor = Hapyfish_Island_Cache_Plant::getCurrentlyVisitor($uid);
		if ($currently_visitor < 1) {
			return false;
		}
		
		$currentlyVisitor = 0;
		$addExp = 0;
		$OUT_TIME = 120;

		try {
			$payTimeAndTicketList = Hapyfish_Island_Cache_Plant::getPlantPayTimeAndTicketList();
			
			if (!$payTimeAndTicketList) {
				return false;
			}
			
			foreach ($plantInfo as $value) {
				if ( $value['wait_visitor_num'] > 0 ) {
					$canDoItem = Hapyfish_Island_Cache_Plant::canOutPlantPeopleOfItem($uid, $value['id']);
					if (!$canDoItem) {
						continue;
					}
                    
                    $plantNb = $payTimeAndTicketList[$value['cid']];
                    //plant default action time
					$ACTION_TIME = round($plantNb['pay_time'] * 0.6);
                    
					$newPlant = array();
					$cnt = 0;
					$payTime = $now - $value['start_pay_time'] - $value['delay_time'];
					//up to now action
                    $peopleCnt = min($value['wait_visitor_num'], floor($seconds / $OUT_TIME));

                    if ( $value['can_find'] < 1 ) {
                        $cnt = $peopleCnt;
                        $type = 1;
                    }//plant hava event
					else if ( $value['event'] > 0 ) {
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
                    
                    $value['deposit'] = $value['deposit'] + $addDeposit;
                    $value['start_deposit'] = $value['start_deposit'] + $addDeposit;
                    $value['wait_visitor_num'] = $value['wait_visitor_num'] - $peopleCnt;
                    
                    Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($value['uid'], $value['id'], $value);
                
					$currentlyVisitor = $currentlyVisitor + $peopleCnt;
				}
			}
			$currentlyVisitor = min($currently_visitor, $currentlyVisitor);
                                
            //update user info
			if ( $addExp > 0 ) {
				Hapyfish_Island_Cache_User::incExp($uid, $addExp);
			}

		    if ( $currentlyVisitor > 0 ) {
		    	Hapyfish_Island_Cache_Plant::decCurrentlyVistor($uid, $currentlyVisitor);
            }
			
            Hapyfish_Island_Cache_Plant::updateLastOutIslandPeopleTime($uid, $now);
			
			if ( $addExp > 0 ) {
				try {
	                //check level up
	                $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	
	                //send activity
	                if ( $levelUp['levelUp'] ) {
	                    Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	                }
	                if ( $levelUp['islandLevelUp'] ) {
	                    //get next level island info
	                    $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	                    $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
	                    //update user achievement island visitor count
	                    $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	                }
				}catch (Exception $e) {
					
				}
			}
		}
		catch (Exception $e) {
			info_log('[outIslandPeople]:'.$e->getMessage(), 'outIslandPeople');
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
    public static function outPlantPeople($uid, $itemId)
    {
        $lastOutIslandPeopleTime = Hapyfish_Island_Cache_Plant::getLastOutIslandPeopleTime($uid);
    	$lastOutPlantPeopleTime = Hapyfish_Island_Cache_Plant::getLastOutPlantPeopleTime($uid, $itemId);
    	
    	$outTime = max($lastOutIslandPeopleTime, $lastOutPlantPeopleTime);
        
        $now = time();
        $seconds = $now - $outTime;

        //last login time < 2 minute
        if ($seconds < 120) {
            return false;
        }
    	
    	$canDoItem = Hapyfish_Island_Cache_Plant::canOutPlantPeopleOfItem($uid, $itemId);
		if (!$canDoItem) {
			return false;
		}

		/*
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $plantInfo = $dalPlant->getUserPlantInfoByItemId($uid, $itemId);
		*/
		$plantInfo =  Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($uid, $itemId);
		if (!$plantInfo) {
			return false;
		}
		
		if ($plantInfo['wait_visitor_num'] < 1) {
			return false;
		}

    	$currently_visitor = Hapyfish_Island_Cache_Plant::getCurrentlyVisitor($uid);
		if ($currently_visitor < 1) {
			return false;
		}

        $plantNb = Hapyfish_Island_Cache_Shop::getPlantById($plantInfo['cid']);
        $currentlyVisitor = 0;
        $addExp = 0;
        $OUT_TIME = 120;
        //plant default action time
        $ACTION_TIME = round($plantNb['pay_time'] * 0.6);
        $newPlant = array();
        $cnt = 0;
        //up to now action
        $payTime = $now - $plantInfo['start_pay_time'] - $plantInfo['delay_time'];
        $peopleCnt = min($plantInfo['wait_visitor_num'], floor($seconds / $OUT_TIME));
        
        if ($peopleCnt < 1) {
        	return false;
        }
        
        if ( $plantInfo['can_find'] < 1 ) {
            $cnt = $peopleCnt;
        }//plant hava event
        else if ( $plantInfo['event'] > 0 ) {
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
		
		$plantInfo['deposit'] = $plantInfo['deposit'] + $addDeposit;
		$plantInfo['start_deposit'] = $plantInfo['start_deposit'] + $addDeposit;
		$plantInfo['wait_visitor_num'] = $plantInfo['wait_visitor_num'] - $peopleCnt;
		
		Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($plantInfo['uid'], $plantInfo['id'], $plantInfo);
		
        $currentlyVisitor = $currentlyVisitor + $peopleCnt;  
        $currentlyVisitor = min($currently_visitor, $currentlyVisitor);    
                      
        //update user info
        if ( $addExp > 0 ) {
			Hapyfish_Island_Cache_User::incExp($uid, $addExp);
        }
        
		if ( $currentlyVisitor > 0 ) {
			Hapyfish_Island_Cache_Plant::decCurrentlyVistor($uid, $currentlyVisitor);
		}
        
        Hapyfish_Island_Cache_Plant::updateLastOutPlantPeopleTime($uid, $itemId, $now);
        
        try {              
            if ( $addExp > 0 ) {
                //check level up
                $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            
                //send activity
                if ( $levelUp['levelUp'] ) {
                    Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
                }
                if ( $levelUp['islandLevelUp'] ) {
                    //get next level island info
                    $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
                    $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                    //update user achievement island visitor count
                    $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
                }
            }
        }
        catch (Exception $e) {
            info_log('[outPlantPeople]:'.$e->getMessage(), 'outPlantPeople');
        }
        return true;
    }
    
    /**
     * check level up
     *
     * @param integer $uid
     * @return boolean
     */
    public static function checkLevelUp($uid)
    {
        $levelUp = false;
        $giftName = '';
        $islandLevelUp = false;
        
        $default = array(
        	'levelUp' => $levelUp,
            'islandLevelUp' => $islandLevelUp,
            'giftName' => $giftName
        );

        
        $userExp = Hapyfish_Island_Cache_User::getExp($uid);
        $userLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
        
        if (empty($userExp) || empty($userLevelInfo)) {
        	return $default;
        }

        if ($userExp >= $userLevelInfo['next_level_exp'] ) {
            //get next level info
	        $dalUser = Dal_Island_User::getDefaultInstance();
	        //get user info
	        $islandUser = $dalUser->getUserLevelInfo($uid);
            
        	$newIslandLevel = Hapyfish_Island_Cache_Island::getIslandLevelInfoByUserLevel($islandUser['level'] + 1);
        	$nextLevel = Hapyfish_Island_Cache_User::getUserLevelInfoByLevel($islandUser['level'] + 2);
        	
        	if (empty($newIslandLevel) || empty($nextLevel) || !isset($newIslandLevel['level'])) {
        		return $default;
        	}
        	
        	//send gift
            $levelGift = Bll_Cache_Island::getLevelGift($islandUser['level'] + 1);
            
            $sendGold = $levelGift[0]['gold'] > 0 ? $levelGift[0]['gold'] : 2;
            
        	$levelUp = true;
            //update user info, level up atfer gold + 2
            $newUser = array('level' => $islandUser['level'] + 1,
                             'next_level_exp' => $nextLevel['exp'],
                             'gold' => $islandUser['gold'] + $sendGold);

            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            
            //check island level up
            if ( $islandUser['island_level'] < $newIslandLevel['level'] ) {
                $islandLevelUp = true;
                $newUser['island_level'] = $newIslandLevel['level'];

                //upgrade user building info
                $dalBuilding->upgradeUserBuilding($uid);
                
                //clear plant basic cache info
                Hapyfish_Island_Cache_Plant::cleanUserUsingPlantBasicInfo($uid);
                
                Hapyfish_Island_Cache_Building::cleanUsingBuilding($uid);
            }
            $dalUser->updateUser($uid, $newUser);
            
            //
            Hapyfish_Island_Cache_User::cleanLevelInfo($uid);
            
            //add user gold info
            try {
            	$dalPayLog = Dal_PayLog::getDefaultInstance();
            	$dalPayLog->addLog($uid, $sendGold);
            }catch (Exception $e) {
            	
            }
            
            //send gift
            $levelGift = Bll_Cache_Island::getLevelGift($islandUser['level'] + 1);
            $dalCard = Dal_Island_Card::getDefaultInstance();
        	$dalPlant = Dal_Island_Plant::getDefaultInstance();

            $now = time();
            for ( $i=0,$iCount=count($levelGift); $i<$iCount; $i++ ) {
                $itemType = substr($levelGift[$i]['cid'], -2);
                $type = substr($levelGift[$i]['cid'], -2, 1);

                if ( $i == 0 ) {
                    $giftName = $levelGift[$i]['name'];
                }
                else {
                    $giftName = $giftName . ',' . $levelGift[$i]['name'];
                }

                if ( $type == 1 ) {
                    $newBuilding = array('uid' => $uid,
                                         'bgid' => $levelGift[$i]['cid'],
                                         'status'=>0,
                                         'buy_time'=> $now,
                                         'item_type' => $itemType);
                    //add user Building
                    $dalBuilding->insertUserBackground($newBuilding);
                }
                else if ( $type == 2 ) {
                    $newBuilding = array('uid' => $uid,
                                         'bid' => $levelGift[$i]['cid'],
                                         'status'=> 0,
                                         'buy_time'=> $now,
                                         'item_type' => $itemType);
                    //add user Building
                    $dalBuilding->addUserBuilding($newBuilding);
                }
                else if ( $type == 3 ) {
                	$plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($levelGift[$i]['cid']);
                	
                    $newPlant = array('uid' => $uid,
                                      'bid' => $levelGift[$i]['cid'],
                                      'status'=> 0,
                                      'item_id' => $plantInfo['item_id'],
                                      'buy_time'=> $now,
                                      'item_type' => $itemType);
                    //add user plant
                    $dalPlant->insertUserPlant($newPlant);
                }
                else if ( $type == 4 ) {
                    $newCard = array('uid' => $uid,
                                     'cid' => $levelGift[$i]['cid'],
                                     'count' => 1,
                                     'buy_time' => $now,
                                     'item_type' => $itemType);
                    //add user card
                    $dalCard->addUserCard($newCard);
                }
            }
            
        }

        $result = array('levelUp' => $levelUp,
                        'islandLevelUp' => $islandLevelUp,
                        'giftName' => $giftName);
        
        if ($levelUp) {
        	$result['newLevel'] = $newUser['level'];
        }
        if ($islandLevelUp) {
            $result['newIslandLevel'] = $newUser['island_level'];
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
   public static function changeHelp($uid, $help)
    {
        $result = array('status' => -1);
    
        if ( !in_array($help, array('1','2','3','4','5','6')) ) {
            return $result;
        }
        
        //get island user info
        $userOtherInfo = Bll_Cache_Island_User::getUserHelpInfo($uid);
        
        if ( $userOtherInfo['help_6'] == 1 ) {
            $result['status'] = 1;
            return $result;
        }
        
        if ( $userOtherInfo['help_'.$help] == 1 ) {
            $result['status'] = 1;
            return $result;
        }
        
        $helpTotal = $userOtherInfo['help_1'] + $userOtherInfo['help_2']
        		   + $userOtherInfo['help_3'] + $userOtherInfo['help_4']
        		   + $userOtherInfo['help_5'];
        
        if ( $help == 6 && $helpTotal < 5 ) {
            return $result;
        }
        
        try {
            $field = 'help_' . $help;
            $newUserHelp = array($field => 1);
            
            $dalUser = Dal_Island_User::getDefaultInstance();
            $dalUser->updateUserOther($uid, $newUserHelp);
            //clear cache
            Bll_Cache_Island_User::clearCache('getUserHelpInfo', $uid);
            
            $result['status'] = 1;
        } catch (Exception $e) {
            info_log('[changeHelp]:' . $e->getMessage(), 'Hapyfish_Island_Bll_User');
            return $result;
        }
        
        try {
        	if ( $help == 6 ) {
                //add 3000 coin
                Hapyfish_Island_Cache_User::incCoin($uid, 3000);
                
                $nowTime = time();
                //加速卡III
                $newCard = array(
                	'uid' => $uid,
                    'cid' => 26441,
                    'count' => 1,
                    'buy_time' => $nowTime,
                    'item_type' => 41
                );
                
                //add user card
                $dalCard = Dal_Island_Card::getDefaultInstance();
                $dalCard->addUserCard($newCard);

                //风车
                $newPlant = array(
                	'uid' => $uid,
                    'bid' => 3931,
                    'status' => 0,
                    'item_id' => 39,
                    'buy_time' => $nowTime,
                    'item_type' => 31
                );
                
                //add user plant
                $dalPlant = Dal_Island_Plant::getDefaultInstance();
                $dalPlant->insertUserPlant($newPlant);

                $result['itemBoxChange'] = true;
                $result['coinChange'] = 3000;
        	}
                
        } catch (Exception $e) {
            	info_log('[changeHelp]:' . $e->getMessage(), 'Hapyfish_Island_Bll_User');
            	return $result;
        }
        
        return $result;
    }
    
	/**
	 * update user today info
	 *
	 * @param integer $uid
	 */
	public static function updateUserTodayInfo($uid)
	{
		$nowTime = time();
		$newUserOther = array('last_login_time' => $nowTime);
		$todayUnixTime = strtotime(date('Y-m-d'));
		
		$lastLoginTime = Hapyfish_Island_Cache_Login::getLastLoginTime($uid);
		$todayLoginCount = Hapyfish_Island_Cache_Login::getTodayLoginCount($uid, $todayUnixTime);
		$activityLoginCount = Hapyfish_Island_Cache_Login::getActivityLoginCount($uid);
		$loginInfo = array(
			'last_login_time' => $lastLoginTime,
			'today_login_count' => $todayLoginCount,
			'activity_login_count' => $activityLoginCount
		);
		
		$activityCount = -1;
        if ( $todayUnixTime > $lastLoginTime ) {
        	$newUserOther['today_login_count'] = 1;
        	$userTitle = Hapyfish_Island_Cache_User::getTitle($uid);
            if ( $userTitle > 0 ) {
	            //get task info by title
	            $taskInfo = Hapyfish_Island_Cache_Task::getAchievementTaskInfoByTitle($userTitle);
		        
		        Hapyfish_Island_Cache_User::incCoinAndExp($uid, $taskInfo['coin'], $taskInfo['exp']);
	        }
	        
	        //add user free lv count
        	$dalCasino = Dal_Casino_Casino::getDefaultInstance();
            $dalCasino->updateUserFreeLvCount($uid);
            
            $activityArray = self::loginActivity($uid, $loginInfo, $todayUnixTime, $nowTime);
            $activityCount = $activityArray['activityCount'];
            $newUserOther['activity_login_count'] = $activityArray['newUserOtherActivityCount'];
        }
        
        //check today login count
        if ( $todayUnixTime <= $lastLoginTime && $todayLoginCount < 3 ) {
            $newUserOther['today_login_count'] = $todayLoginCount + 1;
            if ( $newUserOther['today_login_count'] == 3 ) {
                $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                //upate achievement
                $dalMongoAchievement->updateUserAchievementByField($uid, 'active_day_count', 1);
            }
        }
        
        Hapyfish_Island_Cache_Login::updateLoginInfo($uid, $newUserOther, $todayUnixTime);
        
        $showViewNews = Hapyfish_Island_Cache_Login::showViewNews($uid);
        
        $result = array('activityCount' => $activityCount,
        				'showViewNews' => $showViewNews);
        
        return $result;
	}
	
    /**
     * init swf list
     *
     * @param integer $uid
     */
	public static function loginActivity($uid, $loginInfo, $todayUnixTime, $now)
	{
            $activityCount = -1;
            $newUserOtherActivityCount = 1;
            
            if ( $loginInfo['last_login_time'] < ($todayUnixTime - 24*3600) ) {
                $activityCount = 0;
            }
            else if ( $loginInfo['last_login_time'] < $todayUnixTime && $loginInfo['activity_login_count'] > 0 ) {
                $newUserOtherActivityCount = $loginInfo['activity_login_count'] + 1;
                
                $activityCount = $loginInfo['activity_login_count'];
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
                

                if ( $gold > 0 ) {
                	Hapyfish_Island_Cache_User::incCoin($uid, $coin);
                	Hapyfish_Island_Cache_User::incGold($uid, $gold);
	                
	                $dalPayLog = Dal_PayLog::getDefaultInstance();
                    $dalPayLog->addLog($uid, $gold);
                    
	                $minifeed = array('uid' => $uid,
	                                  'template_id' => 18,
	                                  'actor' => $uid,
	                                  'target' => $uid,
	                                  'title' => array('coin' => $coin, 'gold' => $gold, 'dayCount' => $loginInfo['activity_login_count']),
	                                  'type' => 3,
	                                  'create_time' => $now);
	                
	                Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
                } else {
	                Hapyfish_Island_Cache_User::incCoin($uid, $coin);
	                
	                $minifeed = array('uid' => $uid,
	                                  'template_id' => 17,
	                                  'actor' => $uid,
	                                  'target' => $uid,
	                                  'title' => array('coin' => $coin, 'dayCount' => $loginInfo['activity_login_count']),
	                                  'type' => 3,
	                                  'create_time' => $now);
	
	                Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
                }

            }
            
            //每天登陆送1能量  2010 11 15活动结束
            /*Hapyfish_Island_Cache_User::incGold($uid, 1);     
            $dalPayLog = Dal_PayLog::getDefaultInstance();
            $dalPayLog->addLog($uid, 1, 0, -102);*/
            
            return array('newUserOtherActivityCount' => $newUserOtherActivityCount, 'activityCount' => $activityCount);
	}
	
}