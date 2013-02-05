<?php

/**
 * logic's Operation
 *
 * @package    Hapyfish2/Island/SnsPlus
 * @copyright  Copyright (c) 2008 H F Inc.
 * @create      2010/07/08    zx
 */
class Hapyfish2_Island_Snsplus_Join2
{
	const LOGFILE = 'join2_';

	protected $_uid;

    /**
     * init the user's variables
     *
     * @param array $config ( config info )
     */
    public function __construct($uid)
    {
        $this->_uid = $uid;
    }

    public function setUid($uid)
    {
        $this->_uid = $uid;
    }

    //发宝石
    public function addGold($gold, $reason)
    {
        if (empty($this->_uid)) {
            return false;
        }

        $info = array();
        $now = time();
        $info['gold'] = $gold;
        $info['type'] = (int)$reason + 100;
        $info['time'] = $now;
		$rst = Hapyfish2_Island_Bll_Gold::add($this->_uid, $info);
        if ($rst) {
            //save in log
            $strDate = date('Y-m-d', $now);
            info_log($this->_uid . ',' . $gold . ',' . $now . ',' . $reason, self::LOGFILE . 'addmoney_' . $strDate);
        }

		return $rst;
    }

    //发物品
	public function addItem($itemCode, $itemNum, $reason)
    {
        if (empty($this->_uid)) {
            return false;
        }

        $now = time();
        $uid = $this->_uid;
    	$robot = new Hapyfish2_Island_Bll_Compensation();
    	$robot->setItem($itemCode, $itemNum);
		$rst = $robot->sendOne($uid, LANG_PLATFORM_EVENT_TXT_14);
        if ($rst) {
            //save in log
            $strDate = date('Y-m-d', $now);
            info_log($this->_uid . ',' . $itemCode . ',' . $itemNum . ',' . $now . ',' . $reason, self::LOGFILE . 'additem_' . $strDate);
        }

		return $rst;
    }
}