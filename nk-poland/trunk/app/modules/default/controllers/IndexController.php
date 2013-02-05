<?php

require_once(CONFIG_DIR . '/language.php');

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
        info_log(json_encode($_REQUEST), 'from_Nk');

        if ($_GET['sessionid'] != md5($_GET['userid'].$_GET['sessionkey'].APP_SECRET)) {
            echo 'Error at authentication.';
            exit;
        }

        $this->view->userid = $_GET['userid'];
        $this->view->sessionkey = $_GET['sessionkey'];
        $this->view->sessionid = $_GET['sessionid'];
        $this->view->invitor = $_GET['invitor'];
        
        /*$hasCheck = $this->_request->getParam('hascheck', 0);
        if ( $hasCheck != 1 ) {
            $uid = $_GET['userid'];
            $this->_redirect(HOST.'/index/edituser?uid='.$uid);
        }*/
        
        $this->render();
    }

    public function flashAction()
    {
    	if (APP_STATUS == 0) {
    		$stop = true;
    		if (APP_STATUS_DEV == 1) {
    			$ip = $this->getClientIP();
	    		if ($ip == '27.115.48.202' || $ip == '122.147.63.223' || $ip == '114.32.107.235') {
	    			$stop = false;
	    		}
    		}
    		if ($stop) {
    			header('Location: ' . STATIC_HOST . '/maintance/index.html?v=' . date('YmdHi'));
    			exit;
    		}
    	}

    	/*$userInfo = $this->_request->getParam('user');
    	$friendsInfo = $this->_request->getParam('friends');
    	info_log($userInfo['figureurl'], 'aa');
    	print_r($userInfo);
    	print_r($friendsInfo);
    	exit;*/

    	try {
    		$application = Hapyfish2_Application_Nk::newInstance($this);
        	$application->run();
    	} catch (Exception $e) {
    		err_log($e->getMessage());
    		//echo '加载数据出错，请重新进入。';
    		echo '<div style="text-align:center;margin-top:30px;"><img src="' . STATIC_HOST . '/maintance/images/problem1.gif" alt="加载数据出错，请重新进入" /></div>';
    		exit;
    	}

        $uid = $application->getUserId();
        $isnew = $application->isNewUser();
        $platformUid = $application->getPlatformUid();

        if ($isnew) {
			$ok = Hapyfish2_Island_Bll_User::joinUser($uid);
        	Hapyfish2_Island_Event_Bll_Timegift::setup($uid);
        	if (!$ok) {
    			echo LANG_PLATFORM_INDEX_TXT_10;
    			exit;
        	}

        	//是否邀请好友完成
        	$params = $application->get_params();
        	if ($params['invitor']) {
        	    $invitor = $params['invitor'];
        	    $inviteUser = Hapyfish2_Platform_Bll_UidMap::getUser($invitor);
        	    if ($inviteUser) {
        	        Hapyfish2_Island_Bll_Invite::add($inviteUser['uid'], $uid);
				    info_log($inviteUser['uid'] . ' invite->' . $uid . 'DONE!', 'Bll_Invite_logs');
        	    }
        	}
        } else {
        	$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
        	if (!$isAppUser) {
        		$ok = Hapyfish2_Island_Bll_User::joinUser($uid);
        	    if (!$ok) {
    				echo LANG_PLATFORM_INDEX_TXT_10;
    				exit;
        		}
        	} else {
        		$status = Hapyfish2_Platform_Cache_User::getStatus($uid);
        		if ($status > 0) {
        			if ($status == 1) {
        				$msg = str_replace('{*uid*}', $uid, LANG_PLATFORM_INDEX_TXT_12);
        			} else if ($status == 2) {
        				$msg = str_replace('{*uid*}', $uid, LANG_PLATFORM_INDEX_TXT_13);
        			} else if ($status == 3)  {
        				$msg = str_replace('{*uid*}', $uid, LANG_PLATFORM_INDEX_TXT_14);
        			} else {
        				$msg = str_replace('{*uid*}', $uid, LANG_PLATFORM_INDEX_TXT_15);
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
        if (empty($notice)) {
        	$this->view->showNotice = false;
        } else {
        	$this->view->showNotice = true;
			$this->view->mainNotice = $notice['main'];
			$this->view->subNotice = $notice['sub'];
			$this->view->picNotice = $notice['pic'];
        }

        $this->view->uid = $uid;
        $rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
        $tmp = str_replace("'", "\'", $rowUser['name']);
        $tmp = str_replace('"', '\"', $tmp);
        $this->view->uname = str_replace("'", "\'", $tmp);
        $this->view->platformUid = $platformUid;
        $this->view->showpay = true;
        $this->view->newuser = $isnew ? 1 : 0;

        $this->render();
    }

    public function checkuserAction()
    {
        $uid = $this->_request->getParam('uid');
        $this->view->uid = $uid;
        $this->render();
    }
    
    public function edituserAction()
    {
        $uid = $this->_request->getParam('uid');
        $this->view->uid = $uid;
        $this->render();
    }
    
    public function testAction()
    {
        echo 'hello nk poland';
        exit;
    }
}