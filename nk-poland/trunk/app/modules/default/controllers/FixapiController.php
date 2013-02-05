<?php

class FixapiController extends Zend_Controller_Action
{
	function vaild()
	{
		
	}
	
	function check()
	{
		$uid = $this->_request->getParam('uid');
		if (empty($uid)) {
			$this->echoError(1001, 'uid can not empty');
		}
		
		$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
		if (!$isAppUser) {
			$this->echoError(1002, 'uid error, not app user');
			exit;
		}
		
		return $uid;
	}
	
    protected function echoResult($data)
    {
    	$data['errno'] = 0;
    	echo json_encode($data);
    	exit;
    }
    
    protected function echoError($errno, $errmsg)
    {
    	$result = array('errno' => $errno, 'errmsg' => $errmsg);
    	echo json_encode($result);
    	exit;
    }
    
	public function boatAction()
	{
		$uid = $this->check();
		$id = $this->_request->getParam('id');
		if (empty($id)) {
			$this->echoError(1001, 'id can not empty');
		}
		
		$ok = false;
		$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
		$data = $dalDock->getPosition($uid, $id);
    	if ($data) {
    		$data[4] = $data[3];
    		$data[5] = 0;
    		$data[6] = 0;
    		$data[7] = 0;
    		$key = 'i:u:dock:' . $uid . ':' . $id;
    		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    		$ok = $cache->save($key, $data);
    	}

		$result = $ok ? 1 : 0;
		$data = array('result' => $result);
		$this->echoResult($data);
	}
	
	public function praiseAction()
	{
		$uid = $this->check();
		$view = $this->_request->getParam('view', '0');
		
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid);
		$plants = $data['plants'];
		$praise = 0;
		$plantInfoList = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
		foreach ($plants as $plant) {
			$praise += $plantInfoList[$plant['cid']]['add_praise'];
		}
		
		$buildings = Hapyfish2_Island_HFC_Building::getOnIsland($uid);
		$buildingInfoList = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
		foreach ($buildings as $building) {
			$praise += $buildingInfoList[$building['cid']]['add_praise'];
		}
		
		$useIsland = Hapyfish2_Island_HFC_User::getUserIsland($uid);
		$curPraise = $useIsland['praise'];
		
		$data = array('cal_praise' => $praise, 'user_praise' => $curPraise);
		
		if ($view == '1') {
			$fixed = false;
			if ($curPraise != $praise) {
				$useIsland['praise'] = $praise;
				$fixed = Hapyfish2_Island_HFC_User::updateUserIsland($uid, $useIsland, true);
			}
			if ($fixed) {
				$data['fixed'] = 1;
			} else {
				$data['fixed'] = 0;
			}
		}
		
		$this->echoResult($data);
	}
	
	public function praiseachievementAction()
	{
		$uid = $this->check();
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid);
		$plants = $data['plants'];
		$praise = 0;
		$plantInfoList = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
		foreach ($plants as $plant) {
			$praise += $plantInfoList[$plant['cid']]['add_praise'];
		}
		
		$buildings = Hapyfish2_Island_HFC_Building::getOnIsland($uid);
		$buildingInfoList = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
		foreach ($buildings as $building) {
			$praise += $buildingInfoList[$building['cid']]['add_praise'];
		}
		
		$achi = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
		if ($achi['num_14'] < $praise) {
			$old = $achi['num_14'];
			$achi['num_14'] = $praise;
			Hapyfish2_Island_HFC_Achievement::updateUserAchievement($uid, $achi);
			$data = array('fixed' => 1, 'old' => $old, 'new' => $praise);
		} else {
			$data = array('fixed' => 0);
		}
		$this->echoResult($data);
	}
	
	public function buildinginwarehouseAction()
	{
		$uid = $this->check();
		$data = Hapyfish2_Island_HFC_Building::getInWareHouse($uid);
		$count = 0;
		if ($data) {
			foreach ($data as $id => $building) {
				if ($building['status'] == 1) {
					$building['status'] = 0;
					Hapyfish2_Island_HFC_Building::updateOne($uid, $id, $building, true);
					$count++;
				}
			}
		}
		
		echo $count;
		exit;
	}
	
	public function islandAction()
	{
		$uid = $this->check();
		
		$allIds = Hapyfish2_Island_Cache_Plant::getAllIds($uid);
		print_r($allIds);
		echo '<br/>=====================<br/>';
		
		$ids = Hapyfish2_Island_Cache_Plant::getOnIslandIds($uid);
		print_r($ids);
		echo '<br/>=====================<br/>';
		
		$ids2 = Hapyfish2_Island_Cache_Plant::getInWareHouseIds($uid);
		print_r($ids2);
		echo '<br/>=====================<br/>';
		
		$plantsVO = Hapyfish2_Island_Bll_Plant::getAllOnIsland($uid, $uid);
		print_r($plantsVO);
		echo '<br/>=====================<br/>';
		
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid);
		print_r($data);
		echo '<br/>=====================<br/>';
		exit;
	}
	
	public function islandbuildingAction()
	{
		$uid = $this->check();
		
		$allIds = Hapyfish2_Island_Cache_Building::getOnIslandIds($uid);
		print_r($allIds);
		echo '<br/>=====================<br/>';
		
		$ids = Hapyfish2_Island_Cache_Building::getOnIslandIds($uid);
		print_r($ids);
		echo '<br/>=====================<br/>';
		
		$ids2 = Hapyfish2_Island_Cache_Building::getInWareHouseIds($uid);
		print_r($ids2);
		echo '<br/>=====================<br/>';
		
		$data = Hapyfish2_Island_HFC_Building::getInWareHouse($uid);
		print_r($data);
		echo '<br/>=====================<br/>';
		exit;
	}
	
	public function addachievementAction()
	{
		$uid = $this->check();
		$num = $this->_request->getParam('num');
		if (empty($num)) {
			echo 'num can not empty';
			exit; 
		}
		
		if ($num <=0 || $num > 17) {
			echo 'num error, must > 0 and < 18';
			exit; 
		}
		
		$count = $this->_request->getParam('count');
		if (empty($count) || $count <= 0) {
			echo 'add count error, must > 1';
			exit; 
		}
		
		$field = 'num_' . $num;
		Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, $field, $count);
		
		echo 'OK';
		exit;
	}

}