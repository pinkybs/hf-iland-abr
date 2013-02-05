<?php

/**
 * casino controller
 *
 * @copyright  Copyright (c) 2010 HapyFish Inc. (http://www.hapyfish.com)
 * @create      2010/08/23    Liz
 */
class CasinoController extends Zend_Controller_Action
{
    protected $uid;
    
    /**
     * initialize basic data
     * @return void
     */
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $result = array('status' => '-1', 'content' => 'serverWord_101');
            echo Zend_Json::encode($result);
            exit;
        }
        
		$uid = $auth->getIdentity();

	    $controller = $this->getFrontController();
	    $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
	    $controller->setParam('noViewRenderer', true);

        $this->uid = $uid;
    }
	
    /**
     * init casino
     *
     */
    public function initcasinoAction()
    {
    	$uid = $this->uid;
    	//get casino init
        $casinoInfo = Bll_Casino_Casino::getCasinoInit($uid);
    	
        echo Zend_Json::encode($casinoInfo);
    }
    
    /**
     * raffle
     *
     */
    public function raffleAction()
    {
    	$uid = $this->uid;
    	
    	$id = 'raffle' . $uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id, 5);
        if ( !$lock ) {
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode($result);
            return;
        }
        
    	//raffle
        $result = Bll_Casino_Casino::raffle($uid);
    	
        echo Zend_Json::encode($result);
    }
    
    /**
     * get award
     *
     */
    public function getawardAction()
    {
    	$uid = $this->uid;
    	
    	$id = 'getaward' . $uid;
        $memcached = MyLib_Cache_Memcached::getInstance();
        $lock = $memcached->lock($id, 5);
        if ( !$lock ) {
            $result = array('status' => -1,'content' => 'serverWord_103');
            echo Zend_Json::encode(array('result' => $result));
            return;
        }
        
    	//get award
        $result = Bll_Casino_Casino::getAward($uid);
    	
        echo Zend_Json::encode($result);
    }

    /**
     * change
     *
     */
    public function changeAction()
    {
    	$uid = $this->uid;
    	//get lv init
        $result = Bll_Casino_Casino::changeCasino($uid);
    	
        echo Zend_Json::encode($result);
    }
    
 }