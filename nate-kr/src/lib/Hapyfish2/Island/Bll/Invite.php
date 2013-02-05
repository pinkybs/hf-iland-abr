<?php

class Hapyfish2_Island_Bll_Invite
{
	public static function add($inviteUid, $newUid, $time = null)
	{
		if (!$time) {
			$time = time();
		}

		Hapyfish2_Island_Bll_InviteLog::add($inviteUid, $newUid, $time);

		//add 1000 coin
		Hapyfish2_Island_HFC_User::incUserCoin($inviteUid, 1000);

		//add card
		$ok = Hapyfish2_Island_HFC_Card::addUserCard($inviteUid, 26341, 1);
		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(26341);
		if ($ok) {
			$feed = array(
				'uid' => $inviteUid,
				'actor' => $inviteUid,
				'target' => $newUid,
				'template_id' => 7,
				'title' => array('cardName' => $cardInfo['name']),
				'type' => 3,
				'create_time' => $time
			);
			Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
		} else {
			info_log('[' . $inviteUid . ':' . $newUid, 'invite_failure');
		}
		return true;
	}

	public static function refresh($uid, $list)
	{
		if (empty($list)) {
			return;
		}

		$num1 = count($list);
		$log = Hapyfish2_Island_Bll_InviteLog::getAll($uid);
		$all = true;
		if ($log) {
			$num2 = count($log);
			if ($num1 == $num2) {
				return;
			}
			$all = false;
			$tmp = array();
			foreach ($log as $f) {
				$tmp[$f['fid']] = $f['time'];
			}
		}

		$data = array();
		foreach ($list as $v) {
			$puid = $v['uid'];
			$time = $v['mtime'];
			$user = Hapyfish2_Platform_Cache_UidMap::getUser($puid);
			if ($user) {
				$fid = $user['uid'];
				if ($all) {
					$data[$fid] = array('fid' => $fid, 'time' => strtotime($time));
				} else {
					if (!isset($tmp[$fid])) {
						$data[$fid] = array('fid' => $fid, 'time' => strtotime($time));
					}
				}
			}
		}

		if (!empty($data)) {
			foreach ($data as $user) {
				self::add($uid, $user['fid'], $user['time']);
			}
		}

	}

	public static function inviteDone($newpuid)
	{

		try {
            $dalInvite = Hapyfish2_Island_Event_Dal_InviteSend::getDefaultInstance();
            $lstInviteDone = $dalInvite->lstInviteSend($newpuid);
            if (empty($lstInviteDone)) {
                return true;
            }

            $newUser = Hapyfish2_Platform_Cache_UidMap::getUser($newpuid);
            foreach ($lstInviteDone as $key=>$row) {
                Hapyfish2_Island_Bll_Invite::add($row['uid'], $newUser['uid']);
                info_log($row['uid'] . ' invite->' . $newUser['uid'] . 'DONE!', 'Bll_Invite_logs');
            }

            //complete invite send log
            $dalInvite->update($newpuid);

		} catch (Exception $e) {
			info_log('InviteDone:err', 'Island_Bll_Invite_Err');
			info_log($e->getMessage(), 'Island_Bll_Invite_Err');
			return false;
		}
		return true;
	}
}