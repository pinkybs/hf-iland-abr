<?php

define('ADMIN_USERNAME','admin'); 	// Admin Username
define('ADMIN_PASSWORD','yewushuang_920');  	// Admin Password

class WhoisgoldController extends Zend_Controller_Action
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

	public function collectlistAction()
	{

		$key = 'collectgift';
		$jianglikey = 'jiangliid';
		$timekey = 'time';
		$xiaoxikey = 'xiaoxi';

		$val = Hapyfish2_Island_Event_Bll_Hash::getval($key);
		$jianglival = Hapyfish2_Island_Event_Bll_Hash::getval($jianglikey);
		$time = Hapyfish2_Island_Event_Bll_Hash::getval($timekey);
		$message = Hapyfish2_Island_Event_Bll_Hash::getval($xiaoxikey);

		$keyswitch = "collectcontrolswitch";
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$switch = $cache->get($keyswitch);

		if($switch['uid']) {
			$this->view->switchuid = $switch['uid'];
		}

		if( $val ) {
			$list = unserialize( $val );
			$this->view->list = $list;
		}

		if( $jianglival ) {
			$this->view->jiangli_id = $jianglival;
		}

		if($time) {
			$time = unserialize( $time );
			$time['start'] = date('Y-m-d H:i:s',$time['start']);
			$time['end'] = date('Y-m-d H:i:s',$time['end']);
		    $this->view->time = $time;
		}

		if($message) {
		    $this->view->message = unserialize( $message );
		}
	}

	public function collectlistdoAction()
	{
		$key = 'collectgift';
		$jianglikey = 'jiangliid';
		$timekey = 'time';
		$xiaoxikey = 'xiaoxi';

		$names = $this->_request->getParam('names');
		$cids = $this->_request->getParam('cids');
		$tips = $this->_request->getParam('tips');
		$time['start'] = $this->_request->getParam('start');
		$time['end'] = $this->_request->getParam('end');
		$message['tishi'] = $this->_request->getParam('tishi');
		$message['zhu'] = $this->_request->getParam('zhu');
		$jiangli_id = $this->_request->getParam('jiangli_id');

		$time['start'] = $time['start'] ? strtotime($time['start']) : strtotime('now');
		$time['end'] = strtotime($time['end']);

		$arr = array();
		foreach( $names as $k => $v ) {
			$arr[] = array('name'=>$names[$k], 'cid'=>$cids[$k], 'tip'=>$tips[$k]);
		}
		if( $arr ) {
			Hapyfish2_Island_Event_Bll_Hash::setval($key, serialize( $arr ));
		}
		if( $jiangli_id ) {
			Hapyfish2_Island_Event_Bll_Hash::setval($jianglikey, $jiangli_id );
		}
		if($time){
		   Hapyfish2_Island_Event_Bll_Hash::setval($timekey, serialize($time) );
		}
		if($message){
		    Hapyfish2_Island_Event_Bll_Hash::setval($xiaoxikey, serialize($message) );
		}

		$this->_redirect("whoisgold/collectlist");
	}

	public function controlswitchAction()
	{
		$uids = $this->_request->getParam('uids');
		$type = $this->_request->getParam('type');

		$result = array();
		$result['type'] = $type;
		$result['uid'] = $uids;

		$key = "collectcontrolswitch";
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->set($key,$result);

		$this->_redirect("whoisgold/collectlist");
	}

	public function clearcollectAction()
	{
		Hapyfish2_Island_Event_Bll_Hash::clearall();
		$this->_redirect("whoisgold/collectlist");
	}

}