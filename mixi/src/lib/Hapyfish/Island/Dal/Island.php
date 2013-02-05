<?php

class Hapyfish_Island_Dal_Island extends Hapyfish_Island_Dal_Abstract
{
	protected $table_level_island = 'island_level_island';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Island
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get island level info
     *
     * @param integer $level
     * @return array
     */
    public function getIslandLevelInfo($level)
    {
        $sql = "SELECT * FROM $this->table_level_island WHERE level=:level";
        
        return $this->_rdb->fetchRow($sql, array('level' => $level));
    }
    
    /** get island level info by user level
     *
     * @param integer $level
     * @return array
     */
    public function getIslandLevelInfoByUserLevel($level)
    {
        $sql = "SELECT * FROM $this->table_level_island WHERE need_level<=:level ORDER BY level DESC LIMIT 0,1";
        
        return $this->_rdb->fetchRow($sql, array('level' => $level));
    }
    
}