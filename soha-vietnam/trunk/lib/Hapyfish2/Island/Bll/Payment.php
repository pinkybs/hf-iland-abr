<?php
require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Bll_Payment
{

    /**
     * pay flow
     *
     * @param string $transId
     * @param integer $amount
     * @param integer $gold
     * @param integer $uid
     * @return integer [0-儲值成功 1-儲值失敗 2-該訂單已經儲值過]
     */
    public static function pay($transId, $amount, $gold, $uid)
    {

		$ok = false;
    	try {
	    	$dalPay = Hapyfish2_Island_Dal_PayOrder::getDefaultInstance();
			$rowPay = $dalPay->getOrder($uid, $transId);
			if ($rowPay && $rowPay['status'] == 1) {
				return 2;
			}
    	} catch (Exception $e) {
			info_log($e->getMessage(), 'Island_Bll_Payment_Err');
			return 2;
		}

		try {
            //transaction pay insert
			$time = time();
			$payinfo = array();
			$payinfo['orderid'] = $transId;
			$payinfo['amount'] = $amount;
			$payinfo['gold'] = $gold;
			$payinfo['actual_gold'] = self::_getActualGold($gold);
			$payinfo['order_time'] = $time;
			$payinfo['status'] = 1;
			$payinfo['complete_time'] = $time;
			$payinfo['uid'] = $uid;
			$levInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$payinfo['user_level'] = 0;
			if ($levInfo) {
				$payinfo['user_level'] = $levInfo['level'];
			}
    		$dalPay->regOrder($uid, $payinfo);

    		$userGoldBefore = Hapyfish2_Island_HFC_User::getUserGold($uid);
    		//更新充值记录
    		$loginfo = array(
				'uid' => $uid, 'orderid' => $transId, 'pid' => $transId,
				'amount' => $amount, 'gold' => $payinfo['actual_gold'],
				'create_time' => $time, 'user_level' => $payinfo['user_level'],
				'pay_before_gold' => $userGoldBefore,
				'summary' => $amount.LANG_PLATFORM_BASE_TXT_10.$payinfo['actual_gold'].LANG_PLATFORM_BASE_TXT_02
			);
			$dalPaymentLog = Hapyfish2_Island_Dal_PaymentLog::getDefaultInstance();
			$dalPaymentLog->insert($uid, $loginfo);
			$ok = true;
		} catch (Exception $e) {
		    info_log('[' . $uid . ':' . $transId . ']' . $e->getMessage(), 'payment.err.confirm.1');
		    return 1;
		}

		if ($ok) {
			//发宝石
    		try {
    			$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
    			$dalUser->incGold($uid, $payinfo['actual_gold']);
    			Hapyfish2_Island_HFC_User::reloadUserGold($uid);
    		} catch (Exception $e) {
    			info_log('[' . $uid . ':' . $transId . ']' . $e->getMessage(), 'payment.err.confirm.2');
    			return 1;
    		}

    		//一元店充值信息
    		if ($gold <= 10) {
				Hapyfish2_Island_Event_Bll_OneGoldShop::setPayInfo($uid);
    		} else {
    			//充值送礼物
    			self::_sendAdditionItem($gold, $uid);
    		}

    		return 0;
		}

		return 1;
    }

	private static function _getActualGold($gold)
	{
		return $gold;
		/*$rtnGold = $gold;

//		//单身节加码
//		$changeTime = strtotime('2011-11-12 23:59:59');
//		
//		$nowTime = time();
//		
//		if ($nowTime <= $changeTime) {
//			//计算加送宝石数
//			if ($gold >= 180) {
//				$rtnGold += 150;
//			} else if($gold >= 60) {
//				$rtnGold += 40;
//			} else if($gold >= 30) {
//				$rtnGold += 15;
//			} else if($gold >= 18) {
//				$rtnGold += 6;
//			}
//		} else {
			//计算加送宝石数
			if ($gold >= 183) {
				$rtnGold += 90;
			} else if($gold >= 54) {
				$rtnGold += 25;
			} else if($gold >= 27) {
				$rtnGold += 10;
			} else if($gold >= 13) {
				$rtnGold += 4;
			}
//		}

		return $rtnGold;*/
	}

    private static function _sendAdditionItem($gold, $uid)
    {
    	$robot = new Hapyfish2_Island_Bll_Compensation();
    	$robot->setUid($uid);
    	
//    	//单身节加码
//		$changeTime = strtotime('2011-11-12 23:59:59');
//		
//		$nowTime = time();
//    	
//		if ($nowTime <= $changeTime) {
//			if ($gold >= 180) {
//				$robot->setItem(55141, 40);
//				$robot->setItem(120632, 1);
//				$robot->setItem(120732, 1);
//			} else if ($gold >= 60) {
//				$robot->setItem(55141, 10);
//				$robot->setItem(120632, 1);
//				$robot->setItem(120732, 1);
//			} else if ($gold >= 30) {
//				$robot->setItem(55141, 4);
//				$robot->setItem(120732, 1);
//			} else if ($gold >= 18) {
//				$robot->setItem(55141, 2);
//				$robot->setItem(120632, 1);
//			}
//		} else {
/*
			if ($gold >= 183) {
				$robot->setItem(111441, 60);
				$robot->setItem(126932, 1);
				$robot->setItem(126832, 1);
			} else if ($gold >= 54) {
				$robot->setItem(111441, 20);
				$robot->setItem(126932, 1);
				$robot->setItem(126832, 1);
			} else if ($gold >= 27) {
				$robot->setItem(111441, 8);
				$robot->setItem(126932, 1);
			} else if ($gold >= 13) {
				$robot->setItem(111441, 3);
				$robot->setItem(126832, 1);
			}
		*/	
    		if ($gold >= 800) {
				$robot->setItem(41521, 1);
				$robot->setItem(41621, 1);
				$robot->setItem(41721, 1);
			} else if ($gold >= 400) {
				$robot->setItem(41521, 1);
				$robot->setItem(41621, 1);
			} else if ($gold >= 200) {
				$robot->setItem(41521, 1);
			} 	
//		}

		$robot->send(LANG_PLATFORM_INDEX_TXT_23);

		return true;
    }

}