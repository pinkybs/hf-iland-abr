<?php

/**
 * Event HallWitches
 *
 * @package    Island/Event/Dal
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/10/18    zhangli
*/
class Hapyfish2_Island_Event_Dal_HallWitches
{
    protected static $_instance;
	protected $table_hall_card = 'island_hall_card';
	protected $table_hall_list = 'island_hall_list';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Event_Dal_HallWitches
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_hall_' . $id;
    }

    //卡片信息
    public function getListArr()
    {
    	$sql = "SELECT cid,`name`,odds FROM $this->table_hall_card ORDER BY cid ASC";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

        return $rdb->fetchAll($sql);
    }

    //兑换物品列表和兑换公式
    public function getHallList()
    {
    	$sql = "SELECT need_data,item_id,item_num,coin,gold,starfish FROM $this->table_hall_list";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

        return $rdb->fetchAll($sql);
    }

    //用户卡牌信息
    public function getCard($uid)
    {
    	$TBname = $this->getTableName($uid);

    	$sql = "SELECT card_list FROM $TBname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

        return $rdb->fetchOne($sql, array('uid' => $uid));
    }

    //为用户加入卡片
	public function addCard($uid, $list)
	{
    	$TBname = $this->getTableName($uid);

    	$sql = "INSERT INTO $TBname (uid, card_list) VALUES (:uid, :card_list)";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid, 'card_list' => $list));
	}

	//更新用户卡片
	public function incCard($uid, $list)
	{
    	$TBname = $this->getTableName($uid);

    	$sql = "UPDATE $TBname SET card_list=:card_list WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid, 'card_list' => $list));
	}

}