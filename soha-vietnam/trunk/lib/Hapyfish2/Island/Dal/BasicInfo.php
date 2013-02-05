<?php

class Hapyfish2_Island_Dal_BasicInfo
{
    protected static $_instance;

    protected function getDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getBasicDB($key);
    }

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_BasicInfo
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getFeedTemplate()
    {
    	$sql = "SELECT id,title FROM island_feed_template";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchPairs($sql);
    }

    public function getShipList()
    {
    	$sql = "SELECT sid,name,start_visitor_num,safe_visitor_num,wait_time,safe_time_1,safe_time_2,class_name,coin,gem,level,getcard,cheap_price,cheap_start_time,cheap_end_time FROM island_ship";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getBuildingList()
    {
    	$sql = "SELECT cid,name,class_name,add_praise,price,price_type,sale_price,need_level,nodes,item_type,new,can_buy,cheap_price,cheap_start_time,cheap_end_time FROM island_building";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getPlantList()
    {
    	$sql = "SELECT cid,name,class_name,add_praise,price,price_type,sale_price,need_level,nodes,item_type,item_id,new,can_buy,ticket,pay_time,safe_time,safe_coin_num,need_praise,level,next_level_cid,cheap_price,cheap_start_time,cheap_end_time,act_name FROM island_plant";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getBackgroundList()
    {
    	$sql = "SELECT bgid,name,class_name,add_praise,price,price_type,sale_price,need_level,item_type,new,can_buy,cheap_price,cheap_start_time,cheap_end_time FROM island_background";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getCardList()
    {
    	$sql = "SELECT cid,name,class_name,introduce,price,price_type,sale_price,add_exp,need_level,item_type,plant_level,new,can_buy,cheap_price,cheap_start_time,cheap_end_time FROM island_card";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getDockList()
    {
    	$sql = "SELECT pid,level,power,price FROM island_dock";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getUserLevelList()
    {
    	$sql = "SELECT level,exp FROM island_level_user";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchPairs($sql);
    }

    public function getIslandLevelList()
    {
    	$sql = "SELECT level,need_user_level,need_user_level_2,need_user_level_3,need_user_level_4,island_size,max_visitor,gold,coin FROM island_level_island";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getGiftLevelList()
    {
    	$sql = "SELECT level,cid,name,item_id,item_name,gold FROM island_level_gift";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getAchievementTaskList()
    {
    	$sql = "SELECT id,need_level,need_num,need_field,name,content,time,level,coin,gold,exp,cid,title,honor,next_task,next_two_task FROM island_task_achievement";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getBuildTaskList()
    {
    	$sql = "SELECT id,need_level,need_num,need_field,need_cid,item_id,item_level,name,content,description,time,level,coin,exp,cid,title FROM island_task_build";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getDailyTaskList()
    {
    	$sql = "SELECT id,need_level,need_num,need_field,name,content,description,time,level,coin,exp,cid,title FROM island_task_daily";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function getAchievementTaskByTitle($title)
    {
    	$sql = "SELECT id,need_level,need_num,need_field,name,content,time,level,coin,gold,exp,cid,title,honor,next_task,next_two_task FROM island_task_achievement WHERE title=:title";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchRow($sql, array('title' => $title));
    }

    public function getShipPraiseList()
    {
    	$sql = "SELECT sid,praise,num FROM island_praise_ship";

        $db = $this->getDB();
        $rdb = $db['r'];

        $data = $rdb->fetchAll($sql);
        if ($data) {
        	$list = array();
        	foreach ($data as $item) {
        		$sid = $item['sid'];
        		if (!isset($list[$sid])) {
        			$list[$sid] = array();
        		}
        		$list[$sid][] = array($item['praise'], $item['num']);
        	}

        	return $list;
        }

        return null;
    }

    public function getTitleList()
    {
    	$sql = "SELECT id,title FROM island_title";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchPairs($sql);
    }

    public function getNoticeList()
    {
    	$sql = "SELECT id,title,position,priority,link,opened,create_time FROM island_notice WHERE opened=1 ORDER BY position ASC,priority ASC";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

    public function updateNoticeList($id, $info)
    {
        $tbname = 'island_notice';

        $db = $this->getDB();
        $wdb = $db['w'];
    	$where = $wdb->quoteinto('id = ?', $id);

        $wdb->update($tbname, $info, $where);
    }

    public function getGiftList()
    {
    	$sql = "SELECT id,gid,`name`,img,level,item_type,`sort`,price,is_free FROM island_gift ORDER BY level ASC";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAll($sql);
    }

    public function getStepGiftLevelList()
    {
    	$sql = "SELECT level,coin,item_id,item_num,gold,star FROM island_level_gift_step";

        $db = $this->getDB();
        $rdb = $db['r'];

        return $rdb->fetchAssoc($sql);
    }

}