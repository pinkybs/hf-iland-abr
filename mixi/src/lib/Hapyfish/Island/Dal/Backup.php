<?php

class Hapyfish_Island_Dal_Backup extends Hapyfish_Island_Dal_Abstract
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Backup
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function getPlantPayInfo($n, $page, $size, $time)
    {
        $tbName = 'island_user_plant_' . $n;
        
        $start = ($page - 1) *  $size;
        
        $sql = "SELECT u.id,CONCAT(u.id, $n, u.item_type) AS itemId,u.uid,u.bid AS cid,u.can_find,p.pay_time,
        		u.event,u.wait_visitor_num,u.start_pay_time,u.safecard_time,u.deposit,u.start_deposit,u.delay_time,u.event_manage_time
        		FROM $tbName AS u,island_island_plant AS p WHERE u.bid=p.bid AND u.status=1 AND u.start_deposit>0 AND u.start_pay_time>$time LIMIT $start,$size";
        
        return $this->_rdb->fetchAll($sql);
    }
    
    public function getPlantPayInfoCount($n, $time)
    {
        $tbName = 'island_user_plant_' . $n;
        
        $sql = "SELECT COUNT(*) FROM $tbName WHERE status=1 AND start_deposit>0 AND start_pay_time>$time";
        
        return $this->_rdb->fetchOne($sql);
    }
    
    public function getUserCount($time)
    {
        $sql = "SELECT COUNT(*) FROM island_user_info WHERE last_login_time>$time";
        
        return $this->_rdb->fetchOne($sql);    	
    }
    
    public function getUserIds($page, $size, $time)
    {
        $start = ($page - 1) *  $size;
    	$sql = "SELECT uid FROM island_user_info WHERE last_login_time>$time LIMIT $start,$size";
        
        return $this->_rdb->fetchAll($sql);     	
    }
    
    
}