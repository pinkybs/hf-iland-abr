<?php

class EventController extends Zend_Controller_Action
{
    protected $uid;

    protected $info;

    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
		if (APP_STATUS == 0) {
        	$stop = true;
            if (APP_STATUS_DEV == 1) {
    			$ip = $this->getClientIP();
	    		if ($ip == '27.115.48.202' || $ip == '122.147.63.223' || $ip == '116.247.76.102') {
	    			$stop = false;
	    		}
    		}
    		if ($stop) {
        		$result = array('status' => '-1', 'content' => '停机维护中');
    			$this->echoResult($result);
    		}
    	}

    	$info = $this->vailid();
        if (!$info) {
        	$result = array('status' => '-1', 'content' => 'serverWord_101');
			$this->echoResult($result);
        }

        $this->info = $info;
        $this->uid = $info['uid'];
        $data = array('uid' => $info['uid'], 'puid' => $info['puid'], 'session_key' => $info['session_key']);
        $context = Hapyfish2_Util_Context::getDefaultInstance();
        $context->setData($data);

    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }

    protected function getClientIP()
    {
    	$ip = false;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) {
				array_unshift($ips, $ip);
				$ip = false;
			}
			for ($i = 0, $n = count($ips); $i < $n; $i++) {
				if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
    }

	protected function vailid()
    {
    	$skey = $_COOKIE['hf_skey'];
    	if (!$skey) {
    		return false;
    	}

    	$tmp = explode('.', $skey);
    	if (empty($tmp)) {
    		return false;
    	}
    	$count = count($tmp);
    	if ($count != 5 && $count != 6) {
    		return false;
    	}

        $uid = $tmp[0];
        $puid = $tmp[1];
        $session_key = base64_decode($tmp[2]);
        $t = $tmp[3];

        $rnd = -1;
        if ($count == 5) {
        	$sig = $tmp[4];
	        $vsig = md5($uid . $puid . $session_key . $t . APP_SECRET);
	        if ($sig != $vsig) {
	        	return false;
	        }
        } else if ($count == 6) {
        	$rnd = $tmp[4];
        	$sig = $tmp[5];
        	$vsig = md5($uid . $puid . $session_key . $t . $rnd . APP_SECRET);
        	if ($sig != $vsig) {
	        	return false;
	        }
        }

        //max long time one day
        if (time() > $t + 86400) {
        	return false;
        }

        return array('uid' => $uid, 'puid' => $puid, 'session_key' => $session_key,  't' => $t, 'rnd' => $rnd);
    }

    protected function checkEcode($params = array())
    {
    	if ($this->info['rnd'] > 0) {
    		$rnd = $this->info['rnd'];
    		$uid = $this->uid;
    		$ts = $this->_request->getParam('tss');
    		$authid = $this->_request->getParam('authid');
    		$ok = true;
    		if (empty($authid) || empty($ts)) {
    			$ok = false;
    		}
    		if ($ok) {
    			$ok = Hapyfish2_Island_Bll_Ecode::check($rnd, $uid, $ts, $authid, $params);
    		}
    		if (!$ok) {
    			//Hapyfish2_Island_Bll_Block::add($uid, 1, 2);
    			info_log($uid, 'ecode-err');
	        	$result = array('status' => '-1', 'content' => 'serverWord_101');
	        	setcookie('hf_skey', '' , 0, '/', '.island.qzoneapp.com');
	        	$this->echoResult($result);
    		}
    	}
    }

    protected function echoResult($data)
    {
    	header("Cache-Control: no-store, no-cache, must-revalidate");
    	echo json_encode($data);
    	exit;
    }

    /**
	 *
	 * 时间性礼物
	 */
	public function receivetimegiftAction()
    {

    	$result = Hapyfish2_Island_Event_Bll_Timegift::receive($this->uid);

    	$this->echoResult($result);
    }

    public function getgifttimeAction()
    {
    	$result = Hapyfish2_Island_Event_Bll_Timegift::gettime($this->uid);
    	$this->echoResult($result);
    }

    /*
     * get next time gift,获取下一次时间性礼物的奖品信息
     *
     */
    public function getnexttimegiftAction()
    {
    	$result = Hapyfish2_Island_Event_Bll_Timegift::getNextTimeGift($this->uid);
    	$this->echoResult($result);
    }

	public function setupgifttimeAction()
    {
    	try {
	    	Hapyfish2_Island_Event_Bll_Timegift::setup($this->uid);

			echo 'Ok';
			exit;
    	} catch (Exception $e){
			echo 'false';
			exit;
    	}
    }

	//梦想花园用户登录奖励
    public function recivedreamgardenawardAction()
    {
    	$result = Hapyfish2_Island_Event_Bll_DreamGardenUserAward::receive($this->uid);
    	$this->echoResult($result);
    }

	public function resetreamgardenawardAction()
    {
    	$result = Hapyfish2_Island_Event_Bll_DreamGardenUserAward::reset($this->uid);
    	$this->echoResult($result);
    }

    public function getactivedayAction()
    {
    	$uid = $this->uid;
    	$result = array('status' => -1, 'content' => 'serverWord_110');
    	$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
    	if (!$loginInfo) {
			$this->echoResult($result);
		}

		$this->echoResult(array('day' => $loginInfo['active_login_count'], 'result' => array('status' => 1)));
    }

    public function active5dayAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
    	if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult($result);
		}

    	$result = array('status' => -1, 'content' => 'serverWord_110');
    	$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
        if (!$loginInfo) {
			$this->echoResult($result);
		}

		if ($loginInfo['active_login_count'] < 5) {
			$this->echoResult($result);
		}

    	$isGained = Hapyfish2_Island_Event_Bll_Active5Day::isGained($uid);
		if (!$isGained) {
			$result = Hapyfish2_Island_Event_Bll_Active5Day::gain($uid);
		} else {
			$result['content'] = 'serverWord_151';
		}

        //release lock
        $lock->unlock($key);

		$this->echoResult($result);
    }

    public function getinviteflowstateAction()
    {
    	$uid = $this->uid;
    	$data = Hapyfish2_Island_Event_Bll_InviteFlow::getState($uid);
    	$this->echoResult($data);
    }

    public function inviteawardAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$result = array('result' => array('status' => -1, 'content' => 'serverWord_110'));

		$data = Hapyfish2_Island_Event_Bll_InviteFlow::getState($uid);
		$step = $data['step'];
		$friendCount = count($data['friendsList']);

		if ($step == 1) {
			if ($friendCount < 4) {
				 $this->echoResult($result);
			}
		} else if ($step == 2) {
			if ($friendCount < 3) {
				 $this->echoResult($result);
			}
		} else  if ($step == 3) {
			if ($friendCount < 2) {
				 $this->echoResult($result);
			}
		} else  if ($step == 4) {
			if ($friendCount < 1) {
				 $this->echoResult($result);
			}
		} else {
			$this->echoResult($result);
		}

		$result = Hapyfish2_Island_Event_Bll_InviteFlow::gain($uid, $step);

        //release lock
        $lock->unlock($key);
        $this->echoResult($result);
    }

    //海星商城--面板
	 public function getstarfishexternalmallAction(){
        $uid = $this->uid;
        $result['result'] = array('status' => 1,'content' => '');
        $start = 1307536800;
        $result['data'] = Hapyfish2_Island_Event_Bll_StarfishSale::getSaleList();
        $result['haveInvitedFriendNum'] = Hapyfish2_Island_Event_Bll_StarfishSale::getInviteCount($uid,$start);
        $result['userStarFish'] = Hapyfish2_Island_HFC_User::getUserStarFish($uid);
        $this->echoResult($result);
     }

     //海星商城--购买
     public function starfishexchangeAction(){
         $cid = $this->_request->getParam('cid');
         $uid = $this->uid;
        $key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);
	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}
		$result = Hapyfish2_Island_Event_Bll_StarfishSale::Exchange($uid,$cid);
        $lock->unlock($key);
        $this->echoResult($result);
     }

    /*
	//团购--面板
	public function teambuyAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_TeamBuy::teamBuy($uid);

		$this->echoResult($result);
	}

	//团购--参加
	public function jointeambuyAction()
	{
		$uid = $this->uid;

    	$key = 'ev:teambuy:join:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}

		$result = Hapyfish2_Island_Event_Bll_TeamBuy::joinTeamBuy($uid);
		$lock->unlock($key);

		$this->echoResult($result);
	}

	//团购--购买
	public function buygoodsAction()
	{
		$uid = $this->uid;

    	$key = 'ev:teambuy:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}

		$result = Hapyfish2_Island_Event_Bll_TeamBuy::buyGoods($uid);
		$lock->unlock($key);

		$this->echoResult($result);
	}

	//团购--feed
	public function sendteambuyfeedAction()
	{
		$uid = $this->uid;
		$type = 'TEAMBUY_FEED';

		$result = Hapyfish2_Island_Bll_Activity::send($type, $uid);

        $this->echoResult($result);
	}
*/
	//feed云--加金币
    public function addstromcoinAction()
    {
    	$uid = $this->uid;

    	$result = Hapyfish2_Island_Bll_Strom::addCoin($uid);

    	$this->echoResult($result);
    }

	//feed云--状态
    public function getflashstomstatusAction()
    {
    	$uid = $this->uid;

    	$result = Hapyfish2_Island_Bll_Strom::getStrom($uid);

    	$this->echoResult($result);
    }

	//feed云--发feed
    public function sendstromfeedAction()
    {
    	$uid = $this->uid;
    	$type = 'STROM_FEED';

    	$result = Hapyfish2_Island_Bll_Activity::send($type, $uid);

    	$this->echoResult($result);
    }

	//全屏
	public function fullscreenAction()
	{
		$uid = $this->uid;

		$key = 'i:u:fullScreen:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, 1);

		exit;
	}

	public function getsystimeAction()
	{
//		$result['result'] = array('status' => 1,'content' => '');
//		$result['systime'] = time();

		$result = array ('systime' => time());
		$this->echoResult($result);
	}

	//农历新年：商城特卖
	public function salemallAction()
    {
    	$uid = $this->uid;

    	$key = 'evlock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);

//        if($uid != 6021274) {
//    		$resultVo = array('status' => -1, 'content' => '商品更新中');
//			$this->echoResult(array('result' => $resultVo));
//        }

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$packId = (int)$this->_request->getParam('packId');

		$arySaleInfo = array('start' => '1299513600','end' => '1331136000', 'price_type' => 2, 'price' => 100);
		$items = array();

		if (1 == $packId) {
			$arySaleInfo['id'] = 1009;
			$arySaleInfo['name'] = '忍术商店';
			$arySaleInfo['price_type'] = 2;
			$arySaleInfo['price'] = 34;
			$items[] = array('item_id' => 98232, 'item_num' => 1);
		}
		else if (2 == $packId) {
			$arySaleInfo['id'] = 1109;
			$arySaleInfo['name'] = '忍者武器馆';
			$arySaleInfo['price_type'] = 2;
			$arySaleInfo['price'] = 34;
			$items[] = array('item_id' => 98932, 'item_num' => 1);
		}
		else if (3 == $packId) {
			$arySaleInfo['id'] = 1209;
			$arySaleInfo['name'] = '晓-驿站';
			$arySaleInfo['price_type'] = 2;
			$arySaleInfo['price'] = 34;
			$items[] = array('item_id' => 98732, 'item_num' => 1);
		}
    	else if (4 == $packId) {
			$arySaleInfo['id'] = 1309;
			$arySaleInfo['name'] = '美味拉面馆';
			$arySaleInfo['price_type'] = 2;
			$arySaleInfo['price'] = 34;
			$items[] = array('item_id' => 98432, 'item_num' => 1);
		}
		/*else if (5 == $packId) {
			$arySaleInfo['id'] = 1409;
			$arySaleInfo['name'] = '惊喜大礼包';
			$arySaleInfo['price'] = 208;
			$items[] = array('item_id' => 72431, 'item_num' => 1);
			$items[] = array('item_id' => 72531, 'item_num' => 1);
			$items[] = array('item_id' => 72631, 'item_num' => 1);
			$items[] = array('item_id' => 74841, 'item_num' => 5);
		}*/

		$arySaleInfo['item'] = $items;
		$bllMall = new Hapyfish2_Island_Event_Bll_SaleMall($arySaleInfo);
		if (1 == $arySaleInfo['price_type']) {
			$result = $bllMall->coinSale($uid);
		}
		else {
			$result = $bllMall->goldSale($uid);
		}

		if ($result['result']['status'] < 0) {
			$result['result']['status'] = -1;
		}

    	//release lock
        $lock->unlock($key);

    	//pack sell logs
		if (1 == $result['result']['status']) {
			info_log($arySaleInfo['name'].','.$uid, 'packsale_'.date('Ymd'));
		}

        $this->echoResult($result);
    }


	public function lupawardboxopenedAction()
	{
		$result['result'] = array('status' => 1,'content' => '');

		$uid = $this->uid;

		$gettf = Hapyfish2_Island_Event_Bll_UpgradeGift::getTF($uid);

		$ok = false;
		if (!$gettf) {
			$ok = Hapyfish2_Island_Event_Bll_UpgradeGift::gifttouser($uid);
			if ($ok)
			{
				Hapyfish2_Island_Event_Bll_UpgradeGift::setTF($uid);
				$result['result']['goldChange'] = 10;
				$result['result']['coinChange'] = 10000;
			}
		}

		if (! $ok) {
			$result['result']['content'] = 'serverWord_101';
			$result['result']['status'] = -1;
		}

		$this->echoResult($result);
	}

    public function casinoinitAction()
    {
    	$uid = $this->uid;

    	$list = Hapyfish2_Island_Cache_CasinoAwardType::getAllType();
    	$list = $list['list'];
    	$result = array();

    	foreach ($list as $key => $val) {
    		if ($val['item_cid'] > 0) {
    			$result[] = array('id'=>$val['id'], 'type'=>$val['type'], 'itemCid'=>$val['item_cid']);
    		} else if ($val['type'] == 10) {
    			$result[] = array('id'=>$val['id'], 'type'=>$val['type'], 'num'=>$val['coin']);
    		} else if ($val['type'] == 20) {
    			$result[] = array('id'=>$val['id'], 'type'=>$val['type'], 'num'=>$val['gold']);
    		}
    	}

    	$point = Hapyfish2_Island_Event_Bll_Casino::getUserPoint($uid);

    	$this->echoResult(array('awards'=>$result, 'lvCount'=>$point, 'point'=>$point));
    }

    public function casinoraffleAction()
    {
    	$uid = $this->uid;

    	$betNum = $this->_request->getParam('betNum', 1);

    	$result = Hapyfish2_Island_Bll_Casino::raffle($uid, $betNum);

    	$this->echoResult($result);
    }

	public function getfriendlistAction()
	{
		$uid = $this->uid;
		$list = Hapyfish2_Island_Bll_SearchFriend::getSearchFriend();
		$this->echoResult($list);
	}

	public function sendremindAction()
	{
		$uid = $this->uid;
		$fid = $this->_request->getParam('uid');
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$content = $user['name'].'加你为好友了，快去你的主页看看吧!';
		Hapyfish2_Island_Bll_Remind::addRemind($this->uid, $fid, $content, 0);
		exit;
	}
/*
	// 掉宝箱系统，获取季度列表
	public function getbottlelistAction()
	{
		$uid = $this->uid;
		$result = array('result'=>array('status'=>-1, 'goldChange'=>0), 'list'=>array());
		$key = 'bottle:list';

		$list = Hapyfish2_Island_Cache_Hash::get($key);
		$list = unserialize($list);

		$topcids = array('91932','86432','98332','97331','100632', '102232','106032','115232');

		if ($list) {
			$temp = array();
			foreach ($list as $key => $val) {
				if ($val['online']) {
					unset($val['online']);
					unset($val['tips']);
					$val['qid'] = (int)$val['qid'];
					$temp[] = array_merge($val, array('id'=>(string)$key, 'cid'=>(int)$topcids[$key % count($topcids)]));
				}
			}

			$result['result']['status']=1;
			$result['list'] = $temp;
		}

		$this->echoResult($result);
	}

	// 掉宝箱系统，获取单一季度奖励列表
	public function getbottleinfoAction()
	{
		$uid = $this->uid;
		$result = array('result'=>array('status'=>-1, 'goldChange'=>0), 'list'=>array());
		$idx = $this->_request->getParam('idx');

		if ($idx || $idx == "0") {

			$tf = false;
			$key = 'bottle:list';
			$hash = Hapyfish2_Island_Cache_Hash::get($key);
			$hash = unserialize($hash);

			if ($hash[$idx] && $hash[$idx]['online']) {
				$tf = true;
			}

			if ($tf) {
				$temp = array();
				$list = Hapyfish2_Island_Cache_Bottle::getAllByBottleId($idx);

				foreach ($list['list'] as $key => $val) {
					$tips = ($val['btl_tips'] ? $val['btl_tips'] : '');
					switch ($val['type']) {
						case 'COIN' :
							$temp[] = array('name'=>$val['btl_name'], 'coin'=>(int)$val['coin'], 'tips'=>$tips);
							break;
						case 'GOLD' :
							$temp[] = array('name'=>$val['btl_name'], 'gem'=>(int)$val['gold'], 'tips'=>$tips);
							break;
						case 'STARFISH' :
							$temp[] = array('name'=>$val['btl_name'], 'starfish'=>(int)$val['starfish'], 'tips'=>$tips);
							break;
						case 'PLANT' :
						case 'BUILDING' :
						case 'CARD' :
							$temp[] = array('name'=>$val['btl_name'], 'itemId'=>$val['item_id'], 'itemNum'=>$val['num'], 'tips'=>$tips);
							break;
					}
				}

				$result['result']['status']=1;
				$result['list'] = $temp;

				// 今天免费抽奖了没
				$freeNum = Hapyfish2_Island_Cache_Counter::getBottleTodayTF($uid);
				// 获得玩家卡牌
				$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);

				$result['freeNum']= ($freeNum ? 0 : 1);	// 还有免费的次数
				$result['keyNum'] = ($userCard['86241'] ? $userCard['86241']['count'] : 0);	// 剩余钥匙个数

				$point = Hapyfish2_Island_Event_Bll_Casino::getUserPoint($uid);
				$result['leftNum'] = ($point ? $point : 0);	// 玩家积分
				$result['price1'] = 5;	// 开一次用的岛钻石
				$result['price10'] = 50;	// 开10次用的岛钻石
				$result['cheapPrice1'] = 5;	// 开一次用的岛钻石
				$result['cheapPrice10']= 40;	// 开10次用的岛钻石
				$result['canUseGold'] = 1;	// 是否可以使用岛钻石 1,或0
			}
		}

		$this->echoResult($result);
	}

	// 掉宝箱系统，领取宝箱
	public function bottlereceiveAction()
	{
		$idx = $this->_request->getParam('idx');
		$type = $this->_request->getParam('type');
		$num = abs($this->_request->getParam('count',1));
		$uid = $this->uid;
		$result = Hapyfish2_Island_Bll_Bottle::click($idx, $type, $uid, $num);
		$this->echoResult($result);
	}

	// 掉宝箱系统，获取玩家奖励列表
	public function bottleuserlistAction()
	{
		$uid = $this->uid;
		$result = array('result'=>array('status'=>-1, 'goldChange'=>0), 'list'=>array());

		$list = Hapyfish2_Island_Cache_BottleQueue::getall();
		if ($list) {
			$result['result']['status'] = 1;
			$result['list'] = $list;
			$this->echoResult($result);
		}

		// test
		$result['result']['status'] = 1;
		$result['list'] = array(array('name'=>'lei.wu', 'time'=>time(), 'list'=>array('coin'=>123, 'tips'=>'321')));

		$this->echoResult($result);
	}
*/
	//收集任务--收集面板
	public function collectiontaskAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_Hash::collectionTask($uid);

		$this->echoResult($result);
	}
	//收集任务--领取礼物
	public function getgiftAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_Hash::getGift($uid);

		$this->echoResult($result);
	}

	//淘集市
    public function shaketreeAction()
    {
    	$uid = $this->uid;

    	$result = Hapyfish2_Island_Event_Bll_Xmas::xmasFair($uid);

    	$this->echoResult($result);
    }

    //淘集市feed
	public function sendtaojishifeedAction()
	{
		$uid = $this->uid;
		$type = 'TAOJISHI_FEED';

		Hapyfish2_Island_Bll_Activity::send($type, $uid);

		echo 'OK';
	}

	public function getrankAction()
	{
		$uid = $this->uid;
		$result = Hapyfish2_Island_Bll_Rank::getRank($uid);
		 $this->echoResult($result);
	}

	//碎片换建筑
	public function exchangefragmentAction()
	{
		$uid = $this->uid;
		$type = $this->_request->getParam('type');
		$result = Hapyfish2_Island_Bll_Fragments::exchangeFragment($uid, $type);
		$this->echoResult($result);
	}


   public function loadvalentineAction()
    {
    	$uid = $this->uid;
    	$eventEndTime = 1313596799;//2011-03-15
    	$now = time();
    	//get item count
		$result = Hapyfish2_Island_Event_Bll_Valentine::getUserValentine($uid);
		$result['daysLeftNum'] = ($eventEndTime - $now) > 0 ? ($eventEndTime - $now) : 0;
		$this->echoResult($result);
    }

/*
	//情人节：兑换
    public function exchangevalentineAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$exchangeType = (int)$this->_request->getParam('requestType');//1,2,3,4,5
		$result = Hapyfish2_Island_Event_Bll_Valentine::exchangeRose($uid, $exchangeType);

    	//release lock
        $lock->unlock($key);
        $this->echoResult($result);
    }

    //情人节：读取花
    public function loadvalentineroseAction()
    {
    	$resultVo = array('status' => 1);
    	$uid = $this->uid;
    	$today = date('Ymd');
		$mkey = 'i:u:eventsendrose:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $arySendInfo = $cache->get($mkey);
        $remainCnt = 10;
        if ( $arySendInfo && $arySendInfo['dt'] && $arySendInfo['dt']==$today && $arySendInfo['ids'] ) {
			$remainCnt = (10 - count($arySendInfo['ids'])) > 0 ? (10 - count($arySendInfo['ids'])) : 0;
        }
        $this->echoResult(array('result' => $resultVo, 'todayLeftSendTimes' => $remainCnt));
    }

	//情人节：送花
    public function sendvalentineroseAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$fids = $this->_request->getParam('requestType');
		$result = Hapyfish2_Island_Event_Bll_Valentine::sendRose($uid, $fids);

    	//release lock
        $lock->unlock($key);
        $this->echoResult($result);
    }

	//情人节：索花
    public function askvalentineroseAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$fids = $this->_request->getParam('requestType');
		$result = Hapyfish2_Island_Event_Bll_Valentine::begRose($uid, $fids);

    	//release lock
        $lock->unlock($key);
        $this->echoResult($result);
    }

	//情人节：买花
    public function buyvalentineroseAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

		$num = (int)$this->_request->getParam('requestType');
		$result = Hapyfish2_Island_Event_Bll_Valentine::buyRose($uid, $num);

    	//release lock
        $lock->unlock($key);
        $this->echoResult($result);
    }

	//情人节：排名列表  /情人节：兑换列表
    public function loadvalentinerankAction()
    {
    	$mkey1 = Hapyfish2_Island_Event_Bll_Valentine::CACHE_KEY_EXCHANGE;
		$eventFeed = Hapyfish2_Cache_Factory::getEventFeed();
    	$aryList1 = $eventFeed->get($mkey1);
    	$listVo1 = array();
    	if ($aryList1) {
	    	foreach ($aryList1 as $data) {
				$listVo1[] = array('userName'=>$data[0], 'itemName'=>$data[1], 'time'=>$data[2]);
	    	}
    	}

    	$mkey2 = Hapyfish2_Island_Event_Bll_Valentine::CACHE_KEY_RANK;
		$eventRank = Hapyfish2_Cache_Factory::getEventRank();
    	$aryList2 = $eventRank->get($mkey2);
    	$listVo2 = array();
    	if ($aryList2) {
	    	foreach ($aryList2 as $data) {
				$listVo2[] = array('userName'=>$data[0], 'roseNum'=>$data[1]);
	    	}
    	}
    	$resultVo = array('status' => 1);
		$this->echoResult(array('result' => $resultVo, 'getGiftRankListVo' => $listVo1, 'getRoseRankListVo' => $listVo2));
    }
    //七夕收集领取建筑
    public function exchangeqixiAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}
		$result = Hapyfish2_Island_Event_Bll_Qixi::getGift($uid);
		$lock->unlock($key);
        $this->echoResult($result);
    }
    public function getqixigiftAction()
    {
    	$uid = $this->uid;
    	$key = 'evlock:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
        if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}
		$result = Hapyfish2_Island_Event_Bll_Qixi::xmasFair($uid);
		$lock->unlock($key);
        $this->echoResult($result);
    }
*/
    /*
	//一元店--面板init
	public function onegoldshopAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_OneGoldShop::oneGoldShopInit($uid);
		$this->echoResult($result);
	}

	//一元店--领取本期物品
	public function getonegoldgiftAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_OneGoldShop::getOneGoldGift($uid);
		$this->echoResult($result);
	}

	//一元店--盒子信息
	public function getboxinfoAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_OneGoldShop::getBoxInfo($uid);
		$this->echoResult($result);
	}

	//一元店--盒子领取
	public function getonegoldboxgiftAction()
	{
		$uid = $this->uid;
		$idx = $this->_request->getParam('gift_type');

		$result = Hapyfish2_Island_Event_Bll_OneGoldShop::getOneGoldBox($uid, $idx);
		$this->echoResult($result);
	}

	//一元店--充值按钮
	public function gopayonegoldAction()
	{
		$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_OneGoldShop::goPayOneGold($uid);
		$this->echoResult($result);
	}
*/
    /*
    //读取捕鱼面板
    public function initfishAction()
    {
    	$uid = $this->uid;

    	$result = Hapyfish2_Island_Event_Bll_CatchFish::initFish($uid);
    	$this->echoResult($result);
    }

    //读取捕鱼动态数据
    public function fishuserAction()
    {
    	$uid = $this->uid;

    	$result = Hapyfish2_Island_Event_Bll_CatchFish::fishUser($uid);
    	$this->echoResult($result);
    }

    //捕鱼动作
    public function catchfishAction()
    {
    	$uid = $this->uid;
    	$productid = (int)$this->_request->getParam('id');
    	$helpFlag = (int)$this->_request->getParam('isHideHelpView');
    	$result = Hapyfish2_Island_Event_Bll_CatchFish::catchFish($uid, $productid, $helpFlag);
    	$this->echoResult($result);
    }

    //获取捕鱼排行榜
    public function fishrankAction()
    {
    	$result = Hapyfish2_Island_Event_Bll_CatchFish::catchFishRank();
    	$this->echoResult($result);
    }
    public function getrollrankAction()
    {
    	$result = Hapyfish2_Island_Event_Bll_CatchFish::catchFishRollRank();
    	$this->echoResult($result);
    }
    //兴奋剂减少疲劳时间
    public function rdtireddopAction()
    {
    	$uid = $this->uid;
    	$num = (int)$this->_request->getParam('num');
    	$result = Hapyfish2_Island_Event_Bll_CatchFish::rdTiredDop($uid, $num);
    	$this->echoResult($result);
    }

    //获取用户折扣券
    public function getuserdiscountAction()
    {
    	$uid = $this->uid;
    	$result = Hapyfish2_Island_Event_Bll_CatchFish::getUserDiscount($uid);
    	$this->echoResult($result);
    }
    //用户放弃领取折扣码
    public function canceldiscountAction()
    {
    	$uid = $this->uid;
    	$pid = (int)$this->_request->getParam('id');
    	$discountNumber = $this->_request->getParam('discountnumber');
    	$result = Hapyfish2_Island_Event_Bll_CatchFish::cancelTaoBaoDiscount($uid, $pid, $discountNumber);
    	$this->echoResult($result);
    }
*/
    /*
    //淘金币init
    public function amoygoldinitAction()
    {
    	$uid = $this->uid;

		$AmoyTime = strtotime('2011-10-16 17:00:00');
		$nowTime = time();

		if ($nowTime > $AmoyTime) {
			$result = array('result' => array('status' => -1, 'content' => '请刷新游戏'));
			$this->echoResult($result);
		}

    	$result = Hapyfish2_Island_Event_Bll_AmoyGold::amoyGoldInit($uid);
    	$this->echoResult($result);
    }

    //淘金币兑换
    public function getamoygoldgiftAction()
    {
    	$uid = $this->uid;
    	$idx = $this->_request->getParam('id');

		$AmoyTime = strtotime('2011-10-16 17:00:00');
		$nowTime = time();

		if ($nowTime > $AmoyTime) {
			$result = array('result' => array('status' => -1, 'content' => '请刷新游戏'));
			$this->echoResult($result);
		}

        $key = 'evlock:amoy:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
		if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}

    	$result = Hapyfish2_Island_Event_Bll_AmoyGold::getAmoyGoldGift($uid, $idx);
    	$lock->unlock($key);

    	$this->echoResult($result);
    }
*/
    public function initguoqingAction()
    {
    	$uid = $this->uid;
    	$result = Hapyfish2_Island_Event_Bll_Midautumn::getUserMidautumn($uid);
    	$this->echoResult($result);
    }

    public function passexchangeAction()
    {
    	$uid = $this->uid;
    	$id = $this->_request->getParam('id');
    	$key = 'ev:invite:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}
		$result = Hapyfish2_Island_Event_Bll_Midautumn::Exchange($uid, $id);
    	$lock->unlock($key);

    	$this->echoResult($result);
    }

    public function buypassAction()
    {
    	$uid = $this->uid;
    	$num = $this->_request->getParam('num');
    	$key = 'ev:invite:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}
		$result = Hapyfish2_Island_Event_Bll_Midautumn::buyPass($uid, $num);
    	$lock->unlock($key);

    	$this->echoResult($result);
    }

    public function demandpassAction()
    {
    	$uid = $this->uid;
    	$uids = $this->_request->getParam('list');
    	$result = Hapyfish2_Island_Event_Bll_Midautumn::begPass($uid, $uids);
    	$this->echoResult($result);
    }

    public function donatepassAction()
    {
    	$uid = $this->uid;
    	$type = $this->_request->getParam('type');
    	$num = $this->_request->getParam('num');
    	$uids = $this->_request->getParam('list');

    	$key = 'ev:invite:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}
		if($type == 1){
			$result = Hapyfish2_Island_Event_Bll_Midautumn::sendPass($uid, $uids);
		}else{
			$result = Hapyfish2_Island_Event_Bll_Midautumn::buyFriendPass($uid, $num, $uids);
		}
    	$lock->unlock($key);

    	$this->echoResult($result);
    }
/*
	//万圣节--初始化
    public function halloweeninitAction()
    {
    	$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_HallWitches::halloWeenInit($uid);
		$this->echoResult($result);
    }

    //万圣节--刷新选牌状态
    public function refrushcardchanceAction()
    {
    	$uid = $this->uid;

    	$key = 'ev:hallRef:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key, 2);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}

		$result = Hapyfish2_Island_Event_Bll_HallWitches::refrushCardChance($uid);
		$lock->unlock($key);

		$this->echoResult($result);
    }

	//万圣节--选卡片
    public function hallchoosecardAction()
    {
    	$uid = $this->uid;

    	$key = 'ev:hall:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key, 2);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}

		$result = Hapyfish2_Island_Event_Bll_HallWitches::hallChooseCard($uid);
		$lock->unlock($key);

		$this->echoResult($result);
    }

    //万圣节--兑换列表
	public function exchangelistAction()
	{
    	$uid = $this->uid;

		$result = Hapyfish2_Island_Event_Bll_HallWitches::exchangeList($uid);
		$this->echoResult($result);
	}

	//万圣节--补齐兑换
	public function replenishcardAction()
	{
    	$uid = $this->uid;
    	$groupId = $this->_request->getParam('groupId');

		if ($groupId === false) {
			$result = array('status' => -1, 'content' => 'serverWord_101');
			$this->echoResult(array('result' => $result));
		}

    	$key = 'ev:hallEx:lock:replenish:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key, 2);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}

		$result = Hapyfish2_Island_Event_Bll_HallWitches::replenishCard($uid, $groupId);
		$lock->unlock($key);

		$this->echoResult($result);
	}

	//万圣节--兑换物品
	public function toexchangeAction()
	{
    	$uid = $this->uid;
    	$groupId = $this->_request->getParam('groupId');

		if ($groupId === false) {
			$result = array('status' => -1, 'content' => 'serverWord_101');
			$this->echoResult(array('result' => $result));
		}

    	$key = 'ev:hallEx:lock:' . $uid;
    	$lock = Hapyfish2_Cache_Factory::getLock($uid);
    	$ok = $lock->lock($key, 2);
        if (!$ok) {
			$result = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $result));
		}

		$result = Hapyfish2_Island_Event_Bll_HallWitches::toExchange($uid, $groupId);
		$lock->unlock($key);

		$this->echoResult($result);
	}
*/
    
	//元旦--初始化
	public function newdaysinitAction()
	{
		$uid = $this->uid;
		
		$result = Hapyfish2_Island_Event_Bll_NewDays::newDaysInit($uid);
		
		$this->echoResult($result);
	}
	
	//元旦--砸蛋
	public function newdaysdropeggAction()
	{
		$uid = $this->uid;
		$eid = $this->_request->getParam('hammerId');
		
		$key = 'evlock:newdays:dropegg:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
		if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}
		
		$result = Hapyfish2_Island_Event_Bll_NewDays::newDaysDropEgg($uid, $eid);
		$lock->unlock($key);

		$this->echoResult($result);
	}
	
	//元旦--兑换礼包
	public function newdaystoconvertAction()
	{
		$uid = $this->uid;
		$pid = $this->_request->getParam('giftId');

		$key = 'evlock:newdays:toconvert:' . $uid;
        $lock = Hapyfish2_Cache_Factory::getLock($uid);

	    //get lock
		$ok = $lock->lock($key);
		if (!$ok) {
			$resultVo = array('status' => -1, 'content' => 'serverWord_103');
			$this->echoResult(array('result' => $resultVo));
		}
		
		$result = Hapyfish2_Island_Event_Bll_NewDays::newDaysToConvert($uid, $pid);
		$lock->unlock($key);
		
		$this->echoResult($result);
	}
    
 }