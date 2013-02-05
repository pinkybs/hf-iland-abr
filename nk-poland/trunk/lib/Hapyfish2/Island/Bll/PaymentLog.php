<?php

class Hapyfish2_Island_Bll_PaymentLog
{
	public static function getPayment($uid, $limit = 50)
	{
		try {
			$dalLog = Hapyfish2_Island_Dal_PaymentLog::getDefaultInstance();
			return $dalLog->getPayment($uid, $limit);
		} catch (Exception $e) {

		}

		return null;
	}

	public static function getAddGoldLog($uid, $limit)
	{
		try {
			$dalLog = Hapyfish2_Island_Dal_AddGoldLog::getDefaultInstance();
			return $dalLog->getAll($uid, $limit);
		} catch (Exception $e) {

		}

		return null;
	}
}