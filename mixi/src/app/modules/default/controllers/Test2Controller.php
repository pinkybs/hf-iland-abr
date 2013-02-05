<?php

class TestController extends Zend_Controller_Action
{
    //protected $uid;

    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
		$this->view->staticUrl = Zend_Registry::get('static');
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            //$auth->getStorage()->write(258027420);
        }

        $this->uid = $auth->getIdentity();
    }

    public function publishfeedAction()
    {
        /*
        $taobao = new Taobao_Rest('12029234', '96ad573ff3fef48a84b3fcf7e7da605c', 12029234, 'island');
        echo 'uid: ' . $this->uid;
        echo 'session: ' . $_SESSION['session'];
        $taobao->setUser($this->uid, $_SESSION['session']);
        
        $body = '${actor}在开心岛主中升到了10级，赶快去看看吧~';
        
        $medias = array(
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
        );
        try {
            $taobao->jianghu->feed_publish($body, array('medias' => Zend_Json::encode($medias)));
        }catch (Exception $e) {
            echo $e->getMessage();
        }*/
        
        $type = $this->_request->getParam('type');
        
        if ($type) {
            if ($type == 'USER_LEVEL_UP') {
                Bll_Island_Activity::send('USER_LEVEL_UP', $this->uid, array('level' => 18));
            } else if($type == 'ISLAND_LEVEL_UP') {
                Bll_Island_Activity::send('ISLAND_LEVEL_UP', $this->uid, array('level' => 8));
            } else if($type == 'BUILDING_LEVEL_UP') {
                Bll_Island_Activity::send('BUILDING_LEVEL_UP', $this->uid);
            } else if($type == 'BOAT_LEVEL_UP') {
                Bll_Island_Activity::send('BOAT_LEVEL_UP', $this->uid);
            } else if($type == 'DOCK_EXPANSION') {
                Bll_Island_Activity::send('DOCK_EXPANSION', $this->uid);
            } else if($type == 'MISSION_COMPLETE') {
                Bll_Island_Activity::send('MISSION_COMPLETE', $this->uid);
            } else if($type == 'USER_OBTAIN_TITLE') {
                Bll_Island_Activity::send('USER_OBTAIN_TITLE', $this->uid, array('title' => 'DIY能手'));
            } else if($type == 'BUILDING_DAMAGE') {
                Bll_Island_Activity::send('BUILDING_DAMAGE', $this->uid, array('building' => '蛋糕店'), 23608306);
            } else if($type == 'APP_JOIN') {
                Bll_Island_Activity::send('APP_JOIN', $this->uid);
            }
        }
        
        exit;
    }
    
    public function redistestAction()
    {
        $medias = array(
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
            array('mediaName' => 'island', 'media' => 'http://island.hapyfish.com/static/apps/island/images/sl_03.gif', 'mediaLink' => 'http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234', 'mediaDesc' => 'hapyfish', 'mediaType' => 0),
        );
        
        $context = array(
            'uid' => 20705296,
            'session' => 'adsfasdfsdfsdafsadfsda',
            'data' => array(
                'body' => '${actor}在开心岛主中升到了10级，赶快去看看吧~',
                'params' => array('medias' => Zend_Json::encode($medias))
            )
        );
        
        $queue = new MyLib_Redis_Queue('activity', '192.168.0.102');
        $queue->push($context);
        exit;
    }
    
    public function redispopAction()
    {        
        $queue = new MyLib_Redis_Queue('activity', '192.168.0.102');
        $data = $queue->pop();
        
        print_r($data);
        exit;
    }
    
    public function publishmsgAction()
    {
        $to_uid = 94640398;
        $uid = $this->uid;
        
        $content = '胡立军在<a href="http://jianghu.taobao.com/admin/plugin.htm?appkey=12029234&invite=true&invitor=' 
                    . $to_uid . '&inviter_id=' . $uid . '">开心岛主</a>中邀请你去Ta的岛上做客，费用全包哦~赶快动身吧！';
                    
        $taobao = new Taobao_Rest('12029234', '96ad573ff3fef48a84b3fcf7e7da605c', 12029234, 'island');
        $taobao->setUser($this->uid, $_SESSION['session']);
        
        $type = 1;
        
        try {
            $taobao->jianghu->msg_publish($to_uid, $content, $type);
        }catch (Exception $e) {
            echo $e->getMessage();
        }
        
        exit;        
        
    }
    
    public function feedinsertAction()
    {
        $feed = Dal_Mongo_Feed::getDefaultInstance();
        
        $info = array(
            'uid' => 258027420,
            'actor' => 47459000,
            'target' => 258027420,
            'template_id' => 11,
            'title' => '{"money":"2000"}',
            'type' => 2,
            'create_time' => 1268127039
        );
        //$feed->insertMinifeed($info);
        $feed->insertTest();
        
        exit;
    }
    
    public function getfeedAction()
    {
        $feed = Dal_Mongo_Feed::getDefaultInstance();
        $result = $feed->getMinifeed(258027420);
        
        print_r($result);
        
        exit;
    }
    
    public function onlineAction()
    {
        $uid = $this->uid;
        $taobao = new Taobao_Rest('12029234', '96ad573ff3fef48a84b3fcf7e7da605c', 12029234, 'island');
        
        $taobao->setUser($uid, $_SESSION['session']);
        
        try {
            $bool = $taobao->jianghu->friends_areFriends($uid, $uid);
            
            echo $bool ? 'yes' : 'no';
        }catch (Exception $e) {
            echo $e->getMessage();
        }
                
        exit;  
    }
    
    public function md5Action()
    {
        $start = microtime(true);
        for($i = 0; $i < 10000; $i++) {
            md5('taobao_test_' . $i);
        }
        
        echo microtime(true) - $start;
        exit;
    }
    
    public function sendmsgAction()
    {
        $type = $this->_request->getParam('type');
        if ($type) {
            if ($type == 'INVITE') {
                Bll_Island_Message::send('INVITE', $this->uid, 94640398);
            } else if($type == 'GIFT') {
                Bll_Island_Message::send('GIFT', $this->uid, 94640398, array('gift_id' => 6321));
            }
        }
        
        exit;
    }
    
 }
