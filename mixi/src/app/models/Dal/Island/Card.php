<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    xial
 */
class Dal_Island_Card extends Dal_Abstract{

	protected static $_instance;

    /**
     * table name
     *
     * @var string
     */
    protected $table_card = 'island_card';

	/**
     * table name
     *
     * @var string
     */
    protected $table_user_card = 'island_user_card';

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * add user card
     *
     * @param array $cardInfo
     * @return void
     */
    public function addUserCard($cardInfo)
    {
        $sql = "SELECT * FROM $this->table_user_card WHERE cid=:cid AND uid=:uid ";
        $result = $this->_rdb->fetchRow($sql, array('uid'=>$cardInfo['uid'], 'cid'=>$cardInfo['cid']));

        if ( $result ) {
            $sql = "UPDATE $this->table_user_card SET count = count + :change WHERE cid=:cid AND uid=:uid ";
            $this->_wdb->query($sql, array('uid'=>$cardInfo['uid'], 'cid'=>$cardInfo['cid'], 'change'=>$cardInfo['count']));
        }
        else {
            $this->_wdb->insert($this->table_user_card, $cardInfo);
            $this->_wdb->lastInsertId();
        }
    }

    /**
     * get user Card list
     * @param : integer uid
     * @return: array
     */
    public function getLstUserCardById($uid)
    {
        $sql = "SELECT u.cid,u.count,c.name,c.class_name,c.price,c.introduce,c.type,c.add_exp,c.price_type
        		FROM $this->table_user_card AS u,$this->table_card AS c WHERE u.uid = :uid AND u.cid = c.cid";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get card list
     *
     * @return array
     */
 	public function getLstCard()
    {
        $sql = "SELECT cid,name,class_name AS className,introduce AS content,null AS map,price,price_type AS priceType,
                sale_price AS salePrice,need_level AS needLevel,item_type AS type,new AS isNew
				FROM $this->table_card ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * insert Card
     * param : array
     * return:integer
     */
	public function insertCard($info)
	{
		$this->_wdb->insert($this->table_user_card, $info);
        return $this->_wdb->lastInsertId();
	}

	/**
	 * read Card by uid
	 *
	 * @param integer $uid
	 * @return array
	 */
	public function getLstCardById($uid)
	{
		$sql = "SELECT * FROM $this->table_user_card WHERE uid = :uid AND count > 0";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
	}

	/**
	 * get Card info by id
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getCardById($id)
	{
		$sql = "SELECT * FROM $this->table_card WHERE cid = :id ";
		return $this->_rdb->fetchRow($sql, array('id' => $id));
	}

	/**
	 * check have card by id
	 * @param string uid
	 * @param id integer
	 * @return array
	 */
	public function isHaveCardById($uid, $id)
	{
		$sql = "SELECT uid FROM $this->table_user_card WHERE cid = :id AND uid = :uid AND count > 0";
		return $this->_rdb->fetchOne($sql, array('id' => $id, 'uid' => $uid));
	}

    /**
     * update user card info
     *
     * @param string $uid
     * @param integer $cid
     * @param integer $change
     * @param array $info
     * @return void
     */
	public function updateCardById($uid, $cid, $change)
	{
		$sql = "UPDATE $this->table_user_card SET count=count + :change WHERE uid = :uid AND cid = :cid";
        $this->_wdb->query($sql,array('uid'=>$uid, 'cid' => $cid, 'change'=>$change));
	}

	/**
	 * get user card info by id
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getUserCardById($id)
	{
	    $sql = "SELECT * FROM $this->table_user_card WHERE id=:id ";
	    $this->_rdb->fetchRow($sql, array('id'=>$id));
	}

    /**
     * get user card info by id
     *
     * @param integer $id
     * @return array
     */
    public function getUserCardByIdForUpdate($id)
    {
        $sql = "SELECT id,count FROM $this->table_user_card WHERE id=:id FOR UPDATE ";
        $this->_rdb->fetchRow($sql, array('id'=>$id));
    }

    /**
     * delete user card by id
     *
     * @param integer $id
     * @return void
     */
	public function deleteUserCardById($id)
	{
	    $sql = "UPDATE $this->table_user_card SET count=count-1 WHERE id=:id ";
	    $this->_wdb->query($sql,array('id' => $id));
	}

	/**
	 * get upgraded can have new card
	 * @param integer level
	 * @return array
	 */
	public function getNewLevelCardByLv($level)
	{
		$sql = "SELECT name FROM $this->table_card WHERE need_level = $level";
		$this->_rdb->fetchAll($sql);
	}
}