<?php


class Hapyfish2_Island_Stat_Dal_Tutorial
{
    protected static $_instance;
    private $_tb_day_tutorial = 'day_tutorial';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Stat_Dal_Tutorial
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
        $tbname = $this->_tb_day_tutorial;

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];

        return $wdb->insert($tbname, $info);
    }
    
    public function getDay($day)
    {
        $tbname = $this->_tb_day_tutorial;
        $sql = "SELECT log_time,data FROM $tbname WHERE log_time=:log_time";
        
        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $rdb = $db['r'];
        
        return $rdb->fetchRow($sql, array('log_time' => $day));
    }
    
    
}