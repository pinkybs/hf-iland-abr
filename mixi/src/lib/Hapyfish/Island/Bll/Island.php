<?php

class Hapyfish_Island_Bll_Island
{
    /**
     * load island info
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @return array
     */
    public static function initIsland($ownerUid, $uid)
    {
        //check is app user
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result = array('status' => -1,
                            'content' => 'serverWord_101');
            return $result;
        }
        
        //check is friend
        if ( $ownerUid != $uid ) {
            $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        }
        else {
            $isFriend = false;
        }

    	Hapyfish_Island_Bll_User::outIslandPeople($ownerUid);

    	$dalUser = Dal_Island_User::getDefaultInstance();
        //get owner info
        $islandOwner = $dalUser->getUserForInitIsland($ownerUid);
        if ( !$islandOwner ) {
            return;
        }
        
        //repair user island level
        $islandLevel = Hapyfish_Island_Cache_Island::getIslandLevelInfoByUserLevel($islandOwner['level']);
        if ($islandLevel && isset($islandLevel['level'])) {
        	if (empty($islandOwner['island_level']) || $islandOwner['island_level'] != $islandLevel['level']) {
        		info_log('[UID]: ' . $ownerUid, 'error_island_level');
				$updateUser = array('island_level' => $islandLevel['level']);
				try {
					$dalUser = Dal_Island_User::getDefaultInstance();
					$dalUser->updateUser($ownerUid, $updateUser);
				}catch (Exception $e) {
					
				}
				
				Hapyfish_Island_Cache_User::cleanLevelInfo($ownerUid);
				$islandOwner['island_level'] = $islandLevel['level'];
        	}
        }

        if ( $ownerUid != $uid ) {
            //visit island
            self::visitIsland($ownerUid, $uid);
        }

        //get owner island
        $userBackgroundInfo = Hapyfish_Island_Cache_Background::getUsingBackground($ownerUid);
        
        for ( $j=0,$jCount=count($userBackgroundInfo); $j<$jCount; $j++ ) {
            switch ($userBackgroundInfo[$j]['item_type']) {
                case 11 :
                    $island = $userBackgroundInfo[$j];
                    break;
                case 12 :
                    $sky = $userBackgroundInfo[$j];
                    break;
                case 13 :
                    $sea = $userBackgroundInfo[$j];
                    break;
                case 14 :
                    $dock = $userBackgroundInfo[$j];
                    break;
                default :
                	info_log('uid:'.$ownerUid.'-itemType:',$userBackgroundInfo[$j]['item_type'], 'islandNotice');
                	break;
            }
        }
        
        if ( !isset($island) || !isset($sky) || !isset($sea) || !isset($dock) ) {
        	info_log('uid:'.$ownerUid, 'islandNotice');
        }

        $user = Bll_User::getPerson($ownerUid);

        //get owner buildings info
        $buildings = Hapyfish_Island_Cache_Building::getUsingBuilding($ownerUid);

        //get owner plant info
        $plants = Hapyfish_Island_Cache_Plant::getUserUsingPlant($ownerUid);

        $nowTime = time();
        $itemIdList = array();
        $totalWaitVistor = 0;
        for ( $i=0,$iCount=count($plants); $i<$iCount; $i++ ) {
        	$totalWaitVistor += $plants[$i]['waitVisitorNum'];
            if ( $plants[$i]['waitVisitorNum'] < 1 && $plants[$i]['startDeposit'] < 1 ) {
                $plants[$i]['payRemainder'] = 0;
            }
            else {
                $plants[$i]['payRemainder'] = $plants[$i]['pay_time'] - ($nowTime - $plants[$i]['start_pay_time'] - $plants[$i]['delay_time']);
                $plants[$i]['payRemainder'] = max(0, $plants[$i]['payRemainder']);
            }
        	unset($plants[$i]['pay_time']);
        	unset($plants[$i]['delay_time']);
        	unset($plants[$i]['start_pay_time']);

        	if ( $ownerUid != $uid ) {
            	$itemId = substr($plants[$i]['id'], 0, -3);
            	$itemIdList[] = (string)$itemId;
            	$plants[$i]['itemId'] = $itemId;
        	}
        }

        if ( $ownerUid != $uid ) {
            $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
            $moochArray = $dalMooch->getPlantMoochByIdList($itemIdList);
            for ( $j=0,$jCount=count($plants); $j<$jCount; $j++ ) {
            	$plants[$j]['hasSteal'] = 0;
            	if (isset($moochArray[$plants[$j]['itemId']])) {
            	    $moochUids = $moochArray[$plants[$j]['itemId']];
	                if ( in_array($uid, $moochUids) ) {
	                    $plants[$j]['hasSteal'] = 1;
	                }
            	}
                unset($plants[$j]['itemId']);
            }
        }

        if ( !empty($plants) ) {
        	$buildings = array_merge($buildings, $plants);
        }
        
        $cardStates = array();
        //防御卡
        $defenseTime = 12*3600 - ($nowTime - $islandOwner['defense_card']);
        //保安卡
        $insuranceTime = 6*3600 - ($nowTime - $islandOwner['insurance_card']);
        if ( $defenseTime > 0 ) {
            $cardStates[] = array('cid' => 26841, 'time' => $defenseTime);
        }
        if ( $insuranceTime > 0 ) {
            $cardStates[] = array('cid' => 27141, 'time' => $insuranceTime);
        }

        $currently_visitor = Hapyfish_Island_Cache_Plant::getCurrentlyVisitor($ownerUid);
        //repair total island CurrentlyVistor
        if ($currently_visitor != $totalWaitVistor) {
        	Hapyfish_Island_Cache_Plant::updateCurrentlyVistor($ownerUid, $totalWaitVistor);
        }
        
        $islandVo = array('uid' => $ownerUid,
                          'uname' => $user['name'],
                          'isFriend' => $isFriend,
                          'face' => $user['headurl'],
                          'exp' => $islandOwner['exp'],
        				  'maxExp' => $islandOwner['next_level_exp'],
                          'level' => $islandOwner['level'],
                          'islandName' => $islandOwner['island_name'],
                          'islandLevel' => $islandOwner['island_level'],
                          'island' => $island['bgid'],
                          'sky' => $sky['bgid'],
                          'sea' => $sea['bgid'],
        				  'dock' => $dock['bgid'],
        				  'islandId' => $island['id'],
                          'skyId' => $sky['id'],
                          'seaId' => $sea['id'],
        				  'dockId' => $dock['id'],
                          'praise' => $islandOwner['praise'],
                          'visitorNum' => $currently_visitor,
                          'currentTitle' => $islandOwner['title'],
                          'buildings' => $buildings,
                          'cardStates' => $cardStates);

        $result = array();
        if ( $ownerUid == $uid ) {
            //get user new minifeed count
            $islandVo['newFeedCount'] = Hapyfish_Island_Cache_Counter::getNewMiniFeedCount($uid);

            $dalMongoTitle = Dal_Mongo_Title::getDefaultInstance();
            //get user title list
            $titleList = $dalMongoTitle->getUserTitleList($ownerUid);
            $result['userTitles'] = $titleList;
        }
        
        $dockVo = Hapyfish_Island_Bll_Dock::initDock($ownerUid, $uid);
        
        //get user new remind count
        $islandVo['newRemindCount'] = Hapyfish_Island_Cache_Counter::getNewRemindCount($uid);
        
        //get remind status
        $bllRemind = new Bll_Island_Remind();
        $remindStatus = $bllRemind->getRemindStatus($uid, $ownerUid);
        $islandVo['remindAble1'] = $remindStatus['1'];
        $islandVo['remindAble2'] = $remindStatus['2'];
        $islandVo['remindAble3'] = $remindStatus['3'];
        $islandVo['remindAble4'] = $remindStatus['4'];
        
        $result['islandVo'] = $islandVo;
        $result['dockVo'] = $dockVo;
        return $result;
    }
    
	public static function dumpInitUserIsland($uid)
	{
		$userIsland = self::initIsland($uid, $uid);
		$file = TEMP_DIR . '/inituserisland.' . $uid . '.cache';
		$data = json_encode($userIsland);
		file_put_contents($file, $data);
		return $data;
	}
	
	public static function restoreInitUserIsland($uid)
	{
		$file = TEMP_DIR . '/inituserisland.' . $uid . '.cache';
		if (is_file($file)) {
			return file_get_contents($file);
		} else {
			return self::dumpInitUserIsland($uid);
		}
	}

    /**
     * diy island info
     *
     * @param integer $uid
     * @param array $changesAry
     * @param array $removesAry
     * @return array
     */
    public static function diyIsland($uid, $changesAry, $removesAry)
    {
        $resultVo = array('status' => -1);

        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            $result['resultVo'] = $resultVo;
            return $result;
        }
        
        //check changesAry and removesAry have same item id
        //fixed 2010-09-17
        ///
        if (empty($changesAry) && empty($removesAry)) {
        	/*
        	info_log('[empty]: ' . $uid, 'diyIsland');
            $resultVo['content'] = 'serverWord_101';
            $result['resultVo'] = $resultVo;
            return $result;
			*/
            $resultVo['status'] = 1;
            $resultVo['itemBoxChange'] = true;
            $resultVo['islandChange'] = true;
            $result['resultVo'] = $resultVo;

            //get island info
            $islandVo = self::initIsland($uid, $uid);
            $result['islandVo'] = $islandVo['islandVo'];

            //get user info
            $result['userVo'] = Hapyfish_Island_Bll_User::getUserInit($uid);

            //get user item box info
            $result['items'] = Hapyfish_Island_Bll_Warehouse::loadItems($uid);
            
            $result['dockVo'] = $islandVo['dockVo'];
        	
            return $result;
        }
        
        $tmpAry = array();
        $hasSame = false;
        foreach($changesAry as $item) {
        	$id = $item['id'];
        	if (isset($tmpAry[$id])) {
        		$hasSame = true;
        		break;
        	} else {
        		$tmpAry[$id]  = 1;
        	}
        }
        if ($hasSame) {
        	info_log('[changesAry]: ' . $uid, 'diyIsland');
            $resultVo['content'] = 'serverWord_101';
            $result['resultVo'] = $resultVo;
            return $result;
        }
        $tmpAry = array();
        $hasSame = false;
            foreach($removesAry as $item) {
        	$id = $item['itemId'];
        	if (isset($tmpAry[$id])) {
        		$hasSame = true;
        		break;
        	} else {
        		$tmpAry[$id]  = 1;
        	}
        }
        if ($hasSame) {
        	info_log('[removesAry]: ' . $uid, 'diyIsland');
            $resultVo['content'] = 'serverWord_101';
            $result['resultVo'] = $resultVo;
            return $result;
        }
        ///
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        //$islandUserPraise = $dalUser->getUserPraise($uid);
        $islandUserPraise = Hapyfish_Island_Cache_User::getPraise($uid);

        $praiseChange = 0;
        $buildingChange = 0;
        $backgroundChange = 0;
        $plantChange = 0;
        
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        $userBuildingArray = $dalBuilding->getUserBuildingList($uid);
        
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $userPlantArray = $dalPlant->getUserPlantListForDiy($uid);
        
        try {
            //update changes array
            for( $i=0,$iCount=count($changesAry); $i<$iCount; $i++ ) {
            	$itemType = substr($changesAry[$i]['id'], -2, 1);
                $id = substr($changesAry[$i]['id'], 0, -2);

                if ( $itemType == 2 ) {
                    $id = substr($id, 0, -1);
                    //$userBuilding = $dalBuilding->getUserBuildingById1($id, $uid);
                    $userBuilding = $userBuildingArray[$id];

                    if ( !empty($userBuilding) && $userBuilding['uid'] == $uid ) {
                        $buildingInfo = Hapyfish_Island_Cache_Shop::getBuildingById($userBuilding['bid']);
                        if ( $buildingInfo['add_praise'] > 0 && $userBuilding['status'] != 1 ) {
                            $praiseChange += $buildingInfo['add_praise'];
                        }

    	                //update user building info by building id
    	                $newBuilding = array('x' => $changesAry[$i]['x'],
    	                                     'y' => $changesAry[$i]['y'],
    	                                     'z' => $changesAry[$i]['z'],
    	                                     'mirro' => $changesAry[$i]['mirro'],
    	                                     'can_find' => $changesAry[$i]['canFind'],
    	                                     'status' => 1);
    	                try {
    	                   $dalBuilding->updateUserBuildingById($id, $uid, $newBuilding);
    	                }
    	                catch (Exception $e) {
    	                	info_log('[error_message]-[updateUserBuildingById]:'.$e->getMessage(), 'Bll_Island');
    	                }
    	                
    	                $buildingChange = 1;
                    }
                }
                else if ( $itemType == 3 ) {
                    $id = substr($id, 0, -1);
                    //$userPlant = $dalPlant->getUserPlantById1($id, $uid);
                    $userPlant = $userPlantArray[$id];

                    if ( !empty($userPlant) && $userPlant['uid'] == $uid ) {
                        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['bid']);

                        if ( $plantInfo['add_praise'] > 0 && $userPlant['status'] != 1 ) {
                            $praiseChange += $plantInfo['add_praise'];
                        }

                    	//update user plant info by plant id
    	                $newPlant = array('uid' => $userPlant['uid'],
    	                                  'x' => $changesAry[$i]['x'],
    	                                  'y' => $changesAry[$i]['y'],
    	                                  'z' => $changesAry[$i]['z'],
    	                                  'mirro' => $changesAry[$i]['mirro'],
    	                                  'can_find' => $changesAry[$i]['canFind'],
    	                                  'status' => 1);
                        try {
                            $dalPlant->updateUserPlant($id, $newPlant);
                            
                            Hapyfish_Island_Cache_Plant::casUpdateUserPlantPayInfoById($uid, $id, array('can_find' => $changesAry[$i]['canFind']));
                        }
                        catch (Exception $e) {
                            info_log('[error_message]-[updateUserPlant]:'.$e->getMessage(), 'Bll_Island');
                        }
                        
    	                $plantChange = 1;
                    }
                }
                else if ( $itemType == 1 ) {
                    try {
	                    $type = substr($changesAry[$i]['id'], -2, 2);
	                    $dalBuilding->clearUserBackground($uid, $type);
	
	                    $newBackground = array('status' => 1);
	                    $changeComplete = $dalBuilding->updateUserBackgroundById($id, $uid, $newBackground);
	                    if ( empty($changeComplete) ) {
	                    	switch ( $type ) {
	                    		case 11 :
	                    			$newBgid = 25411;
	                    			break;
                                case 12 :
                                    $newBgid = 23212;
                                    break;
                                case 13 :
                                    $newBgid = 22213;
                                    break;
                                case 14 :
                                    $newBgid = 25914;
                                    break;
	                    	}
	                    	$background = array('uid' => $uid,
		                                        'bgid' => $newBgid,
		                                        'status' => 1,
		                                        'buy_time' => time(),
		                                        'item_type' => $type);
	                    	$dalBuilding->insertUserBackground($background);
	                    	info_log('[emptyBackground]:uid-'.$uid.',bgid-'.$newBgid, 'emptyBackground');
	                    }
                    }
                    catch (Exception $e) {
                        info_log('[error_message]-[clearUserBackground]:'.$e->getMessage(), 'Bll_Island');
                    }
                    
                    $backgroundChange = 1;
                }
            }

            $removeVisitorNum = 0;
            //update removes array
            for( $j=0,$jCount=count($removesAry); $j<$jCount; $j++ ) {
            	$itemType = substr($removesAry[$j]['itemId'], -2, 1);
                $id = substr($removesAry[$j]['itemId'], 0, -2);

                if ( $itemType == 2 ) {
                    $id = substr($id, 0, -1);
                    //$userBuilding = $dalBuilding->getUserBuildingById1($id, $uid);
                    $userBuilding = $userBuildingArray[$id];
                    
                    if ( !empty($userBuilding) && $userBuilding['uid'] == $uid ) {
                        //update user building info by building id
                        $newBuilding = array('status' => 0);
                    
                        try {
                           $dalBuilding->updateUserBuildingById($id, $uid, $newBuilding);
                        }
                        catch (Exception $e) {
                            info_log('[error_message]-[updateUserBuildingById-2]:'.$e->getMessage(), 'Bll_Island');
                        }
                        
                        $buildingInfo = Hapyfish_Island_Cache_Shop::getBuildingById($userBuilding['bid']);
                        if ( $buildingInfo['add_praise'] > 0 ) {
                            $praiseChange -= $buildingInfo['add_praise'];
                        }
                        $buildingChange = 1;
                    }
                }
                else if ( $itemType == 3 ) {
                    $id = substr($id, 0, -1);
                    
                    $userPlant = $userPlantArray[$id];
                    if ( !empty($userPlant) && $userPlant['uid'] == $uid ) {
    	                //update user plant info by building id
    	                Hapyfish_Island_Cache_Plant::removePlantById($uid, $id);

                        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['bid']);
                        if ( $plantInfo['add_praise'] > 0 ) {
                            $praiseChange -= $plantInfo['add_praise'];
                        }
                        $removeVisitorNum += $userPlant['wait_visitor_num'];
                        $plantChange = 1;
                    }
                }
                else if ( $itemType == 1 ) {
                    $newBackground = array('status' => 0);
                
                    try {
                        $dalBuilding->updateUserBackgroundById($id, $uid, $newBackground);
                    }
                    catch (Exception $e) {
                        info_log('[error_message]-[updateUserBackgroundById-2]:'.$e->getMessage(), 'Bll_Island');
                    }
                    
                    $backgroundChange = 1;
                }
            }

            if ( $removeVisitorNum > 0 ) {
            	//update user currently visitor num
            	Hapyfish_Island_Cache_Plant::decCurrentlyVistor($uid, $removeVisitorNum);
            }

            if ( $praiseChange != 0 ) {
                if ( $islandUserPraise + $praiseChange < 0 ) {
                    $praiseChange = -$islandUserPraise;
                }
                
                Hapyfish_Island_Cache_User::updatePraise($uid, $praiseChange);
            }
        
            if ( $buildingChange == 1 ) {
                //clear user building cache
                Hapyfish_Island_Cache_Building::cleanUsingBuilding($uid);
            }
            if ( $plantChange == 1 ) {
                //clear user plant cache
                Hapyfish_Island_Cache_Plant::cleanUserUsingPlantBasicInfo($uid);
            }
            if ( $backgroundChange == 1 ) {
            	//clear user background cache
                Hapyfish_Island_Cache_Background::cleanUsingBackground($uid);
            }

            if ( $praiseChange != 0 ) {
                //update user achievement praise
                $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                $userAchievementPraise = $dalMongoAchievement->getUserAchievementByField($uid, 'num_13');
                if ( $userAchievementPraise < $islandUserPraise + $praiseChange ) {
                    $dalMongoAchievement->updateUserAchievement($uid, array('num_13' => $islandUserPraise + $praiseChange));
                }
            }

            $resultVo['status'] = 1;
            $resultVo['itemBoxChange'] = true;
            $resultVo['islandChange'] = true;
            $result['resultVo'] = $resultVo;

            //get island info
            $islandVo = self::initIsland($uid, $uid);
            $result['islandVo'] = $islandVo['islandVo'];

            //get user info
            $result['userVo'] = Hapyfish_Island_Bll_User::getUserInit($uid);

            //get user item box info
            $result['items'] = Hapyfish_Island_Bll_Warehouse::loadItems($uid);
            
            $result['dockVo'] = $islandVo['dockVo'];
        }
        catch (Exception $e) {
            info_log('[diyIsland]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Island');
            $resultVo['status'] = -1;
            $resultVo['content'] = 'serverWord_110';
            $result['resultVo'] = $resultVo;
            return $result;
        }

        return $result;
    }

    /**
     * visit island
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @return array
     */
    public static function visitIsland($ownerUid, $uid)
    {
        $isTodayVisit = Hapyfish_Island_Cache_Visit::isTodayVisit($uid, $ownerUid);
        if ( !$isTodayVisit ) {
            Hapyfish_Island_Cache_Visit::setTodayVisit($uid, $ownerUid);
        }
    }

}