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
class Dal_Island_Praise extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_praise = 'island_praise';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get add visitore count by praise
     *
     * @param integer $praise
     * @return array
     */
    public function getAddVisitorByPraise($praise)
    {
        $sql = "SELECT max(visitor_count) FROM island_praise WHERE praise <= :praise ";

        return $this->_rdb->fetchOne($sql, array('praise'=>$praise));
    }

    /**
     * get add visitore count by praise
     *
     * @param integer $praise
     * @param integer $shipId
     * @return array
     */
    public function getShipAddVisitorByPraise($shipId, $praise)
    {
        $sql = "SELECT max(num) FROM island_praise_ship WHERE praise <= :praise AND sid=:sid ";

        return $this->_rdb->fetchOne($sql, array('praise'=>$praise, 'sid'=>$shipId));
    }

    /**
     * get add visitore count by sid
     *
     * @param integer $shipId
     * @return array
     */
    public function getShipAddVisitorBySid($shipId)
    {
        $sql = "SELECT praise,num FROM island_praise_ship WHERE sid=:sid ";

        $praiseList = $this->_rdb->fetchAll($sql, array('sid'=>$shipId));
        $result = array();
        foreach ( $praiseList as $value ) {
            $result[] = $value['praise'] . ',' . $value['num']; 
        }
        return $result;
    }
}