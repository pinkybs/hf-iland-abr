<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/08    Liz
 */
class Dal_Island_Compute extends Dal_Abstract
{
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
     * insert compute count
     *
     * @param array $info
     * @return void
     */
    public function insertComputeCount($info)
    {
        $this->_wdb->insert('compute_count', $info);
    }

    /**
     * get level count list
     *
     * @return array
     */
    public function getLevelCountList()
    {
        $sql = "select level,count(1) as count from island_user group by level order by level";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get pay user level count list
     *
     * @return array
     */
    public function getPayLevelCountList($start)
    {
        $sql = "select level,count(1) as count from island_user 
                where uid in(SELECT uid FROM island_user_info WHERE last_login_time >= $start AND today_login_count >= 1)  
                group by level order by level";
        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get level count
     *
     * @param integer $level
     * @return integer
     */
    public function getLevelCount($level)
    {
        $sql = "SELECT count(1) FROM island_user WHERE level=:level";
        return $this->_rdb->fetchOne($sql, array('level' => $level));
    }

    /**
     * get level count
     *
     * @param integer $level
     * @return integer
     */
    public function getLevelCountByTime($level, $start, $end)
    {
        $sql = "SELECT count(1) FROM island_user WHERE level=:level AND create_time >= $start AND create_time < $end ";
        return $this->_rdb->fetchOne($sql, array('level' => $level));
    }

    public function getAllUserCount()
    {
        $sql = "SELECT count(1) FROM island_user ";
        return $this->_rdb->fetchOne($sql);
    }

    public function getAddUserCountByTime($start, $end)
    {
        $sql = "SELECT count(1) FROM island_user WHERE create_time >= $start AND create_time < $end";
        return $this->_rdb->fetchOne($sql);
    }

    public function getActiveCountByTime($start)
    {
        $sql = "SELECT count(1) FROM island_user_info WHERE last_login_time >= $start AND today_login_count >= 1";
        return $this->_rdb->fetchOne($sql);
    }

    public function getComputeCountByTime($time)
    {
        $sql = "SELECT * FROM compute_count WHERE create_time=:time";
        return $this->_rdb->fetchRow($sql, array('time'=>$time));
    }

    public function getPayCountByTime($start, $end)
    {
        $sql = "SELECT count(1) FROM island_payment WHERE create_time < $end AND create_time >= $start AND status=1";
        return $this->_rdb->fetchOne($sql);
    }

    public function getPayCountByTimeWebMoney($start, $end)
    {
        $sql = "SELECT count(1) FROM island_webmoney_pay WHERE create_time < $end AND create_time >=$start AND complete=1";
        return $this->_rdb->fetchOne($sql);
    }
    
    public function getPayCountByAmount($start, $end)
    {
        $sql = "SELECT amont,count(1) AS count FROM island_payment 
                WHERE status=1 AND create_time>$start AND create_time<$end GROUP BY amont";
        $result = $this->_rdb->fetchAll($sql);
        
        $payTypeList = array('10'=>0,'20'=>0,'50'=>0,'100'=>0);
        foreach ( $result AS $pay ) {
        	if ( $pay['amont'] == 500 ) {
        		$payTypeList['10'] = $pay['count'];
        	}
        	else if ( $pay['amont'] == 1000 ) {
        		$payTypeList['20'] = $pay['count'];
        	}
        	else if ( $pay['amont'] == 2000 ) {
        		$payTypeList['50'] = $pay['count'];
        	}
        	else if ( $pay['amont'] == 3000 ) {
        		$payTypeList['100'] = $pay['count'];
        	}
        	else if ( $pay['amont'] == 5000 ) {
        		$payTypeList['200'] = $pay['count'];
        	}
        	else if ( $pay['amont'] == 10000 ) {
        		$payTypeList['500'] = $pay['count'];
        	}
        }
        return $payTypeList;
    }
    
    public function getGoldCountByTime($start, $end)
    {
        $sql = "SELECT sum(amont) FROM island_payment WHERE create_time < $end AND create_time >= $start AND status=1";
        $result = $this->_rdb->fetchOne($sql);
        if ( empty($result) ) {
        	$result = 0;
        }
        return $result;
    }

    public function getGoldCountByTimeWebMoney($start, $end)
    {
        $sql = "SELECT sum(money) FROM island_webmoney_pay WHERE create_time < $end AND create_time >=$start AND complete=1";
        $result = $this->_rdb->fetchOne($sql);
        if ( empty($result) ) {
        	$result = 0;
        }
        return $result;
    }
    
    public function getGoldCountAll()
    {
        $sql = "SELECT sum(amont) FROM island_payment WHERE status=1";
        $result = $this->_rdb->fetchOne($sql);
        if ( empty($result) ) {
            $result = 0;
        }
        return $result;
    }
    
    /**
     * get pay gold sum and total people
     *
     * @param integer $start
     * @param integer $end
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getGoldSumByTime($pageIndex = 1, $pageSize = 20)
    {
        $start = ($pageIndex - 1) * $pageSize;

        $sql = "SELECT pay_amount,COUNT(pay_amount) AS pay_num FROM (SELECT SUM(amont) AS pay_amount,COUNT(uid) AS counts 
                FROM island_payment WHERE status = 1 GROUP BY uid) AS sum1
                GROUP BY sum1.pay_amount ORDER BY pay_amount DESC LIMIT $start,$pageSize";

        return $this->_rdb->fetchAll($sql);
    }

    public function getNoLoginCount($start, $end)
    {
        $sql = "SELECT count(1) FROM island_user_info WHERE last_login_time >= $start AND last_login_time < $end";
        return $this->_rdb->fetchOne($sql);
    }
    
    
}