<?php

class Hapyfish2_Island_Event_Dal_CatchFish
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Event_Dal_CatchFish
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    protected function getBasicDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getBasicDB($key);
    }   
    public function getTBTableName($uid)
    {
    	$id = floor($uid/8) % 10;
    	return 'island_taobao_userdiscount_' . $id;
    }
	public function getFishDomain()
	{
    	$db = $this->getBasicDB();
        $rdb = $db['r'];
        $sql = 'SELECT * FROM island_catchfish_domain';
        return $rdb->fetchAll($sql);
	}     
	/*
	 * 通过当前航路 获取鱼
	 * */
	public function getFishList($level)
	{
		$data = array();
		$db = $this->getBasicDB();
        $rdb = $db['r'];
    	$sql = 'SELECT id,probability FROM island_catchfish_info WHERE level=:level';
    	$data = $rdb->fetchAll($sql, array('level'=>$level));
    	return $data;
	}
	/**
	 * 获取所有鱼信息
	 */
	public function getFishListAll()
	{
		$data = array();
		$db = $this->getBasicDB();
        $rdb = $db['r'];
    	$sql = "SELECT * FROM island_catchfish_info WHERE gifts!='' GROUP BY class_name";
    	$data = $rdb->fetchAll($sql);
    	return $data;
	}	
	public function getFishInfo($id) {
		$data = array();
		$db = $this->getBasicDB();
        $rdb = $db['r'];	
        $sql = 'SELECT id,level,name,class_name,gifts FROM island_catchfish_info WHERE id=:id';	
        $info = $rdb->fetchRow($sql, array('id'=>$id));
    	return $info;     
	}
	//获取面板的淘宝商品信息
	public function getProduct()
	{
		$date = date('Ymd');
		$db = $this->getBasicDB();
        $rdb = $db['r'];
        $sql = 'SELECT * FROM island_taobao_product WHERE date=:date LIMIT 4';
        $data = $rdb->fetchAll($sql, array('date'=>$date));
        return $data;	
	}
	public function checkNum($level, $productid, $discount)
	{
		$db = $this->getBasicDB();
        $rdb = $db['r'];		
		$sql='SELECT num FROM island_taobao_probability WHERE pid=:pid AND level=:level AND discount=:discount';
		$data = $rdb->fetchOne($sql, array('pid'=>$productid, 'level'=>$level, 'discount'=>$discount));
		return $data;
	}
	//获取折扣券的概率
	public function getProductProbability($level, $productid)
	{
		$db = $this->getBasicDB();
        $rdb = $db['r'];
        $sql = 'SELECT * FROM island_taobao_probability WHERE level=:level AND pid=:pid ORDER BY discount ASC';
        $data = $rdb->fetchAll($sql, array('level'=>$level, 'pid'=>$productid));
        return $data;		
	}
	//给用户添加折扣券
	public function addTaoBaoDiscount($uid, $number, $productid, $discount, $level)
	{
		$time = time();
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $tableName = $this->getTBTableName($uid);	
	    $rows = array(
	    	'uid'		=>	$uid, 
	    	'pid'		=>	$productid, 
	    	'number'	=>	$number,
	    	'gettime'	=>	$time,
	    	'discount'	=>	$discount,
	    	'level'		=>	$level,
	    	'status'	=>	1
	    );
	    return $wdb->insert($tableName, $rows);        	
	}
	/*
	 * 更新用户折扣券
	 * 放弃领取
	 * */
	public function cancelTaoBaoDiscount($uid, $pid, $number)
	{
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $tableName = $this->getTBTableName($uid);
		$sql = "UPDATE $tableName SET status=0 WHERE uid=:uid AND pid=:pid AND number=:number";
		return $wdb->query($sql, array('uid'=>$uid, 'number'=>$number, 'pid'=>$pid));  
	}
	
	//获取用户折扣券
	public function getUserDiscount($uid)
	{
		$db = Hapyfish2_Db_Factory::getDB($uid);
		$rdb = $db['r'];
		$tableName = $this->getTBTableName($uid);
		$sql = 'SELECT pid,number,discount,gettime,status,level FROM '.$tableName.' WHERE uid=:uid AND status!=0 ORDER BY gettime DESC LIMIT 20';
		$data = $rdb->fetchAll($sql, array('uid'=>$uid));

		$bdb = $this->getBasicDB();
		$rbdb = $bdb['r'];
		if($data) {
			foreach($data as $k=>$v) {
				$sql = 'SELECT name FROM island_taobao_product WHERE pid=:pid';
				$productName = $rbdb->fetchOne($sql, array('pid'=>$v['pid']));
				$data[$k]['name'] = $productName;
				$sql = 'SELECT urla,urlb FROM island_taobao_probability WHERE pid=:pid AND discount=:discount AND level=:level';
				$dsInfo=array();
				$dsInfo = $rbdb->fetchRow($sql, array('pid'=>$v['pid'], 'discount'=>$v['discount'], 'level'=>$v['level']));
				$data[$k]['urla'] = $dsInfo['urla'];
				$data[$k]['urlb'] = $dsInfo['urlb'];
			}
		}
		return $data;
	}
	
	public function updateDiscountNum($productid, $discount) {
    	$db = $this->getBasicDB();
        $wdb = $db['w'];
		$sql = "UPDATE island_taobao_probability SET num=num-1 WHERE pid=:pid AND discount=:discount";
		return $wdb->query($sql, array('pid'=>$productid, 'discount'=>$discount));  		
	}
	
	
	//以下方法供后台用
	public function getDiscountInfo($uid, $number)
	{
		$db = Hapyfish2_Db_Factory::getDB($uid);
		$rdb = $db['r'];
		$tableName = $this->getTBTableName($uid);
		$sql = 'SELECT * FROM '.$tableName.' WHERE uid=:uid AND number=:number';
		$data = $rdb->fetchRow($sql, array('uid'=>$uid, 'number'=>$number));
		return $data;	
	}
	public function updateDiscountInfo($uid, $number)
	{
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $tableName = $this->getTBTableName($uid);
		$sql = "UPDATE $tableName SET status=2 WHERE uid=:uid AND number=:number";
		return $wdb->query($sql, array('uid'=>$uid, 'number'=>$number));  	
	}
	public function getProductById($pid)
	{
		$db = $this->getBasicDB();
		$rdb = $db['r'];
		$sql = 'SELECT * FROM island_taobao_product WHERE pid=:pid';
		$data = $rdb->fetchRow($sql, array('pid'=>$pid));
		return $data;
	}
	public function updateProductById($pid, $fileds)
	{
		$db = $this->getBasicDB();
		$wdb = $db['w'];	
		$where = $wdb->quoteinto('pid = ?', $pid);
    	return $wdb->update("island_taobao_product", $fileds, $where);	
	}
	public function getProbabilityById($productid)
	{
		$db = $this->getBasicDB();
        $rdb = $db['r'];
        $sql = 'SELECT * FROM island_taobao_probability WHERE pid=:pid ORDER BY level ASC,discount ASC';
        $data = $rdb->fetchAll($sql, array('pid'=>$productid));
        return $data;		
	}
	public function updateProbabilityById($id, $fileds)
	{
		$db = $this->getBasicDB();
		$wdb = $db['w'];	
		$where = $wdb->quoteinto('id = ?', $id);
    	return $wdb->update("island_taobao_probability", $fileds, $where);	
	}
	public function checkProduct($pid) 
	{
		$db = $this->getBasicDB();
		$rdb = $db['r'];
		$sql = 'SELECT COUNT(*) FROM island_taobao_product WHERE pid=:pid';
		$count = $rdb->fetchOne($sql, array('pid'=>$pid));
		return $count;
	}
	public function addProduct($fields)
	{
    	$db = $this->getBasicDB();
        $wdb = $db['w'];
	    return $wdb->insert("island_taobao_product", $fields);        	
	}
	public function checkProbability($pid, $discount, $level) 
	{
		$db = $this->getBasicDB();
		$rdb = $db['r'];
		$sql = 'SELECT COUNT(*) FROM island_taobao_probability WHERE pid=:pid AND discount=:discount AND level=:level';
		$count = $rdb->fetchOne($sql, array('pid'=>$pid, 'discount'=>$discount, 'level'=>$level));
		return $count;
	}
	public function addProbability($fields)
	{
    	$db = $this->getBasicDB();
        $wdb = $db['w'];
	    return $wdb->insert("island_taobao_probability", $fields);        	
	}				
}