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
class Dal_Island_Level extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_level_user = 'island_level_user';

    /**
     * table name
     *
     * @var string
     */
    protected $table_level_island = 'island_level_island';

    /**
     * table name
     *
     * @var string
     */
    protected $table_level_gift = 'island_level_gift';
    
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert level info
     *
     * @param array $info
     * @return integer
     */
    public function insertLevel($info)
    {
        $this->_wdb->insert($this->table_level_user, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get user level info
     *
     * @param integer $level
     * @return array
     */
    public function getLevelInfo($level)
    {
        $sql = "SELECT * FROM $this->table_level_user WHERE level=:level ";
        return $this->_rdb->fetchRow($sql, array('level'=>$level));
    }

    /**
     * get island level info
     *
     * @param integer $level
     * @return array
     */
    public function getIslandLevelInfo($level)
    {
        $sql = "SELECT * FROM $this->table_level_island WHERE level=:level ";
        return $this->_rdb->fetchRow($sql, array('level'=>$level));
    }

    /**
     * get island level info
     *
     * @param integer $level
     * @return array
     */
    public function getIslandLevelInfoByUserLevel($level)
    {
        $sql = "SELECT * FROM $this->table_level_island WHERE need_level<=:level ORDER BY level DESC LIMIT 0,1 ";
        return $this->_rdb->fetchRow($sql, array('level'=>$level));
    }
    
    /**
     * get the max island level
     *
     * @return integer
     */
    public function getIslandMaxLevel()
    {
        $sql = "SELECT MAX(level) FROM $this->table_level_island ";
        return $this->_rdb->fetchOne($sql);
    }

    /**
     * get level gift info
     *
     * @param integer $level
     * @return array
     */
    public function getLevelGift($level)
    {
        $sql = "SELECT level,cid,name,gold FROM $this->table_level_gift WHERE level=:level ";
        return $this->_rdb->fetchAll($sql, array('level'=>$level));
    }

    /**
     * get level list info
     *
     * @return array
     */
    public function getLevelList()
    {
        $sql = "SELECT u.level,g.gold AS addGem,g.cid AS addCid,i.island,i.visitor_count,u.exp FROM island_level_user AS u 
                LEFT JOIN island_level_island AS i ON u.level = i.need_level 
                LEFT JOIN island_level_gift AS g ON u.level = g.level ORDER BY u.level";
        return $this->_rdb->fetchAll($sql);
    }
}