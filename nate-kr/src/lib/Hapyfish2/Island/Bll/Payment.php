<?php

class Hapyfish2_Island_Bll_Payment
{
	public static $_validTypeList = array(
			'1' => array('name' => '20다이아몬드',  'price' => 10,  'gold' => 20,  'code' => '102'),
			'2' => array('name' => '45다이아몬드',  'price' => 20,  'gold' => 45,  'code' => '202'),
			'3' => array('name' => '70다이아몬드',  'price' => 30,  'gold' => 70,  'code' => '302'),
			'4' => array('name' => '125다이아몬드', 'price' => 50,  'gold' => 125, 'code' => '402'),
			'5' => array('name' => '260다이아몬드', 'price' => 100, 'gold' => 260, 'code' => '502'),
	);

    public static function createOrderId($uid, $type)
    {
        //seconds 10 lens
        //$ticks = floor(microtime(true) * 1000);
        list($usec, $sec) = explode(" ", microtime());
        $ticks = $sec . floor($usec*1000);

        //server id
        if (defined('SERVER_ID')) {
            $serverid = SERVER_ID;
        } else {
            $serverid = '0';
        }

        return (string)$ticks . '_' . $serverid . '_' . $type . '_' . $uid;
    }

	public static function createOrder($uid, $orderId, $type)
	{

		if (!isset(self::$_validTypeList[$type])) {
			return null;
		}

		$info = self::$_validTypeList[$type];
		//$orderId = self::createOrderId($uid, $type);

		$order = array(
			'pname' => $info['name'],
			'pnumber' => 1,
			'pcode' => $info['code'],
			'amount' => $info['price'],
			'orderid' => $orderId
		);

		//add db
		$info = array(
			'orderid' => $order['orderid'],
			'amount' => $order['amount'],
			'gold' => $info['gold'],
			'create_time' => time(),
			'uid' => $uid
		);

		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$info['user_level'] = $userLevelInfo['level'];

        try {
			$dalPayOrder = Hapyfish2_Island_Dal_PayOrder::getDefaultInstance();
			$dalPayOrder->insert($uid, $info);
		} catch (Exception $e) {
			info_log($e->getMessage(), 'payorder-err');
			return null;
		}

		return $order;
	}

	public static function getOrder($uid, $orderid)
	{
		try {
			$dalPayOrder = Hapyfish2_Island_Dal_PayOrder::getDefaultInstance();
			return $dalPayOrder->getOrder($uid, $orderid);
		} catch (Exception $e) {
		    info_log($e->getMessage(), 'payorder-err');
			return null;
		}
	}

	public static function confirm($uid, $orderid, $info)
	{
		try {
			$dalPayOrder = Hapyfish2_Island_Dal_PayOrder::getDefaultInstance();
			$order = $dalPayOrder->getOrder($uid, $orderid);
		} catch (Exception $e) {
			return 2;
		}

		if (!$order) {
			return 2;
		}

		if ($order['status'] != '0') {
			return 3;
		}

		$gold = $order['gold'];
		if ($gold <= 0) {
			return -1;
		}

		$ok = false;
		//发宝石
		try {
			$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
			$dalUser->incGold($uid, $gold);
			Hapyfish2_Island_HFC_User::reloadUserGold($uid);
			$ok = true;
		} catch (Exception $e) {
			info_log('[' . $uid . ':' . $orderid . ']' . $e->getMessage(), 'payment.err.confirm.1');
			return 1;
		}

		if ($ok) {
			$time = time();
			//更新订单状态
			$updateinfo = array('pid' => $info['pid'], 'status' => 1, 'complete_time' => $time);
			$loginfo = array(
				'uid' => $uid, 'orderid' => $orderid, 'pid' => $info['pid'],
				'amount' => $order['amount'], 'gold' => $gold,
				'create_time' => $time, 'user_level' => $order['user_level'],
				'pay_before_gold' => $info['pay_before_gold']
			);
			try {
				$dalPayOrder->update($uid, $orderid, $updateinfo);
				//更新充值记录
				$dalPayLog = Hapyfish2_Island_Dal_PayLog::getDefaultInstance();
				$dalPayLog->insert($uid, $loginfo);
			} catch (Exception $e) {
				info_log('[' . $uid . ':' . $orderid . ']' . $e->getMessage(), 'payment.err.confirm.2');
			}

			try {
			    //addition item present
			    self::_sendAdditionItem($gold, $uid);
			} catch (Exception $e) {
				info_log('[' . $uid . ':' . $orderid . ']' . $e->getMessage(), 'payment.err.confirm.additem');
			}

			return 0;
		}

		return 1;
	}

	/*
20宝石
40+5宝石+招财小猫-元宝
60+10宝石+招财小猫-金币
100+25宝石+招财小猫-进财
200+60宝石+招财三猫组
	*/
    private static function _sendAdditionItem($gold, $uid)
    {

    	$robot = new Hapyfish2_Island_Bll_Compensation();
		if ($gold == 45) {
			$robot->setItem(33821, 1);
		}
		else if ($gold == 70) {
			$robot->setItem(36621, 1);
		}
		else if ($gold == 125) {
            $robot->setItem(53221, 1);
		}
    	else if ($gold == 260) {
            $robot->setItem(33821, 1);
            $robot->setItem(36621, 1);
            $robot->setItem(53221, 1);
		}
		else {
		    return true;
		}
		require_once(CONFIG_DIR . '/language.php');
		$robot->sendOne($uid, LANG_PLATFORM_PAY_TXT_201);
		return true;
    }
}