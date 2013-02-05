<?php

require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Event_Bll_SaleMall
{

	protected $_arySaleInfo;
	/*      array struct
	 		<id>109</id>特卖id
	    	<name>金福星礼包</name>
			<start_date>2011-01-21</start_date>
			<end_date>2011-02-09</end_date>
			<price>108</price>
			<price_type>2</price_type>1-coin 2-gold
			<item>
				<item_id>75231</item_id>
				<item_num>1</item_num>
			</item>
			<item>......</item>
	 * itemInfo = array('id'=>109,'name'=>'金福星礼包','start'=>'1295539200','end'=>'1297180800', 'price_type'=>1, 'price'=>108)
	 *
	*/
	public function __construct($itemInfo = null)
	{
		$this->_arySaleInfo = $itemInfo;
	}

	public function setSaleInfo($itemInfo)
	{
		$this->_arySaleInfo = $itemInfo;
	}

	/**
	 * sale shop item by gold
	 * @param : integer uid
	 * @return: array integer 1 - OK /-1 NG:not sale date / -3 NG:gold not enough  / -4 NG:param error / -5 NG:other error
	 */
	public function goldSale($uid)
	{
		if (!$this->_isInSaleDate()) {
			$resultVo['status'] = -1;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}
		if (empty($uid)) {
			$resultVo['status'] = -4;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}

		$arySale = $this->_arySaleInfo;

		//get user gold
		$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
        if (!$balanceInfo) {
        	$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_140';
        	return array('result' => $resultVo);
        }

        //is gold enough
		$userGold = $balanceInfo['balance'];
		if ($userGold < $arySale['price']) {
			$resultVo['status'] = -3;
			$resultVo['content'] = 'serverWord_140';
			return array('result' => $resultVo);
		}

		$isVip = $balanceInfo['is_vip'];
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$userLevel = $userLevelInfo['level'];

		//dec user gold
		$goldInfo = array(
			'uid' => $uid,
			'cost' => $arySale['price'],
			'summary' => LANG_PLATFORM_EVENT_TXT_23 . $arySale['name'],
			'cid' => $arySale['id'],
			'num' => 1
		);
        $ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
			
        if($ok2) {
        	//send item
			$robot = new Hapyfish2_Island_Bll_Compensation();
			foreach ($arySale['item'] as $aryItem) {
				$robot->setItem($aryItem['item_id'], $aryItem['item_num']);
			}
			$robot->setFeedTitle($arySale['name']);
			$ok3 = $robot->sendOne($uid, LANG_PLATFORM_EVENT_TXT_24);
			
			try {
				Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $goldInfo['cost']);
			
				//task id 3068,task type 19
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
				if ( $checkTask['status'] == 1 ) {
					$result['finishTaskId'] = $checkTask['finishTaskId'];
				}
	        } catch (Exception $e) {
	
	        }
			
			$resultVo['status'] = 1;
			$resultVo['goldChange'] = -$arySale['price'];
			return array('result' => $resultVo);
		} else {
			info_log(Zend_Json::encode($goldInfo), 'payorder_failure');
			$resultVo['status'] = -5;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}
	}

	/**
	 * sale shop item by coin
	 * @param : integer uid
	 * @return: array integer 1 - OK /-1 NG:not sale date / -2 NG:coin not enough / -4 NG:param error / -5 NG:other error
	 */
	public function coinSale($uid)
	{
		if (!$this->_isInSaleDate()) {
			$resultVo['status'] = -1;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}
		if (empty($uid)) {
			$resultVo['status'] = -4;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}

		$arySale = $this->_arySaleInfo;

		//get user coin
		$userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);
        if ($userCoin < $arySale['price']) {
        	$resultVo['status'] = -2;
            $resultVo['content'] = 'serverWord_137';
            return array('result' => $resultVo);
        }

		//sale
		//send item
		$robot = new Hapyfish2_Island_Bll_Compensation();
		foreach ($arySale['item'] as $aryItem) {
			$robot->setItem($aryItem['item_id'], $aryItem['item_num']);
		}
		$robot->setFeedTitle($arySale['name']);
		$ok = $robot->sendOne($uid, LANG_PLATFORM_EVENT_TXT_24);
		if ($ok) {
			$now = time();
			$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $arySale['price']);
			if ($ok2) {
				//add log
				$summary = LANG_PLATFORM_EVENT_TXT_23 . $arySale['name'];
				Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $arySale['price'], $summary, $now);
				
				//update user buy coin
		        try {
					Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $arySale['price']);
				
					//task id 3012,task type 14
					$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3001);
					if ( $checkTask['status'] == 1 ) {
						$result['finishTaskId'] = $checkTask['finishTaskId'];
					}
		        } catch (Exception $e) {
		
		        }
			}
			else {
				info_log('coinSale-decUserCoin failed!'.$uid, 'SaleMall_Err');
			}
			$resultVo['status'] = 1;
			$resultVo['coinChange'] = -$arySale['price'];
			return array('result' => $resultVo);
		}
		else {
			$resultVo['status'] = -5;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}
	}



    private function _isInSaleDate()
    {
        $arySale = $this->_arySaleInfo;
        $now = time();
        if (empty($arySale) || $now < $arySale['start'] || $now > $arySale['end']) {
        	return false;
        }
        return true;
    }


}