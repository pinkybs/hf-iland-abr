<?php

/**
 * api controller
 * init each index page
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/19    Liz
 */
class ApiController extends Zend_Controller_Action
{
    protected $uid;

    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
    	$auth = Zend_Auth::getInstance();

        if (!$auth->hasIdentity()) {
            $result = array('status' => '-1', 'content' => 'serverWord_101');
            echo Zend_Json::encode($result);
            exit;
        }
        
        $uid = $auth->getIdentity();
        $f = Bll_User::isFibbden($uid);
        if ($f) {
            $result = array('status' => '-1', 'content' => 'forbidden');
            echo Zend_Json::encode($result);
            exit;
        }

        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);

        $this->uid = $uid;
        //$_SESSION['active_time_last'] = $_SERVER['REQUEST_TIME'];
    }

    /**
     * init swf
     *
     */
    public function initswfAction()
    {
        require (CONFIG_DIR . '/swfconfig.php');
        echo Zend_Json::encode($swfResult);
    }
    
    /**
     * add remind Action
     *
     */
    public function addremindAction()
    {
        $uid = $this->uid;
        $fid = $this->_request->getParam('fid');
        $type = $this->_request->getParam('type');
        $content = $this->_request->getParam('content');
        
        $result = Hapyfish_Island_Bll_Remind::addRemind($uid, $fid, $content, $type);
        
        echo Zend_Json::encode($result);
    }
    
    public function readremindAction()
    {
        $pageIndex = $this->_request->getParam('pageIndex', 1);
        $pageSize = $this->_request->getParam('pageSize', 50);
        $uid = $this->uid;
        
        $remindList = Hapyfish_Island_Bll_Remind::getRemind($uid, $pageIndex, $pageSize);
        
        echo Zend_Json::encode($remindList);
    }

    /**
     * mooch visitor Action
     *
     */
    public function moochvisitorAction()
    {
        $uid = $this->uid;
        $ownerUid = $this->_request->getParam('ownerUid');
        $positionId = $this->_request->getParam('positionId');

        $id = 'moochvisitor' . $uid . $ownerUid . $positionId;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id);
        if ( !$lock ) {
            $result['status'] = -1;
            $result = array('status' => -1,'content' => 'serverWord_102');
            echo Zend_Json::encode(array('result' => $result));
            return;
        }

        $result = Hapyfish_Island_Bll_Dock::mooch($uid, $ownerUid, $positionId);

        echo Zend_Json::encode($result);
    }

    /**
     * load island Action
     *
     */
    function initislandAction()
    {
        $ownerUid = $this->_request->getParam('ownerUid', $this->uid);

        if ( $ownerUid == '28899703' ) {
            $gmIsland = Hapyfish_Island_Bll_Island::restoreInitUserIsland($ownerUid, $ownerUid);
            echo $gmIsland;
        }
        else {
            $result = Hapyfish_Island_Bll_Island::initIsland($ownerUid, $this->uid);
            echo Zend_Json::encode($result);
        }
    }

    /**
     * load island Action
     *
     */
    function diyislandAction()
    {
        $changesAry = $this->_request->getParam('changes');
        $removesAry = $this->_request->getParam('removes');

        $changesAry = Zend_Json::decode($changesAry);
        $removesAry = Zend_Json::decode($removesAry);

        $result = Hapyfish_Island_Bll_Island::diyIsland($this->uid, $changesAry, $removesAry);
        
        echo Zend_Json::encode($result);
    }

    /**
     * load shop Action
     *
     */
    function loadshopAction()
    {
    	$result = Hapyfish_Island_Bll_Shop::loadShop();

        echo Zend_Json::encode($result);
    }

    /**
     * buy item Action
     *
     */
    function buyitemAction()
    {
        $itemBoxAry = $this->_request->getParam('toItemBox');
        $islandAry = $this->_request->getParam('toIsland');

        $itemBoxAry = Zend_Json::decode($itemBoxAry);
        $islandAry = Zend_Json::decode($islandAry);

        $result = array();
        
        if (!empty($itemBoxAry)) {
            //buy item
            $result = Hapyfish_Island_Bll_Shop::buyItemArray($this->uid, $itemBoxAry);
        }
        if (!empty($islandAry)) {
            //buy Building
            $result = Hapyfish_Island_Bll_Shop::buyIslandArray($this->uid, $islandAry);
        }       

        $itemBox = Hapyfish_Island_Bll_Warehouse::loadItems($this->uid);

        $result = array('resultVo' => $result, 'items' => $itemBox);
        
        echo Zend_Json::encode($result);
    }

    /**
     * sale item Action
     *
     */
    function saleitemAction()
    {
        $itemArray = $this->_request->getParam('items');

        $id = 'saleitem' . $itemArray . $this->uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id);
        if ( !$lock ) {
            $result['status'] = -1;
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode($result);
            return;
        }

		$itemArray = Zend_Json::decode($itemArray);
		
		$result = Hapyfish_Island_Bll_Shop::saleItemArray($this->uid, $itemArray);

        $itemBox = Hapyfish_Island_Bll_Warehouse::loadItems($this->uid);

        $result = array('resultVo' => $result, 'items' => $itemBox);
        echo Zend_Json::encode($result);
    }

    /**
     * change help
     *
     */
    function changehelpAction()
    {
        $help = $this->_request->getParam('step');

        $result = Hapyfish_Island_Bll_User::changeHelp($this->uid, $help);
        
        echo Zend_Json::encode($result);
    }

    /**
     * harvest plant
     *
     */
    function harvestplantAction()
    {
        $itemId = $this->_request->getParam('itemId');

        $id = 'harvestplant' . $itemId . $this->uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id);
        if ( !$lock ) {
            $result['status'] = -1;
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode($result);
            return;
        }

        $result = Hapyfish_Island_Bll_Plant::harvestPlant($this->uid, $itemId);
        
        echo Zend_Json::encode($result);
    }

    /**
     * mooch plant
     *
     */
    function moochplantAction()
    {
        $fid = $this->_request->getParam('fid');
        $itemId = $this->_request->getParam('itemId');

        $id = 'moochplant' . $fid . $itemId . $this->uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id);
        if ( !$lock ) {
            $result['status'] = -1;
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode($result);
            return;
        }

        $result = Hapyfish_Island_Bll_Plant::moochPlant($this->uid, $fid, $itemId);
        
        echo Zend_Json::encode($result);
    }

    /**
     * manage plant
     *
     */
    function manageplantAction()
    {
        $itemId = $this->_request->getParam('itemId');
        $eventType = $this->_request->getParam('eventType');
        $ownerUid = $this->_request->getParam('ownerUid');

        $id = 'moochplant' . $itemId . $eventType . $ownerUid . $this->uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id);
        if ( !$lock ) {
            $result['status'] = -1;
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode(array('resultVo' => $result));
            return;
        }

        $result = Hapyfish_Island_Bll_Plant::managePlant($this->uid, $itemId, $eventType, $ownerUid);
        
        echo Zend_Json::encode($result);
    }

    /**
     * upgrade plant
     *
     */
    function upgradeplantAction()
    {
        $itemId = $this->_request->getParam('itemId');

        $id = 'upgradeplant' . $itemId . $this->uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id);
        if ( !$lock ) {
            $result['status'] = -1;
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode(array('resultVo' => $result));
            return;
        }

        $result = Hapyfish_Island_Bll_Plant::upgradePlant($this->uid, $itemId);
        
        echo Zend_Json::encode($result);
    }

    /**
     * finish task
     *
     */
    function finishtaskAction()
    {
        $taskId = $this->_request->getParam('taskId');

        $id = 'finishtask' . $taskId . $this->uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id, 100);
        if ( !$lock ) {
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode($result);
            return;
        }

        $result = Hapyfish_Island_Bll_Task::finishTask($this->uid, $taskId);

        //$memcached->unlock($id);

        echo Zend_Json::encode($result);
    }

    /**
     * read task
     *
     */
    function readtaskAction()
    {
    	$result = Hapyfish_Island_Bll_Task::readTask($this->uid);
        
        echo Zend_Json::encode($result);
    }

    /**
     * read task
     *
     */
    function readtitleAction()
    {
        $ownerUid = $this->_request->getParam('uid', $this->uid);

        $result = Hapyfish_Island_Bll_Task::readTitle($this->uid, $ownerUid);
        
        echo Zend_Json::encode($result);
    }

    /**
     * read task
     *
     */
    function changetitleAction()
    {
        $titleId = $this->_request->getParam('titleId');

        $result = Hapyfish_Island_Bll_Task::changeTitle($this->uid, $titleId);
        
        echo Zend_Json::encode($result);
    }

    /**
     * read ship
     *
     */
    function readshipAction()
    {
        $pid = $this->_request->getParam('positionId');
        $result = Hapyfish_Island_Bll_Dock::readShip($this->uid, $pid);
        echo Zend_Json::encode($result);
    }

    /**
     * unlock ship
     *
     */
    function unlockshipAction()
    {
        $shipId = $this->_request->getParam('boatId');
        $priceType = $this->_request->getParam('priceType');
        $pid = $this->_request->getParam('positionId');
        
        $result = Hapyfish_Island_Bll_Dock::unlockShip($this->uid, $shipId, $pid, $priceType);
        echo Zend_Json::encode($result);
    }

    /**
     * change ship
     *
     */
    function changeshipAction()
    {
        $shipId = $this->_request->getParam('boatId');
        $positionId = $this->_request->getParam('positionId');
        
        $result = Hapyfish_Island_Bll_Dock::changeShip($this->uid, $shipId, $positionId);
        
        echo Zend_Json::encode($result);
    }


    /**
     * init dock Action
     *
     */
    function initdockAction()
    {
        $ownerUid = $this->_request->getParam('ownerUid', $this->uid);
        
        $result = Hapyfish_Island_Bll_Dock::initDock($ownerUid, $this->uid);
        echo Zend_Json::encode($result);
    }

    /**
     * init user Action
     *
     */
    function inituserAction()
    {
		echo Hapyfish_Island_Cache_BasicInfo::getInitVoData();
    }

    /**
     * init user Action
     *
     */
    function inituserinfoAction()
    {
        $uid = $this->uid;
        
        $todayInfoResult = Hapyfish_Island_Bll_User::updateUserTodayInfo($uid);
        
        //get user info
        $userVo = Hapyfish_Island_Bll_User::getUserInit($uid);
        
        $userVo['signAward'] = $todayInfoResult['activityCount'];
        $userVo['news'] = true;//$todayInfoResult['showViewNews'];
        	
        //get user item box info
        $itemBox = Hapyfish_Island_Bll_Warehouse::loadItems($uid);
        
        //title info
        $title = Hapyfish_Island_Bll_Task::readTitle($uid, $uid);
        
        //system time
        $systemTime = time();
        
        $result = array('user' => $userVo, 'items' => $itemBox, 'title' => $title, 'systemTime' => $systemTime);
        
        echo Zend_Json::encode($result);
    }

    /**
     * add boat Action
     *
     */
	function addboatAction()
	{
		$result = Hapyfish_Island_Bll_Dock::addBoat($this->uid);

		echo Zend_Json::encode($result);
	}

	/**
	 * receive boat Action
	 *
	 */
	function receiveboatAction()
	{
		$pid = $this->_request->getParam('positionId');
        $uid = $this->uid;

        $result = Hapyfish_Island_Bll_Dock::receiveBoat($uid, $pid);
        
		echo Zend_Json::encode($result);
	}

	/**
	 * read card Action
	 *
	 */
	function readcardAction()
	{
		$result = Hapyfish_Island_Bll_Warehouse::loadItems($this->uid);

		echo Zend_Json::encode($result);
	}

	function usecardAction()
	{
		$cid = $this->_request->getParam('cid');
		$itemId = $this->_request->getParam('itemId');
		$onwerUid = $this->_request->getParam('ownerUid');

		$uid = $this->uid;
		$pid = $this->_request->getParam('positionId');

        $result = array();
        
		if ($pid) {
			$result = Hapyfish_Island_Bll_Card::speedCard($uid, $pid, $cid);
		} else {
			$result = Hapyfish_Island_Bll_Card::useCard($uid, $onwerUid, $cid, $itemId);
		}

		echo Zend_Json::encode($result);
	}

	/**
	 * read feed Action
	 *
	 */
	function readfeedAction()
	{
		$pageIndex = $this->_request->getParam('pageIndex', 1);
		$pageSize = $this->_request->getParam('pageSize', 50);

		$uid = $this->uid;

		$feedLst = Hapyfish_Island_Bll_Feed::getFeed($uid, $pageIndex, $pageSize);
		echo Zend_Json::encode($feedLst);
	}

	function getfriendsAction()
	{
		$pageIndex = $this->_request->getParam('pageIndex', 1);
        $pageSize = $this->_request->getParam('pageSize', 20);
        
		$bllRank = new Bll_Island_Rank();

		$uid = $this->uid;
		$rankResult = $bllRank->loadFriends($uid, $pageIndex, $pageSize);

		echo Zend_Json::encode($rankResult);
	}

	public function onlineAction()
	{
	    $uid = $this->uid;
	    $online = true;

        if (!$online) {
            $result = array('status' => '-1', 'content' => 'serverWord_101');
        } else {
            $result = array('status' => '1', 'content' => 'OK');
        }

        echo Zend_Json::encode($result);

        exit;
	}
	
	//mixi album up photo
	public function uploadpictureAction() 
	{
		$uid = $this->uid; 
		try {
			$stream = fopen($_FILES['picture']['tmp_name'], 'r');
			$output = stream_get_contents($stream);
			$tt = new Bll_TokyoTyrant($uid);
			$updone = $tt->saveObject('foruploadpicture'.$uid, $output);
			fclose($stream);			
		}
		catch (Exception $e) {
           info_log($e->getMessage(), 'uppic');
           $updone = false;
        }
        
		if ($updone) {
			$now = time();
			$picUrl = Zend_Registry::get('host') . '/callback/getpic';
			$picUrl .= '/uid/' . $uid . '/time/' . $now . '/sig/' . md5($uid.$now.APP_SECRET);
			$result = array('result' => array('status' => '1', 'content' => 'OK'), 'picurl' => $picUrl);
		}
		else {
			$result = array('result' => array('status' => '-1', 'content' => 'アップロード失敗'));
		}
        echo Zend_Json::encode($result);
        exit;
	}
 }