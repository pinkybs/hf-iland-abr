<?php

require_once 'Dal/Abstract.php';

class Dal_Mongo_Task extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Task
     *
     * @return Dal_Mongo_Task
     */
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
        if ( $info['type'] == 1 ) {
            $dateTime = date('Ymd');
            $name = 'user_daily_task_' . $dateTime;
        }
        else if ( $info['type'] == 2 ) {
            $name = 'user_build_task';
        }
        else {
            $name = 'user_achievement_task';
        }
        return $this->_mg->mixi_island->$name->insert($info);
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
        $taskType = substr($tid, 0, 1);
        
        if ( $taskType == 1 ) {
            $dateTime = date('Ymd');
            $name = 'user_daily_task_' . $dateTime;
        }
        else if ( $taskType == 2 ) {
            $name = 'user_build_task';
        }
        else {
            $name = 'user_achievement_task';
        }
        
        $result = $this->_mg->mixi_island->$name->findOne(array('uid' => $uid, 'tid' => $tid));
        
        if ($result) {
            return $result;
        }
        
        return false;
    }

    /**
     * get user task list 
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTaskList($uid)
    {
        $dateTime = date('Ymd');
        $name = 'user_daily_task_' . $dateTime;
        $cursor = $this->_mg->mixi_island->$name
                    ->find(array('uid' => $uid));
        
        $dailyTask = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            $dailyTask[] = $v;
        }
    
        $cursor = $this->_mg->mixi_island->user_build_task
                    ->find(array('uid' => $uid));
        
        $buildTask = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            $buildTask[] = $v;
        }
    
        $cursor = $this->_mg->mixi_island->user_achievement_task
                    ->find(array('uid' => $uid));
        
        $achievementTask = array();
        while($cursor->hasNext()) {
            $v = $cursor->getNext();
            $achievementTask[] = $v;
        }
        
        $userTaskList = array_merge($dailyTask, $buildTask, $achievementTask);
        return $userTaskList;
    }

    /**
     * delete user task info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteTask($uid)
    {
        $dateTime = date('Ymd');
        $name = 'user_daily_task_' . $dateTime;
        $this->_mg->mixi_island->$name->remove(array('uid' => $uid));
        
        $this->_mg->mixi_island->user_build_task->remove(array('uid' => $uid));
        
        $this->_mg->mixi_island->user_achievement_task->remove(array('uid' => $uid));
    }
}