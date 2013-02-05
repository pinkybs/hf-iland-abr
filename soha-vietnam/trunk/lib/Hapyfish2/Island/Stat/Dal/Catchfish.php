<?php


class Hapyfish2_Island_Stat_Dal_Catchfish
{
    protected static $_instance;
    
    private $_tb = 'stat_catchfish';

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
    public function update($day,$count)
    {
        $tbname = $this->_tb;

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];
		$sql = 'UPDATE '.$tbname.' SET count=:count WHERE create_time=:date AND count=0';
    	return $wdb->query($sql, array('count'=>$count, 'date'=>$day));  
    }
    public function updateUserNum($day,$usernums)
    {
        $tbname = $this->_tb;

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];
		$sql = 'UPDATE '.$tbname.' SET usernums=:usernums WHERE create_time=:date AND usernums=0';
    	return $wdb->query($sql, array('usernums'=>$usernums, 'date'=>$day));  
    }  
    public function updateCoinAndCard($day,$coin, $card)
    {
        $tbname = $this->_tb;

        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];
		$sql = 'UPDATE '.$tbname.' SET coin=:coin,card=:card WHERE create_time=:date AND coin=0 AND card=0';
    	return $wdb->query($sql, array('coin'=>$coin, 'card'=>$card, 'date'=>$day));  
    }        
}