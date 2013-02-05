<?php
require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Bll_Payment
{

    /**
     * pay flow
     *
     * @param string $transId
     * @param string $currency
     * @param integer $amount
     * @param integer $gold
     * @param integer $uid
     * @return integer [0-儲值成功 1-儲值失敗 2-該訂單已經儲值過]
     */
    public static function pay($transId, $currency, $amount, $gold, $uid)
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
			return 1;
		}

		try {
            //transaction pay insert
			$time = time();
			$payinfo = array();
			$payinfo['orderid'] = $transId;
			$payinfo['amount'] = $amount;
			$payinfo['currency'] = $currency;
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

    		//充值送礼物
    		self::_sendAdditionItem($gold, $uid);
    		return 0;
		}

		return 1;
    }

	private static function _getActualGold($gold)
	{
		$rtnGold = $gold;

		return $rtnGold;
	}

    private static function _sendAdditionItem($gold, $uid)
    {
    	/*$robot = new Hapyfish2_Island_Bll_Compensation();
    	$robot->setUid($uid);
		if ($gold >= 120) {

			$robot->setItem(86241, 20);
			$robot->setItem(105432, 1);
		} else if ($gold >= 80) {
			$robot->setItem(86241, 10);
			$robot->setItem(105232, 1);

		} else if ($gold >= 40) {
			$robot->setItem(86241, 5);
		} else if ($gold >= 20) {
			$robot->setItem(86241, 3);
		}

		$robot->send(LANG_PLATFORM_INDEX_TXT_23);
		*/
		return true;
    }

}