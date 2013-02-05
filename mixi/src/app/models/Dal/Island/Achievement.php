<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Dal_Island_Achievement extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_achievement = 'island_user_achievement';

/**
     * table name
     *
     * @var string
     */
    protected $table_user_achievement_today = 'island_user_achievement_today';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert today achievement
     *
     * @param array $info
     * @return void
     */
    public function insertTodayAchievement($info)
    {
        $this->_wdb->insert($this->table_user_achievement_today, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user achievement
     *
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function updateUserAchievement($uid, $info)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update($this->table_user_achievement, $info, $where);
    }

    /**
     * update user achievement by field
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateUserAchievementByField($uid, $field, $change)
    {
        $sql = "UPDATE $this->table_user_achievement SET $field = $field + :change WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user today achievement by field
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateUserTodayAchievementByField($uid, $field, $change)
    {
        $sql = "UPDATE $this->table_user_achievement_today SET $field = $field + :change WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * delete user today achievement info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteUserTodayAchievement($uid)
    {
        $sql = "UPDATE $this->table_user_achievement_today SET num_1=0, num_2=0, num_3=0, num_4=0, num_5=0, num_6=0, 
                num_7=0, num_8=0 WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * get user achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserAchievement($uid)
    {
        $sql = "SELECT * FROM $this->table_user_achievement WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get user today achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTodayAchievement($uid)
    {
        $sql = "SELECT * FROM $this->table_user_achievement_today WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get user achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserAchievementByField($uid, $field)
    {
        $sql = "SELECT $field FROM $this->table_user_achievement WHERE uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * get user today achievement info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTodayAchievementByField($uid, $field)
    {
        $sql = "SELECT $field FROM $this->table_user_achievement_today WHERE uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
//****************************************************************************************

    /**
     * insert achievement
     *
     * @param array $info
     * @return void
     */
    public function insertAchievement($info)
	{
		$this->_wdb->insert($this->table_user_achievement, $info);
        return $this->_wdb->lastInsertId();
	}

	/**
	 * update user today achievement
	 *
	 * @param integer $uid
	 * @param array $info
	 * @return void
	 */
	public function updateTodayAchievement($uid, $info)
	{
		$where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update($this->table_user_achievement_today, $info, $where);
	}
}