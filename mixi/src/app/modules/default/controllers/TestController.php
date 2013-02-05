<?php

class TestController extends Zend_Controller_Action
{
    //protected $uid;

    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
		$this->view->staticUrl = Zend_Registry::get('static');
        $auth = Zend_Auth::getInstance();
        $this->uid = $auth->getIdentity();

        $p = $this->_request->getParam('p');
        //$this->uid != '258027420' && $this->uid != '283848137' && $this->uid != '290434000'
        if ( !in_array($this->uid, array('258027420', '283848137', '290434000', '281222990', '320206578','22112313')) && $p != '496700' ) {
            echo '不准非法进入1！';
            exit;
        }
        if (!$auth->hasIdentity()) {
            echo '不准非法进入2！';
            exit;
            //$auth->getStorage()->write();
        }

        $this->uid = $auth->getIdentity();
    }
    
    function getpowerAction()
    {
    	$uid = $this->_request->getParam('uid');
    	//update user acheviment friends numner count
        $bllDock = new Bll_Island_Dock();
        $count = $bllDock->getPower($uid);
        echo Zend_Json::encode($count);
    }
    
    function getuserfifaAction()
    {
        $uid = $this->_request->getParam('uid');
        $dalMongoFifa = Dal_Mongo_Fifa::getDefaultInstance();
        $userFifaInfo = $dalMongoFifa->getUserFifaInfo($uid);
        echo Zend_Json::encode($userFifaInfo);
    }
    
    function getactivitylistAction()
    {
        $level = $this->_request->getParam('level');
        $time = $this->_request->getParam('time');
        $pageIndex = $this->_request->getParam('pageIndex');
        $pageSize = $this->_request->getParam('pageSize');
        
        $dalTest = Dal_Island_Test::getDefaultInstance();
        $userList = $dalTest->getActivityUserList($level, $time, $pageIndex, $pageSize);
        
        $result = array('userList' => $userList, 'test' => 123);
        $bllTest = new Bll_Island_Test();
        $bllTest->postData($result);
        
        echo Zend_Json::encode($userList);
    }
    
    /**
     * update user praise
     *
     */
    function updateuserpraiseAction()
    {
        try {
	    	$uid = $this->_request->getParam('uid');
	    	$dalTest = Dal_Island_Test::getDefaultInstance();
	    	$plantPraise = $dalTest->getUserPlantPraise($uid);
	    	$buildingPraise = $dalTest->getUserBuildingPraise($uid);
	    	$backgroundPraise = $dalTest->getUserBackgroundPraise($uid);
	    	
	    	$allPraise = $plantPraise + $buildingPraise + $backgroundPraise;
	    	
	    	$dalUser = Dal_Island_User::getDefaultInstance();
	    	$userPraise = $dalUser->getUserPraise($uid);
	    	
	    	$info = array('praise' => $allPraise);
	    	$dalUser->updateUser($uid, $info);
            echo '更新用户装适度成功！<br/>用户原装适度：'.$userPraise.'<br/>更新后装适度：'.$allPraise;
        }
        catch (Exception $e) {
            echo '更新用户装适度没有成功，请再来一次！';
        }
    }
    
    function adduserfifaAction()
    {
        $pageIndex = $this->_request->getParam('pageIndex', 1);
        $pageSize = $this->_request->getParam('pageSize', 10000);
    	$bllTest = new Bll_Island_Test();
    	$bllTest->addUserFifaCoin($pageIndex, $pageSize);
    }
    
    function getuserbackgroundAction()
    {
    	$uid = $this->_request->getParam('uid');
    	$dalIsland = Dal_Island_Island::getDefaultInstance();
    	$bgInfo = $dalIsland->getUsingBackground($uid);
    	$dalBuilding = Dal_Island_Building::getDefaultInstance();
    	$itemBoxBgInfo = $dalBuilding->getItemBoxBackground($uid);
    	
    	$result = array('usingBg' => $bgInfo, 'boxBg' => $itemBoxBgInfo);
    	echo Zend_Json::encode($result);
    }
    
    function updateuserbackgroundAction()
    {
    	try {
	        $uid = $this->_request->getParam('uid');
	        $id = $this->_request->getParam('id');
	        
	        $newBg = array('status' => 1);
	        $dalBuilding = Dal_Island_Building::getDefaultInstance();
	    	$dalBuilding->updateUserBackgroundById($id, $uid, $newBg);
    	    Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
            echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }
    
    function getfifaAction()
    {
    	$id = $this->_request->getParam('id');
    	$pcid = $this->_request->getParam('pcid');
        $gid = $this->_request->getParam('gid');
    	$gameInfo = Bll_Cache_Island_Fifa::getGameInfoById($id);
    	$groupWinInfo = Bll_Cache_Island_Fifa::getGroupWinInfo($gid, $pcid);
    	$result = array('gameInfo' => $gameInfo, 'groupWinInfo' => $groupWinInfo);
    	echo Zend_Json::encode($result);
    }
    
    function addfifaAction()
    {
    	$pcid = $this->_request->getParam('pcid');
        $dalMongoFifa = Dal_Mongo_Fifa::getDefaultInstance();
        $dalMongoFifa->insertFifaResult(array('pcid' => $pcid));
    }
    
    function updatefifaAction()
    {
    	$id1 = $this->_request->getParam('id1');
        $id2 = $this->_request->getParam('id2');
        $id3 = $this->_request->getParam('id3');
        $id4 = $this->_request->getParam('id4');
        $status1 = $this->_request->getParam('status1');
        $status2 = $this->_request->getParam('status2');
        $status3 = $this->_request->getParam('status3');
        $status4 = $this->_request->getParam('status4');
        $pcid = $this->_request->getParam('pcid');

        echo '球队id=><br/>id1:'.$id1.'<br/>'.'id2:'.$id2.'<br/>'.'id3:'.$id3.'<br/>'.'id4:'.$id4.'<br/>';
        echo '<br/>球队成绩=>(0,未开始;1,主队胜;2,客队胜;3,平局)<br/>status1:'.$status1.'<br/>'.'status2:'.$status2.'<br/>'.'status3:'.$status3.'<br/>'.'status4:'.$status4.'<br/>';
        echo '<br/>批次Id:'.$pcid.'<br/><br/>';
        try {
	        $dalTest = Dal_Island_Test::getDefaultInstance();
	        if ( $id1 > 0 ) {
	        	$dalTest->updateFifaResult($id1, $status1);
	        }
	        if ( $id2 > 0 ) {
	            $dalTest->updateFifaResult($id2, $status2);
	        }
	        if ( $id3 > 0 ) {
	            $dalTest->updateFifaResult($id3, $status3);
	        }
	        if ( $id4 > 0 ) {
	            $dalTest->updateFifaResult($id4, $status4);
	        }

	        $dalMongoFifa = Dal_Mongo_Fifa::getDefaultInstance();
	        $dalMongoFifa->insertFifaResult(array('pcid' => $pcid));

            Bll_Cache_Island_Fifa::clearCache('2getGameInfoById', $id1);
            Bll_Cache_Island_Fifa::clearCache('2getGameInfoById', $id2);
            Bll_Cache_Island_Fifa::clearCache('2getGameInfoById', $id3);
            Bll_Cache_Island_Fifa::clearCache('2getGameInfoById', $id4);
            
            for ( $i=1; $i<25; $i++ ) {
            	for ( $j=1; $j<25; $j++) {
            		$key = array($i, $j);
            		Bll_Cache_Island_Fifa::clearCache('getGroupWinInfo', $key);
            	}
            }
            
	        echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }

    function getuserinviteAction()
    {
        $uid = $this->_request->getParam('uid');

        //check user invite count
        $dalInviteCount = Dal_Mongo_InviteCount::getDefaultInstance();
        $inviteCount = $dalInviteCount->getCount($uid);

        echo '邀请数：'.$inviteCount;
    }

    function clearusercacheAction()
    {
    	try {
	        $uid = $this->_request->getParam('uid');
	        Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
	        Bll_Cache_Island_User::clearCache('getUsingBuilding', $uid);
	        Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
	        Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
	        Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
	        Bll_Cache_Island_Dock::clearCache('getUserPositionCount', $uid);
	        Bll_Cache_Island_User::clearCache('getUserShipList', $uid);
	        Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
            Bll_Cache_Island_User::clearCache('getUserLastPlantTime', $uid);
    	
            echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }

    function getfriendsAction()
    {
        $bllTest = new Bll_Island_Test();

        $uid = $this->uid;
        $rankResult = $bllTest->loadFriends($uid);

        echo Zend_Json::encode($rankResult);
    }

    function getinviteAction()
    {
    	$dalTest = Dal_Mongo_Test::getDefaultInstance();
    	$allInviteCount = $dalTest->getAllInviteCount();
    	$completeInviteCount = $dalTest->getCompleteInviteCount();

    	echo '总邀请数：'.$allInviteCount;
    	echo '<br/>';
    	echo '通过邀请链接成功进入数：'.$completeInviteCount;
    }

    function betchAction()
    {
    	$time = $this->_request->getParam('time', time());
    	try {
	        $bllBatchWork = new Bll_Island_BatchWork();
            $bllBatchWork->doComputeByDay($time, 'mixi');

	        echo 1;
    	}
        catch (Exception $e) {
            echo -1;
        }
    }

    function updatevisitorAction()
    {
    	try {
	    	$uid = $this->_request->getParam('uid');
	    	$dalUser = Dal_Island_User::getDefaultInstance();
	    	$islandUser = $dalUser->getUserLevelInfo($uid);

	        //get island info
	        $islandLevel = Bll_Cache_Island::getIslandLevelInfo($islandUser['island_level']);

	        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
	        //update user achievement island visitor count
	        $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $islandLevel['visitor_count']));
	        echo 1;
    	}
    	catch (Exception $e) {
    		echo -1;
    	}
    }

    function getachievementAction()
    {
        $uid = $this->_request->getParam('uid');
        $dalAchievement = Dal_Mongo_Achievement::getDefaultInstance();
        $info = $dalAchievement->getUserAchievement($uid);
        $today = $dalAchievement->getUserTodayAchievement($uid);
        $result = array('all' => $info,'today' => $today);
        echo Zend_Json::encode($result);
    }

    public function getappfriendsAction()
    {
        $uid = $this->_request->getParam('uid');
        try {
            $renren = Xiaonei_Renren::getInstance();
            $renren->setUser($uid, $_SESSION['session_key']);
            $fids = $renren->getFriendIds();
            print_r($fids);
            echo '<br/>';
            if ($fids !== null) {
                $oldfids = Bll_Friend::getFriends($uid);
                $updatefriends = true;
                if (!empty($oldfids)) {
                    print_r($oldfids);
                    echo '<br/>';
                    if (count($fids) == count($oldfids)) {
                        $diff = array_diff($fids, $oldfids);
                        if (empty($diff)) {
                            $updatefriends = false;
                        }
                    }
                }
                if ($updatefriends) {
                    echo 'updatefriends';
                    Bll_Friend::updateFriends($uid, $fids);
                } else {
                    echo 'same';
                }
            }

        }catch (Exception $e) {
            print_r($e->getMessage());
        }

        echo $uid;
        exit;
    }

    function getfriendAction()
    {
        $uid = $this->_request->getParam('uid');
        $fid = $this->_request->getParam('fid');

        $isFriend = Bll_Friend::isFriend($uid, $fid);
        $isFriend2 = Bll_Friend::isFriend($fid, $uid);

        $myList = Bll_Friend::getFriendIds($uid);
        $friendList = Bll_Friend::getFriendIds($fid);

        $dalFriend = Dal_Mongo_Friend::getDefaultInstance();
        $myMongoList = $dalFriend->getFriends($uid);
        $friendMongoList = $dalFriend->getFriends($fid);

        $result = array('uid' => $uid,
                        'fid' => $fid,
                        'isMyFriend' => $isFriend,
                        'isHisFriend' => $isFriend2,
                        'myList' => $myList,
                        'myMongoList' => $myMongoList,
                        'hisList' => $friendList,
                        'hisMongoList' => $friendMongoList);
        echo Zend_Json::encode($result);
    }

    function getuserlistAction()
    {
        $uid = $this->_request->getParam('uid', $this->uid);
        $bllTest = new Bll_Island_Test();

        $result = $bllTest->getAbnormality($uid);
        echo Zend_Json::encode($result);
    }
    
    function addindexallAction()
    {
        try {
            $dalTest = Dal_Mongo_Test::getDefaultInstance();
            $dalTest->addIndexAll($month, $dayStart, $dayEnd);
            echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }

    function addindexmoochAction()
    {
        try {
            $dalTest2 = Dal_Mongo_Test2::getDefaultInstance();
            $dalTest2->addIndexMooch();
            echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }
    
    function addindexAction()
    {
        $month = $this->_request->getParam('month');
        $dayStart = $this->_request->getParam('dayStart');
        $dayEnd = $this->_request->getParam('dayEnd');
        try {
	        $dalTest = Dal_Mongo_Test::getDefaultInstance();
	        $dalTest->addIndex($month, $dayStart, $dayEnd);
            echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }

    function droptableAction()
    {
        $month = $this->_request->getParam('month');
        $dayStart = $this->_request->getParam('dayStart');
        $dayEnd = $this->_request->getParam('dayEnd');
        try {
	        $dalTest = Dal_Mongo_Test::getDefaultInstance();
	        $dalTest->dropTable($month, $dayStart, $dayEnd);
            echo '成功！';
        }
        catch (Exception $e) {
            echo '没有成功，请再来一次！';
        }
    }

    function addusershipallAction()
    {
        $start = $this->_request->getParam('start', 0);
        $end = $this->_request->getParam('end', 1000);
        $dalTest = Dal_Island_Test::getDefaultInstance();
        $userList = $dalTest->getUserList($start, $end);

        $bllDock = new Bll_Island_Dock();
        foreach ($userList as $uid) {
            $bllDock->addUserShip($uid['uid']);
        }
    }

    function usermoochAction()
    {
        $uid = $this->_request->getParam('uid');

        $dalMooch = Dal_Mongo_Mooch2::getDefaultInstance();

        $itemIdList = array();
        $itemIdList[] = '1';
        $itemIdList[] = '2';

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        $plants = $dalPlant->getUsingPlant($this->uid);

        $itemIdList = array();
        for ( $i=0,$iCount=count($plants); $i<$iCount; $i++ ) {
            $itemId = substr($plants[$i]['id'], 0, -3);
            $itemIdList[] = $itemId;
            $plants[$i]['itemId'] = $itemId;
        }

        $moochArray = $dalMooch->getPlantMoochByIdList($itemIdList);
        for ( $j=0,$jCount=count($plants); $j<$jCount; $j++ ) {
            $moochUids = $moochArray[$plants[$j]['itemId']];
            if ( in_array($uid, $moochUids) ) {
                $plants[$j]['hasSteal'] = 1;
            }
            else {
                $plants[$j]['hasSteal'] = 0;
            }
            unset($plants[$j]['itemId']);
        }
    }

    function moochplantAction()
    {
        $uid = $this->_request->getParam('uid');
        $id = $this->_request->getParam('id');

        $result = -1;

        $dalMooch = Dal_Mongo_Mooch2::getDefaultInstance();
        $mooch = array('uid' => (string)$uid,'id' => (string)$id);
        $dalMooch->insertPlantMooch($mooch);

        $result = $dalMooch->getPlantMooch($uid, $id);
        echo $result;
    }

    function moochdockAction()
    {
        $uid = $this->_request->getParam('uid');
        $ownerUid = $this->_request->getParam('ouid');
        $positionId = $this->_request->getParam('pid');

        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        //get mooch info
        $moochInfo = $dalMooch->hadMoochDock($uid, $ownerUid, $positionId);

        info_log('$moochInfo-'.$moochInfo, 'mooch_dock');
        if ( $moochInfo ) {
            $result['content'] = '做人要厚道，您已经拉过一次了。';
            $result = array('result' => $result);
            return $result;
        }

        echo $result;
    }

    public function cleargmAction()
    {
        Bll_Cache_Island::cleanIslandShop('initIsland319806839319806839');
        Bll_Cache_Island::cleanIslandShop('initIsland402171497402171497');
        Bll_Cache_Island::cleanIslandShop('initIsland100001107412424100001107412424');
        echo 1;
    }

    public function clearmemorycacheAction()
    {
        Bll_Cache_Island::cleanIslandShop('3getPlantList');
        Bll_Cache_Island::cleanIslandShop('3getPlantListByLevel4');
        Bll_Cache_Island::cleanIslandShop('3getShopPlantList');

        //Bll_Cache_Island::cleanIslandShop('getBuildTask2036');
        Bll_Cache_Island::cleanIslandShop('5getBuildTaskList');
        Bll_Cache_Island::cleanIslandShop('5getBuildTaskNeedInfoList');
    	/*Bll_Cache_Island::cleanIslandShop('getPlantList');
    	$plantList = Bll_Cache_Island::getPlantList();
    	for ( $i=0,$iCount=count($plantList);$i<$iCount;$i++ ){
    		Bll_Cache_Island::cleanIslandShop('getPlantById', $plantList[$i]['bid']);
    	}

    	Bll_Cache_Island::cleanIslandShop('getBuildTaskList');
    	$buildTaskList = Bll_Cache_Island::getBuildTaskList();
    	for ( $j=0,$jCount=count($buildTaskList);$j<$jCount;$j++ ) {
    		Bll_Cache_Island::cleanIslandShop('getBuildTask', $buildTaskList[$j]['taskClassId']);
    	}*/
    	echo 1;
    }

    public function changehelpAction()
    {
        $help = '2';
        $newHelp = (int)$help;
    	info_log('changehelp-5','test');
    	$changeUid = $this->_request->getParam('changeUid');

        $bllTest = new Bll_Island_Test();
        info_log('changehelp-6','test');
        $bllTest->changeHelp($changeUid);
    }

    public function copyuserAction()
    {
        $oldUid = $this->_request->getParam('oldUid');
        $newUid = $this->_request->getParam('newUid');
        $password = $this->_request->getParam('password');
        if ( $password != '496700' ) {
            return ;
        }

        $bllTest = new Bll_Island_Test();
        $bllTest->copyUser($oldUid, $newUid);
        echo true;
    }

    public function clearuserAction()
    {
        $uid = $this->_request->getParam('uid');

        $key = Bll_Cache_User::getCacheKey('isUpdated', $uid);
        Bll_Cache::delete($key);

        Bll_Cache_User::cleanPerson($uid);

        Bll_Cache_User::cleanFriends($uid);
    }

    public function deleteuserAction()
    {
    	try {
	        $uid = $this->_request->getParam('uid');
	        $manage = $this->_request->getParam('manageUid');
	        if ( $manage != '496700' ) {
	            return ;
	        }

	        $dalUser = Dal_Island_User::getDefaultInstance();
	        $dalUser->deleteUser($uid);

	        $dalDock = Dal_Island_Dock::getDefaultInstance();
	        $dalDock->deleteUserShip($uid);

	        //delete task
	        $dalMongoTask = Dal_Mongo_Task::getDefaultInstance();
	        $dalMongoTask->deleteTask($uid);
	        //delete title
	        $dalMongoTitle = Dal_Mongo_Title::getDefaultInstance();
	        $dalMongoTitle->deleteTitle($uid);
	        //delete feed
	        $dalMongoFeed = Dal_Mongo_Feed::getDefaultInstance();
	        //update user feed status
	        $dalMongoFeed->updateFeedStatus($uid, true);
	        $dalMongoFeed->deleteFeed($uid, true);
	        //delete visit
	        $dalMongoVisit = Dal_Mongo_Visit::getDefaultInstance();
	        $dalMongoVisit->deleteVisit($uid);
	        //delete mooch
	        $dalMongoMooch = Dal_Mongo_Mooch::getDefaultInstance();
	        $dalMongoMooch->deletePlantMoochByUid($uid);
	        $dalMongoMooch->deleteDockMoochByUid($uid);
	        //delete mooch
	        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
	        $dalMongoAchievement->deleteUserTodayAchievement($uid);
	        $dalMongoAchievement->deleteUserAchievement($uid);
	        
	        Bll_Cache::delete(Bll_Cache_User::getCacheKey('isAppUser', $uid));
            Bll_Cache_Island_User::clearCache('getUsingBackground', $uid);
            Bll_Cache_Island_User::clearCache('getUsingBuilding', $uid);
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
            Bll_Cache_Island_Dock::clearCache('getUserPositionCount', $uid);
            Bll_Cache_Island_User::clearCache('getUserShipList', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
            Bll_Cache_Island_User::clearCache('getUserHelpInfo', $uid);
            
	        echo 1;
        }
        catch (Exception $e) {
            echo -1;
        }
    }

    public function clearcacheAction()
    {
        $memcache_obj = new Memcache;
        $memcache_obj->addServer('192.168.0.86', 11211);
        $memcache_obj->addServer('192.168.0.87', 11211);
        //$memcache_obj->addServer('127.0.0.1', 11311);

        $memcache_obj->flush();
    }

    public function testclearcacheAction()
    {
        $memcache_obj = new Memcache;
        $memcache_obj->addServer('127.0.0.1', 11211);

        $memcache_obj->flush();
    }

    public function taobaoclearcacheAction()
    {
        $memcache_obj = new Memcache;
        $memcache_obj->addServer('192.168.15.1', 11211);
        $memcache_obj->addServer('192.168.15.5', 11211);
        $memcache_obj->addServer('192.168.15.6', 11211);
        $memcache_obj->flush();
    }

    public function fbclearcacheAction()
    {
        $memcache_obj = new Memcache;
        $memcache_obj->addServer('10.67.223.42', 11211);
        $memcache_obj->addServer('10.67.223.44', 11211);
        $memcache_obj->flush();
    }

    public function testtaobaoclearcacheAction()
    {
        $memcache_obj = new Memcache;
        $memcache_obj->addServer('127.0.0.1', 11311);

        $memcache_obj->flush();
    }

    public function addgiftAction()
    {
        require_once 'Bll/Island/Gift.php';
        $bllGift = new Bll_Island_Gift();
        $bllGift->insertGift(20705296, 94640398, 6321);
    }

    public function publishfeedAction()
    {
        $type = $this->_request->getParam('type');

        if ($type) {
            if ($type == 'USER_LEVEL_UP') {
                Bll_Island_Activity::send('USER_LEVEL_UP', $this->uid, array('level' => 18));
            } else if($type == 'ISLAND_LEVEL_UP') {
                Bll_Island_Activity::send('ISLAND_LEVEL_UP', $this->uid, array('level' => 8));
            } else if($type == 'BUILDING_LEVEL_UP') {
                Bll_Island_Activity::send('BUILDING_LEVEL_UP', $this->uid);
            } else if($type == 'BOAT_LEVEL_UP') {
                Bll_Island_Activity::send('BOAT_LEVEL_UP', $this->uid);
            } else if($type == 'DOCK_EXPANSION') {
                Bll_Island_Activity::send('DOCK_EXPANSION', $this->uid);
            } else if($type == 'MISSION_COMPLETE') {
                Bll_Island_Activity::send('MISSION_COMPLETE', $this->uid);
            } else if($type == 'USER_OBTAIN_TITLE') {
                Bll_Island_Activity::send('USER_OBTAIN_TITLE', $this->uid, array('title' => 'DIY能手'));
            } else if($type == 'BUILDING_DAMAGE') {
                Bll_Island_Activity::send('BUILDING_DAMAGE', $this->uid, array('building' => '蛋糕店'), 23608306);
            } else if($type == 'APP_JOIN') {
                Bll_Island_Activity::send('APP_JOIN', $this->uid);
            }
        }

        exit;
    }

    public function redistestAction()
    {
        $medias = array(
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
        );

        $context = array(
            'uid' => 20705296,
            'session' => 'adsfasdfsdfsdafsadfsda',
            'data' => array(
                'body' => '${actor}在快乐岛主中升到了10级，赶快去看看吧~',
                'params' => array('medias' => Zend_Json::encode($medias))
            )
        );

        $queue = new MyLib_Redis_Queue('activity', '192.168.0.102');
        $queue->push($context);
        exit;
    }

    public function redispopAction()
    {
        $queue = new MyLib_Redis_Queue('activity', '192.168.0.102');
        $data = $queue->pop();

        print_r($data);
        exit;
    }

    public function publishmsgAction()
    {
        $to_uid = 94640398;
        $uid = $this->uid;

        $content = '胡立军在<a href="http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234&invite=true&invitor='
                    . $to_uid . '&inviter_id=' . $uid . '">快乐岛主</a>中邀请你去Ta的岛上做客，费用全包哦~赶快动身吧！';

        $taobao = new Taobao_Rest('12029234', '96ad573ff3fef48a84b3fcf7e7da605c', 12029234, 'island');
        $taobao->setUser($this->uid, $_SESSION['session']);

        $type = 1;

        try {
            $taobao->jianghu->msg_publish($to_uid, $content, $type);
        }catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;

    }

    public function onlineAction()
    {
        $uid = $this->uid;
        $taobao = new Taobao_Rest('12029234', '96ad573ff3fef48a84b3fcf7e7da605c', 12029234, 'island');

        $taobao->setUser($uid, $_SESSION['session']);

        try {
            $bool = $taobao->jianghu->friends_areFriends($uid, $uid);

            print_r($bool);
        }catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;
    }

    public function md5Action()
    {
        $start = microtime(true);
        for($i = 0; $i < 10000; $i++) {
            md5('taobao_test_' . $i);
        }

        echo microtime(true) - $start;
        exit;
    }

    public function sendmsgAction()
    {
        $type = $this->_request->getParam('type');
        if ($type) {
            if ($type == 'INVITE') {
                Bll_Island_Message::send('INVITE', $this->uid, 94640398);
            } else if($type == 'GIFT') {
                Bll_Island_Message::send('GIFT', $this->uid, 94640398, array('gift_id' => 6321));
            }
        }

        exit;
    }

    public function clearAction()
    {
        for($i=1;$i<=11;$i++) {
            Bll_Cache_FeedTemplate::clearInfo(1, $i);
        }

        echo 'OK';

        exit;
    }

    public function sessionAction()
    {
        echo 'session.cookie_lifetime: ' . ini_get('session.cookie_lifetime') . '<br/>';
        echo 'session.gc_maxlifetime: ' . ini_get('session.gc_maxlifetime') . '<br/>';
        echo 'session.save_handler: ' . ini_get('session.save_handler') . '<br/>';
        echo 'session.save_path: ' . ini_get('session.save_path') . '<br/>';
        exit;
    }

    public function addmongouserAction()
    {
        $bllTest = new Bll_Island_Test();
        $bllTest->addMongoUser();
    }

    public function addmongofriendAction()
    {
        $n = $this->_request->getParam('num');
        $count = $this->_request->getParam('count', 100000);

        $bllTest = new Bll_Island_Test();

        $bllTest->addMongoUser($n, $count);

        $bllTest->addMongoFriend($n, $count);
    }

    public function apitestAction()
    {
        $m = $this->_request->getParam('m');
        if($m == 'user') {
    	    $uid = $this->uid;
    	    $renren = Xiaonei_Renren::getInstance();

    	    $renren->setUser($uid, $_SESSION['session_key']);

        	try {
                $ids = $renren->getFriendIds();
                print_r($ids);
            }catch (Exception $e) {
                print_r($e->getMessage());
            }
        } else if($m == 'fids') {
     	    $uid = $this->uid;
    	    $renren = Xiaonei_Renren::getInstance();

    	    $renren->setUser($uid, $_SESSION['session_key']);

        	try {
                $ids = $renren->client->friends_getAppFriends();
                print_r($ids);
            }catch (Exception $e) {
                 print_r($e->getMessage());
            }
        }

        exit;
    }

    public function checkfriendAction()
    {
    	$uid = $this->_request->getParam('uid');
    	info_log('-1', 'checkFriend');

    	try {
    		$this->_renren = Xiaonei_Renren::getInstance();
            $fids = $this->_renren->getFriendIds();
            info_log('-2', 'checkFriend');

            if ($fids !== null) {
                $oldfids = Bll_Friend::getFriends($uid);
                $updatefriends = true;
                info_log('1', 'checkFriend');
                if (!empty($oldfids)) {
                info_log('2', 'checkFriend');
                    if (count($fids) == count($oldfids)) {
                info_log('3', 'checkFriend');
                        $diff = array_diff($fids, $oldfids);
                        if (empty($diff)) {
                info_log('4', 'checkFriend');
                            $updatefriends = false;
                        }
                    }
                }

                if ($updatefriends) {
                info_log('5', 'checkFriend');
                    Bll_Friend::updateFriends($uid, $fids);
                    //update user acheviment friends numner count
                    $bllDock = new Bll_Island_Dock();
                    $bllDock->getPower($uid);
                }
            }

            Bll_Cache_User::setUpdated($uid);

            echo 1;
    	}catch (Exception $e) {
            echo $e->getMessage();
        }
    }

 }
