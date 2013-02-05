<?php

class Hapyfish2_Island_Bll_Invite
{
    const CONNECT_TIMEOUT = 4;
    const TIMEOUT = 4;
    const DNS_CACHE_TIMEOUT = 600;
    const RETRIES = 3;

	public static function add($inviteUid, $newUid, $time = null)
	{
	    $log = Hapyfish2_Island_Bll_InviteLog::getAll($inviteUid);
		foreach ($log as $f) {
			if ($f['fid'] == $newUid) {
				return false;
			}
		}

		if (!$time) {
			$time = time();
		}

		Hapyfish2_Island_Bll_InviteLog::add($inviteUid, $newUid, $time);
		Hapyfish2_Island_Bll_Fragments::updateInviteNum($inviteUid);
		//add 1000 coin
		Hapyfish2_Island_HFC_User::incUserCoin($inviteUid, 1000);

		//add card
		//$ok = Hapyfish2_Island_HFC_Card::addUserCard($inviteUid, 26341, 1);
		//$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(26341);
		//add 3 starfish
		$starNum = 3;
		$ok = Hapyfish2_Island_Bll_StarFish::add($inviteUid, $starNum, '');
		$targetuser = Hapyfish2_Platform_Bll_User::getUser($newUid);

		$feed = str_replace('{*nickname*}', $targetuser['name'], LANG_PLATFORM_INDEX_TXT_22);

		if ($ok) {
			$feed = array(
				'uid' => $inviteUid,
				'actor' => $inviteUid,
				'target' => $newUid,
				'template_id' => 0,
				//'title' => array('cardName' => $cardInfo['name']),
				'title' => array('title' => $feed),
				'type' => 3,
				'create_time' => $time
			);
			Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
		} else {
			info_log('[' . $inviteUid . ':' . $newUid, 'invite_failure');
		}
	}

}