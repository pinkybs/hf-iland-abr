<?php

class Hapyfish2_Island_Bll_Friend
{
	public static function getRankList($uid, $pageIndex = 1, $pageSize = 50)
	{
        require_once(CONFIG_DIR . '/language.php');
		$friendList = array();
		$friendList[] = array(
			'uid' => 134,
			'name' => LANG_PLATFORM_BASE_TXT_14,
			'face' => STATIC_HOST . '/apps/island/images/lele2.jpg',
			'exp' => 999999999,
			'level' => 99,
			'canSteal' => 0
		);
		$robot = Hapyfish2_Island_Bll_Robot::getFriendList($uid);
		if($robot){
			foreach($robot as $k => $v){
				$friendList[] = $v;
			}
		}
		$fids = Hapyfish2_Platform_Bll_Friend::getFriendIds($uid);
		if (empty($fids)) {
			$fids = array($uid);
		} else {
			$fids[] = $uid;
		}

		foreach ($fids as $fid) {
			$userInfo = Hapyfish2_Island_HFC_User::getUser($fid, array('exp' => 1, 'level' => 1));
			if ($userInfo) {
				$info = Hapyfish2_Platform_Bll_User::getUser($fid);
				$friendList[] = array(
					'uid' => $fid,
					'name' => $info['name'],
					'face' => $info['figureurl'],
					'exp' => $userInfo['exp'],
					'level' => $userInfo['level'],
					'canSteal' => 0
				);
			}
		}

		return array('friends' => $friendList, 'maxPage' => 1);
	}

}