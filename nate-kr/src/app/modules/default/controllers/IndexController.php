<?php

/**
 * island index controller
 *
 * @copyright  Copyright (c) 2010 HapyFish
 * @create      2010/10    lijun.hu
 */
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
        $this->view->tm = time();
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

    public function indexAction()
    {

    	if (APP_STATUS == 0) {
    		$ip = $this->getClientIP();
    		if ($ip != '116.232.78.124' && $ip != '183.15.146.221') {
    			header('Location: ' . STATIC_HOST . '/maintance/index.html?v=2011010601');
    			exit;
    		}
    	}

    	//info_log(Zend_Json::encode($this->getRequest()->getParams()), 'callback');

		try {
			$appInstance = Hapyfish2_Application_NateCy::newInstance($this);
			$isValid = $appInstance->run();
		} catch (Exception $e) {
    		err_log($e->getMessage());
    		//echo '加载数据出错，请重新进入。';
    		echo 'DB 오류 입니다. 다시 접속해 주세요.';
    		//echo '<div style="text-align:center;margin-top:30px;"><img src="' . STATIC_HOST . '/maintance/images/problem1.gif" alt="loading data error,please reload later" /></div>';
    		exit;
    	}

		if (!$isValid) {
			echo 'DB 오류 입니다. 다시 접속해 주세요.';
			exit;
		}

        $uid = $appInstance->getUserId();
        $isnew = $appInstance->isNewUser();
        $platformUid = $appInstance->getPlatformUid();

        if ($isnew) {
        	$ok = Hapyfish2_Island_Bll_User::joinUser($uid);
        	if (!$ok) {
    			//echo '创建初始化数据出错，请重新进入。';
    			echo 'DB 초기화 에러 입니다. 다시 접속해 주세요.';
    			exit;
        	}

        	//是否邀请好友完成
        	Hapyfish2_Island_Bll_Invite::inviteDone($platformUid);
        } else {
        	$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
        	if (!$isAppUser) {
        		$ok = Hapyfish2_Island_Bll_User::joinUser($uid);
        	    if (!$ok) {
    				//echo '创建初始化数据出错，请重新进入。';
    				echo 'DB 초기화 에러 입니다. 다시 접속해 주세요.';
    				exit;
        		}
        	} else {
        		$status = Hapyfish2_Platform_Cache_User::getStatus($uid);
        		if ($status > 0) {
        			if ($status == 1) {
        				//$msg = '该帐号(小岛门牌号:' . $uid . ')因使用外挂或违规已被封禁，有问题请联系管理员QQ:1471558464';
        				$msg = '이 ID(아일랜드 UID:'.$uid.')는 해킹 혹은 불법사용으로 인하여 접속금지 되었습니다. dground club으로 문의하세요.';
        			} else if ($status == 2) {
        				//$msg = '该帐号(小岛门牌号:' . $uid . ')因数据出现异常被暂停使用，有问题请联系管理员QQ:1471558464';
        				$msg = '이 ID(아일랜드 UID:'.$uid.')는 DB 이상으로 인하여 사용정지 되었습니다. dground club으로 문의하세요.';
        			} else if ($status == 3)  {
        				//$msg = '该帐号(小岛门牌号:' . $uid . ')因利用bug被暂停使用[待处理后恢复]，有问题请联系管理员QQ:1471558464';
        				$msg = '이 ID(아일랜드 UID:'.$uid.')는 버그로 인하여 사용정지 되었습니다. dground club으로 문의하세요.';
        			} else {
        				//$msg = '该帐号(小岛门牌号:' . $uid . ')暂时不能访问，有问题请联系管理员QQ:1471558464';
        				$msg = '이 ID(아일랜드 UID:'.$uid.')는 접속에 이상이 생겼습니다. dground club으로 문의하세요.';
        			}

        			echo $msg;
        			exit;
        		}
        	}
        }

        //update friend count achievement
		$count = Hapyfish2_Platform_Bll_Friend::getFriendCount($uid);
        if ($count > 0) {
        	$achievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
        	if ($achievement['num_16'] < $count) {
        		$achievement['num_16'] = $count;
        		Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $achievement);
        	}
        }

        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

        //korea add user licence
        $lcnkey = 'i:u:playgamelcn:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$this->view->allowPlay = $cache->get($lcnkey);

        $this->view->hf_skey = $appInstance->getSKey();
        $this->view->uid = $uid;
        $rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
        $tmp = str_replace("'", "\'", $rowUser['name']);
        $tmp = str_replace('"', '\"', $tmp);
        $this->view->uname = $tmp;
        $this->view->platformUid = $platformUid;
        $this->view->showpay = true;
        $this->view->newuser = $isnew ? 1 : 0;
        $this->render();
    }

    public function playgameAction()
    {
        $uid = $this->_request->getParam('id');
        if (Hapyfish2_Island_Cache_User::isAppUser($uid)) {
            $lcnkey = 'i:u:playgamelcn:' . $uid;
            $cache = Hapyfish2_Cache_Factory::getMC($uid);
            $cache->set($lcnkey, '1');
        }
        echo json_encode(array('status'=>$uid));
        exit;
    }

    public function testAction()
    {
    	echo 'hello nate<br />';
    	echo $this->_request->getActionName();
    	echo urlencode('&');
    	exit;
    }
 }

