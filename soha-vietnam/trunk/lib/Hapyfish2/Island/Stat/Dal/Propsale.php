<?php
class Hapyfish2_Island_Stat_Dal_Propsale
{
    protected static $_instance;

    private $_tbName = 'day_prop_sale';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Stat_Dal_DaycLoadTm
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getData($dtBegin, $dtEnd, $cid)
    {
    	$tbname = $this->_tbName;
    	$sql = "SELECT cid,date,num,coin,gold FROM $tbname WHERE date>=:dtBegin AND date<=:dtEnd AND cid=:cid";

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('dtBegin' => $dtBegin, 'dtEnd' => $dtEnd, 'cid'=>$cid));
    }
    
    public function getTotal($dtBegin, $dtEnd, $cid)
    {
    	$tbname = $this->_tbName;
    	$sql = "SELECT cid,sum(num) as tnum,sum(coin) as tcoin,sum(gold) as tgold FROM $tbname WHERE date>=:dtBegin AND date<=:dtEnd AND cid=:cid group by cid";

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $rdb = $db['r'];

        return $rdb->fetchRow($sql, array('dtBegin' => $dtBegin, 'dtEnd' => $dtEnd, 'cid'=>$cid));
    }
    
    public function insertDb($info)
    {
    	$tbname = $this->_tbName;
        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];

        return $wdb->insert($tbname, $info);
    }
}