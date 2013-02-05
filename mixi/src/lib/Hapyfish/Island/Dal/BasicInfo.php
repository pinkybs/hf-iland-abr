<?php


class Hapyfish_Island_Dal_BasicInfo extends Hapyfish_Island_Dal_Abstract
{

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Login
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	public function getBackgroundList()
	{
		$sql = 'SELECT bgid AS cid,name,class_name AS className,introduce AS content,null AS map,price,price_type AS priceType,
		        sale_price AS salePrice,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew
				FROM island_island_background';
		
		return $this->_rdb->fetchAll($sql);
	}
	
	public function getBuildingList()
	{
		$sql = 'SELECT bid AS cid,name,class_name AS className,content,map,price,price_type AS priceType,sale_price AS salePrice,
		        nodes,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew
				FROM island_island_building';
				
		return $this->_rdb->fetchAll($sql);
	}
	
	public function getPlantList()
	{
		$sql = 'SELECT bid AS cid,name,class_name AS className,content,map,price,price_type AS priceType,sale_price AS salePrice,
		        nodes,item_type AS type,need_level AS needLevel,add_praise AS addPraise,new AS isNew,level,ticket,
		        pay_time AS payTime,safe_time AS safeTime,safe_coin_num AS safeCoinNum,need_praise AS needPraise,
		        next_level_bid AS nextCid,act_name AS actName FROM island_island_plant';
		        
		return $this->_rdb->fetchAll($sql);
	}
	
	public function getCardList()
	{
        $sql = 'SELECT cid,name,class_name AS className,introduce AS content,null AS map,price,price_type AS priceType,
                sale_price AS salePrice,need_level AS needLevel,item_type AS type,new AS isNew FROM island_card';
				
        return $this->_rdb->fetchAll($sql);
	}
	
	public function getLevelList()
	{
        $sql = 'SELECT u.level,g.gold AS addGem,g.cid AS addCid,i.island,i.visitor_count,u.exp FROM island_level_user AS u 
                LEFT JOIN island_level_island AS i ON u.level = i.need_level 
                LEFT JOIN island_level_gift AS g ON u.level = g.level ORDER BY u.level';
        
        return $this->_rdb->fetchAll($sql);
	}
	
	public function getDailyTaskList()
	{
        $sql = 'SELECT id AS taskClassId,1 AS type,content,name,need_field AS needType,null AS needCid,need_num AS needNum,
                level,need_level AS unLockLevel,coin AS addCoin,exp AS addExp,cid AS addItemCid,1 AS addItemNum,title AS addTitle 
                FROM island_task_daily';
                
        return $this->_rdb->fetchAll($sql);
	}
	
	public function getBuildTaskList()
	{
        $sql = 'SELECT id AS taskClassId,2 AS type,content,name,need_field AS needType,need_cid AS needCid,need_num AS needNum,
                level,need_level AS unLockLevel,coin AS addCoin,exp AS addExp,cid AS addItemCid,1 AS addItemNum,title AS addTitle 
                FROM island_task_build';
                
        return $this->_rdb->fetchAll($sql);
	}
	
	public function getAchievementTaskList()
	{
        $sql = 'SELECT a.id AS taskClassId,3 AS type,a.content,a.name,a.need_field AS needType,null AS needCid,a.need_num AS needNum,
                a.level,a.need_level AS unLockLevel,a.coin AS addCoin,a.exp AS addExp,a.cid AS addItemCid,1 AS addItemNum,t.title AS addTitle,
                next_task AS nextTaskId,next_two_task AS nextTwoTaskId 
                FROM island_task_achievement AS a,island_title AS t WHERE a.title=t.id';
                
        return $this->_rdb->fetchAll($sql);
	}
	
	public function getTitleList()
	{
        $sql = 'SELECT t.id,t.title AS name,a.coin,a.exp FROM island_title AS t,island_task_achievement AS a WHERE t.id=a.title ORDER BY id';
                
        return $this->_rdb->fetchAll($sql);
	}
	
	public function getShipList()
	{
        $sql = 'SELECT sid AS boatId,sid AS level,name,class_name AS className,start_visitor_num AS startVisitorNum, 
                safe_visitor_num AS safeVisitorNum,wait_time AS waitTime,safe_time_1 AS safeTime1,safe_time_2 AS safeTime2, 
                coin,gem,level AS needLevel FROM island_ship';

        return $this->_rdb->fetchAll($sql);
	}
	
    public function getShipAddVisitorBySid($shipId)
    {
        $sql = 'SELECT praise,num FROM island_praise_ship WHERE sid=:sid';
        $praiseList = $this->_rdb->fetchAll($sql, array('sid' => $shipId));
        
        $result = array();
        foreach ( $praiseList as $value ) {
            $result[] = $value['praise'] . ',' . $value['num']; 
        }
        return $result;
    }
}