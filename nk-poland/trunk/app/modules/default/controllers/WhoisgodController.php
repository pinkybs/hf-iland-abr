<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com,
 * 2011-4-29
 * */
define('ADMIN_USERNAME','admin'); 					// Admin Username
define('ADMIN_PASSWORD','fanqienigexihongshi');  	// Admin Password

class WhoisgodController extends Zend_Controller_Action
{
	
	protected $_btl_key = 'bottle:list';
	
	public function init()
	{
		// http 401 验证
		if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
           $_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME ||$_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
			Header("WWW-Authenticate: Basic realm=\"Who is god of wealth, Login\"");
			Header("HTTP/1.0 401 Unauthorized");

			echo <<<EOB
				<html><body>
				<h1>Rejected!</h1>
				<big>Wrong Username or Password!</big>
				</body></html>
EOB;
			exit;
		}
		
		$this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
	}
		
	public function indexAction()
	{
		$list = array();
		$list = Hapyfish2_Island_Cache_Hash::get($this->_btl_key);
		$list = unserialize($list);
		$list = is_array($list) ? $list : array(); 
		
		
		
		$this->view->list = $list;
	}
	
	// 增加一个寻宝季
	public function addbottleoneAction()
	{
		$list = array();
		$list = Hapyfish2_Island_Cache_Hash::get($this->_btl_key);
		$list = unserialize($list);
		$list = is_array($list) ? $list : array(); 
		$count = count($list);
		
		$list[ $count ] = array('name'=>"寻宝第1季", 'tips'=>"寻宝第1季", 'online'=>false, 'qid'=>'1'); 
		$list = Hapyfish2_Island_Cache_Hash::set($this->_btl_key, serialize($list));
		
		Hapyfish2_Island_Cache_Bottle::initRows($count, 20);
		
		echo $count;
		
		exit();
	}
	
	public function editbottleAction()
	{
		$id = $this->_request->getParam('id');
		$erron=-1;
		
		if (empty($id) && $id !== '0') {
			$erron = 1;
		}
		
		if ($this->_request->isPost()) {
			
			if ($erron == -1) {
				
				$online = $this->_request->getParam('online');
				$online = empty($online) ? false : true;
				$name = $this->_request->getParam('name');
				$tips = $this->_request->getParam('tips');
				$qid = $this->_request->getParam('qid');
				
				$btl_ids = $this->_request->getParam('btl_ids');
				$btl_name = $this->_request->getParam('btl_name');
				$btl_tips = $this->_request->getParam('btl_tips');
				$btl_type = $this->_request->getParam('btl_type');
				$btl_itemids = $this->_request->getParam('btl_itemids');
				$btl_num = $this->_request->getParam('btl_num');
				$btl_odds = $this->_request->getParam('btl_odds');
				$btl_coin = $this->_request->getParam('btl_coin');
				$btl_gold = $this->_request->getParam('btl_gold');
				$btl_starfish = $this->_request->getParam('btl_starfish');
				
				
				$list = Hapyfish2_Island_Cache_Hash::get($this->_btl_key);
				$list = unserialize($list);
				$list[$id]['online'] = $online;
				$list[$id]['name'] = $name;
				$list[$id]['tips'] = $tips;
				$list[$id]['qid'] = $qid;
				$list = serialize($list);
				
				Hapyfish2_Island_Cache_Hash::set($this->_btl_key, $list);
				
				$a = $b = 0;
				
				foreach ($btl_ids as $key => $val) {
					$info = array('btl_name'=>$btl_name[$key], 'btl_tips'=>$btl_tips[$key],
					'type'=>$btl_type[$key], 'coin'=>$btl_coin[$key], 'gold'=>$btl_gold[$key],
					'item_id'=>$btl_itemids[$key], 'odds'=>$btl_odds[$key], 'num'=>$btl_num[$key],
					'starfish'=>$btl_starfish[$key]);
					
					Hapyfish2_Island_Cache_Bottle::update($btl_ids[$key], $id, $info);
				}
				
				
			}
			
		}
		
		
		
		$list = Hapyfish2_Island_Cache_Hash::get($this->_btl_key);
		$list = unserialize($list);
		$info = $list[$id];
		
		$this->view->info = $info;
		
		$list = Hapyfish2_Island_Cache_Bottle::getAllByBottleId($id);
		$this->view->list = $list['list'];
				
		
		$this->view->id = $id;
		$this->view->erron = $erron;
	}
	
	
	
	// 给玩家发送钥匙
    public function sendcardtouserAction()
    {
    	$uid = $this->_request->getParam('uid');
    	if ($uid) {
    		$com = new Hapyfish2_Island_Bll_Compensation();
	    	$com->setItem('86241', 10);
	    	$com->sendOne($uid,'');
	    	echo 'ok';
    	} else {
    		echo 'no';
    	}
    	exit();
    }
    
    // 清除今天领奖缓存
    public function clearbottletodaytfAction()
    {
    	$uid = $this->_request->getParam('uid');
    	if ($uid) {
    		$key = 'bottle:todaytf:' . $uid;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$cache->delete($key);
			echo 'ok';
    	} else {
    		echo 'no';
    	}
    	
		exit();
    }
    
    // 重置掉宝箱缓存
    public function reloadbottleallAction()
   	{
   		$btl_id = $this->_request->getParam('btl_id');
   		if ($btl_id) {
   			$hashkey = 'bottle:list';
			Hapyfish2_Island_Cache_Hash::reloadVal($hashkey);
			Hapyfish2_Island_Cache_Bottle::reloadAllByBottleId($btl_id);
			echo 'ok';
   		} else {
   			echo 'no';
   		}
   		
		exit();
    }

    
    private function _prerand($items, $num)
    {
    	$temp = array();
    	
    	for ($i=0; $i<$num; $i++) {
    		$rand = rand ($items['interval'][0]['a'], $items['interval'][19]['b']);
    		
    		foreach ($items['interval'] as $key => $val) {
				if ($val['a'] <= $rand && $rand < $val['b']) {
					$temp[] = $val['id'];
					break;
				}
			}
    	}
    	
    	return $temp;
    }
    
    // 转多少个十次，可以把6个物品集齐
   	public function precomputed10Action()
   	{
   		$btl_id = $this->_request->getParam('btl_id', 0);			// 第几季
   		$ids = $this->_request->getParam('ids', '8,9,13,14,17,18');	// 监控的物品
   		$ids = explode(',', $ids);
   		
   		
   		
//   		$bottlelist = Hapyfish2_Island_Cache_Hash::get($this->_btl_key);
//   		$bottlelist = unserialize($bottlelist);
//   		
//   		$this->view->bottlelist = $bottlelist;
   		
   		
   		
   		if ($btl_id || $btl_id == 0) {
   			
   			// 获得季，物品
   			$items = Hapyfish2_Island_Cache_Bottle::getAllByBottleId($btl_id);
   			$sum = 0;
   			
   			for ($i=0; $i<1000; $i++) {
   			
	   			$hash = array();
	   			$total = 0;
	   			while (true) {
	   				
	   				$total++;
	   			
		   			$randlist = $this->_prerand($items, 10);
		   			
		   			foreach ($randlist as $key => $val) {
		   				if (isset($hash)) {
		   					$hash[$val]++;
		   				} else {
		   					$hash[$val] = 1;
		   				}
		   			}
		   			
		   			$tf = true;
		   			foreach ($ids as $ikey => $ival) {
		   				if (empty($hash[$ival])) {
		   					$tf = false;
		   				}
		   			}
		   			
		   			if ($tf) 
		   				break;
	
	   			}
	   			
	   			$sum = $sum + $total;
   			}
   			
   			echo $sum / 1000;
//   			echo $total . "<br />";
//   			ksort($hash);
   			
   			Zend_Debug::dump($hash);
   			$this->view->values = $items;
   		}
   		
   		exit();
   	}
   	
   	public function precomputed1Action()
   	{
   		$btl_id = $this->_request->getParam('btl_id', 0);			// 第几季
   		$ids = $this->_request->getParam('ids', '8,9,13,14,17,18');	// 监控的物品
   		$ids = explode(',', $ids);
   		
   		if ($btl_id || $btl_id == 0) {
   			
   			// 获得季，物品
   			$items = Hapyfish2_Island_Cache_Bottle::getAllByBottleId($btl_id);
			
   			$sum = 0;
   			
   			for ($i=0; $i<1000; $i++) {
   			
	   			$hash = array();
	   			$total = 0;
	   			while (true) {
	   				
	   				$total++;
	   			
		   			$randlist = $this->_prerand($items, 1);
		   			
		   			foreach ($randlist as $key => $val) {
		   				if (isset($hash)) {
		   					$hash[$val]++;
		   				} else {
		   					$hash[$val] = 1;
		   				}
		   			}
		   			
		   			$tf = true;
		   			foreach ($ids as $ikey => $ival) {
		   				if (empty($hash[$ival])) {
		   					$tf = false;
		   				}
		   			}
		   			
		   			if ($tf) 
		   				break;
	
	   			}
	   			$sum = $sum + $total;
   			}
   			
   			echo $sum / 1000;
   			
//   			echo $total . "<br />";
//   			ksort($hash);
   			
   			Zend_Debug::dump($hash);
   			$this->view->values = $items;
   		}
   		
   		exit();
   		
   	}
   	
   	
   	public function reloadbtlAction()
   	{
   		$btl_id = $this->_request->getParam('btl_id');
   		
   		Zend_Debug::dump(Hapyfish2_Island_Cache_Bottle::reloadAllByBottleId($btl_id));
   		
   		exit();
   	}
   	
   	public function getuserinfoAction()
   	{
   		$uid = $this->_request->getParam('uid');
   		
   		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
   		
   		Zend_Debug::dump($user);
   		
   		exit();
   	}
   	
   	public function getqueueAction()
   	{
   		Hapyfish2_Island_Cache_BottleQueue::getall();
   	}
	
   	public function clearqueueAction()
   	{
   		Zend_Debug::dump(Hapyfish2_Island_Cache_BottleQueue::clear());
   		exit();
   	}

}