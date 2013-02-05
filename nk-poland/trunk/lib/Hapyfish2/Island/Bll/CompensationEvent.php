<?php

class Hapyfish2_Island_Bll_CompensationEvent
{
	public static function gain($uid, $id)
	{
		$compensation = new Hapyfish2_Island_Bll_Compensation();
		//金币10000
		$compensation->setCoin(10000);
		//船只加速卡II 5张
		$compensation->setItem(26341, 5);
		//码头保安卡 5张
		$compensation->setItem(27141, 5);
		//2星建设卡 5张
		$compensation->setItem(56641, 5);
		//3星露营 1个
		$compensation->setItem(3132, 1);
		//$compensation->setFeedTitle('');
		$ok = $compensation->sendOne($uid, '[System]');

		if ($ok) {
			$info = array(
				'id' => $id,
				'uid' => $uid,
				'create_time' => time()
			);
			try {
				$dalCompensationLog = Hapyfish2_Island_Dal_CompensationLog::getDefaultInstance();
				$dalCompensationLog->insert($uid, $info);
			} catch (Exception $e) {
				info_log($uid . ':' . $id, 'CompensationEvent_Gain');
			}
		}
	}

	public static function isGained($uid, $id)
	{
		$result = true;

		try {
			$dalCompensationLog = Hapyfish2_Island_Dal_CompensationLog::getDefaultInstance();
			$data = $dalCompensationLog->getOne($uid, $id);
			if ($data) {
				$result = true;
			} else {
				$result = false;
			}
		} catch (Exception $e) {

		}

		return $result;
	}
}