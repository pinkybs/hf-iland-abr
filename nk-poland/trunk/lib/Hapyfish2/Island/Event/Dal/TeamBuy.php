<?php

class Hapyfish2_Island_Event_Dal_TeamBuy
{
	protected static $_instance;

	protected $table_teambuy_info = 'island_teambuy_info';
	protected $table_user_teambuy = 'island_user_teambuy';

	public static function getDefaultInstance()
    {

        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * get teambuy info
     */
    public function getTeamBuyInfo()
    {
    	$sql = " SELECT * FROM $this->table_teambuy_info WHERE `status`=1 ";

		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

    	return $rdb->fetchRow($sql);
    }

    /**
     * update teambuy status
     */
    public function updateTeamBuyStatus($gid)
    {
    	$sql = " UPDATE $this->table_teambuy_info SET `status`=-1 WHERE gid=:gid ";

    	$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

    	$wdb->query($sql, array('gid' => $gid));
    }

	/**
	 * join team buy
	 */
	public function joinTeamBuy($uid)
	{
    	$sql = "INSERT INTO $this->table_user_teambuy(uid) VALUES (:uid)";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

        return $wdb->query($sql, array('uid' => $uid));
	}

	/**
	 * get join num
	 */
	 public function getJoinNum()
	 {
	 	$sql = " SELECT count(uid) FROM $this->table_user_teambuy ";

		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

	 	return $rdb->fetchOne($sql);
	 }

	/**
	 * get has buy num
	 */
	public function getHasBuyNum()
	{
		$sql = " SELECT count(uid) FROM $this->table_user_teambuy WHERE `status`=1 ";

 	    $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

	 	return $rdb->fetchOne($sql);
	}

	/**
	 * get join teambuy info
	 */
	public function getJoinTeamBuyInfo($uid)
	{
		$sql = " SELECT `status` FROM $this->table_user_teambuy WHERE uid=:uid ";

 	    $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

	 	return $rdb->fetchOne($sql, array('uid' => $uid));
	}

	/**
	 * update user has buy status
	 */
	public function updateHasBuy($uid)
	{
    	$sql = " UPDATE $this->table_user_teambuy SET `status`=1 WHERE uid=:uid ";

    	$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

    	$wdb->query($sql, array('uid' => $uid));
	}

	/**
	 * get has join teambuy uid
	 */
	public function getHasJoinTeamBuyUser()
	{
		$sql = " SELECT uid FROM $this->table_user_teambuy ";

 	    $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

	 	return $rdb->fetchAll($sql);
	}

	/**
	 * delete teambuy user
	 */
	public function clearTeamBuyUser()
	{
        $sql = "DELETE FROM $this->table_user_teambuy ";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

        $wdb->query($sql);
	}

	public function getTeamBuyMessage()
	{
		$sql = " SELECT * FROM $this->table_teambuy_info ";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

    	return $rdb->fetchRow($sql);
	}

	public function updateTeamBuyInfo($action)
	{
		$sql = "UPDATE $this->table_teambuy_info SET gid=:gid,`name`=:name,start_time=:start_time,ok_time=:ok_time,buy_time=:buy_time,max_price=:max_price,min_price=:min_price,min_num=:min_num,max_num=:max_num,start_num=:start_num,bec_num=:bec_num,bec_price=:bec_price,scale_gold=:scale_gold,scale_coin=:scale_coin";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

		$wdb->query($sql, array('gid' => $action['gid'],
								'name' => $action['name'],
								'start_time' => $action['start_time'],
								'ok_time' => $action['ok_time'],
								'buy_time' => $action['buy_time'],
								'max_price' => $action['max_price'],
								'min_price' => $action['min_price'],
								'min_num' => $action['min_num'],
								'max_num' => $action['max_num'],
								'start_num' => $action['start_num'],
								'bec_num' => $action['bec_num'],
								'bec_price' => $action['bec_price'],
								'scale_gold' => $action['scale_gold'],
								'scale_coin' => $action['scale_coin']));
	}

	public function switchTeamBuy($act)
	{
		$sql = "UPDATE $this->table_teambuy_info SET `status`=:status";

		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

		$wdb->query($sql, array('status' => $act));
	}

}