<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/09    Liz
 */
class Dal_Island_Task extends Dal_Abstract
{
    protected static $_instance;

    /**
     * table name
     *
     * @var string
     */
    protected $table_task_achievement = 'island_task_achievement';

    /**
     * table name
     *
     * @var string
     */
    protected $table_task_build = 'island_task_build';

    /**
     * table name
     *
     * @var string
     */
    protected $table_task_daily = 'island_task_daily';

    /**
     * table name
     *
     * @var string
     */
    protected $table_user_task = 'island_user_task';

    /**
     * table name
     *
     * @var string
     */
    protected $table_user_title = 'island_user_title';

    /**
     * table name
     *
     * @var string
     */
    protected $table_title = 'island_title';

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user task info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserTask($info)
    {
        $this->_wdb->insert($this->table_user_task, $info);
    }

	/**
     * insert user title info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserTitle($info)
    {
        $this->_wdb->insert($this->table_user_title, $info);
    }

    /**
     * delete user today task 
     *
     * @param integer $uid
     * @return void
     */
    public function deleteUserTodayTask($uid)
    {
        $sql = "Delete FROM $this->table_user_task WHERE type=1 AND uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * get achievement task info by id
     *
     * @param integer $id
     * @return array
     */
    public function getAchievementTask($id)
    {
        $sql = "SELECT * FROM $this->table_task_achievement WHERE id=:id ";

        return $this->_rdb->fetchRow($sql, array('id'=>$id));
    }

    /**
     * get build task info by id
     *
     * @param integer $id
     * @return array
     */
    public function getBuildTask($id)
    {
        $sql = "SELECT * FROM $this->table_task_build WHERE id=:id ";

        return $this->_rdb->fetchRow($sql, array('id'=>$id));
    }

    /**
     * get daily task info by id
     *
     * @param integer $id
     * @return array
     */
    public function getDailyTask($id)
    {
        $sql = "SELECT * FROM $this->table_task_daily WHERE id=:id ";

        return $this->_rdb->fetchRow($sql, array('id'=>$id));
    }

    /**
     * get achievement task list
     *
     * @return array
     */
    public function getAchievementTaskList()
    {
        $sql = "SELECT a.id AS taskClassId,3 AS type,a.content,a.name,a.need_field AS needType,null AS needCid,a.need_num AS needNum,
                a.level,a.need_level AS unLockLevel,a.coin AS addCoin,a.exp AS addExp,a.cid AS addItemCid,1 AS addItemNum,t.title AS addTitle,
                next_task AS nextTaskId,next_two_task AS nextTwoTaskId 
                FROM $this->table_task_achievement AS a,$this->table_title AS t WHERE a.title=t.id ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get build task list
     *
     * @return array
     */
    public function getBuildTaskList()
    {
        $sql = "SELECT id AS taskClassId,2 AS type,content,name,need_field AS needType,need_cid AS needCid,need_num AS needNum,
                level,need_level AS unLockLevel,coin AS addCoin,exp AS addExp,cid AS addItemCid,1 AS addItemNum,title AS addTitle 
                FROM $this->table_task_build ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get build task list
     *
     * @return array
     */
    public function getBuildTaskNeedInfoList()
    {
        $sql = "SELECT id AS taskClassId,2 AS type,content,name,need_field AS needType,need_cid AS needCid,need_num AS needNum,
                level,need_level AS unLockLevel,coin AS addCoin,exp AS addExp,cid AS addItemCid,1 AS addItemNum,title AS addTitle, 
                item_id,item_level  
                FROM $this->table_task_build ";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get daily task list
     *
     * @return array
     */
    public function getDailyTaskList()
    {
        $sql = "SELECT id AS taskClassId,1 AS type,content,name,need_field AS needType,null AS needCid,need_num AS needNum,
                level,need_level AS unLockLevel,coin AS addCoin,exp AS addExp,cid AS addItemCid,1 AS addItemNum,title AS addTitle 
                FROM $this->table_task_daily ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user task info by id
     *
     * @param integer $uid
     * @param integer $tid
     * @return array
     */
    public function getUserTask($uid, $tid)
    {
        $sql = "SELECT * FROM $this->table_user_task WHERE tid=:tid AND uid=:uid ";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'tid' => $tid));
    }

    /**
     * get user task list 
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTaskList($uid)
    {
        $sql = "SELECT tid FROM $this->table_user_task WHERE uid=:uid ";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get task info by title
     *
     * @param integer $title
     * @return array
     */
    public function getTaskInfoByTitle($title)
    {
        $sql = "SELECT * FROM $this->table_task_achievement WHERE title=:title ";
        return $this->_rdb->fetchRow($sql, array('title' => $title));
    }
    
    /**
     * get title info by id
     *
     * @param integer $id
     * @return array
     */
    public function getTitleById($id)
    {
        $sql = "SELECT * FROM $this->table_title WHERE id=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get title info by id
     *
     * @param integer $id
     * @return array
     */
    public function getTitleList()
    {
        $sql = "SELECT t.id,t.title AS name,a.coin,a.exp FROM $this->table_title AS t,island_task_achievement AS a 
                WHERE t.id=a.title ORDER BY id";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get user using title 
     *
     * @param integer $uid
     * @return string
     */
    public function getUserTitleByStatus($uid)
    {
        $sql = "SELECT title FROM $this->table_user_title WHERE status=1 AND uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user using title 
     *
     * @param integer $uid
     * @return string
     */
    public function getUserTitleList($uid)
    {
        $sql = "SELECT title FROM $this->table_user_title WHERE uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user title by title id 
     *
     * @param integer $uid
     * @param integer $titleId
     * @return string
     */
    public function getUserTitleByTitle($uid, $titleId)
    {
        $sql = "SELECT * FROM $this->table_user_title WHERE title=:titleId AND uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'titleId' => $titleId));
    }
}