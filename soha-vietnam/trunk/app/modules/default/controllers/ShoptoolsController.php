<?php
class ShoptoolsController extends Zend_Controller_Action
{
	protected $_authcodekey = 'a6999j';
	
	
	public function init()
	{
		$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
	}
	
    protected function echoResult($data)
    {
    	header("Cache-Control: no-store, no-cache, must-revalidate");
    	echo json_encode($data);
    	exit;
    }
	
	public function inituserAction()
	{
		$auth = $this->_request->getParam('auth');
		$auth = $this->authcode($auth);
		if ( $auth  ) {
			
			$params = explode('&', $auth);
			$hash = array();
			foreach ($params as $key => $val) {
				
				list($a, $b) = explode('=', $val);
				$hash[$a] = $b;
				
			}
			
			$data = Hapyfish2_Platform_Bll_UidMap::getUser($hash['puid']);
			$uid = $data['uid'];
			$coin = Hapyfish2_Island_HFC_User::getUserCoin($uid);
			$gold = Hapyfish2_Island_HFC_User::getUserGold($uid);
			
			$this->echoResult(array('coin'=>$coin, 'gold'=>$gold));
		}
		
	}
	
	public function exchangeAction()
	{
		$auth = $this->_request->getParam('auth');
		$auth = $this->authcode($auth);
		if ( $auth  ) {
			
			// 解密传过来的数值
			$params = explode('&', $auth);
			$hash = array();
			foreach ($params as $key => $val) {
				list($a, $b) = explode('=', $val);
				$hash[$a] = $b;
			}
			
			// 获取传过来的数值
			$a = explode(',', $hash['a']);
			$b = explode(',', $hash['b']);
			$c = explode(',', $hash['c']);
			// 获取商家平台uid和puid
			$data = Hapyfish2_Platform_Bll_UidMap::getUser($hash['puid']);
			$uid = $data['uid'];
			$puid = $hash['puid'];
			// 获取商家金币和宝石
			$coin = Hapyfish2_Island_HFC_User::getUserCoin($uid);
			$gold = Hapyfish2_Island_HFC_User::getUserGold($uid);
			// 获取商家等级
			$userLevel = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$userLevel = $userLevel['level'];
			
			$sumGold = 0;
			$sumCoin = 0;
			
			foreach ($b as $key => $val) {
				$sumGold = $sumGold + abs($val);
			}
			foreach ($c as $key => $val) {
				$sumCoin = $sumCoin + abs($val); 
			}
			
			$ok = true;
			// 玩家钻石不够
			if ($sumGold > $gold) {
				$ok = false;
			}
			// 玩家金币不够
			if ($sumCoin > $coin) {
				$ok = false;
			}
			
			
			
			
			if ($ok) {
				
				// 扣除玩家资源
				if ($sumGold > 0) {
					$goldInfo = array(	'uid'=>$uid,
									'cost'=>$sumGold, 
									'summary'=>'商家发送', 
									'user_level'=>$userLevel,
									'cid'=>'0', 
									'num'=>'1');
					Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
				}
				if ($sumCoin > 0) {
					Hapyfish2_Island_HFC_User::decUserCoin($uid, $sumCoin, true);					
				}
				
				// 发送
				$tfs = array();
				foreach ($a as $key => $val) {
					
					
					$a[$key] = abs($a[$key]);
					$b[$key] = abs($b[$key]);
					$c[$key] = abs($c[$key]);
					
					$b[$key] = $b[$key] ? $b[$key] : 0;
					$c[$key] = $c[$key] ? $c[$key] : 0;
					
					if ($a[$key] && ($b[$key] || $c[$key])) {
						
						$temp = Hapyfish2_Platform_Bll_UidMap::getUser($a[$key]);
						
						$com = new Hapyfish2_Island_Bll_Compensation();
						$com->setGold($b[$key]);
						$com->setCoin($c[$key]);
						$tf = $com->sendOne($temp['uid'],'恭喜你收到');
						$tf = $tf ? '1' : '0';
						$tfs[] = $tf;
						
						$log = Hapyfish2_Util_Log::getInstance();
						$log->report('shoptools', array($puid, $uid, $a[$key], $b[$key], $c[$key], $tf ));
					}
					
				}
				
				$this->echoResult(array('tfs'=>$tfs));
			}
			
		}
	}
	
	
	
	
	
	
	
	
	public  function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) 
	{

		$ckey_length = 4;
		$key = md5($key ? $key : $this->_authcodekey);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	
	}
	
}