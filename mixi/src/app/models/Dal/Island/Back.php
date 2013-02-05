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
class Dal_Island_Back extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'backup_user_info';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * insert user info
     *
     * @param array $info
     * @return integer
     */
    public function insertUser($info)
    {
        $this->_wdb->insert($this->table_user, $info);
    }

  
    /**
     * update user info
     *
     * @param string $uid
     * @param array $info
     * @return void
     */
    public function updateBackUser($uid, $info)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        return $this->_wdb->update($this->table_user, $info, $where);
    }
    

    /**
     * get user level info
     *
     * @param integer $uid
     * @return array
     */
    public function getBackUpUserInfo($uid)
    {
        $sql = "SELECT *
                FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    
    /**
     * update the plant for user
     *
     * @param integer $uid
     * @return array
     */
    public function updateUserPlant($uid)
    {
        $tbName = $this->getTableName($uid,'island_user_plant');
        $sql = "UPDATE $tbName SET status=0 WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid));
    }
    
    /**
     * update the building for user
     *
     * @param integer $uid
     * @return array
     */
    public function updateUserBuilding($uid)
    {
        $tbName = $this->getTableName($uid,'island_user_building');
        $sql = "UPDATE $tbName SET status=0  WHERE uid=:uid ";
        $this->_wdb->query($sql, array('uid'=>$uid));
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
     * insert the present 
     *
     * @param varchar $sql
     * @return null
     */
    public function updateUserPresent($sql)
    {
        $this->_wdb->query($sql);
    }
    
    /**
     * check have card by id
     * @param string uid
     * @param id integer
     * @return array
     */
    public function isHaveCardById($uid, $id)
    {
        $sql = "SELECT uid FROM island_user_card WHERE cid = :id AND uid = :uid ";
        return $this->_rdb->fetchOne($sql, array('id' => $id, 'uid' => $uid));
    }
    
    /**
     * get plant list
     * @param null
     * @return array
     */
    public function getPresenPlantList()
    {
    	$sql = "SELECT bid AS cid,name,class_name AS className,content,map,price,price_type AS priceType,sale_price AS salePrice,
		        nodes,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew,level,ticket,
		        pay_time AS payTime,safe_time AS safeTime,safe_coin_num AS safeCoinNum,need_praise AS needPraise,
		        next_level_bid AS nextCid,act_name AS actName FROM island_island_plant GROUP by name ORDER BY bid asc";
		return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get plant list
     * @param null
     * @return array
     */
    public function getPresenBuildingList()
    {
    	$sql = "SELECT bid AS cid,name,class_name AS className,content,map,price,price_type AS priceType,sale_price AS salePrice,
		        nodes,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew
				FROM island_island_building GROUP by name ORDER BY bid";
		return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * insert notice
     *
     * @param array $info
     * @return integer
     */
    public function insertNotice($info)
    {
        $this->_wdb->insert('island_public_notice', $info);
    }

  
    /**
     * update notice
     *
     * @param string $uid
     * @param array $info
     * @return void
     */
    public function updateNotice($id, $info)
    {
        $where = $this->_wdb->quoteinto('id = ?', $id);
        return $this->_wdb->update('island_public_notice', $info, $where);
    }
    
    
    /**
     * get one  notice
     *
     * @param string $id
     * @return array
     */
    public function getNoticeById($id)
    {
        $sql = "SELECT * FROM island_public_notice WHERE id = :id";
        return $this->_rdb->fetchRow($sql, array('id' => $id));
    }
    
    /**
     * get all
     *
     * @return array
     */
    public function getAllNotcie()
    {
        $sql = "SELECT * FROM island_public_notice ORDER BY position ASC,priority ASC";
        return $this->_rdb->fetchAll($sql);
    }
}