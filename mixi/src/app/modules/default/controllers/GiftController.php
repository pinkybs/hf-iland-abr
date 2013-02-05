<?php

/**
 * island index controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Hulj
 */
class GiftController extends Zend_Controller_Action
{
    protected $uid;
    
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            echo '<html><body><script type="text/javascript">window.top.location="http://mixi.jp/run_appli.pl?id=' . APP_ID . '";</script></body></html>';
            exit;
        }
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
        $this->view->hostUrl = Zend_Registry::get('host');

        $this->view->mixi_platform_api_url = $_COOKIE['mixi_platform_api_url'];
        $this->uid = $auth->getIdentity();
    }

    public function topAction()
    {
        $recipientIds = $this->_request->getParam('recipient');
        $dalUser = Dal_Island_User::getDefaultInstance();
        $userInfo = $dalUser->getUser($this->uid);
        
        if (!empty($recipientIds)) {
            /*
            $appUrl = 'http://mixi.jp/run_appli.pl?id=' . APP_ID;
            //$appParams = $this->_request->getParam('appParams');
            //$appUrl = $appUrl . '&appParams=' . urlencode($appParams);
            $this->view->sendMessage = true;
            $this->view->recipient = $recipientIds;
            $this->view->title = 'プレゼント';
            //{*actor*}送了您一份来自【{*app_link*}】的礼物，赶快打开看看吧！
            $actor_info = Bll_User::getPerson($this->uid);

            $this->view->body = $actor_info['name'] . 'があなたに【ドリーム☆アイランド　' . $appUrl . '】のギフトを送ってくれたよ。さっそく確認しにいこう！';*/
            
            //ＡさんがＢさんにxxx（礼物名称）をあげました
            $this->view->sendMessage = true;
            $gid = $this->_request->getParam('gid');
            
            $actor_info = Bll_User::getPerson($this->uid);
            $target_info =  Bll_User::getPerson($recipientIds);
            
            $gift = Bll_Cache_Island::getGiftById($gid);
            
            //$this->view->message = $actor_info['name'] . 'さんが' . $target_info['name'] . 'さんに' . $gift['name'] . 'をあげました。';
            $this->view->message = $target_info['name'] . 'さんに' . $gift['name'] . 'を送ったよー！デコってね！';
            $this->view->messageImg = Zend_Registry::get('static') . '/apps/island/images/gift/items/' . $gift['img'];
        }

        $this->view->giftList = array_chunk(Bll_Cache_Island::getGiftList(),12);
        $this->view->userInfo = $userInfo;
        $this->render();
    }

    public function friendsAction()
    {
        $gid = $this->_request->getPost('gid');
        
        if (empty($gid)) {
            $this->_redirect('/gift/top');
        }
        
        //$friends = Bll_Cache_User::getMixiFriends($this->uid);
        
        $friendIds = Bll_Friend::getFriends($this->uid);
        $friends = array();
        foreach ($friendIds as $key=>$value) {
            $friends[] = array('uid' => $value);
        }
        
        Bll_User::appendPeople($friends);        
                
        $dalGift = Dal_Island_Gift::getDefaultInstance();
        $gift = $dalGift->getGiftById($gid);
        
        $dalGift2 = Dal_Mongo_Gift::getDefaultInstance();
        $count = $dalGift2->getGiftStatus($this->uid);

        $friendsArray = array_chunk($friends, 15);
        $pageCount = count($friendsArray);
        $this->view->friendsArray = $friendsArray;
        $this->view->pageCount = $pageCount;
        
        $pageArray = array();
        for ( $i = 0; $i < $pageCount; $i++ ) {
        	$pageArray[$i] = 1;
        }
        $this->view->pageArray = $pageArray;
        
        $this->view->gid = $gid;
        $this->view->gift = $gift;
        $this->view->friends = $friends;
        $this->view->count = $count;
        $this->render();
    }
    
    public function sendAction()
    {
        $gid = $this->_request->getPost('gid');
        $ids = $this->_request->getPost('ids');
        
        $dalGift2 = Dal_Mongo_Gift::getDefaultInstance();
        $sendCount = $dalGift2->getGiftStatus($this->uid);

        if (!empty($gid) && !empty($ids)) {
        	$gift = Bll_Cache_Island::getGiftById($gid);
            if (!$gift) {
            	//info_log($uid, 'gift_err');
            	$this->_redirect('/gift/top');
            	return;
        	}
        	
            $in_fids = array();
            $out_fids = array();
            foreach ($ids as $fid) {
                if (Bll_User::isAppUser($fid)) {
                    $in_fids[] = $fid;
                } else {
                    $out_fids[] = $fid;
                }
            }
            $count = $sendCount - count($ids);
            if ($count >=0 ) {
                Bll_Island_Gift::sendGift($gid, $this->uid, $count, $in_fids, $out_fids);
            }
        }
        
        /*
        $st = floor(microtime(true)*1000);
        $sig = md5($gid . $this->uid . $st . APP_KEY . APP_SECRET);
        $appParam = array('hf_sender'=>$this->uid,'hf_gift_id'=>$gid,'hf_st'=>"$st",'hf_sig'=>$sig);
        Bll_Island_Log::addSendGift($this->uid, $ids, $gid, time(), $sig);
        */
        
        $ids = implode(',', $ids);
        //$this->_redirect($this->view->baseUrl . '/gift/top/recipient/' . $ids . '/appParams/' . Zend_Json::encode($appParam));
        $this->_redirect($this->view->baseUrl . '/gift/top/recipient/' . $ids . '/gid/' . $gid);
        exit;
    }
    
    /**
     * magic function
     *   if call the function is undefined,then forward to not found
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        return $this->_forward('top');
    }
}

