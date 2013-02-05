<?php

class InviteController extends Zend_Controller_Action
{
    public function init()
    {
        $info = $this->vailid();
        if (!$info) {
            echo '<html><body><script type="text/javascript">window.top.location="http://soap.soha.vn/apps/dtd";</script></body></html>';
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

	public function friendsAction()
    {
        $puid = $this->info['puid'];
        //$friends = Bll_Cache_User::getTaobaoNotJoinFriends($this->uid, $_SESSION['session']);
        $taobao = Taobao_Rest::getInstance();
        $taobao->setUser($puid, $this->info['session_key']);
        $ptFriends = $taobao->jianghu_getFriends();

        $friends = null;
        if ( !empty($ptFriends) ) {
	        foreach ($ptFriends as $pfid => $data) {
	            $inGame = Hapyfish2_Platform_Bll_UidMap::getUser($pfid);
	            //if is in game friends
	            if (empty($inGame)) {
	                $friends[] = array('uid' => $pfid, 'name' => $data['name'], 'thumbnail' => $data['thumbnail']);
	            }
	        }
        }

        $count = count($friends);
        $this->view->count = $count;
        $this->view->friends = $friends;
		if(is_array($friends)){
			$friendsArray = array_chunk($friends, 16);
		}else{
			$friendsArray = array();
		}
        $pageCount = count($friendsArray);
        $this->view->friendsArray = $friendsArray;
        $this->view->pageCount = $pageCount;

        $pageArray = array();
        for ( $i = 0; $i < $pageCount; $i++ ) {
            $pageArray[$i] = 1;
        }
        $this->view->pageArray = $pageArray;
        $this->render();
    }

    public function sendAction()
    {
        $puid = $this->info['puid'];
		$ids = $this->_request->getParam('ids');
        if(!empty($ids)) {
            foreach($ids as $id) {
                Hapyfish2_Island_Bll_Message::send('INVITE', $puid, $id);
            }
        }
        $this->_redirect($this->view->baseUrl . '/invite/top');
        exit;
    }

 }
