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
class Dal_Island_Building extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_background = 'island_user_background';

    /**
     * table name
     *
     * @var string
     */
    protected $table_user_building = 'island_user_building';

    /**
     * table name
     *
     * @var string
     */
    protected $table_island_building = 'island_island_building';

    /**
     * table name
     *
     * @var string
     */
    protected $table_island_background = 'island_island_background';

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
            $tbname = $this->table_user_building;
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }

    /**
     * insert island building info
     *
     * @param array $info
     * @return integer
     */
    public function insertIslandBuilding($info)
    {
        $this->_wdb->insert($this->table_island_building, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert user background info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserBackground($info)
    {
        $this->_wdb->insert($this->table_user_background, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * add user building
     *
     * @param array $info
     * @return void
     */
    public function addUserBuilding($info)
    {
        $tbName = $this->getTableName($info['uid']);
        $this->_wdb->insert($tbName, $info);
        $this->_wdb->lastInsertId();
    }

    /**
     * add user background
     * @param integer $uid
     * @param array $bgInfo
     * @return void
     */
    public function addUserBackground($uid, $bgInfo)
    {
    	$sql = "UPDATE $this->table_user_background SET status=0 WHERE item_type=:itemType AND status=1 AND uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'itemType'=>$bgInfo['item_type']));

        $this->_wdb->insert($this->table_user_background, $bgInfo);
    }

    /**
     * upgrade user building info
     *
     * @param integer $uid
     * @return void
     */
    public function upgradeUserBuilding($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "UPDATE $tbName SET x=x+1,y=y+1 WHERE uid=:uid AND status=1";
        $this->_wdb->query($sql,array('uid'=>$uid));

        $tbName1 = $this->getTableName($uid, 'island_user_plant');
        $sql = "UPDATE $tbName1 SET x=x+1,y=y+1 WHERE uid=:uid AND status=1";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * update user building info by building id
     *
     * @param string $id
     * @param string $uid
     * @param array $building
     * @return void
     */
    public function updateUserBuildingById($id, $uid, $building)
    {
        $tbName = $this->getTableName($uid);
        $where = "id = " .$this->_wdb->quote($id) . " AND uid= " . $this->_wdb->quote($uid);
        $this->_wdb->update($tbName, $building, $where);
    }

    /**
     * update user background info by id
     *
     * @param string $id
     * @param string $uid
     * @param array $background
     * @return void
     */
    public function updateUserBackgroundById($id, $uid, $background)
    {
        $where = "id = " .$this->_wdb->quote($id) . " AND uid= " . $this->_wdb->quote($uid);
        return $this->_wdb->update($this->table_user_background, $background, $where);
    }

    /**
     * clear user background by uid
     *
     * @param string $uid
     * @return void
     */
    public function clearUserBackground($uid, $type)
    {
        $sql = "UPDATE $this->table_user_background SET status=0 WHERE item_type=:itemType AND uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'itemType'=>$type));
    }

    /**
     * get user using building
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingBuilding($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT concat(id,$n,item_type) AS id,bid AS cid,x,y,z,mirro FROM $tbName WHERE status=1 AND uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user background by id
     *
     * @param integer $id
     * @return array
     */
    public function getUserBackgroundById($id)
    {
        $sql = "SELECT * FROM $this->table_user_background WHERE id=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get user background by id
     *
     * @param integer $id
     * @return array
     */
    public function getUserBackgroundByIdForUpdate($id)
    {
        $sql = "SELECT id,uid,bgid,item_type,status FROM $this->table_user_background WHERE id=:id FOR UPDATE ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get background info by id
     *
     * @param integer $bgId
     * @return array
     */
    public function getBackgroundById($bgId)
    {
        $sql = "SELECT * FROM $this->table_island_background WHERE bgid=:bgid ";
        return $this->_rdb->fetchRow($sql, array('bgid' => $bgId));
    }

    /**
     * delete user background by id
     *
     * @param integer $id
     * @return void
     */
    public function deleteUserBackgroundById($id)
    {
        $sql = "DELETE FROM $this->table_user_background WHERE id=:id ";
        $this->_wdb->query($sql,array('id'=>$id));
    }

    /**
     * get user building list
     *
     * @param integer $uid
     * @return array
     */
    public function getUserBuildingList($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT id,uid,bid,status FROM $tbName WHERE uid=:uid ";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));
        
        $buildingList = array();
        foreach ($result as $value) {
        	$buildingList[$value['id']] = array('uid' => $value['uid'],
        	                                    'bid' => $value['bid'],
        	                                    'status' => $value['status']);
        }
        
        return $buildingList;
    }
    
    /**
     * get user building by id
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserBuildingById($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT * FROM $tbName WHERE id=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get user building by id
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserBuildingById1($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT uid,bid,status FROM $tbName WHERE id=:id ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    /**
     * get user building by id
     *
     * @param integer $id
     * @param integer $uid
     * @return array
     */
    public function getUserBuildingByIdForUpdate($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT id,uid,bid,status FROM $tbName WHERE id=:id FOR UPDATE ";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }

    /**
     * get building info by id
     *
     * @param integer $bid
     * @return array
     */
    public function getBuildingById($bid)
    {
        $sql = "SELECT * FROM $this->table_island_building WHERE bid=:bid ";
        return $this->_rdb->fetchRow($sql, array('bid' => $bid));
    }

    /**
     * delete user building by id
     *
     * @param integer $id
     * @param integer $uid
     * @return void
     */
    public function deleteUserBuildingById($id, $uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "DELETE FROM $tbName WHERE id=:id ";
        $this->_wdb->query($sql,array('id'=>$id));
    }

    /**
	 * get background list
	 *
	 * @return: array
	 */
    public function getBgList()
    {
		$sql = "SELECT bgid AS cid,name,class_name AS className,introduce AS content,null AS map,price,price_type AS priceType,
		        sale_price AS salePrice,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew
				FROM $this->table_island_background ";
		return $this->_rdb->fetchAll($sql);
    }

    /**
	 * get building list
	 *
	 * @return: array
	 */
    public function getBuildingList()
    {
		$sql = "SELECT bid AS cid,name,class_name AS className,content,map,price,price_type AS priceType,sale_price AS salePrice,
		        nodes,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew
				FROM $this->table_island_building ";
		return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user itembox (building)
     *
     * @param integer $uid
     * @return array
     */
    public function getItemBoxBuilding($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT concat(id,$n,item_type) AS id,bid AS cid FROM $tbName
        		WHERE status=0 AND uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user itembox (Background)
     *
     * @param integer $uid
     * @return array
     */
    public function getItemBoxBackground($uid)
    {
        $sql = "SELECT concat(id,item_type) AS id,bgid AS cid FROM $this->table_user_background
        		WHERE status=0 AND uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * count building status by bid
     * @param integer $uid
     * @param integer $bid
     * @return integer
     */
    public function getBuildingCnt($uid, $bid)
    {
        $tbName = $this->getTableName($uid);
		$sql = "SELECT count(1) AS count FROM $tbName WHERE status=0 AND uid=:uid AND bid = :bid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'bid' => $bid));
    }

    /**
     * count background status by bgid
     * @param integer $uid
     * @param integer $bgid
     * @return integer
     */
	public function getBackgroundCnt($uid, $bgid)
    {
		$sql = "SELECT count(*) AS count FROM $this->table_user_background WHERE status=0 AND uid=:uid AND bgid = :bgid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid, 'bgid' => $bgid));
    }

    /**
     * get user using building all info
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingBuildingAll($uid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT * FROM $tbName WHERE status=1 AND uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user using background all info
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingBgAll($uid)
    {
        $sql = "SELECT * FROM $this->table_user_background WHERE status=1 AND uid=:uid ";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
}