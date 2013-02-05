<?php


class Hapyfish_Island_Dal_User extends Hapyfish_Island_Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'island_user';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_User
     */
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
    
    public function getCoin($uid)
    {
        $sql = "SELECT coin FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    public function getGold($uid)
    {
        $sql = "SELECT gold FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    public function getExp($uid)
    {
        $sql = "SELECT exp FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    public function incExp($uid, $exp)
    {
        $sql = "UPDATE $this->table_user SET exp=exp+:exp WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'exp'=>$exp));    	
    }
    
    public function incCoin($uid, $coin)
    {
        $sql = "UPDATE $this->table_user SET coin=coin+:coin WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'coin'=>$coin));    	
    }
    
    public function decCoin($uid, $coin)
    {
        $sql = "UPDATE $this->table_user SET coin=coin-:coin WHERE uid=:uid AND coin>=:coin";
        $this->_wdb->query($sql, array('uid'=>$uid, 'coin'=>$coin));      	
    }
    
    public function incCoinAndExp($uid, $coin, $exp)
    {
        $sql = "UPDATE $this->table_user SET coin=coin+:coin,exp=exp+:exp WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'coin'=>$coin, 'exp'=>$exp));    	
    }
    
    public function incGold($uid, $gold)
    {
        $sql = "UPDATE $this->table_user SET gold=gold+:gold WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'gold'=>$gold));    	
    }
    
    public function decGold($uid, $gold)
    {
        $sql = "UPDATE $this->table_user SET gold=gold-:gold WHERE uid=:uid AND gold>=:gold";
        $this->_wdb->query($sql, array('uid'=>$uid, 'gold'=>$gold));      	
    }

    /**
     * update user power
     *
     * @param integer $uid
     * @param integer $change
     * @return void
     */
    public function updateUserPower($uid, $change)
    {
        $now = time();
        $sql = "UPDATE $this->table_user SET power=power + :change AND last_power_time=$now WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * update user info by field name
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateUserByField($uid, $field, $change)
    {
        $sql = "UPDATE $this->table_user SET $field = $field + :change WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }


    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForInitIsland($uid)
    {
        $sql = "SELECT exp,next_level_exp,level,island_level,island_name,praise,currently_visitor,title,defense_card,insurance_card 
                FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForVisitor($uid)
    {
        $sql = "SELECT level,defense_card,currently_visitor FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForMoochOwner($uid)
    {
        $sql = "SELECT uid,insurance_card FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForReceive($uid)
    {
        $sql = "SELECT uid,island_level,currently_visitor,praise,exp,level FROM $this->table_user WHERE uid=:uid ";
        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForMooch($uid)
    {
        $sql = "SELECT exp,level,island_level,currently_visitor,queue_visitor FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserForHarvest($uid)
    {
        $sql = "SELECT coin,exp,level FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }
    
    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserDockInfo($uid)
    {
        $sql = "SELECT uid,coin,exp,praise,level,island_level,position_count,currently_visitor,queue_visitor,insurance_card,
                insurance_type,defense_card,mood_word_count FROM $this->table_user WHERE uid=:uid ";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user level info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserLevelInfo($uid)
    {
        $sql = "SELECT uid,next_level_exp,level,island_name,island_level FROM $this->table_user WHERE uid=:uid";

        return $this->_rdb->fetchRow($sql,array('uid'=>$uid));
    }

    /**
     * get user praise info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPraise($uid)
    {
        $sql = "SELECT praise FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    public function updateUserPraise($uid, $change)
    {
        $sql = "UPDATE $this->table_user SET praise=praise + :change WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'change'=>$change));
    }

    /**
     * get user title
     *
     * @param integer $uid
     * @return array
     */
    public function getUserTitle($uid)
    {
        $sql = "SELECT title FROM $this->table_user WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    public function updateUserTitle($uid, $title)
    {
        $sql = "UPDATE $this->table_user SET title=:title WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'title'=>$title));
    }
    
    public function getUserDefenseCardTime($uid)
    {
        $sql = "SELECT defense_card FROM $this->table_user WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    public function updateUserDefenseCardTime($uid, $time)
    {
        $sql = "UPDATE $this->table_user SET defense_card=:time WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'time'=>$time));
    }
    
    public function getUserInsuranceCardTime($uid)
    {
        $sql = "SELECT insurance_card FROM $this->table_user WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    public function updateUserInsuranceCardTime($uid, $time)
    {
        $sql = "UPDATE $this->table_user SET insurance_card=:time WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid, 'time'=>$time));
    }
    
    /**
     * get user position count
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPositionCount($uid)
    {
        $sql = "SELECT position_count FROM $this->table_user WHERE uid=:uid";

        return $this->_rdb->fetchOne($sql,array('uid'=>$uid));
    }
    
    public function updateUserPositionCount($uid, $change)
    {
        $sql = "UPDATE $this->table_user SET position_count=position_count+:change WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid' => $uid, 'change' => $change));
    }
    
    /**
     * get user level info
     *
     * @param integer $level
     * @return array
     */
    public function getUserLevelInfoByLevel($level)
    {
        $sql = "SELECT * FROM island_level_user WHERE level=:level";
        
        return $this->_rdb->fetchRow($sql, array('level' => $level));
    }
}