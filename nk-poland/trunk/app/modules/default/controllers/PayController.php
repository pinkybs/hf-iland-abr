<?php

class PayController extends Zend_Controller_Action
{
    protected $uid;

    protected $info;

    public function init()
    {
    	$info = $this->vailid();
        if (!$info) {
            echo '<html><body><script type="text/javascript">window.top.location="http://apps.facebook.com/'.APP_NAME.'/";</script></body></html>';
            exit;
        }

        $this->info = $info;
        $this->uid = $info['uid'];
        $data = array('uid' => $info['uid'], 'puid' => $info['puid'], 'session_key' => $info['session_key']);
        $context = Hapyfish2_Util_Context::getDefaultInstance();
        $context->setData($data);

        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
        $this->view->uid = $info['uid'];
        $this->view->platformUid = $info['puid'];

        $constCode = 'JKikd*l;21%@*(1Dad^';
		//$uid = $uid;
		$platform = 'fb';
		$game = 'fb_island';
		$time = time();
		$checkcode = md5($uid.$constCode.$time);
		$this->view->faqUrl = 'http://feedback.snsplus.com/?uid=' . $uid . '&platform=' . $platform . '&game=' .$game . '&checkcode=' . $checkcode . '&time='.$time;
		$snsCode = md5('game_code='.APP_NAME.'time='.$time.'uid='.$uid.APP_SECRET);
		$this->view->snsUrl = 'http://sn.service.snsplus.com/?game_code='.APP_NAME.'&uid='.$uid.'&time='.$time.'&sig='.$snsCode;

    	/* for 外联通  20110702 */
		$this->view->connectSnsplus = '';
        $entrance = $this->_request->getParam('entrance');
        //from snsplus
        if ('connect' == $entrance) {
            $this->view->connectSnsplus = 'entrance=connect';
        }
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

	public function topAction()
	{
        $uid = $this->uid;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$user['gold'] = Hapyfish2_Island_HFC_User::getUserGold($uid);

		$this->view->user = $user;
		$this->render();
	}

    public function payAction()
    {
        $uid = $this->uid;
		/*
		測試請求地址：http://test-pay.snsplus.com/api/getpaymenturl
		正式請求地址：http://pay.snsplus.com/api/getpaymenturl
		*/
        $entrance = $this->_request->getParam('entrance');
        $api_url = 'http://pay.snsplus.com/api/getpaymenturl';
        $param = array();
		$param['game_coding'] = 'AIFSAA';
		$param['uid'] = $this->info['puid'];
		$param['signed'] = '1';
        if ('connect' == $entrance) {
		    $param['entrance'] = 'connect';
		}
		$post_string = http_build_query($param);

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$paymenturl = @curl_exec($ch);
		@curl_close($ch);

        /*
        $limit_uids = array('579418666',
                            '1177325294',
                            '1640183732',
                            '100000271335713',
                            '1526002634',
                            '1095877457',
                            '817308554',
                            '100000138260101',
                            '766589061',
                            '573648228',
                            '1018066459',
                            '1174038253',
                            '564138216',
                            '100000100757344',
                            '1496300630',
                            '100000189996355',
                            '1849555388',
                            '1806655922',
                            '100000718267940',
                            '541289768',
                            '100000490156245',
                            '100001052452664',
                            '100000212739414',
                            '100000428600337',
                            '100000075313234',
                            '703824034',
                            '905790458');

        if (in_array($application->getUserId(), $limit_uids)) {
            $this->view->html = "<iframe src='$url'  height='1100' width='750' name='pay_page' frameborder='0'></iframe>";
        }*/

        $this->view->html = "<iframe src='$paymenturl' height='1100' width='750' name='pay_page' frameborder='0'></iframe>";
		$this->render();
    }

    public function logAction()
    {
		$uid = $this->uid;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$user['gold'] = Hapyfish2_Island_HFC_User::getUserGold($uid);

    	$logs = Hapyfish2_Island_Bll_PaymentLog::getPayment($uid, 50);
    	if (!$logs) {
    		$count = 0;
    		$logs = '[]';
    	} else {
    		$count = count($logs);
    		$logs = json_encode($logs);
    	}
    	$pageSize = 25;
    	$this->view->user = $user;
		$this->view->logs = $logs;
        $this->view->count = $count;
        $this->view->pageSize = 25;
        $this->view->pageNum = ceil($count/$pageSize);
        $this->render();
    }

    public function systemAction()
    {
    	$uid = $this->uid;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$user['gold'] = Hapyfish2_Island_HFC_User::getUserGold($uid);

    	$notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

    	$logs = Hapyfish2_Island_Bll_PaymentLog::getAddGoldLog($uid, 50);
    	if (!$logs) {
    		$count = 0;
    		$logs = '[]';
    	} else {
    		foreach($logs as $k => $v){
    			if(!$v['summary']){
					switch ($v['type']) {
						case 0:
							$summary = '系統贈送';
						break;
						case 1:
							$summary = '升級獎勵';
						break;
						case 2:
							$summary = '抽獎獲得';
						break;
						case 3:
							$summary = '活動獲得';
						break;
						case 4:
							$summary = '邀請好友';
						break;
						case 5:
							$summary = '師徒獎勵';
						break;
						case 6:
							$summary = '登陸翻牌';
						break;
						case 10:
							$summary = '粉絲獎勵';
						break;
						default:
							$summary = '系統贈送';
						break;
					}
					$logs[$k]['summary'] = $summary;
				}
    		}

    		$count = count($logs);
    		$logs = json_encode($logs);
    	}

    	$pageSize = 25;
    	$this->view->user = $user;
		$this->view->logs = $logs;
        $this->view->count = $count;
        $this->view->pageSize = 25;
        $this->view->pageNum = ceil($count/$pageSize);
        $this->render();
    }

}