<?php

require_once 'Dal/Abstract.php';

class Dal_PayLog extends Dal_Abstract
{
    /**
     * user table name
     *
     * @var string
     */
    protected $_table = 'renren_pay_log';

    protected static $_instance;

    /**
     * get Dal_PayLog instance
     *
     * @return Dal_PayLog
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getLogs($uid)
    {
        $sql = "SELECT * FROM $this->_table WHERE uid=:uid AND create_time>1276869600 ORDER BY create_time DESC";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    public function getLogsByMonth($uid, $year, $month)
    {
        $startTime = $year.$month.'01';
        if ($month == 12) {
            $endTime = ($year + 1).'0101';
        }
        else {
            $month += 1;
            $month = $month > 9 ? $month : '0'.$month;
            $endTime = $year.$month.'01';
        }
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);

        $sql = "SELECT * FROM $this->_table WHERE uid=:uid AND create_time>= $startTime AND create_time<$endTime ORDER BY create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    public function addLog($uid, $gold, $amount = 0, $order_id = 0, $time = null)
    {
        $log = array(
        	'uid' => $uid,
        	'gold' => $gold,
        	'amount' => $amount,
        	'order_id' => $order_id
        );

        if (!$time) {
        	$log['create_time'] = time();
        }
        else {
        	$log['create_time'] = $time;
        }

    	$this->_wdb->insert($this->_table, $log);
    }
}