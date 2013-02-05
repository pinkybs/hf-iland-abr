<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    xial
 */
class Dal_Island_Island extends Dal_Abstract
{
	protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user island
     *
     * @param array $info
     * @return void
     */
    public function insertIsland($info)
    {
        $this->_wdb->insert('island_user_island', $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get user using background list info
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingBackground($uid)
    {
        $sql = "SELECT * FROM island_user_background WHERE status=1 AND uid=:uid";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }


    /**
     * get user background info
     *
     * @param integer $uid
     * @param integer $bgId
     * @return array
     */
	public function getUserBackgroundByBgid($uid, $bgId)
    {
		$sql = "SELECT * FROM island_user_background WHERE bgid = :bgid AND uid=:uid";

		return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'bgid'=>$bgId));
    }
/****************************************************************************************/

    /**
     * get user island
     *
     * @param integer $uid
     * @return array
     */
    public function getUserIslandById($uid)
    {
		$sql = "SELECT * FROM island_user_island WHERE uid = :uid";

		return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }
}