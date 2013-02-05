<?php

class PayController extends Zend_Controller_Action
{
    protected $uid;

    protected $info;

    public function init()
    {
    	$info = $this->vailid();
        if (!$info) {
        	$this->_redirect(HOST.'/index/login');
            //echo '<html><body><script type="text/javascript">window.top.location="'.HTTP_PROTOCOL.'apps.facebook.com/'.APP_NAME.'/";</script></body></html>';
            exit;
        }

        $uid = $info['uid'];
        $puid = $info['puid'];
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
        //$api_url = 'http://test-pay.snsplus.com/api/getpaymenturl';
        $api_url = 'http://pay.snsplus.com/api/getpaymenturl';
        $param = array();
		$param['game_coding'] = 'AIVSAG';
		$param['uid'] = $this->info['puid'];
		$param['entrance'] = 'connect';
		$post_string = http_build_query($param);
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$paymenturl = @curl_exec($ch);
		$errno = @curl_errno($ch);
        $error = @curl_error($ch);
		@curl_close($ch);
//info_log($api_url.'?'.$post_string,'aaab');
//info_log($paymenturl,'aaab');
if ($errno != CURLE_OK) {
info_log($errno.$error,'err_payurlget');
}
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
    	$pageSize = 14;
    	$this->view->user = $user;
		$this->view->logs = $logs;
        $this->view->count = $count;
        $this->view->pageSize = 14;
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
							$summary = 'Bảng quà';
						break;
						case 1:
							$summary = 'Quà lên cấp';
						break;
						case 2:
							$summary = 'Quà bốc thăm';
						break;
						case 3:
							$summary = 'Quà event';
						break;
						case 4:
							$summary = 'Mời bạn';
						break;
						case 5:
							$summary = 'Quà sư đồ';
						break;
						case 6:
							$summary = 'Quà đăng nhập';
						break;
						case 10:
							$summary = 'Quà hâm mộ';
						break;
						default:
							$summary = 'Bảng quà';
						break;
					}
					$logs[$k]['summary'] = $summary;
				}
    		}

    		$count = count($logs);
    		$logs = json_encode($logs);
    	}

    	$pageSize = 14;
    	$this->view->user = $user;
		$this->view->logs = $logs;
        $this->view->count = $count;
        $this->view->pageSize = 14;
        $this->view->pageNum = ceil($count/$pageSize);
        $this->render();
    }

}