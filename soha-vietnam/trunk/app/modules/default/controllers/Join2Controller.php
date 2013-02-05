<?php

class Join2Controller extends Zend_Controller_Action
{
	/**
     * initialize basic data
     * @return void
     */
    public function init()
    {
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }

    protected function _echoResult($code, $message = '')
    {
    	$result = array('result' => $code, 'message' => $message);
    	echo Zend_Json::encode($result);
    	exit;
    }

    public function gamemoneyAction()
    {
    	$puid = $this->_request->getPost('uid');
    	$time = $this->_request->getPost('time');
    	$game_money = $this->_request->getPost('game_money');
    	$reason = (int)$this->_request->getPost('reason');// 原因类型 0活动奖励 / 1用户补偿 / 2物品贩卖
    	$sig = $this->_request->getPost('sig');

        if (empty($puid)){
    		$this->_echoResult(2, 'uid error');
    	}
       	if (empty($sig)){
    		$this->_echoResult(2, 'signature error');
    	}
        if (empty($game_money) || $game_money <=0 || $game_money > 5000){
    		$this->_echoResult(2, 'game_money error. allow range [1~5000]');
    	}

    	$signature = md5('game_money=' . $game_money . 'reason=' . $reason . 'time=' . $time . 'uid=' . $puid . APP_SECRET);

    	if ($signature != $sig) {
    		$this->_echoResult(2, 'signature error');
    	}

        if (!is_numeric($puid)) {
    	    $this->_echoResult(2, 'user not exist');
    	}
    	$user = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
        //$rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
    	if (empty($user)) {
            $this->_echoResult(2, 'user not exist');
    	}

    	$uid = $user['uid'];
    	$bllJoin2 = new Hapyfish2_Island_Snsplus_Join2($uid);
    	$rst = $bllJoin2->addGold($game_money, $reason);
		if ($rst) {
    		$this->_echoResult(1, 'success');
		}
		else {
			$this->_echoResult(2, 'gamemoney error');
		}
    }

    public function gameitemAction()
    {
       	$puid = $this->_request->getPost('uid');
    	$time = $this->_request->getPost('time');
    	$item_id = $this->_request->getPost('item_id');
    	$item_num = $this->_request->getPost('item_num');
    	$reason = (int)$this->_request->getPost('reason');//0活动奖励 / 1用户补偿 / 2物品贩卖
    	$sig = $this->_request->getPost('sig');

    	if (empty($puid)){
    		$this->_echoResult(2, 'uid error');
    	}
       	if (empty($sig)){
    		$this->_echoResult(2, 'signature error');
    	}

    	$signature = md5('item_id=' . $item_id . 'item_num=' . $item_num . 'reason=' . $reason . 'time=' . $time . 'uid=' . $puid . APP_SECRET);
		info_log($signature,'test_signature');
    	if ($signature != $sig) {
			$this->_echoResult(2, 'signature error');
    	}

        if (!is_numeric($puid)) {
    	    $this->_echoResult(2, 'user not exist');
    	}

    	$user = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
    	//$rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
    	if (empty($user)) {
            $this->_echoResult(2, 'user not exist');
    	}
    	$uid = $user['uid'];
    	$bllJoin2 = new Hapyfish2_Island_Snsplus_Join2($uid);
    	$rst = $bllJoin2->addItem($item_id, $item_num, $reason);
		if ($rst) {
    		$this->_echoResult(1, 'success');
		}
		else {
			$this->_echoResult(2, 'gameitem error');
		}
    }


    public function userloginAction()
    {
       	$snsplus_uid = $this->_request->getParam('snsplus_uid');
       	$platform_uid = $this->_request->getParam('platform_uid');
       	$timeline = $this->_request->getParam('timeline');
    	$time = $this->_request->getParam('time');
    	$sig = $this->_request->getParam('sig');

    	$signature = md5($snsplus_uid.$platform_uid.$timeline.$time.APP_SECRET);
        if ($signature != $sig) {
			$result = array('islogin' => 0, 'upgrade_record' => array(), 'status' => -1);
            echo Zend_Json::encode($result);
    	    exit;
    	}

    	$aryTmLine = explode('_', $timeline);
    	if (count($aryTmLine) < 2) {
    	    $result = array('islogin' => 0, 'upgrade_record' => array(), 'status' => -1);
            echo Zend_Json::encode($result);
    	    exit;
    	}

        if (!is_numeric($platform_uid)) {
    	    $result = array('islogin' => 0, 'upgrade_record' => array(), 'status' => 0);
            echo Zend_Json::encode($result);
    	    exit;
    	}

    	$user = Hapyfish2_Platform_Bll_UidMap::getUser($platform_uid);
    	//$rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
        if (empty($user)) {
            $result = array('islogin' => 0, 'upgrade_record' => array(), 'status' => 0);
            echo Zend_Json::encode($result);
    	    exit;
    	}
    	$uid = $user['uid'];

    	$sTime = $aryTmLine[0];
    	$eTime = $aryTmLine[1];
    	$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
    	if ($loginInfo['last_login_time'] < $sTime) {
    	    $result = array('islogin' => 0);
    	}
    	else {
    	    $result = array('islogin' => 1);
    	}
    	$levInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
    	$userLev = $levInfo['level'];
    	$tm = date('Y-m-d H:i:s');
    	$result['upgrade_record'] = array($tm => $userLev);
    	$result['status'] = 1;
        echo Zend_Json::encode($result);
    	exit;
    }

    public function userlevelAction()
    {
       	$snsplus_uid = $this->_request->getParam('snsplus_uid');
       	$platform_uid = $this->_request->getParam('platform_uid');
       	$timeline = $this->_request->getParam('timeline');
    	$time = $this->_request->getParam('time');
    	$sig = $this->_request->getParam('sig');

    	$signature = md5($snsplus_uid.$platform_uid.$timeline.$time.APP_SECRET);
        if ($signature != $sig) {
			$result = array('newer' => 0, 'level' =>0, 'status' => -1);
            echo Zend_Json::encode($result);
    	    exit;
    	}

    	$aryTmLine = explode('_', $timeline);
    	if (count($aryTmLine) < 2) {
    	    $result = array('newer' => 0, 'level' =>0, 'status' => -1);
            echo Zend_Json::encode($result);
    	    exit;
    	}

        if (!is_numeric($platform_uid)) {
    	    $result = array('newer' => 0, 'level' =>0, 'status' => 0);
            echo Zend_Json::encode($result);
    	    exit;
    	}

    	$user = Hapyfish2_Platform_Bll_UidMap::getUser($platform_uid);
    	//$rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
        if (empty($user)) {
            $result = array('newer' => 0, 'level' =>0, 'status' => 0);
            echo Zend_Json::encode($result);
    	    exit;
    	}
    	$uid = $user['uid'];

    	$sTime = $aryTmLine[0];
    	$eTime = $aryTmLine[1];
    	$rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
    	if ($rowUser['create_time'] < $sTime) {
    	    $result = array('newer' => 0);
    	}
    	else {
    	    $result = array('newer' => 1);
    	}

    	$levInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
    	$userLev = $levInfo['level'];
    	$result['level'] = $userLev;
    	$result['status'] = 1;
        echo Zend_Json::encode($result);
    	exit;
    }
}