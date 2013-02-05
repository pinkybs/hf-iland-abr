<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Bll_Island_Island extends Bll_Abstract
{
    /**
     * load island info
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @return array
     */
    public function initIsland($ownerUid, $uid)
    {
        $dalUser = Dal_Island_User::getDefaultInstance();

        //check is app user
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result = array('status' => -1,
                            'content' => 'serverWord_101');
            return $result;
        }

		$blluser = new Bll_Island_User();
    	$blluser->outIslandPeople($ownerUid);

        //get owner info
        $islandOwner = $dalUser->getUserForInitIsland($ownerUid);
        if ( !$islandOwner ) {
            return;
        }

        if ( $ownerUid != $uid ) {
            //visit island
            $this->visitIsland($ownerUid, $uid);
        }

        //get owner island
        $userBackgroundInfo = Bll_Cache_Island_User::getUsingBackground($ownerUid);
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

        //check is friend
        if ( $ownerUid != $uid ) {
            $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        }
        else {
            $isFriend = false;
        }

        //get owner buildings info
        $buildings = Bll_Cache_Island_User::getUsingBuilding($ownerUid);

        //get owner plant info
        $plants = Bll_Cache_Island_User::getUsingPlant($ownerUid);

        $nowTime = time();
        $itemIdList = array();
        for ( $i=0,$iCount=count($plants); $i<$iCount; $i++ ) {
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
                          'visitorNum' => $islandOwner['currently_visitor'],
                          'currentTitle' => $islandOwner['title'],
                          'buildings' => $buildings,
                          'cardStates' => $cardStates);

        $result = array();
        if ( $ownerUid == $uid ) {
            //get user new minifeed count
            $islandVo['newFeedCount'] = Bll_Island_Feed::getNewMiniFeedCount($uid);

            $dalMongoTitle = Dal_Mongo_Title::getDefaultInstance();
            //get user title list
            $titleList = $dalMongoTitle->getUserTitleList($ownerUid);
            $result['userTitles'] = $titleList;
        }

        $bllDock = new Bll_Island_Dock();
        $dockVo = $bllDock->initDock($ownerUid, $uid);
        
        $dalRemind = Dal_Mongo_Remind::getDefaultInstance();
        //get user new remind count
        $islandVo['newRemindCount'] = $dalRemind->getNewRemindCount($uid);
        
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

    /**
     * rand load island info
     *
     * @param integer $uid
     * @return array
     */
    public function randIsland($uid)
    {
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get app user max id
        $maxId = $dalUser->getMaxId();

        for ( $i = 0; $i < 10; $i++ ) {
            $idArray = array('0' => rand(1, $maxId),
                             '1' => rand(1, $maxId),
                             '2' => rand(1, $maxId));

            //get uid array by id array
            $uidArray = $dalUser->getUidInArray($idArray);

            if (!empty($uidArray)) {
                $ownerUid = $uidArray['0']['uid'];
                break;
            }
        }

        $islandVo = $this->initIsland($ownerUid, $uid);

        return $islandVo;
    }

    /**
     * diy island info
     *
     * @param integer $uid
     * @param array $changesAry
     * @param array $removesAry
     * @return array
     */
    public function diyIsland($uid, $changesAry, $removesAry)
    {
        $resultVo = array('status' => -1);

        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            $result['resultVo'] = $resultVo;
            return $result;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUserPraise = $dalUser->getUserPraise($uid);

        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();

        $praiseChange = 0;
        $buildingChange = 0;
        $backgroundChange = 0;
        $plantChange = 0;
        
        $userBuildingArray = $dalBuilding->getUserBuildingList($uid);
        $userPlantArray = $dalPlant->getUserPlantListForDiy($uid);
        
        //begin transaction
        //$this->_wdb->beginTransaction();
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
                        $buildingInfo = Bll_Cache_Island::getBuildingById($userBuilding['bid']);
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
                        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);

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
                        }
                        catch (Exception $e) {
                            info_log('[error_message]-[updateUserPlant]:'.$e->getMessage(), 'Bll_Island');
                        }
    	                $plantChange = 1;
                    }
                }
                else if ( $itemType == 1 ) {
                    try {
                    	//$dalTest = Dal_Island_Test::getDefaultInstance();
                    	//$oldBackgroundCount = $dalTest->getUserUsingBackgroundCount();
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
	                    
	                    /*$newBackgroundCount = $dalTest->getUserUsingBackgroundCount();
	                    if ( $newBackgroundCount != $oldBackgroundCount ) {
	                    info_log('[emptyBackground]:oldbgCount-'.$oldBackgroundCount.',newbgCount-'.$newBackgroundCount.',uid-'.$uid.',bgid-'.$newBgid, 'emptyBackgroundNew');
	                    }*/
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
                        
                        $buildingInfo = Bll_Cache_Island::getBuildingById($userBuilding['bid']);
                        if ( $buildingInfo['add_praise'] > 0 ) {
                            $praiseChange -= $buildingInfo['add_praise'];
                        }
                        $buildingChange = 1;
                    }
                }
                else if ( $itemType == 3 ) {
                    $id = substr($id, 0, -1);
                    //$userPlant = $dalPlant->getUserPlantById1($id, $uid);
                    $userPlant = $userPlantArray[$id];
                    if ( !empty($userPlant) && $userPlant['uid'] == $uid ) {
    	                //update user plant info by building id
    	                $newPlant = array('uid' => $userPlant['uid'],
    	                                  'status' => 0,
    	                				  'event' => 0,
    	                				  'wait_visitor_num' => 0,
    	                				  'start_pay_time' => 0,
    	                				  'deposit' => 0,
    	                				  'start_deposit' => 0,
                                          'delay_time' => 0,
                                          'event_manage_time' => 0);
    	                
                        try {
                           $dalPlant->updateUserPlant($id, $newPlant);
                        }
                        catch (Exception $e) {
                            info_log('[error_message]-[updateUserPlant-2]:'.$e->getMessage(), 'Bll_Island');
                        }
                        
                        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);
                        if ( $plantInfo['add_praise'] > 0 ) {
                            $praiseChange -= $plantInfo['add_praise'];
                        }
                        $removeVisitorNum = $removeVisitorNum + $userPlant['wait_visitor_num'];
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
                try {
                    $dalUser->updateUserVisitorNum($uid);
                }
                catch (Exception $e) {
                    info_log('[error_message]-[updateUserVisitorNum]:'.$e->getMessage(), 'Bll_Island');
                }
            }

            if ( $praiseChange != 0 ) {
                if ( $islandUserPraise + $praiseChange < 0 ) {
                    $praiseChange = -$islandUserPraise;
                }
                
                try {
                    $dalUser->updateUserByField($uid, 'praise', $praiseChange);
                }
                catch (Exception $e) {
                    info_log('[error_message]-[updateUserByField]:'.$e->getMessage(), 'Bll_Island');
                }
            }

            //end of transaction
            //$this->_wdb->commit();
        
            if ( $buildingChange == 1 ) {
                //clear user building cache
                Bll_Cache_Island_User::clearCache('getUsingBuilding', $uid);
            }
            if ( $plantChange == 1 ) {
                //clear user plant cache
                Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
                Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
                Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
            }
            if ( $backgroundChange == 1 ) {
            	//clear user background cache
                Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
            }

            if ( $praiseChange != 0 ) {
                //update user achievement praise
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
            $islandVo = $this->initIsland($uid, $uid);
            $result['islandVo'] = $islandVo['islandVo'];

            //get user info
            $bllUser = new Bll_Island_User();
            $userVo = $bllUser->getUserInit($uid);
            $result['userVo'] = $userVo;

            //get user item box info
            $bllCard = new Bll_Island_Card();
            $result['items'] = $bllCard->readItem($uid);

            $result['dockVo'] = $islandVo['dockVo'];
        }
        catch (Exception $e) {
            //$this->_wdb->rollBack();
            info_log('[error_message]-[diyIsland]:'.$e->getMessage(), 'transaction');
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
    public function visitIsland($ownerUid, $uid)
    {
        $isTodayVisit = Bll_Cache_Island_Visit::isTodayVisit($uid, $ownerUid);
        if ( !$isTodayVisit ) {
            Bll_Cache_Island_Visit::setTodayVisit($uid, $ownerUid);
        }
    }

}