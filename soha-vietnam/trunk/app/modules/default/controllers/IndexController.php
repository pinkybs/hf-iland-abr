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
        info_log(json_encode($_REQUEST), 'from_oauth_client');

    	if (APP_STATUS == 0) {
    		$stop = true;
    		if (APP_STATUS_DEV == 1) {
    			$ip = $this->getClientIP();
	    		if ($ip == '27.115.48.202' || $ip == '122.147.63.223' || $ip == '116.247.76.102') {
	    			$stop = false;
	    		}
    		}
    		if ($stop) {
    			//header('Location: ' . STATIC_HOST . '/maintance/index.html?v=' . date('YmdHi') . '01');
    			header('Location:http://static-ak.snsplus.com/operation/repair_page/VN/index.html');
    			exit;
    		}
    	}

    	try {
    		$application = Hapyfish2_Application_Sohavn::newInstance($this);
    		$retValidate = $application->checkValidate();
    		if ($retValidate) {
    		    throw new Exception('check validate failed:'.$retValidate);
    		}
        	$application->run();
    	} catch (Exception $e) {
    		err_log($e->getMessage());
    		//echo '加载数据出错，请重新进入。';
    		echo '<div style="text-align:center;margin-top:30px;"><img src="' . STATIC_HOST . '/maintance/images/problem2.gif" alt="加载数据出错，请重新进入" /></div>';
    		exit;
    	}

        $uid = $application->getUserId();
        $isnew = $application->isNewUser();
        $platformUid = $application->getPlatformUid();
        

        if ($isnew) {
			$ok = Hapyfish2_Island_Bll_User::joinUser($uid);
        	Hapyfish2_Island_Event_Bll_Timegift::setup($uid);
        	if (!$ok) {
    			echo '创建初始化数据出错，请重新进入。';
    			exit;
        	}
	        $hfInviteSig = $_COOKIE['hf_invite_sig'];
	        if($hfInviteSig) {
		         $sig = base64_decode($hfInviteSig);
		         info_log($sig.'-'.$uid, 'invitejoin');
		         if($sig) {
			        $dalInvite = Hapyfish2_Island_Dal_InviteLog::getDefaultInstance();
			        $isExists = $dalInvite->getInvite($sig);
			        //info_log($isExists['actor'].'-'.$uid, 'invitejoin');
			        if($isExists) {
				        $inviteUid = $isExists['actor'];	
				        Hapyfish2_Island_Bll_Invite::add($inviteUid, $uid);
				        $dalInvite->deleteInvite($sig);
				        setcookie('hf_invite_sig', '' , 0, '/', str_replace('http://', '.', HOST));
				        info_log('inviter:'.$inviteUid.',uid:'.$uid, 'invitejoin');
			        }
		         }
	         }

        } else {
        	$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
        	if (!$isAppUser) {
        		$ok = Hapyfish2_Island_Bll_User::joinUser($uid);
        	    if (!$ok) {
    				echo '创建初始化数据出错，请重新进入。';
    				exit;
        		}
        	} else {
        		$status = Hapyfish2_Platform_Cache_User::getStatus($uid);
        		if ($status > 0) {
        			if ($status == 1) {
        				$msg = '该帐号(小岛门牌号:' . $uid . ')因使用外挂或违规已被封禁，有问题请联系管理员QQ:1471558464';
        			} else if ($status == 2) {
        				$msg = '该帐号(小岛门牌号:' . $uid . ')因数据出现异常被暂停使用，有问题请联系管理员QQ:1471558464';
        			} else if ($status == 3)  {
        				$msg = '该帐号(小岛门牌号:' . $uid . ')因利用bug被暂停使用[待处理后恢复]，有问题请联系管理员QQ:1471558464';
        			} else {
        				$msg = '该帐号(小岛门牌号:' . $uid . ')暂时不能访问，有问题请联系管理员QQ:1471558464';
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
				try {
        			Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $achievement);

					//task id 3018,task type 16
					Hapyfish2_Island_Bll_Task::checkTask($uid, 3018);
				} catch (Exception $e) {
				}
        	}
        }

        $notice = Hapyfish2_Island_Cache_BasicInfo::getNoticeList();
        $notice = array();
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

        $this->view->uid = $uid;
        $this->view->platformUid = $platformUid;
        $this->view->showpay = true;
        $this->view->newuser = $isnew ? 1 : 0;
        
        //序号
        $time = time();
        $snsCode = md5('game_code='.APP_NAME.'time='.$time.'uid='.$platformUid.APP_SECRET);
        $this->view->snsUrl = 'http://sn.service.snsplus.com/?game_code='.APP_NAME.'&uid='.$platformUid.'&time='.$time.'&sig='.$snsCode;
        
        $this->render();
    }

    public function loginAction()
    {
    	$requestIds = "";
		if($_REQUEST['request_ids']) {
			$requestIds = @explode("?",$_REQUEST['request_ids']);
			//info_log($requestIds[0], 'login_oauth_client');
			setcookie('hf_invite_sig', base64_encode($requestIds[0]) , 0, '/', str_replace('http://', '.', HOST));
		}
        $oauthRest = new Ming_OAuthRest();
        $url = $oauthRest->getAuthorizeURL(HOST);
        if ($url) {
            $this->_redirect($url);
        }
        else {
            echo 'error in oauth Authorize';
        }
        exit;
    }

    public function testAction()
    {
        echo 'hello vietnam';
        exit;
    }
}