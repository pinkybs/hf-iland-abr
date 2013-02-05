<?php

class Hapyfish_Island_Cache_Task
{
    /**
     * get build task info by id
     *
     * @return array
     */
    public static function getBuildTask($id)
    {
		$key = 'BuildTask_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$task = $cache->get($key);
		if ($task === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$task = $db->getBuildTask($id);
			if ($task) {
				$cache->add($key, $task, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $task;
    }
    
    /**
     * get daily task info by id
     *
     * @return array
     */
    public static function getDailyTask($id)
    {
		$key = 'DailyTask_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$task = $cache->get($key);
		if ($task === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$task = $db->getDailyTask($id);
			if ($task) {
				$cache->add($key, $task, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $task;
    }
    
    /**
     * get achievement task info by id
     *
     * @return array
     */
    public static function getAchievementTask($id)
    {
		$key = 'AchievementTask_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$task = $cache->get($key);
		if ($task === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$task = $db->getAchievementTask($id);
			if ($task) {
				$cache->add($key, $task, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $task;
    }
    
    /**
     * get achievement task list
     *
     * @return array
     */
    public static function getAchievementTaskList()
    {
		$key = 'AchievementTaskList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$list = $db->getAchievementTaskList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    /**
     * get build task list
     *
     * @return array
     */
    public static function getBuildTaskList()
    {
		$key = 'BuildTaskList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$list = $db->getBuildTaskList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    /**
     * get daily task list
     *
     * @return array
     */
    public static function getDailyTaskList()
    {
		$key = 'DailyTaskList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$list = $db->getDailyTaskList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    /**
     * get build task need info list
     *
     * @return array
     */
    public static function getBuildTaskNeedInfoList()
    {
		$key = 'BuildTaskNeedInfoList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$list = $db->getBuildTaskNeedInfoList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;
    }
    
    /**
     * get title info by id
     *
     * @return array
     */
    public static function getTitleById($id)
    {
		$key = 'TitleInfo_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$info = $db->getTitleById($id);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
    }
    
    /**
     * get task info by title
     *
     * @return array
     */
    public static function getAchievementTaskInfoByTitle($id)
    {
		$key = 'AchievementTaskInfoByTitle_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Task::getDefaultInstance();
			$info = $db->getAchievementTaskInfoByTitle($id);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;    	
    }

}