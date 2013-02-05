<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/1    xial
 */
class Dal_Island_Plant extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_plant = 'island_user_plant';
	protected $table_island_plant = 'island_island_plant';

    protected static $_instance;

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

    /**
     * insert plant person info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserPlant($info)
    {
        $tbName = $this->getTableName($info['uid']);
        return $this->_wdb->insert($tbName, $info);
    }

    /**
     * update user plant
     * @param integer $id
     * @param array $info
     * @return void
     */
    public function updateUserPlant($id, $info)
    {
        $tbName = $this->getTableName($info['uid']);
		$where = $this->_wdb->quoteinto('id = ?', $id);
        $this->_wdb->update($tbName, $info, $where);
    }

    /**
     * delete user plant by id
     * @param integer $uid
     * @param integer $id
     * @return void
     */
    public function deleteUserPlantById($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "DELETE FROM $tbName WHERE id=:id ";
        $this->_wdb->query($sql, array('id'=>$id));
    }

    /**
     * get user plant by id
     * @param integer $uid
     * @return array
     */
    public function getListUserPlant($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
		$sql = "SELECT CONCAT(id, $n, item_type) AS itemId, bid AS cid, 0 AS num FROM $tbName
		        WHERE uid = :uid AND status = 1 AND can_find = 1";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get list user plant by id
     * @param integer $uid
     * @return array
     */
    public function getListUserPlantById($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
		$sql = "SELECT id,CONCAT(id, $n, item_type) AS itemId, bid AS cid, 0 AS num, event AS eventId
		        FROM $tbName WHERE uid = :uid AND status = 1 AND can_find = 1";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user plant info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPlantListAll($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT id,CONCAT(id, $n, item_type) AS itemId,uid, bid AS cid, event AS eventId, wait_visitor_num, 
                start_pay_time, safecard_time, deposit, start_deposit, delay_time, event_manage_time, level, 
                item_id, 0 AS num,can_find 
                FROM $tbName WHERE status = 1 AND uid = :uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user plant info by item id
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
    public function getUserPlantInfoByItemId($uid, $itemId)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT id,CONCAT(id, $n, item_type) AS itemId,uid, bid AS cid, event AS eventId, wait_visitor_num, 
                start_pay_time, safecard_time, deposit, start_deposit, delay_time, event_manage_time, level, 
                item_id, 0 AS num,can_find 
                FROM $tbName WHERE status = 1 AND uid = :uid AND id=:id";

        return $this->_rdb->fetchRow($sql, array('uid' => $uid, 'id' => $itemId));
    }
    
    /**
     * get user plant info
     *
     * @param integer $uid
     * @return array
     */
    public function getListUserPlantAllById($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
    	$sql = "SELECT id,CONCAT(id, $n, item_type) AS itemId,uid, bid AS cid, event AS eventId, wait_visitor_num,
    			start_pay_time, safecard_time, deposit, start_deposit, delay_time, event_manage_time, level,
    			item_id, 0 AS num
    			FROM $tbName WHERE status = 1 AND can_find = 1 AND uid = :uid";

    	return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get current level all plant
     *
     * @param integer $level
     * @param array $bids
     * @return array
     */
    public function getListIslandPlant($level, $bids)
    {
    	$bids = $this->_rdb->quote($bids);
    	$sql = "SELECT level, item_id, 0 AS itemId, bid AS cid, 0 AS num, 0 AS eventId FROM $this->table_island_plant 
    	       WHERE need_level < $level AND price > 0 AND can_buy=1 ";
    	if ($bids) {
			$sql .= " AND bid NOT IN ($bids)";
    	}
    	return $this->_rdb->fetchAll($sql);
    }

    /**
     * get plant info by level
     *
     * @param integer $level
     * @return array
     */
    public function getPlantListByLevel($level)
    {
    	$sql = "SELECT 0 AS itemId, bid AS cid, 0 AS num, 0 AS eventId,item_id,level
    	        FROM $this->table_island_plant WHERE need_level = :nextLevel AND price > 0 AND can_buy=1 ";
    	return $this->_rdb->fetchAll($sql, array('nextLevel' => $level + 1));
    }

    /**
     * get user plant info by id
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserPlantById($id, $uid)
    {
        $tbName = $this->getTableName($uid);
		$sql = "SELECT * FROM $tbName WHERE id = :id";
		return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get user plant list info for diy
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserPlantListForDiy($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT id,uid,bid,status,wait_visitor_num FROM $tbName WHERE uid = :uid";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        
        $plantList = array();
        foreach ($result as $value) {
            $plantList[$value['id']] = array('uid' => $value['uid'],
                                             'bid' => $value['bid'],
                                             'status' => $value['status'],
                                             'wait_visitor_num' => $value['wait_visitor_num']);
        }
        
        return $plantList;
    }
    
    /**
     * get user plant info by id
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserPlantById1($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT uid,bid,status,wait_visitor_num FROM $tbName WHERE id = :id";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    /**
     * get user plant info by id for update
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserPlantByIdForupdate($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT * FROM $tbName WHERE id = :id FOR UPDATE ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get nb plant info by bid
     *
     * @param integer $bid
     * @return array
     */
    public function getNbPlantById($bid)
    {
		$sql = "SELECT * FROM $this->table_island_plant WHERE bid = :bid";
		return $this->_rdb->fetchRow($sql, array('bid' => $bid));
    }

    /**
     * get user plant count
     *
     * @param integer $uid
     * @return integer
     */
    public function getCountUserPlantById($uid)
    {
        $tbName = $this->getTableName($uid);
		$sql = "SELECT COUNT(1) FROM $tbName WHERE uid = :uid";
		return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user event plant count
     *
     * @param integer $uid
     * @return integer
     */
    public function getCountUserEventPlantById($uid)
    {
        $tbName = $this->getTableName($uid);
		$sql = "SELECT COUNT(1) FROM $tbName WHERE uid = :uid AND event = 1";
		return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get user all plant bid
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPlantBidByid($uid)
    {
        $tbName = $this->getTableName($uid);
		$sql = "SELECT bid FROM $tbName WHERE uid = :uid GROUP BY bid";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user using building
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingPlant($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT concat(u.id, $n, u.item_type) AS id,u.bid AS cid,u.x,u.y,u.z,u.mirro,u.event,u.wait_visitor_num AS waitVisitorNum,
                u.start_pay_time,u.deposit,u.start_deposit AS startDeposit,u.can_find AS canFind,p.pay_time,u.delay_time
        		FROM $tbName AS u,$this->table_island_plant AS p WHERE u.bid=p.bid AND u.status=1 AND u.uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user using building by id
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUsingPlantById($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT concat(u.id, $n, u.item_type) AS id,u.bid AS cid,u.x,u.y,u.z,u.mirro,u.event,u.wait_visitor_num AS waitVisitorNum,
                u.start_pay_time,u.deposit,u.start_deposit AS startDeposit,u.can_find AS canFind,p.pay_time,u.delay_time
                FROM $tbName AS u,$this->table_island_plant AS p
        		WHERE u.bid=p.bid AND u.status=1 AND u.id = :id";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get user itembox (plant)
     *
     * @param integer $uid
     * @return array
     */
    public function getItemBoxPlant($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT concat(id, $n, item_type) AS id,bid AS cid,level FROM $tbName
        		WHERE status=0 AND uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
	 * get plant list
	 *
	 * @return: array
	 */
    public function getPlantList()
    {
		$sql = "SELECT bid AS cid,name,class_name AS className,content,map,price,price_type AS priceType,sale_price AS salePrice,
		        nodes,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew,level,ticket,
		        pay_time AS payTime,safe_time AS safeTime,safe_coin_num AS safeCoinNum,need_praise AS needPraise,
		        next_level_bid AS nextCid,act_name AS actName FROM $this->table_island_plant ";
		return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user plant list
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPlantList($uid)
    {
        $tbName = $this->getTableName($uid);
        //$sql = "SELECT bid,count(1) AS count FROM $tbName WHERE uid=:uid GROUP BY bid";
        $sql = "SELECT * FROM (SELECT bid,item_id,level FROM $tbName WHERE uid=:uid ORDER BY level DESC) AS c GROUP BY item_id";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));

        $plantList = array();
        for ($i=0,$iCount=count($result); $i<$iCount; $i++) {
            $plantList[$result[$i]['item_id']] = $result[$i]['level'];
        }
        return $plantList;
    }

    /**
     * get user plant list
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPlantListByItemId($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT bid,item_id,level FROM $tbName WHERE uid=:uid ORDER BY level DESC";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        return $result;
    }
    
    /**
     * get user plant info by item id 
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
    public function getUserPlantByItemId($uid, $itemId)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT max(level) FROM $tbName WHERE item_id = :item_id AND uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('item_id' => $itemId, 'uid' => $uid));
    }

    /**
     * get user plant info by item id 
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
    public function getPlantListByItemId($uid, $itemId)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT * FROM $tbName WHERE item_id = :item_id AND uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('item_id' => $itemId, 'uid' => $uid));
    }
    
    /**
     * exists user have plant
     *
     * @param integer $uid
     * @param integer $id
     * @return integer
     */
    public function isPlantExistsById($uid, $id)
    {
        $tbName = $this->getTableName($uid);
		$sql = "SELECT COUNT(id) FROM $tbName WHERE uid = :uid AND id = :id";
		$cnt = $this->_rdb->fetchOne($sql, array('uid' => $uid, 'id' => $id));
		return $cnt > 0 ? true : false;
    }

    /**
     * get user using plant all info
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingPlantAll($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT * FROM $tbName WHERE status=1 AND uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user fifa plant bid
     *
     * @param integer $uid
     * @return integer
     */
    public function getUserFifaPlant($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT bid FROM $tbName WHERE bid < 41532 AND bid > 38232 AND uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    
}