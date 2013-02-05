<?php
require_once(CONFIG_DIR . '/language.php');

class ConsumelogsController extends Zend_Controller_Action
{
    protected $uid;

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

    public function init()
    {
        $info = $this->vailid();
        if (!$info) {
        	echo '<html><body><script type="text/javascript">window.top.location="http://i.taobao.com/apps/show.htm?appkey=12029234";</script></body></html>';
        	exit;
        }

        $this->info = $info;
        $this->uid = $info['uid'];

        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
        $this->view->uid = $info['uid'];
        $this->view->platformUid = $info['puid'];

        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

        /* for 外联通  20110702 */
		$this->view->connectSnsplus = '';
        $entrance = $this->_request->getParam('entrance');
        //from snsplus
        if ('connect' == $entrance) {
            $this->view->connectSnsplus = 'entrance=connect';
        }
    }

    public function coinAction()
    {
		$uid = $this->uid;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$user['gold'] = Hapyfish2_Island_HFC_User::getUserGold($uid);
		$time = time();
		$year = date('Y', $time);
		$month = (int)date('n', $time);

    	$logs = Hapyfish2_Island_Bll_ConsumeLog::getCoin($uid, $year, $month, 50);
    	if (!$logs) {
    		$count = 0;
    		$logs = '[]';
    	} else {
    		$count = count($logs);
    		$logs = json_encode($logs);
    	}
    	$pageSize = 25;
    	$this->view->user = $user;
    	$this->view->date = $year . LANG_PLATFORM_INDEX_TXT_01 . $month . LANG_PLATFORM_INDEX_TXT_02;
		$this->view->logs = $logs;
        $this->view->count = $count;
        $this->view->pageSize = 25;
        $this->view->pageNum = ceil($count/$pageSize);
        $this->render();
    }

    public function goldAction()
    {
		$uid = $this->uid;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		$user['face'] = $user['figureurl'];
		$user['gold'] = Hapyfish2_Island_HFC_User::getUserGold($uid);
		$time = time();
		$year = date('Y', $time);
		$month = (int)date('n', $time);
		$isPrev = (int)$this->_request->getParam('prev');
		$selMonth = $month;
		if ($isPrev) {
		    $selTime = mktime(0, 0, 0, $month, 1, $year);
		    $selTime = $selTime - 172800;
		    $selMonth = (int)date('n', $selTime);
		}

    	$logs = Hapyfish2_Island_Bll_ConsumeLog::getGold($uid, $year, $selMonth, 50);
    	if (!$logs) {
    		$count = 0;
    		$logs = '[]';
    	} else {
    		$count = count($logs);
    		$logs = json_encode($logs);
    	}
    	$pageSize = 25;
    	$this->view->user = $user;
    	$this->view->date = $year . LANG_PLATFORM_INDEX_TXT_01 . $selMonth . LANG_PLATFORM_INDEX_TXT_02;
    	$this->view->isPrev = $isPrev;
		$this->view->logs = $logs;
        $this->view->count = $count;
        $this->view->pageSize = 25;
        $this->view->pageNum = ceil($count/$pageSize);
        $this->render();
    }

    function __call($methodName, $args)
    {
        echo '400';
        exit;
    }

}