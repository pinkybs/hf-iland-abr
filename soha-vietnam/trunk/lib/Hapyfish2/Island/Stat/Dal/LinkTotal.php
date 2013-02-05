<?php

class Hapyfish2_Island_Stat_Dal_LinkTotal
{
    protected static $_instance;
    
    private $_tb_linkTotal = 'link_total';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Stat_Dal_Main
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function addLinkTotal($strDate, $link_total)
    {
        $tbname = $this->_tb_linkTotal;
    	$sql = "INSERT INTO $tbname (create_date, link_total) VALUES (:create_date, :link_total)";
    	
        $db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $wdb = $db['w'];
        
        $wdb->query($sql, array('create_date' => $strDate, 'link_total' => $link_total));
    }
    
    public function checkLinkData($startDate = 0, $endDate = 0)
    {
    	$tbname = $this->_tb_linkTotal;
    	
    	if ($endDate == 0) {
    		$endDate = $startDate;
    	} else if ($startDate == 0) {
    		$startDate = $endDate;
    	}
    	
    	$sql = "SELECT link_total FROM $tbname WHERE create_date BETWEEN :start_date AND :end_date";
    	
		$db = Hapyfish2_Db_FactoryStat::getStatLogDB();
        $rdb = $db['r'];
        
        return $rdb->fetchCol($sql, array('start_date' => $startDate, 'end_date' => $endDate));
    }
}