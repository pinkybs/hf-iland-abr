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

    	$rowUser = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
    	if (empty($rowUser)) {
            $this->_echoResult(2, 'user not exist');
    	}
    	$uid = $rowUser['uid'];
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

    	if ($signature != $sig) {
			$this->_echoResult(2, 'signature error');
    	}

    	$rowUser = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
    	if (empty($rowUser)) {
            $this->_echoResult(2, 'user not exist');
    	}
    	$uid = $rowUser['uid'];
    	$bllJoin2 = new Hapyfish2_Island_Snsplus_Join2($uid);
    	$rst = $bllJoin2->addItem($item_id, $item_num, $reason);
		if ($rst) {
    		$this->_echoResult(1, 'success');
		}
		else {
			$this->_echoResult(2, 'gameitem error');
		}
    }



}