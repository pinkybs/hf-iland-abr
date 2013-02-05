<?php

class Hapyfish2_Island_Bll_Compensation
{
	protected $_coin;

	protected $_gold;
	protected $_fragment;

	protected $_goldAddType;

	protected $_items;

	protected $_uids;

	protected $_blockUids;

	protected $_feedTitle;

	public function __construct()
	{
		$this->_coin = 0;
		$this->_gold = 0;
		$this->_starFish = 0;
		$this->_fragment = 0;
		$this->_items = array();
		$this->_uids = array();
		$this->_blockUids = array();
		$this->_feedTitle = '';
	}

	public function setCoin($coin)
	{
		$this->_coin = $coin;
	}

	public function setGold($gold, $type=0)
	{
		$this->_gold = $gold;
		$this->_goldAddType = $type;
	}

	public function setStarFish($starFish)
	{
		$this->_starFish = $starFish;
	}


	public function setFragment($fragment)
	{
		$this->_fragment = $fragment;
	}

	public function setItem($cid, $count)
	{
		if (isset($this->_items[$cid])) {
			$this->_items[$cid] += $count;
		} else {
			$this->_items[$cid] = $count;
		}
	}

	public function setUid($uid)
	{
		$this->_uids[] = $uid;
	}

	public function setUids($uids)
	{
		foreach ($uids as $uid) {
			$this->_uids[] = $uid;
		}
	}

	public function setBlockUids($begin, $end)
	{
		$this->_blockUids[] = array('begin' => $begin, 'end' => $end);
	}

	public function setFeedTitle($title)
	{
		$this->_feedTitle = $title;
	}

	public function send($feedPrefix = '[System]')
	{
		$num = 0;
		foreach ($this->_uids as $uid) {
			$ok = $this->sendOne($uid, $feedPrefix);
			if ($ok) {
				$num++;
			}
		}

		foreach ($this->_blockUids as $block) {
			$begin = $block['begin'];
			$end = $block['end'];
			for($i = $begin; $i <= $end; $i++) {
				$ok = $this->sendOne($i, $feedPrefix);
				if ($ok) {
					$num++;
				}
			}
		}

		return $num;
	}

	public function addCard($uid, $cid, $itemType, $count = 1)
	{
		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return false;
		}

		$ok = Hapyfish2_Island_HFC_Card::addUserCard($uid, $cid, $count);

		return $ok;
	}

	public function addBackGround($uid, $cid, $itemType)
	{
		$bgInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($cid);
		if (!$bgInfo) {
			return false;
		}

		$newBackground = array(
			'uid' => $uid,
			'bgid' => $cid,
			'item_type' => $itemType,
			'buy_time' => time()
		);

		$ok = Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground);

		return $ok;
	}

	public function addBuilding($uid, $cid, $itemType)
	{
		$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($cid);
		if (!$buildingInfo) {
			return false;
		}

		$newBuilding = array(
			'uid' => $uid,
			'cid' => $cid,
			'item_type' => $itemType,
			'status' => 0,
			'buy_time' => time()
		);

		$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);

		return $ok;
	}

	public function addPlant($uid, $cid, $itemType)
	{
		$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);
		if (!$plantInfo) {
			return false;
		}

		$newPlant = array(
			'uid' => $uid,
			'cid' => $cid,
			'item_type' => $itemType,
			'item_id' => $plantInfo['item_id'],
			'level' => $plantInfo['level'],
			'status' => 0,
			'buy_time' => time()
		);

		$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);

		return $ok;
	}

	public function addItem($uid, $cid, $count = 1)
	{
		$type = substr($cid, -2);
		$itemType = substr($cid, -2, 1);

		$desp = '';
		//itemType,1x->card,2x->background,3x->plant,4x->building
		if ($itemType == 1) {
			$sCount = 0;
			for($i = 0 ; $i < $count; $i++) {
				$ok = $this->addBackground($uid, $cid, $type);
				if ($ok) {
					$sCount++;
				}
			}
			if ($sCount > 0) {
				$bgInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($cid);
				$desp = $bgInfo['name'] . ' x ' .$sCount;
			}
		} else if ($itemType == 2) {
			$sCount = 0;
			for($i = 0 ; $i < $count; $i++) {
				$ok = $this->addBuilding($uid, $cid, $type);
				if ($ok) {
					$sCount++;
				}
			}
			if ($sCount > 0) {
				$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($cid);
				$desp = $buildingInfo['name'] . ' x ' . $sCount;
			}
		} else if ($itemType == 3) {
			$sCount = 0;
			for($i = 0 ; $i < $count; $i++) {
				$ok = $this->addPlant($uid, $cid, $type);
				if ($ok) {
					$sCount++;
				}
			}
			if ($sCount > 0) {
				$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);
				$desp = $plantInfo['name'] . ' x ' . $sCount;
			}
        } else if ($itemType == 4) {
			$ok = $this->addCard($uid, $cid, $type, $count);
			if ($ok) {
				$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
				$desp = $cardInfo['name'] . ' x ' . $count;
			}
		}

		return $desp;
	}

	public function sendOne($uid, $feedPrefix)
	{
		$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
		if (!$isAppUser) {
			return false;
		}
        require_once(CONFIG_DIR . '/language.php');
		$ok = false;

		$desp = array();
		if ($this->_coin > 0) {
			$ok = Hapyfish2_Island_HFC_User::incUserCoin($uid, $this->_coin);
			if ($ok) {
				$desp[] = $this->_coin . LANG_PLATFORM_BASE_TXT_01;
			}
		}

		if ($this->_gold > 0) {
			$ok = Hapyfish2_Island_Bll_Gold::add($uid, array('gold'=>$this->_gold, 'type'=>$this->_goldAddType));
			if ($ok) {
				$desp[] = $this->_gold . LANG_PLATFORM_BASE_TXT_02;
			}
		}

		if ($this->_starFish > 0) {
			$ok = Hapyfish2_Island_Bll_StarFish::add($uid, $this->_starFish, 'daily');
			if ($ok) {
				require_once(CONFIG_DIR . '/language.php');

				$desp[] = $this->_starFish . LANG_PLATFORM_BASE_TXT_16;
			}
		}

		if($this->_fragment > 0){
			$userFragment = Hapyfish2_Island_Bll_Fragments::getUserFragments($uid);
			$fragment = $this->_fragment + $userFragment['fragment_num'];
			$ok = Hapyfish2_Island_Bll_Fragments::updateFragments($uid, array('fragment_num'=> $fragment));
			require_once(CONFIG_DIR . '/language.php');
			$desp[] = $this->_fragment . LANG_PLATFORM_BASE_TXT_19;
		}


		foreach ($this->_items as $cid => $count) {
			$str = $this->addItem($uid, $cid, $count);
			if ($str != '') {
				$desp[] = $str;
			}
		}

		if (count($desp) > 0) {
			$ok = true;
			if ($this->_feedTitle != '') {
				$title = $feedPrefix . $this->_feedTitle;
			} else {
				$title = $feedPrefix . implode(', ', $desp);
			}
			$feed = array(
				'uid' => $uid,
				'template_id' => 0,
				'actor' => 134,
				'target' => $uid,
				'type' => 3,
				'title' => array('title' => $title),
				'create_time' => time()
			);
			Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
		}

		return $ok;
	}

}