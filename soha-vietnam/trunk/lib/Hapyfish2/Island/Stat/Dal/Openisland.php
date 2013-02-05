<?php


class Hapyfish2_Island_Stat_Dal_Openisland
{
    protected static $_instance;
    
    private $_tb = 'openisland';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Stat_Dal_Openisland
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function insert($info)
    {
        $tbname = $this->_tb;

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];

    	return $wdb->insert($tbname, $info);
    }
    
}