<?php

class Hapyfish_Island_Dal_Plant extends Hapyfish_Island_Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_plant = 'island_user_plant';
	protected $table_island_plant = 'island_island_plant';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Plant
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getTableName($uid, $tbname = null)
    {
        if ( $tbname == null ) {
            $tbname = $this->table_user_plant;
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
	//id
	//itemId[u.id, $n, u.item_type]
	//cid[bid]   : 装饰物id，对应island_island_building表bid
	//x,y,z      : 坐标
	//mirro      : 镜像,0:非镜像,1:镜像
	//level      : 等级
	//item_id    : 对应island_plant表中的item_id
	//can_find   : 游客是否可以走到此建筑
	//pay_time   : 结算时间
    public function getPlantInfoById($uid, $id)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT u.id,u.uid,CONCAT(u.id, $n, u.item_type) AS itemId,u.bid AS cid,u.level,u.x,u.y,u.z,u.mirro,u.item_id,u.can_find,p.pay_time
        		FROM $tbName AS u,$this->table_island_plant AS p WHERE u.bid=p.bid AND u.status=1 AND u.id=:id";
        
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    public function getUsingPlantInfo($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT id,concat(id, $n, item_type) AS itemId,bid AS cid,level,item_id,x,y,z,mirro,can_find
        		FROM $tbName WHERE status=1 AND uid=:uid";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));    	
    }
    
    public function updateUserPlant($id, $info)
    {
        $tbName = $this->getTableName($info['uid']);
		$where = $this->_wdb->quoteinto('id = ?', $id);
        $this->_wdb->update($tbName, $info, $where);
    }
    
    public function getPlantPayTimeList()
    {
         $sql = "SELECT bid,pay_time FROM $this->table_island_plant";
         $data = $this->_rdb->fetchAll($sql);
         
         $result = array();
         if ($data) {
         	foreach ($data as $row) {
         		$result[$row['bid']] = $row['pay_time'];
         	}
         }
         
         return $result;
    }
    
    public function getPlantPayTimeAndTicketList()
    {
         $sql = "SELECT bid,pay_time,ticket FROM $this->table_island_plant";
         $data = $this->_rdb->fetchAll($sql);
         
         $result = array();
         if ($data) {
         	foreach ($data as $row) {
         		$result[$row['bid']] = array('pay_time' => $row['pay_time'], 'ticket' => $row['ticket']);
         	}
         }
         
         return $result;
    }
    
    //id
    public function getUserPlantIds($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT id FROM $tbName WHERE status=1 AND uid=:uid";
        $data = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        $ids = null;
        if ($data){
        	$ids = array();
        	foreach ($data as $row) {
        		$ids[] = $row['id'];
        	}
        }
        
        return $ids;
    }
    
    public function removePlantById($uid, $id)
    {
         $tbName = $this->getTableName($uid);
         $sql = "UPDATE $tbName SET status=0 WHERE id=:id";
         $this->_wdb->query($sql, array('id'=>$id));
    }
    
    /**
     * get nb plant info by bid
     *
     * @param integer $bid
     * @return array
     */
    public function getNbPlantById($bid)
    {
		$sql = "SELECT * FROM $this->table_island_plant WHERE bid=:bid";
		return $this->_rdb->fetchRow($sql, array('bid' => $bid));
    }
    
    
}