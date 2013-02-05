<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/08/23    Liz
 */
class Dal_Casino_Casino extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_casino_Casino = 'casino_Casino';
    
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
            $tbname = 'island_user_plant';
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
    /**
     * insert info
     *
     * @param array $sql
     * @return void
     */
    public function insertInfo($sql)
    {
        $this->_wdb->query($sql);
    }
    
    /**
     * insert user award
     *
     * @param array $array
     * @return void
     */
    public function insertUserAward($array)
    {
    	$this->_wdb->insert('casino_user', $array);
        $this->_wdb->lastInsertId();
    }

    /**
     * delete user drop award
     *
     * @param integer $uid
     * @return void
     */
	public function clearUserDropAward($uid)
	{
	    $sql = "UPDATE casino_user SET status=-1 WHERE status=0 AND uid=:uid ";
	    $this->_wdb->query($sql,array('uid' => $uid));
	}
	
    /**
     * update user get award
     *
     * @param integer $uid
     * @return void
     */
	public function updateUserGetAward($uid)
	{
	    $sql = "UPDATE casino_user SET status=1 WHERE status=0 AND uid=:uid ";
	    $this->_wdb->query($sql,array('uid' => $uid));
	}

    /**
     * update award remain count
     *
     * @return array
     */
    public function updateAwardRemainCount($bid, $id, $change)
    {
        $sql = "UPDATE casino_award SET remain_count=remain_count+$change WHERE id=:id AND bid=:bid ";
        $this->_wdb->query($sql, array('bid' => $bid, 'id' => $id));
    }
    
    /**
     * update coupon info by id and bid
     *
     * @param integer $bid
     * @param integer $id
     * @param array $update
     * @return void
     */
    public function updateCouponById($bid, $id, $update)
    {
        $where = "id = " .$this->_wdb->quote($id) . " AND bid= " . $this->_wdb->quote($bid);
        $this->_wdb->update('casino_coupon', $update, $where);
    }
    
    /**
     * update coupon info by uid and bid
     *
     * @param integer $bid
     * @param integer $uid
     * @param array $update
     * @return void
     */
    public function updateCouponByUid($bid, $uid, $aid, $update)
    {
        $where = "uid = " .$this->_wdb->quote($uid) . " AND bid= " . $this->_wdb->quote($bid) . " AND aid= " . $this->_wdb->quote($aid);
        $this->_wdb->update('casino_coupon', $update, $where);
    }

    /**
     * clear lock coupon
     *
     * @param integer $bid
     * @param integer $uid
     * @return void
     */
    public function clearLockCoupon($bid, $uid)
    {
        $sql = "UPDATE casino_coupon SET uid=0,get_time=0,STATUS=0 WHERE STATUS=1 AND bid=:bid AND uid=:uid ";
        $this->_wdb->query($sql, array('bid' => $bid, 'uid' => $uid));
    }
    
    /**
     * has get by bid
     *
     * @return array
     */
    public function hasGetByBid($uid, $bid)
    {
        $sql = "SELECT uid FROM casino_user WHERE status=1 AND award IN(1,2,3,4,5,6,7,8,22) AND uid=:uid AND bid=:bid ";
        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'bid'=>$bid));
        return empty($result) ? false : true;
    }
    
    /**
     * get shop list
     *
     * @return array
     */
    public function getShopList()
    {
        $sql = "SELECT bid AS id,name,map,link FROM casino_shop ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get today shop list
     *
     * @return array
     */
    public function getTodayShopList($time)
    {
        $sql = "SELECT bid AS id,name,map,link FROM casino_shop WHERE time=:time ORDER BY gid ASC";
        return $this->_rdb->fetchAll($sql, array('time'=>$time));
    }
    
    /**
     * get prize info
     *
     * @return array
     */
    public function getPrizeInfo($bid)
    {
        $sql = "SELECT * FROM casino_shop WHERE bid=:bid";
        return $this->_rdb->fetchRow($sql, array('bid' => $bid));
    }
    
    /**
     * get award list
     *
     * @return array
     */
    public function getAwardList()
    {
        $sql = "SELECT id,type,item_cid FROM casino_award_type ";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get award odds list
     *
     * @return array
     */
    public function getAwardOddsList()
    {
        $sql = "SELECT id,odds FROM casino_award_type ";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get award id list
     *
     * @return array
     */
    public function getAwardIdList()
    {
        $sql = "SELECT id FROM casino_award ";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get user coupon list
     *
     * @return array
     */
    public function getUserCouponList($uid)
    {
        $sql = "SELECT c.id,c.code,c.get_time AS getDate,a.content,a.link 
        		FROM casino_coupon AS c,casino_award AS a 
        		WHERE c.bid=a.bid AND c.aid=a.id AND c.status=2 AND c.uid=:uid";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get new counpon by bid
     *
     * @return array
     */
    public function getNewCouponByBid($bid, $aid)
    {
        $sql = "SELECT min(id) FROM casino_coupon WHERE status=0 AND bid=:bid AND aid=:aid ";
        return $this->_rdb->fetchOne($sql, array('bid'=>$bid, 'aid'=>$aid));
    }
    
    /**
     * get user coupon by bid
     *
     * @return array
     */
    public function getUserCouponByBid($uid, $bid, $aid)
    {
        $sql = "SELECT c.id,c.code,c.get_time AS getDate,a.content,a.link FROM casino_coupon AS c,casino_award AS a 
        		WHERE c.aid=a.id AND c.bid=a.bid AND c.status=2 AND c.bid=:bid AND c.aid=:aid AND c.uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'bid'=>$bid, 'aid'=>$aid));
    }
    
    /**
     * get user casino count
     *
     * @param integer $uid
     * @return array
     */
    public function getUserCasinoCount($uid)
    {
        $sql = "SELECT casino_count,casino_count_buy FROM island_user_info WHERE uid=:uid";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get remain coupon count by bid
     *
     * @param integer $bid
     * @return array
     */
    public function getRemainCouponCountByBid($bid, $aid)
    {
        $sql = "SELECT COUNT(1) FROM casino_coupon WHERE STATUS=0 AND bid=:bid AND aid=:aid";
        return $this->_rdb->fetchOne($sql, array('bid' => $bid, 'aid'=>$aid));
    }
    
    
    /**
     * get user award 
     *
     * @return array
     */
    public function getUserAward($uid)
    {
        $sql = "SELECT * FROM casino_user WHERE status=0 AND uid=:uid ";
        return $this->_rdb->fetchRow($sql, array('uid' => $uid));
    }

    /**
     * get award info 
     *
     * @return array
     */
    public function getAwardInfo($award)
    {
        $sql = "SELECT * FROM casino_award_type WHERE id=:award ";
        return $this->_rdb->fetchRow($sql, array('award' => $award));
    }
    
    /**
     * get award remain count
     *
     * @return array
     */
    public function getAwardRemainCount($bid, $id)
    {
        $sql = "SELECT remain_count FROM casino_award WHERE id=:id AND bid=:bid ";
        return $this->_rdb->fetchOne($sql, array('bid' => $bid, 'id' => $id));
    }
    
    /**
     * get user free count 
     *
     * @return integer
     */
    public function getUserFreeCount($uid)
    {
        $sql = "SELECT count FROM island_user_card WHERE cid=55041 AND uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * get user buy count 
     *
     * @return integer
     */
    public function getUserBuyCount($uid)
    {
        $sql = "SELECT count FROM island_user_card WHERE cid=55141 AND uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * get coupon award by Bid
     *
     * @return array
     */
    public function getCouponAwardByBid($bid)
    {
        $sql = "SELECT a.bid,a.id,a.count,t.type FROM casino_award AS a,casino_award_type AS t 
        		WHERE a.id=t.id AND t.type=2 AND bid=:bid ";
        return $this->_rdb->fetchAll($sql, array('bid' => $bid));
    }

    /**
     * get add coupon info
     *
     * @return array
     */
    public function getAddCouponInfo()
    {
        $sql = "SELECT * FROM casino_coupon_add ";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get coupon info by coupon
     *
     * @return array
     */
    public function getCouponInfoByCoupon($coupon)
    {
        $sql = "SELECT c.bid,c.aid,c.uid,c.code,c.get_time,c.use_time,c.status,a.content FROM casino_coupon AS c,casino_award AS a 
				WHERE c.bid=a.bid AND c.aid=a.id AND c.code=:coupon ";
        return $this->_rdb->fetchRow($sql, array('coupon' => $coupon));
    }

    /**
     * use coupon complete
     *
     * @return array
     */
    public function useCouponComplete($coupon)
    {
    	$now = time();
	    $sql = "UPDATE casino_coupon SET status=-1,use_time=$now WHERE code=:coupon ";
	    $this->_wdb->query($sql, array('coupon' => $coupon));
    }

    /**
     * get user lv point
     *
     * @param int uid
     * @return int
     */
    public function getUserLvPoint($uid)
    {
        $sql = "SELECT point FROM casino_user_point WHERE uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * get lv point rank
     *
     * @param int $point
     * @return int
     */
    public function getUserLvPointRank($point)
    {
        $sql = "SELECT COUNT(1) FROM casino_user_point WHERE point>:point ";
        $result = $this->_rdb->fetchOne($sql, array('point' => $point));
        return $result + 1;
    }
    
    /**
     * update user lv point
     *
     * @param int $uid
     * @param int $point
     * @return void
     */
    public function updateUserLvPoint($uid, $point)
    {
    	$sql = "SELECT COUNT(1) FROM casino_user_point WHERE uid=:uid ";
    	$result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
    	
    	if ( $result == 1 ) {
		    $sql = "UPDATE casino_user_point SET point = point + :point WHERE uid=:uid ";
		    $this->_wdb->query($sql, array('uid' => $uid, 'point' => $point));
    	}
    	else {
    		$array = array('uid' => $uid, 'point' => $point);
    		$this->_wdb->insert('casino_user_point', $array);
    		$this->_wdb->lastInsertId();
    	}
    }
    
    /**
     * update user free lv count
     *
     * @param int $uid
     * @return void
     */
    public function updateUserFreeLvCount($uid)
    {
    	$sql = "SELECT COUNT(1) FROM island_user_card WHERE uid=:uid AND cid=55041 ";
    	$result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
    	
    	if ( $result == 1 ) {
		    $sql = "UPDATE island_user_card SET count = 1 WHERE uid=:uid AND cid=55041 ";
		    $this->_wdb->query($sql, array('uid' => $uid));
    	}
    	else {
    		$newCard = array(
        		'uid' => $uid,
				'cid' => 55041,
        		'count' => 1,
				'buy_time' => time(),
				'item_type' => 41);
    		$this->_wdb->insert('island_user_card', $newCard);
    	}
    }
    
    /**
     * get first lv point
     *
     * @return int
     */
    public function getFirstLvPoint()
    {
        $sql = "SELECT max(point) FROM casino_user_point ";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get all lv point
     *
     * @return int
     */
    public function getAllLvPoint()
    {
        $sql = "SELECT sum(point) FROM casino_user_point ";
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * get all join lv user count
     *
     * @return int
     */
    public function getAllJoinLvUserCount()
    {
        $sql = "SELECT COUNT(1) FROM casino_user_point ";
        return $this->_rdb->fetchOne($sql);
    }
    
    /**
     * check user is join lv
     *
     * @param integer $uid
     * @return array
     */
    public function isJoinLv($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT bid FROM $tbName WHERE bid=60131 AND uid=:uid";
        $result = $this->_rdb->fetchOne($sql, array('uid' => $uid));
        return $result > 0 ? true : false;
    }

    /**
     * get first lv point
     *
     * @return int
     */
    public function clearLv($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "DELETE FROM $tbName WHERE uid=:uid AND bid=60131 ";
        $this->_wdb->query($sql, array('uid' => $uid));
    }
    
}