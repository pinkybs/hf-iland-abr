<?php

class InviteController extends Zend_Controller_Action
{
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
    	$this->render();
    }

}