<?php

class Hapyfish2_Island_Bll_InviteLog
{
	public static function add($uid, $fid, $t = null)
	{
		$ok = false;
		if (!$t) {
			$t = time();
		}
		$info = array(
			'uid' => $uid,
			'fid' => $fid,
			'time' => $t
		);

		Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_18', 1);

		try {
			$dalLog = Hapyfish2_Island_Dal_InviteLog::getDefaultInstance();
			$dalLog->insert($uid, $info);

			$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
			$dalUser->update($fid, array('inviter' => $uid));

			$ok = true;

		} catch (Exception $e) {

		}

		return $ok;
	}

	public static function getAll($uid)
	{
		try {
			$dalLog = Hapyfish2_Island_Dal_InviteLog::getDefaultInstance();
			return $dalLog->getAll($uid);
		} catch (Exception $e) {
		}

		return null;
	}

	public static function getAllOfFlow($uid)
	{
		//2011-05-19  开始1305730800
		$time = 1305730800;
		try {
			$dalLog = Hapyfish2_Island_Dal_InviteLog::getDefaultInstance();
			return $dalLog->getAllByTime($uid, $time);
		} catch (Exception $e) {
		}

		return null;
	}
}