<?php

/**
 * island index controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Hulj
 */
class InviteController extends Zend_Controller_Action
{
    protected $uid;

    public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
        $this->view->hostUrl = Zend_Registry::get('host');
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            echo '<html><body><script type="text/javascript">window.top.location="http://mixi.jp/run_appli.pl?id=' . APP_ID . '";</script></body></html>';
            exit;
        }

        $this->view->mixi_platform_api_url = $_COOKIE['mixi_platform_api_url'];
        $this->uid = $auth->getIdentity();
    }

    public function topAction()
    {
        $this->render();
    }

    public function friendsAction()
    {
        $friends = Bll_Cache_User::getTaobaoNotJoinFriends($this->uid, $_SESSION['session']);

        $count = count($friends);
        /*$rows = ceil($count/4);
        $d = $rows*4 - $count;
        if ($d > 0) {
            for($i = 0; $i < $d; $i++) {
                $friends[] = false;
            }
        }
        $data = array();
        for($j = 0; $j < $rows; $j++) {
            $data[$j] = array($friends[$j*4], $friends[$j*4+1], $friends[$j*4+2], $friends[$j*4+3]);
        }*/
        $this->view->count = $count;
        $this->view->friends = $friends;
        
        $friendsArray = array_chunk($friends, 16);
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
        $ids = $this->_request->getParam('ids');
        if(!empty($ids)) {
            foreach($ids as $id) {
                Bll_Island_Message::send('INVITE', $this->uid, $id);
            }
        }
        $this->_redirect($this->view->baseUrl . '/invite/top');
        exit;
    }
 }
