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
class Dal_Island_User extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'island_user';

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
        $n = $uid % 10;
        return $tbname . '_' . $n;
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
     * insert user other info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserOther($info)
    {
        $this->_wdb->insert('island_user_info', $info);
    }
    
    /**
     * init user card info
     *
     * @param array $uid
     * @return void
     */
    public function initUserCard($uid)
    {
        $now = time();
        
        $sql = "SELECT cid FROM island_user_card WHERE cid=26441 AND uid=:uid ";
        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid));
        
        if ( $result > 0 ) {
            $sql = "UPDATE island_user_card SET count=count+1 WHERE cid=26441 AND uid=:uid ";
            $this->_wdb->query($sql,array('uid'=>$uid));
        }
        else {
            $sql = "INSERT INTO `island_user_card`(`uid`,`cid`,`count`,`buy_time`,`item_type`) values ( :uid, 26441, 1, $now, 41);";
            $this->_wdb->query($sql,array('uid'=>$uid));
        }
    }

    /**
     * init user background info
     *
     * @param array $uid
     * @return void
     */
    public function initUserBackground($uid)
    {
        $now = time();
        $sql = "INSERT INTO `island_user_background`(`uid`,`bgid`,`status`,`buy_time`,`item_type`) values ( :uid, 25411, 1, $now, 11),
                                                                                                          ( :uid, 23212, 1, $now, 12),
                                                                                                          ( :uid, 22213, 1, $now, 13),
                                                                                                          ( :uid, 25914, 1, $now, 14);";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * init user building info
     *
     * @param array $uid
     * @return void
     */
    public function initUserBuilding($uid)
    {
        $tbName = $this->getTableName($uid, 'island_user_building');
        $now = time();
        $sql = "INSERT INTO $tbName(`uid`,`bid`,`x`,`y`,`z`,`mirro`,`status`,`buy_time`,`item_type`)
                                           values (:uid, '6721', '0','4','0','0','1', $now, '21'),
                                                  (:uid, '6721', '1','7','0','0','1', $now, '21'),
                                                  (:uid, '6721', '0','5','0','0','1', $now, '21'),
                                                  (:uid, '6721', '0','6','0','0','1', $now, '21'),
                                                  (:uid, '6721', '5','0','0','0','1', $now, '21'),
                                                  (:uid, '6721', '6','0','0','0','1', $now, '21'),
                                                  (:uid, '7521', '4','8','0','1','1', $now, '21'),
                                                  (:uid, '33421', '6','2','0','0','1', $now, '21'),
                                                  (:uid, '33421', '3','2','0','0','1', $now, '21'),
                                                  (:uid, '33521', '9','3','0','0','1', $now, '21'),
                                                  (:uid, '33521', '9','6','0','0','1', $now, '21'),
                                                  (:uid, '33621', '6','9','0','1','1', $now, '21');";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * init user plant info
     *
     * @param array $uid
     * @return void
     */
    public function initUserPlant($uid)
    {
        $now = time();
        $tableName = $this->getTableName($uid, 'island_user_plant');
        $sql = "INSERT INTO $tableName (`uid`,`bid`,`x`,`y`,`z`,`mirro`,`status`,`item_id`,`buy_time`,`item_type`,`deposit`,`start_deposit`)
                                        values (:uid, '21232', '4','1','0','0','1','212', $now, '32', '0', '0'),
                                               (:uid, '632', '6','7','0','0','1','6', $now, '32', '300', '300');";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * init user dock info
     *
     * @param array $uid
     * @return void
     */
    public function initUserDock($uid)
    {
        $now = time();
        $sql = "INSERT INTO `island_user_dock`(`uid`,`position_id`,`status`,`ship_id`,`start_visitor_num`,`remain_visitor_num`,`receive_time`)
                                       values (:uid, '1', '2','1','5','5', $now),
                                              (:uid, '2', '2','1','5','5', $now),
                                              (:uid, '3', '2','1','5','5', $now);";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * init user ship info
     *
     * @param array $uid
     * @return void
     */
    public function initUserShip($uid)
    {
        $tableName = $this->getTableName($uid, 'island_user_ship');
        $sql = "INSERT INTO $tableName(`uid`,`position_id`,`ship_id`)
                                       values (:uid, '1', '1'),
                                              (:uid, '2', '1'),
                                              (:uid, '3', '1');";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    /**
     * update user info
     *
     * @param string $uid
     * @param array $info
     * @return void
     */
    public function updateUser($uid, $info)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update($this->table_user, $info, $where);
    }

    /**
     * update user power
     *
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserPower($uid, $change)
    {
        $now = time();
        $sql = "UPDATE $this->table_user SET power=power + :change AND last_power_time=$now WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user info by field name
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateUserByField($uid, $field, $change)
    {
        $sql = "UPDATE $this->table_user SET $field = $field + :change WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user info by multiple field name
     *
     * @param integer $uid
     * @param array $param
     * @return void
     */
    public function updateUserByMultipleField($uid, $param)
    {
        $change = array();
        foreach ( $param as $k => $v ) {
            $change[] = $k . '=' . $k . '+' . $v;
        }
        $s1 = join(',', $change);

        $sql = "UPDATE $this->table_user SET $s1 WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

	/**
     * update user visitor num
     *
     * @param string $uid
     * @return void
     */
    public function updateUserVisitorNum($uid)
    {
        $tableName = $this->getTableName($uid, 'island_user_plant');
        $sql = "UPDATE $this->table_user SET currently_visitor=(SELECT sum(wait_visitor_num) FROM $tableName
                WHERE status = 1 AND uid=:uid ) WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUser($uid)
    {
        $sql = "SELECT * FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForInitIsland($uid)
    {
        $sql = "SELECT exp,next_level_exp,level,island_level,island_name,praise,currently_visitor,title,defense_card,insurance_card 
                FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForVisitor($uid)
    {
        $sql = "SELECT level,defense_card,currently_visitor FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForMoochOwner($uid)
    {
        $sql = "SELECT uid,insurance_card FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForReceive($uid)
    {
        $sql = "SELECT uid,island_level,currently_visitor,praise,exp,level FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForMooch($uid)
    {
        $sql = "SELECT exp,level,island_level,currently_visitor,queue_visitor FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForAddBoat($uid)
    {
        $sql = "SELECT position_count,level,coin,praise,exp FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForUnlock($uid)
    {
        $sql = "SELECT level,coin,gold FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForHarvest($uid)
    {
        $sql = "SELECT coin,exp,level FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserDockInfo($uid)
    {
        $sql = "SELECT uid,coin,exp,praise,level,island_level,position_count,currently_visitor,queue_visitor,insurance_card,
                insurance_type,defense_card,mood_word_count FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user level info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserLevelInfo($uid)
    {
        $sql = "SELECT uid,exp,next_level_exp,level,coin,gold,island_name,island_level,praise,help,title,power
                FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user praise info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPraise($uid)
    {
        $sql = "SELECT praise FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }

    /**
     * get user position count
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPositionCount($uid)
    {
        $sql = "SELECT position_count FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    /**
     * get user login info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserLoginInfo($uid)
    {
        $sql = "SELECT coin,exp,title,today_login_count 
                FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user title
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTitle($uid)
    {
        $sql = "SELECT title FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    /**
     * get user info for update
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForUpdate($uid)
    {
        $sql = "SELECT uid,coin,gold,power,praise,exp,level,island_level FROM $this->table_user WHERE uid=:uid FOR UPDATE ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * check user in app
     *
     * @param array $uid
     * @return integer
     */
    public function isHaveUser($uid)
    {
		$sql = "SELECT uid FROM $this->table_user WHERE uid=:uid ";
		return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
        /**
     * get user status field
     *
     * @param string $uid
     * @return integer
     */
    public function getUserStatus($uid)
    {
        $sql = "SELECT status FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));      
    }
    
    /**
     * update user status field
     *
     * @param string $uid
     * @param int $status
     */
    public function updateUserStatus($uid, $status = 0)
    {
        $sql = "UPDATE $this->table_user SET status=:status WHERE uid=:uid";
        $this->_wdb->query($sql, array('status' => $status, 'uid'=>$uid));      
    }

    /**
     * get app friend count
     *
     * @param array $friendIds
     * @return integer
     */
    public function getAppFriendCount($friendIds)
    {
        $friendIds = $this->_rdb->quote($friendIds);

        $sql = "SELECT count(1) FROM $this->table_user WHERE uid IN($friendIds) ";

        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get app friendids
     *
     * @param array $friendIds
     * @return array
     */
    public function getAppFriendIds($friendIds)
    {
        $friendIds = $this->_rdb->quote($friendIds);

        $sql = "SELECT uid FROM $this->table_user WHERE uid IN($friendIds) ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get app user in array
     *
     * @param array $idArray
     * @return array
     */
    public function getUidInArray($idArray)
    {
        $idArray = $this->_rdb->quote($idArray);

        $sql = "SELECT uid FROM $this->table_user WHERE id IN($idArray) ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get app user max id
     *
     * @return integer
     */
    public function getMaxId()
    {
        $sql = "SELECT MAX(id) FROM $this->table_user ";
        return $this->_rdb->fetchOne($sql);
    }

/********************************************************************************/

    /**
     * get app friendsids list
     *
     * @param array $friendsIds
     * @param integer $pageSize
     * @param integer $pageIndex
     * @return array
     */
	public function getAppLstFriends($friendsIds, $pageSize = 10, $pageIndex = 1)
	{
		$friendIds = $this->_rdb->quote($friendsIds);
		$start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT uid,exp,level,next_level_exp,praise,coin,gold,power FROM $this->table_user
                WHERE uid IN($friendIds) LIMIT $start, $pageSize";

        return $this->_rdb->fetchAll($sql);
	}

	/**
	 * insert invite table
	 *
	 * @param array $info
	 * @return integer
	 */
	public function insertUserInvite($info)
	{
		$this->_wdb->insert('island_user_invite', $info);
        return $this->_wdb->lastInsertId();

	}

	/**
     * update user invite info
     *
     * @param string $uid
     * @param array $info
     * @return void
     */
    public function updateUserinvite($uid, $info)
    {
        $where = $this->_wdb->quoteinto('id = ?', $uid);
        $this->_wdb->update('island_user_invite', $info, $where);
    }

    /**
     * get invite info
     * @param integer $actor
     * @param integer $target
     * @return array
     */
    public function getInviteInfo($actor, $target)
    {
		$sql = "SELECT * FROM island_user_invite WHERE actor=:actor AND target=:target AND status = 0 ";
        return $this->_rdb->fetchRow($sql,array('actor'=>$actor , 'target' => $target));
    }

    /**
     * update user visitor num
     *
     * @param string $uid
     * @return void
     */
    public function deleteUser($uid)
    {
        $buildingName = $this->getTableName($uid, 'island_user_building');
        $plantName = $this->getTableName($uid, 'island_user_plant');
        $shipName = $this->getTableName($uid, 'island_user_ship');
        $sql = "DELETE FROM island_user WHERE uid=:uid;
                DELETE FROM island_user_info WHERE uid=:uid;
                DELETE FROM island_user_background WHERE uid=:uid;
                DELETE FROM $buildingName WHERE uid=:uid;
                DELETE FROM $plantName WHERE uid=:uid;
                DELETE FROM island_user_card WHERE uid=:uid;
                DELETE FROM island_user_dock WHERE uid=:uid;
                DELETE FROM $shipName WHERE uid=:uid;";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * get user other info
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserOtherInfo($uid)
    {
        $sql = "SELECT uid,last_login_time,today_login_count,activity_login_count FROM island_user_info WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }

    /**
     * get user help info
     * 
     * @param integer $uid
     * @return array
     */
    public function getUserHelpInfo($uid)
    {
        $sql = "SELECT uid,help_1,help_2,help_3,help_4,help_5,help_6 FROM island_user_info WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    }
    
    
    /**
     * update user last login time
     *
     * @param string $uid
     * @return void
     */
    public function updateUserLastLoginTime($uid)
    {
    	$sql = "SELECT * FROM island_user_info WHERE uid=:uid";
    	$result = $this->_rdb->fetchRow($sql, array('uid'=>$uid));
    	
    	$now = time();
    	if ( empty($result) ) {
    		$this->_wdb->insert('island_user_info', array('uid' => $uid));
    	}
    	else {
	        $sql = "UPDATE island_user_info SET last_login_time=$now WHERE uid=:uid ";
	        $this->_wdb->query($sql, array('uid'=>$uid));
    	}
        
    }

    /**
     * update user other info
     *
     * @param string $uid
     * @param array $info
     * @return void
     */
    public function updateUserOther($uid, $info)
    {
        $where = $this->_wdb->quoteinto('uid = ?', $uid);
        $this->_wdb->update('island_user_info', $info, $where);
    }
}