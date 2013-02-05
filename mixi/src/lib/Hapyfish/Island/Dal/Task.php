<?php

class Hapyfish_Island_Dal_Task extends Hapyfish_Island_Dal_Abstract
{
    protected $table_task_achievement = 'island_task_achievement';

    protected $table_task_build = 'island_task_build';

    protected $table_task_daily = 'island_task_daily';

    protected $table_user_task = 'island_user_task';
    
    protected $table_title = 'island_title';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Task
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get build task info by id
     *
     * @param integer $id
     * @return array
     */
    public function getBuildTask($id)
    {
        $sql = "SELECT * FROM $this->table_task_build WHERE id=:id";

        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    /**
     * get daily task info by id
     *
     * @param integer $id
     * @return array
     */
    public function getDailyTask($id)
    {
        $sql = "SELECT * FROM $this->table_task_daily WHERE id=:id";

        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    /**
     * get achievement task info by id
     *
     * @param integer $id
     * @return array
     */
    public function getAchievementTask($id)
    {
        $sql = "SELECT * FROM $this->table_task_achievement WHERE id=:id";

        return $this->_rdb->fetchRow($sql, array('id' => $id));
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
                FROM $this->table_task_achievement AS a,$this->table_title AS t WHERE a.title=t.id";
                
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
                FROM $this->table_task_build";
                
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
                FROM $this->table_task_daily";
                
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
                item_id,item_level FROM $this->table_task_build";
                
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get title info by id
     *
     * @param integer $id
     * @return array
     */
    public function getTitleById($id)
    {
        $sql = "SELECT * FROM $this->table_title WHERE id=:id";
        
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    /**
     * get task info by title
     *
     * @param integer $title
     * @return array
     */
    public function getAchievementTaskInfoByTitle($title)
    {
        $sql = "SELECT * FROM $this->table_task_achievement WHERE title=:title";
        
        return $this->_rdb->fetchRow($sql, array('title' => $title));
    }
    
}