<?php

define('ADMIN_USERNAME','admin'); 					// Admin Username
define('ADMIN_PASSWORD','yewushuang_920');  	// Admin Password

class MycatchfishController extends Zend_Controller_Action
{


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

	public function productAction()
	{
		$this->view->hostUrl = HOST;
		$act = $this->_request->getParam('act');
		$pid = $this->_request->getParam('pid');
		if($act == 'search') {
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$productInfo = $dalFish->getProductById($pid);

			$this->view->productinfo = $productInfo;
		}
		if($act == 'update') {
			$fields = array();
			$fields['name'] = trim($this->_request->getParam('name'));
			$fields['probability'] = trim($this->_request->getParam('probability'));
			$fields['picpath']= trim($this->_request->getParam('picpath'));
			$fields['url'] = trim($this->_request->getParam('url'));
			$fields['content'] = trim($this->_request->getParam('content'));
			$fields['flag'] = trim($this->_request->getParam('flag'));
			$fields['date'] = trim($this->_request->getParam('date'));
			
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$dalFish->updateProductById($pid, $fields);
			//清缓存
			$key = 'i:e:tb:pd';
			$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
			$cache->delete($key);
			$key = 'i:e:u:fishpinfo' .$pid;
			$cache->delete($key);
		}	
		$this->view->pid = $pid;
	}
	public function addproductAction()
	{
		$this->view->hostUrl = HOST;
		$act = $this->_request->getParam('act');
		if($act == 'add') {
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$fields = array();
			$fields['pid'] = trim($this->_request->getParam('pid'));
			$count = $dalFish->checkProduct($fields['pid']);
			if($count) {
				//已经添加过该商品ID了
				$this->view->msg = '已经添加过该商品了';
			}else {
				if($fields['pid']) {
					$fields['name'] = trim($this->_request->getParam('name'));
					$fields['probability'] = trim($this->_request->getParam('probability'));
					$fields['picpath']= trim($this->_request->getParam('picpath'));
					$fields['url'] = trim($this->_request->getParam('url'));
					$fields['content'] = trim($this->_request->getParam('content'));
					$fields['flag'] = trim($this->_request->getParam('flag'));
					$fields['date'] = trim($this->_request->getParam('date'));	
					$insertId = $dalFish->addProduct($fields);
					if($insertId) {					
						$this->view->msg = '添加成功';	
						//清缓存
						$key = 'i:e:tb:pd';
						$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
						$cache->delete($key);
						$key = 'i:e:u:fishpinfo' .$fields['pid'];
						$cache->delete($key);											
					}else {
						$this->view->msg = '添加失败';	
					}
				}else {
					$this->view->msg = '请输入商品ID';	
				}
			}
		}
	}
	public function probabilityAction()
	{
		$this->view->message = '';
		$this->view->hostUrl = HOST;
		$pid = $this->_request->getParam('pid');
		$act = $this->_request->getParam('act');
		if($act == 'search') {
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$list = $dalFish->getProbabilityById($pid);
			if($list) {
				foreach($list as $k=>$v) {
					$productInfo = $dalFish->getProductById($v['pid']);
					$list[$k]['pname'] = $productInfo['name'];
				}
			}
			$this->view->list = $list;
			$this->view->pid = $pid;
		}
		if($act == 'update') {
			$id = $this->_request->getParam('id');
			$probability = $this->_request->getParam('probability');
			$num = $this->_request->getParam('num');
			$urla = $this->_request->getParam('urla');
			$urlb = $this->_request->getParam('urlb');
			
			$count = count($id);
			for($i=0;$i<$count;$i++) {
				$fields = array();
				$iid = $id[$i];
				$fields['probability'] = trim($probability[$i]);
				$fields['num'] = trim($num[$i]);
				$fields['urla'] = trim($urla[$i]);
				$fields['urlb'] = trim($urlb[$i]);
				$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
				$dalFish->updateProbabilityById($iid, $fields);
			}
			//清缓存
			$pids =  $this->_request->getParam('pids');
			$productid = $pids[0];
			$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');	
			for($m=1;$m<=5;$m++) {
				$key = 'i:e:tb:pd:prob:l:pid:' . $m . ':' . $productid;
				$cache->delete($key);
			}
			$this->view->message = '操作成功';
			$this->view->pid = $productid;
		}
	}
	public function addprobabilityAction()
	{
		$this->view->hostUrl = HOST;
		$act = $this->_request->getParam('act');
		if($act == 'add') {
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$fields = array();	
			$fields['pid'] = trim($this->_request->getParam('pid'));
			$fields['discount'] = trim($this->_request->getParam('discount'));
			$fields['level'] = trim($this->_request->getParam('level'));
			$fields['probability'] = trim($this->_request->getParam('probability'));
			$fields['num'] = trim($this->_request->getParam('num'));
			$fields['urla'] = trim($this->_request->getParam('urla'));
			$fields['urlb'] = trim($this->_request->getParam('urlb'));
			//检测是否已经添加过
			$count = $dalFish->checkProbability($fields['pid'], $fields['discount'], $fields['level']);
			if($count) {
				$this->view->msg = '已经添加过了';
			}else {	
				if($fields['pid'] && isset($fields['discount']) && $fields['level']) {
					$insertId = $dalFish->addProbability($fields);
					if($insertId) {					
						$this->view->msg = '添加成功.刚才添加的折扣数为<font color=blue><b>'.$fields['discount'].'</b></font>,领域位置为<font color=blue><b>'.$fields['level'].'</b></font>';
						//清缓存
						$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');	
						for($m=1;$m<=5;$m++) {
							$key = 'i:e:tb:pd:prob:l:pid:' . $m . ':' . $fields['pid'];
							$cache->delete($key);
						}											
					}else {
						$this->view->msg = '添加失败';	
					}	
				}else {
					$this->view->msg = '请填写完整';
				}			
			}			
		}		
	}
	public function statAction()
	{
		$this->view->hostUrl = HOST;
		$act = $this->_request->getParam('act');
		$date = $this->_request->getParam('date');
		if($act == 'search') {
			$tableName = 'stat_catchfish';
			$db = Hapyfish2_Db_FactoryStat::getStatLogDB();
			$rdb = $db['r'];
			$sql='SELECT * FROM '.$tableName.' WHERE create_time=:create_time';
			$info = $rdb->fetchRow($sql, array('create_time'=>$date));
			$this->view->info = $info;
		}
		$this->view->date = $date;
	}
	public function rankAction()
	{
		$this->view->hostUrl = HOST;
		$act = $this->_request->getParam('act');
		$date = $this->_request->getParam('date');
		if($act == 'search') {
			$tableName = 'catchfish_rank';
			$db = Hapyfish2_Db_Factory::getEventDB('db_0');
			$rdb = $db['r'];
			$sql='SELECT * FROM '.$tableName.' WHERE date=:date';
			$list = $rdb->fetchAll($sql, array('date'=>$date));
			$this->view->list = $list;
		}
		$this->view->date = $date;
	}		
}