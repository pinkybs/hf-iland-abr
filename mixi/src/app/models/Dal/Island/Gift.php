<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/28    xial
 */
class Dal_Island_Gift extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_gift = 'island_user_gift';
	protected $table_island_gift = 'island_gift';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user position info
     *
     * @param array $info
     * @return integer
     */
    public function insertGift($info)
    {
        $this->_wdb->insert($this->table_user_gift, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update gift
     * @param integer $id
     * @param array $info
     * @return void
     */
    public function updateGift($id, $info)
    {
		$where = $this->_wdb->quoteinto('id = ?', $id);
        $this->_wdb->update($this->table_user_gift, $info, $where);
    }

    /**
     * get all send gift
     * @return array
     */
    public function getSendGiftList()
    {
		$sql = "SELECT * FROM $this->table_island_gift ORDER BY sort ";
		return $this->_rdb->fetchAll($sql);
    }

    /**
     * get gift info by id
     * @param integer $gid
     * @return array
     */
    public function getGiftById($gid)
    {
		$sql = "SELECT * FROM $this->table_island_gift WHERE gid = :gid";
		return $this->_rdb->fetchRow($sql, array('gid' => $gid));
    }

    /**
     * get user invite info by uid
     * @param array $info
     * @return array
     */
    public function getGiftInviteInfoById($info)
    {
		$sql = "SELECT * FROM $this->table_user_gift WHERE actor = :inviteUid AND target = :newUid AND status = 0";
		return $this->_rdb->fetchAll($sql, array('inviteUid' => $info['inviteUid'], 'newUid' => $info['newUid']));
    }
}