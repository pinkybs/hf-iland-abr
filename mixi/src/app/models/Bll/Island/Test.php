<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/01    Liz
 */
class Bll_Island_Test extends Bll_Abstract
{
	public function addUserFifaCoin($pageIndex, $pageSize)
	{
		$dalTest = Dal_Island_Test::getDefaultInstance();
		$uidList = $dalTest->getUserFifaList($pageIndex, $pageSize);
	
		$dalUser = Dal_Island_User::getDefaultInstance();
		$array = array('coin'=>40000,'gold'=>10);
		
        for ($i=0,$iCount=count($uidList); $i<$iCount; $i++) {
        	
            try{
	            //begin transaction
	            $this->_wdb->beginTransaction();
	            
	            $dalUser->updateUserByMultipleField($uidList[$i]['uid'], $array);
	            $dalTest->deleteUserFifa($uidList[$i]['uid']);
	            
	            $this->_wdb->commit();
            
                $minifeed = array('uid' => $uidList[$i]['uid'],
                                  'template_id' => 101,
                                  'actor' => $uidList[$i]['uid'],
                                  'target' => $uidList[$i]['uid'],
                                  'type' => 3,
                                  'create_time' => time());
                Bll_Island_Feed::insertMiniFeed($minifeed);
	        }catch (Exception $e) {
	            $this->_wdb->rollBack();
	            info_log('[addUserFifa]:'.$e->getMessage(), 'addUserFifa');
	            return false;
	        }
        }
        return true;
	}
	
	public function addUserFifa()
	{
		//$time = date('2010-06-25 00:00:00');
		$dalMongoTest = Dal_Mongo_Test::getDefaultInstance();
		$dalTest = Dal_Island_Test::getDefaultInstance();
		$uidList = $dalMongoTest->getFifaList();
		for ($i=0,$iCount=count($uidList); $i<$iCount; $i++) {
			$dalTest->insertUserFifa($uidList[$i]);
		}
	}
	
    public function readTask($uid)
    {
        $dalMongoTask = Dal_Mongo_Task::getDefaultInstance();
        
        //get task list
        $dailyTask = Bll_Cache_Island::getDailyTaskList();
        $buildTask = Bll_Cache_Island::getBuildTaskNeedInfoList();
        $achievementTask = Bll_Cache_Island::getAchievementTaskList();

        //get user task list
        $userTaskList = $dalMongoTask->getUserTaskList($uid);
        $userTask = array();
        for ( $m=0,$mCount=count($userTaskList); $m<$mCount; $m++ ) {
            $userTask[] = $userTaskList[$m]['tid'];
        }
        
        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
        //get user today achievement info
        $userTodayAchievement = $dalMongoAchievement->getUserTodayAchievement($uid);
        
        //get user achievement info
        $userAchievement = $dalMongoAchievement->getUserAchievement($uid);
        
        //get user plant list array
        /*$userPlantListArray = Bll_Cache_Island_User::getUserPlantList($uid);
        $userPlantList = $userPlantListArray['userPlantList'];
        $userPlantItemIdList = $userPlantListArray['userPlantItemIdList'];*/
        
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user all plant
        $userPlantList = $dalPlant->getUserPlantList($uid);
        //get user plant list by item id
        $userPlantItemIdList = $dalPlant->getUserPlantListByItemId($uid);
        
        //get user ship list
        $userShipList = Bll_Cache_Island_User::getUserShipList($uid);
        
        $userDailyTask = array();
        $userBuildTask = array();
        $userAchievementTask = array();
        
        for ( $j = 0,$jCount = count($dailyTask); $j < $jCount; $j++ ) {
            $field = 'num_' . $dailyTask[$j]['needType'];     
            $dailyTask[$j]['currentGetNum'] = isset($userTodayAchievement[$field]) ? $userTodayAchievement[$field] : 0;
            
            if ( in_array($dailyTask[$j]['taskClassId'], $userTask) ) {
                $dailyTask[$j]['state'] = 2;
            }
            else {
                if ( $dailyTask[$j]['currentGetNum'] >= $dailyTask[$j]['needNum'] ) {
                    $dailyTask[$j]['state'] = 1;
                }
                else {
                    $dailyTask[$j]['state'] = 0;
                }
            }
            $userDailyTask[] = array('taskClassId' => $dailyTask[$j]['taskClassId'],
                                     'currentGetNum' => $dailyTask[$j]['currentGetNum'],
                                     'state' => $dailyTask[$j]['state']);
        }

        $unsetAry = array();
        for ( $k = 0,$kCount = count($achievementTask); $k < $kCount; $k++ ) {
            if ( !in_array($achievementTask[$k]['taskClassId'], $unsetAry) ) {
                $field = 'num_' . $achievementTask[$k]['needType'];
                
                $achievementTask[$k]['currentGetNum'] = isset($userAchievement[$field]) ? $userAchievement[$field] : 0;
                
                if ( in_array($achievementTask[$k]['taskClassId'], $userTask) ) {
                    $achievementTask[$k]['state'] = 2;
                    if ( $achievementTask[$k]['level'] != 3 ) {
                        unset($achievementTask[$k]);
                    }
                }
                else {
                    if ( $achievementTask[$k]['currentGetNum'] >= $achievementTask[$k]['needNum'] ) {
                        $achievementTask[$k]['state'] = 1;
                    }
                    else {
                        $achievementTask[$k]['state'] = 0;
                    }

                    if ( $achievementTask[$k]['level'] == 1 ) {
                        $unsetAry[] = $achievementTask[$k]['nextTaskId'];
                        $unsetAry[] = $achievementTask[$k]['nextTwoTaskId'];
                    }
                    else if ( $achievementTask[$k]['level'] == 2 ) {
                        $unsetAry[] = $achievementTask[$k]['nextTaskId'];
                    }
                }
                
                if ( isset($achievementTask[$k]) ) {
                $userAchievementTask[] = array('taskClassId' => $achievementTask[$k]['taskClassId'],
                                               'currentGetNum' => $achievementTask[$k]['currentGetNum'],
                                               'state' => $achievementTask[$k]['state']);
                }
            }
        }

        for ( $l = 0,$lCount = count($buildTask); $l < $lCount; $l++ ) {
            $buildTask[$l]['currentGetNum'] = 0;

            if ( in_array($buildTask[$l]['taskClassId'], $userTask) ) {
                $buildTask[$l]['state'] = 2;
            }
            else {
                if ( $buildTask[$l]['needType'] == 9 ) {
                    if ( $buildTask[$l]['item_id'] > 0 ) {
                        //need num == 1
                        if ( $buildTask[$l]['needNum'] == 1 ) {
                            if ( isset($userPlantList[$buildTask[$l]['item_id']]) && $userPlantList[$buildTask[$l]['item_id']] >= $buildTask[$l]['item_level'] ) {
                                $buildTask[$l]['currentGetNum'] = 1;
                            }
                        }
                        else if ( $buildTask[$l]['needNum'] > 1 ) {
                            foreach ($userPlantItemIdList as $plant) {
                                if ( $plant['item_id'] == $buildTask[$l]['item_id'] && $plant['level'] >= $buildTask[$l]['item_level'] ) {
                                    $buildTask[$l]['currentGetNum'] += 1;
                                }
                            }
                        }
                    }

                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                    
                }
                else if ( $buildTask[$l]['needType'] == 11 ) {
                    //get user ship count by ship id
                    if ( isset($userShipList[$buildTask[$l]['needCid']]) ) {
                        $buildTask[$l]['currentGetNum'] = $userShipList[$buildTask[$l]['needCid']];
                    }
                    
                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                }
                else if ( $buildTask[$l]['needType'] == 12 ) {
                    $field = 'num_' . $buildTask[$l]['needType'];
                    if ( isset($userAchievement[$field]) ) {
                        $buildTask[$l]['currentGetNum'] = $userAchievement[$field] + 3;
                    }

                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                }
                else {
                    $field = 'num_' . $buildTask[$l]['needType'];
                    if ( isset($userAchievement[$field]) ) {
                        $buildTask[$l]['currentGetNum'] = $userAchievement[$field];
                    }

                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                }
            }
            unset($buildTask[$l]['item_id']);
            unset($buildTask[$l]['item_level']);
            
            $userBuildTask[] = array('taskClassId' => $buildTask[$l]['taskClassId'],
                                     'currentGetNum' => $buildTask[$l]['currentGetNum'],
                                     'state' => $buildTask[$l]['state']);
        }
        $taskList = array_merge($userDailyTask, $userBuildTask, $userAchievementTask);

        return array('tasks' => $taskList);
    }
    
	public function addGold($uid, $gold)
	{
		$isAppUser = Bll_User::isAppUser($uid);
		if ( !$isAppUser ) {
			return '该用户还没有加入游戏。';
		}
		
		$dalUser = Dal_Island_User::getDefaultInstance();
		
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
			//get user for update
	        $forUpdateUser = $dalUser->getUserForUpdate($uid);
	        
            //update user
            $newUser = array('gold' => $forUpdateUser['gold'] + $gold);
            $dalUser->updateUser($uid, $newUser);

            //end of transaction
            $this->_wdb->commit();

            return '成功添加:' . $gold . '个宝石';
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return '系统错误！';
        }
	}

    public function addCoin($uid, $gold, $coin, $level, $exp, $nextLevelExp, $praise)
    {
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            return '该用户还没有加入游戏。';
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            
            //update user
            $newUser = array('gold' => $forUpdateUser['gold'] + $gold, 
                             'coin' => $forUpdateUser['coin'] + $coin);
            
            if ( $level > 0 ) {
            	$newUser['level'] = $level;
            }
            if ( $exp > 0 ) {
                $newUser['exp'] = $exp;
            }
            if ( $nextLevelExp > 0 ) {
                $newUser['next_level_exp'] = $nextLevelExp;
            }
            if ( $praise > 0 ) {
                $newUser['praise'] = $praise;
            }
            $dalUser->updateUser($uid, $newUser);

            //end of transaction
            $this->_wdb->commit();

            return '成功添加:' . $gold . '个宝石,' . $gold . '个金币。<br/>修改：' . $gold . '等级,' . $gold . '经验,' . $gold . '下级经验。';
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            return '系统错误！';
        }
    }
    
    public function getAbnormality($uid)
    {
        $dalFeed = Dal_Mongo_Test::getDefaultInstance();
        $feedList = $dalFeed->getAbnormalityFeed($uid);
        
        return $feedList;
    }
    
    /**
     * copy user
     *
     * @param integer $oldUid
     * @param integer $newUid
     * @return array
     */
	public function copyUser($oldUid, $newUid)
    {
        //$dalUser = Dal_Island_User::getDefaultInstance();
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $dalTest = Dal_Island_Test::getDefaultInstance();
        
        $dalTest->cleanUserIsland($newUid);
        
        $buildingList = $dalBuilding->getUsingBuildingAll($oldUid);
        foreach ( $buildingList as $building ) {
            unset($building['id']);
            $building['uid'] = $newUid;
            $dalBuilding->addUserBuilding($building);
        }

        $plantList = $dalPlant->getUsingPlantAll($oldUid);
        foreach ( $plantList as $plant ) {
            unset($plant['id']);
            $plant['uid'] = $newUid;
            $dalPlant->insertUserPlant($plant);
        }
        
        $backgroundList = $dalBuilding->getUsingBgAll($oldUid);
        foreach ( $backgroundList as $background ) {
            unset($background['id']);
            $background['uid'] = $newUid;
            $dalBuilding->insertUserBackground($background);
        }
        
        $dalTest->updateNewPlant($newUid);
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        $islandOwner = $dalUser->getUserLevelInfo($oldUid);
        
        $newUser = array('level' => $islandOwner['level'],
                         'exp' => $islandOwner['exp'],
                         'next_level_exp' => $islandOwner['next_level_exp'],
                         'island_level' => $islandOwner['island_level'],
                         'praise' => $islandOwner['praise']);
        $dalUser->updateUser($newUid, $newUser);
        
        return true;
    }
    
    public function addMongoUser($n, $count)
    {
        $dalUser = Dal_Mongo_User::getDefaultInstance();
        
        $user = array();
        $user['headurl'] = 'http://head.xiaonei.com/photos/0/0/men_head.gif';
        $user['tinyurl'] = 'http://head.xiaonei.com/photos/0/0/men_tiny.gif';
        $user['sex'] = 1;
        $user['shop_id'] = 0;
        
        $userAry = array();
        //for ( $m=0; $m<10; $m++ ) {
            //$n = $m;
            for ( $i=$n; $i<$count; $i=$i+10 ) {
                $user['uid'] = (string)$i;
                $user['name'] = $i;
                $userAry[] = $user;
            }
            
            $dalUser->addPersonAry($userAry, $n%10);
            
        //}
    }
    
    public function addMongoFriend($n, $count)
    {
        $dalUser = Dal_Mongo_User::getDefaultInstance();
        
        $friendList = array();
        $friend = array();
        
        //for ( $m=0; $m<10; $m++ ) {
            //$n = $m;
            for ( $i=$n; $i<$count; $i=$i+10 ) {
                $friend['uid'] = (string)$i;
                $friend['fids'] = array();
                for ( $j=1; $j <= 20; $j++ ) {
                    $m = $i+$j;
                    $friend['fids'][] = (string)$m;
                }
                
                $friendList[] = $friend;
            }
            
            $dalUser->addFriendAry($friendList, $n%10);
        //}
    }
    
    public function changeHelp($changeUid)
    {
    	info_log('changehelp-1','test');
    	$dalUser = Dal_Island_User::getDefaultInstance();
    	
    	$newUser = array('help' => 6);
    	info_log('changehelp-2','test');
    	$dalUser->updateUser($changeUid, $newUser);
    }
    
    public function salePlant($uid, $id)
    {
        $result = array('status' => -1);//1001436232
info_log('salePlant -uid- '.$uid.'-- id -'.$id, 'saleplant');
        $dalUser = Dal_Island_User::getDefaultInstance();
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant by id
        $userPlant = $dalPlant->getUserPlantById($id);
info_log('salePlant -uid1- '.$userPlant['uid'], 'saleplant');
        if ( $userPlant['uid'] != $uid ) {
            $result['content'] = '仓库中已经没有此道具可以出售。';
            return $result;
        }

        //get Plant info
        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);

        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            //get user Plant for update
            $forUpdateUserPlant = $dalPlant->getUserPlantByIdForUpdate($id, $uid);
            info_log('salePlant -uid1- '.$forUpdateUserPlant['uid'], 'saleplant');
            if ( $forUpdateUserPlant['uid'] != $uid ) {
                $this->_wdb->rollBack();
                $result['content'] = '仓库中已经没有此道具可以出售。';
                return $result;
            }
/*
            //update user
            $newUser = array('coin' => $forUpdateUser['coin'] + $plantInfo['sale_price']);
            $dalUser->updateUser($uid, $newUser);

            //delete user Plant by id
            $dalPlant->deleteUserPlantById($id, $uid);

            //end of transaction
            $this->_wdb->commit();*/

            $result['status'] = 1;
            $result['content'] = '道具售出成功。';
            $result['coinChange'] = $plantInfo['sale_price'];
            $result['goldChange'] = 0;
            $result['itemBoxChange'] = true;
            if ( $userPlant['status'] == 1 ) {
                $result['islandChange'] = true;
            }
            else {
                $result['islandChange'] = false;
            }
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }

        return $result;
    }
    
    public function visitorsInvite($uid, $number)
    {
    	$this->_wdb->beginTransaction();
        try {
        	
        $visitorsAry = array();
        $now = time();
info_log('visitors-1','visitors');
        $dalUser = Dal_Island_User::getDefaultInstance();
        $userInfo = $dalUser->getUserDockInfo($uid);

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $plantInfo1 = $dalPlant->getListUserPlantAllById($uid);
        $bids = $dalPlant->getUserPlantBidByid($uid);
        $plantInfo2 = $dalPlant->getListIslandPlant($userInfo['level'], $bids);
info_log('visitors-2','visitors');
        foreach ($plantInfo2 as $key2 => $value2) {
            foreach ($plantInfo1 as $value1) {
                if ( $value1['item_id'] == $value2['item_id'] && $value2['level'] < $value1['level'] ) {
                    unset($plantInfo2[$key2]);
                }
                else {
                    unset($plantInfo2[$key2]['level']);
                    unset($plantInfo2[$key2]['item_id']);
                }
            }
        }
info_log('visitors-3','visitors');
        $plantInfo3 = Bll_Cache_Island::getPlantListByLevel($userInfo['level']);
        if (!$plantInfo1 && !$plantInfo2 && !$plantInfo3) {
            return;
        }

        $flag = 0;
        if($plantInfo1) $flag += 4;
        if($plantInfo2) $flag += 2;
        if($plantInfo3) $flag += 1;

        $config_map = array(0 => array(0, 0, 0),
                            1 => array(0, 0, 1),
                            2 => array(0, 1, 0),
                            3 => array(0, 0.95, 0.05),
                            4 => array(1, 0, 0),
                            5 => array(0.95, 0, 0.05),
                            6 => array(0.8, 0.2, 0),
                            7 => array(0.8, 0.15, 0.05));

        $numAry = $config_map[$flag];
        $num1 = round($numAry[0] * $number);
        $num2 = round($numAry[1] * $number);
        $num3 = $number - $num1 - $num2;
info_log('visitors-4','visitors');
        $updateVisitor = 0;
        if ($plantInfo1 && $num1) {
            //rand distribution
            for ($i = 0; $i < $num1; $i++ )
            {
                $randNum = rand(0, count($plantInfo1) - 1);
                $plantInfo1[$randNum]['num'] = $plantInfo1[$randNum]['num'] + 1 ;
            }

            $result1 = array();
            //update db
            foreach ($plantInfo1 as $key => $value) {
                $resultAry = array();
                $plantNb = Bll_Cache_Island::getPlantById($value['cid']);
info_log('visitors-5','visitors');
                if ( $plantInfo1[$key]['num'] != 0 ) {
                    $time = $now - $value['start_pay_time'] - $plantNb['pay_time'] - $value['delay_time'];
                    $updatePlant = array();
                    if ( $value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
                        $updatePlant['start_pay_time'] = $now;
                    }

                    if ($value['eventId'] == 0 && $time < 0 && $value['event_manage_time'] == 0) {
                        if (($now - $userInfo['defense_card']) >= 12*3600 ) {
                            $rand = rand(1, 100);
                            if ( $rand < 11 ) {
                                $updatePlant['event'] = 1;
                                $value['eventId'] = 1;
                            }
                        }
                    }

                    $ticket = $plantNb['ticket'] * $value['num'];
                    $updatePlant['wait_visitor_num'] = $value['wait_visitor_num'] + $value['num'];
                    $updatePlant['deposit'] = $value['deposit'] + $ticket;
                    $updatePlant['start_deposit'] = $value['start_deposit'] + $ticket;
                    $updatePlant['uid'] = $value['uid'];
info_log('visitors-6','visitors');
info_log('visitors-6-uid'.$updatePlant['uid'],'visitors');
info_log('visitors-6-wait_visitor_num'.$updatePlant['wait_visitor_num'],'visitors');
                    //update db
                    $dalPlant->updateUserPlant($value['id'], $updatePlant);
                    $updateVisitor = $updateVisitor + $value['num'];

                    $resultAry['itemId'] = $value['itemId'];
                    $resultAry['cid'] = $value['cid'];
                    $resultAry['num'] = $value['num'];
                    $resultAry['eventId'] = $value['eventId'];
info_log('visitors-7','visitors');
                    $result1[] = $resultAry;
                }
                //delete 'id' property
                array_shift($plantInfo1[$key]);
            }
            //update user table visitor
            if ($updateVisitor) {
                $dalUser->updateUser($uid, array('currently_visitor' => $userInfo['currently_visitor'] + $updateVisitor));
            }
        }
info_log('visitors-8','visitors');
        //rand distribution 15% people
        if ($plantInfo2 && $num2) {
            for ($i = 0; $i < $num2; $i++ )
            {
                $randNum = array_rand($plantInfo2);
                $plantInfo2[$randNum]['num'] = $plantInfo2[$randNum]['num'] + 1 ;
            }
        }

        //rand distribution 5% people
        if ($plantInfo3 && $num3) {
            for ($i = 0; $i < $num3; $i++ )
            {
                $randNum = array_rand($plantInfo3);
                $plantInfo3[$randNum]['num'] = $plantInfo3[$randNum]['num'] + 1 ;
            }
        }
        
        if ( !empty($result1) ) {
            $visitorsAry = array_merge($result1, $plantInfo2, $plantInfo3);
        }
        else {
            $visitorsAry = array_merge($plantInfo2, $plantInfo3);
        }
info_log('visitors-9','visitors');
        //$visitorsAry = array_merge($result1, $plantInfo2, $plantInfo3);
        return $visitorsAry;
        
            //end of transaction
            $this->_wdb->commit();
        }
        catch (Exception $e) {
            info_log('visitors-04','visitors');
            $this->_wdb->rollBack();
            $result['status'] = -1;
            return $result;
        }
    }
    
    /**
     * load island info
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @return array
     */
    public function initIsland($ownerUid, $uid)
    {
        $startTime = floor(microtime(true)*1000);
        info_log('start-1:'.$startTime);
        
        $dalIsland = Dal_Island_Island::getDefaultInstance();
        $dalUser = Dal_Island_User::getDefaultInstance();
        
        //check is app user
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result = array('status' => -1,
                            'content' => '您已断线，请点击按钮刷新页面。');
            return $result;
        }
        
        //if ( $ownerUid == $uid ) {
            $blluser = new Bll_Island_User();
            $blluser->outIslandPeople($ownerUid);
        //}

        //get owner info
        $islandOwner = $dalUser->getUser($ownerUid);
        if ( !$islandOwner ) {
            return;
        }
        
        $startTime = floor(microtime(true)*1000);
        info_log('start-2:'.$startTime);

        //if ( $ownerUid != $uid ) {
            //visit island
            $this->visitIsland($ownerUid, $uid);
        //}

        $startTime = floor(microtime(true)*1000);
        info_log('start-3:'.$startTime);
        
        //get owner island
        $userBackgroundInfo = $dalIsland->getUsingBackground($ownerUid);
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
            }
        }

        $startTime = floor(microtime(true)*1000);
        info_log('start-4:'.$startTime);
        
        if ( $ownerUid == 1  ) {
            $user = array('name' => $islandOwner['island_name'],
                          'headurl' => 'http://head.xiaonei.com/photos/0/0/men_head.gif');
            $isFriend = false;
        }
        else {
            $user = Bll_User::getPerson($ownerUid);

            //check is friend
            if ( $ownerUid != $uid ) {
                $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
            }
            else {
                $isFriend = false;
            }
        }

        $startTime = floor(microtime(true)*1000);
        info_log('start-5:'.$startTime);
        
        $dalBuilding = Dal_Island_Building::getDefaultInstance();
        //get owner buildings info
        $buildings = $dalBuilding->getUsingBuilding($ownerUid);

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get owner plant info
        $plants = $dalPlant->getUsingPlant($ownerUid);

        //$dalMooch = Dal_Mongo_Mooch::getDefaultInstance();

        $dalTest = Dal_Mongo_Test::getDefaultInstance();
        
        $startTime = floor(microtime(true)*1000);
        info_log('start-6:'.$startTime);
        
        $userMoochArray = $dalTest->getPlantMoochByUid($uid, $ownerUid);
        
        $startTime = floor(microtime(true)*1000);
        info_log('start-7:'.$startTime);
        
        $nowTime = time();
        for ( $i=0,$iCount=count($plants); $i<$iCount; $i++ ) {
            $plants[$i]['payRemainder'] = $plants[$i]['pay_time'] - ($nowTime - $plants[$i]['start_pay_time'] - $plants[$i]['delay_time']);
            $plants[$i]['payRemainder'] = max(0, $plants[$i]['payRemainder']);
            //$eventRemainder = $nowTime - $plants[$i]['start_pay_time'] - $plants[$i]['pay_time'] * 0.6;
            //$plants[$i]['eventRemainder'] = $eventRemainder > 0 ? $eventRemainder : 0;
            unset($plants[$i]['pay_time']);
            unset($plants[$i]['delay_time']);
            unset($plants[$i]['start_pay_time']);
            $itemId = substr($plants[$i]['id'], 0, -3);
            //get user mooch plant info
            //$moochInfo = $dalMooch->getPlantMooch($uid, $ownerUid, $itemId);
            $moochInfo = in_array($itemId, $userMoochArray);
            if ( $moochInfo ) {
                $plants[$i]['hasSteal'] = 1;
            }
            else {
                $plants[$i]['hasSteal'] = 0;
            }
        }

        if ( !empty($plants) ) {
            $buildings = array_merge($buildings, $plants);
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
                          'buildings' => $buildings);
        
        
        $result = array();
        if ( $ownerUid == $uid ) {
            //get user new minifeed count
            $islandVo['newFeedCount'] = Bll_Island_Feed::getNewMiniFeedCount($uid);
            
            $dalMongoTitle = Dal_Mongo_Title::getDefaultInstance();        
            //get user title list
            $titleList = $dalMongoTitle->getUserTitleList($ownerUid);
            $result['userTitles'] = $titleList;
        }
        
        $startTime = floor(microtime(true)*1000);
        info_log('start-8:'.$startTime);
        
        $bllDock = new Bll_Island_Dock();
        $dockVo = $bllDock->initDock($ownerUid);
        
        $startTime = floor(microtime(true)*1000);
        info_log('start-9:'.$startTime);
        
        $result['islandVo'] = $islandVo;
        $result['dockVo'] = $dockVo;
        return $result;
    }

    /**
     * read ship list
     *
     * @param integer $uid
     * @return array
     */
    public function readShip($uid)
    {
        $dalDock = Dal_Mongo_Dock::getDefaultInstance();
        //get user ship list
        $shipList = $dalDock->getUserShipList($uid);
        return $shipList;
    }
    
    /**
     * unlock ship 
     *
     * @param integer $uid
     * @param integer $shipId
     * @param integer $priceType
     * @return array
     */
    public function unlockShip($uid, $shipId, $priceType)
    {
        $resultVo = array('status' => -1);
        
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $resultVo['content'] = '您已断线，请点击按钮刷新页面。';
            return $resultVo;
        }
        
        $dalDock = Dal_Mongo_Dock::getDefaultInstance();
        //get user boat by id
        $userShip = $dalDock->getUserShipById($uid, $shipId);
        if ( $userShip ) {
            $resultVo['content'] = '您已经升级了这艘船。';
            return $resultVo;
        }
    
        //get db ship info
        $shipInfo = Bll_Cache_Island::getShip($shipId);
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user island info
        $islandUser = $dalUser->getUserLevelInfo($uid);

        if ($islandUser['level'] < $shipInfo['level']) {
            $resultVo['content'] = '等级不够哦！升级这艘船需要' . $shipInfo['level'] . '级!';
            return $resultVo;
        }

        $price = $priceType == 1 ? 'coin' : 'gold';
        $ifPrice = $priceType == 1 ? 'coin' : 'gem';
        $priceContent = $priceType == 1 ? '金币' : '宝石';

        if ($islandUser[$price] < $shipInfo[$ifPrice]) {
            $resultVo['content'] = $priceContent . '不够哦！升级这艘船需要' . $shipInfo[$ifPrice] . $priceContent . '!';
            return $resultVo;
        }
        info_log('unlockship-1-', 'unlcokship');
        if ( $price == 'coin' && $shipInfo['coin'] < 1 ) {
            $resultVo['content'] = '这艘船只能用宝石购买哦！';
            return $resultVo;
        }

        //begin transaction
        $this->_wdb->beginTransaction();
        try{
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ($forUpdateUser[$price] < $shipInfo[$ifPrice]) {
                $resultVo['content'] = $priceContent . '不够哦！升级这艘船需要' . $shipInfo[$ifPrice] . $priceContent . '!';
                return $resultVo;
            }
info_log('unlockship-2-', 'unlcokship');
            //update user info
            $userAry = array('exp' => 0);
            if ( $priceType == 1 ) {
                $userAry['coin'] = $forUpdateUser['coin'] - $shipInfo['coin'];
            }
            else {
                $userAry['gold'] = $forUpdateUser['gold'] - $shipInfo['gem'];
            }

            $addExp = 9;
            $userAry['exp'] = $forUpdateUser['exp'] + $addExp;

            $dalUser->updateUser($uid, $userAry);

            $bllDock = new Bll_Island_Dock();
            //check level up
            $levelUp = $bllDock->checkLevelUp($uid);
info_log('unlockship-3-', 'unlcokship');
            $this->_wdb->commit();

            $dalMongoDock = Dal_Mongo_Dock::getDefaultInstance();
            $dalMongoDock->insertUserShip(array('uid' => (string)$uid, 'ship_id' => (string)$shipId));
            info_log('unlockship-5-', 'unlcokship');
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement ship level
            $userAchievementShipLevel = $dalMongoAchievement->getUserAchievementByField($uid, 'num_11');
            if ( $userAchievementShipLevel < $shipInfo['sid'] + 1 ) {
                $dalMongoAchievement->updateUserAchievement($uid, array('num_11' => $shipInfo['sid'] + 1));
            }
info_log('unlockship-4-', 'unlcokship');
            $content = '船只升级成功！';
            if ($levelUp['levelUp']) {
                $str = "恭喜，升到第". $forUpdateUser['level'] + 1 ."级了！获得了奖品" . $levelUp['giftName'] . "！";
                $content = $str . $content;
            }

            $resultVo['content'] = $content;
            $resultVo['expChange'] = + $addExp;
            if ( $priceType == 1 ) {
                $resultVo['coinChange'] = -$shipInfo['coin'];
            }
            else {
                $resultVo['goldChange'] = -$shipInfo['gem'];
            }
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            $resultVo['content'] = '系统错误';
            return $resultVo;
        }
    
        //send activity
        $feed = Bll_Island_Activity::send('BOAT_LEVEL_UP', $uid);
        $resultVo['feed'] = $feed;
        if ( $levelUp['levelUp'] ) {
            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $forUpdateUser['level'] + 1));
            $resultVo['feed'] = $feed;
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }
        
        return $resultVo;
    }

    /**
     * change ship 
     *
     * @param integer $uid
     * @param integer $shipId
     * @param integer $pid
     * @return array
     */
    public function changeShip($uid, $shipId, $pid)
    {
        $resultVo = array('status' => -1);
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $resultVo['content'] = '您已断线，请点击按钮刷新页面。';
            return $resultVo;
        }
    
        $dalMongoDock = Dal_Mongo_Dock::getDefaultInstance();
        //get user boat by id
        $userShip = $dalMongoDock->getUserShipById($uid, $shipId);
        if ( !$userShip ) {
            $resultVo['content'] = '您还没有升级这艘船，请先升级后再来吧。';
            return $resultVo;
        }
        
        $dalDock = Dal_Island_Dock::getDefaultInstance();
        //get user position info
        $positionInfo = $dalDock->getUserPositionById($uid, $pid);
        if ( $positionInfo['ship_id'] == $shipId ) {
            $resultVo['content'] = '当前船位上已经是这艘船，不能更换了。';
            return $resultVo;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get ship info
        $shipInfo = Bll_Cache_Island::getShip($shipId);
    
        //begin transaction
        $this->_wdb->beginTransaction();
        try{
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
         
            //get add visitor count by user praise
            $addVisitor = Bll_Cache_Island::getAddVisitorByPraise($forUpdateUser['praise']);
            //get start visitor num
            $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

            //update new boat
            $newShip = array('ship_id' => $shipId,
                             'status' => 1,
                             'receive_time' => time(),
                             'is_usecard_one' => 0,
                             'speedup' => 0,
                             'speedup_type' => 0,
                             'start_visitor_num' => $startVisitorNum,
                             'remain_visitor_num' => $startVisitorNum);
            $dalDock->updateUserPosition($uid, $pid, $newShip);

            $this->_wdb->commit();

            $resultVo['content'] = '船位更换船只成功';
            $resultVo['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            $resultVo['content'] = '系统错误';
            return $resultVo;
        }
        
        return $resultVo;
    }
    
    public function addUserShip($uid)
    {
        $dalDock = Dal_Island_Dock::getDefaultInstance();
        $maxShip = $dalDock->getUserMaxShip($uid);
        
        $dalMongoDock = Dal_Mongo_Dock::getDefaultInstance();
        $userShipList = $dalMongoDock->getUserShipList($uid);
        
        $count = $maxShip + 1;
        for ( $i=1; $i<$count; $i++ ) {
            if ( !in_array($i, $userShipList) ) {
                $dalMongoDock->insertUserShip(array('uid' => (string)$uid, 'ship_id' => (string)$i));
            }
        }
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
        $todayDate = date('Ymd');
        
        $isTodayVisit = Bll_Cache_Island_Visit::isTodayVisit($uid, $ownerUid);
        if ( !$isTodayVisit ) {
            $dalMongoVisit = Dal_Mongo_Visit::getDefaultInstance();
            $isTodayVisitMongo = $dalMongoVisit->getUserTodayVisitInfo($uid, $ownerUid, $todayDate);
            if ( !$isTodayVisitMongo ) {
                $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                //update user achievement,num_6
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
                //update user today achievement,num_6
                $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_6', 1);
            }

            Bll_Cache_Island_Visit::setTodayVisit($uid, $ownerUid);
            Bll_Cache_Island_Visit::setVisit($uid, $ownerUid);
        }
        else {
            $isVisit = Bll_Cache_Island_Visit::isVisit($uid, $ownerUid);
            if ( !$isVisit ) {
                $dalMongoVisit = Dal_Mongo_Visit::getDefaultInstance();
                $isVisitMongo = $dalMongoVisit->getUserVisitInfo($uid, $ownerUid, $todayDate);
                if ( !$isVisitMongo ) {
                    $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                    //update user achievement,num_6
                    $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
                }

                Bll_Cache_Island_Visit::setVisit($uid, $ownerUid);
            }
        }
        
    
        /*$dalMongoVisit = Dal_Mongo_Visit::getDefaultInstance();
        $isTodayVisitMongo = $dalMongoVisit->getUserTodayVisitInfo($uid, $ownerUid, $todayDate);
        info_log('visit-1', 'initisland');
        if ( !$isTodayVisitMongo ) {
            info_log('visit-2', 'initisland');
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement,num_6
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
            //update user today achievement,num_6
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_6', 1);
            info_log('visit-3', 'initisland');
        }
        else {
            $isVisitMongo = $dalMongoVisit->getUserVisitInfo($uid, $ownerUid, $todayDate);
            info_log('visit-4', 'initisland');
            if ( !$isVisitMongo ) {
                info_log('visit-5', 'initisland');
                $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
                //update user achievement,num_6
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
                info_log('visit-6', 'initisland');
            }
        }*/
    }
    
    /**
     * visit island
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @return array
     */
    public function visitIslandOld($ownerUid, $uid)
    {
        $dalVisit = Dal_Mongo_Visit::getDefaultInstance();

        $nowTime = time();
        $todayDate = date('Ymd');

        //get user visit info
        $userVisit = $dalVisit->getUserVisitInfo($uid, $ownerUid);

        //get user today visit info
        $userTodayVisit = $dalVisit->getUserTodayVisitInfo($uid, $ownerUid, $todayDate);

        $newVisit = array('uid' => $uid,
                          'fid' => $ownerUid,
                          'create_time' => $nowTime);

        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            
            if ( !$userVisit ) {
                //insert user visit info
                $dalVisit->insertUserVisit($newVisit);

                //update user achievement,num_6
                $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
            }

            if ( !$userTodayVisit ) {
                //insert user today visit info
                $dalVisit->insertUserTodayVisit($newVisit, $todayDate);

                //update user today achievement,num_6
                $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_6', 1);
            }

            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
    }

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

        $bllUser = new Bll_Island_User();
        //user init data
        $resultInitVo['user'] = $bllUser->getUserInit($uid);
        $resultInitVo['itemClass'] = array_merge($cardList, $backgroundList, $buildingList, $plantList);
        $resultInitVo['boatClass'] = $this->boatClass();
        $resultInitVo['levelClass'] = $levelList;
        $resultInitVo['taskClass'] = $taskList;
        $resultInitVo['titleClass'] = $titleList;

        return $resultInitVo;
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
}