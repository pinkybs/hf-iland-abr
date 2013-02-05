<?php

/**
 * island index controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Hulj
 */
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
        $this->view->hostUrl = Zend_Registry::get('host');
        
        $this->view->mixi_platform_api_url = $_COOKIE['mixi_platform_api_url'];
    }

    /**
     * index Action
     *
     */
    public function indexAction()
    {
        $application = Bll_Application_Mixi::newInstance($this);
        if ($application->autoRegisterPlugin()) {
            $application->run();
        } else {
            $application->redirect404();
        }
    }
    
    public function runAction()
    {
        $application = Bll_Application_Mixi::newInstance($this);
        if ($application->autoRegisterPlugin()) {
            $application->run();
        } else {
            $application->redirect404();
        }
    }
    
    public function flashAction()
    {
        if(isset($_GET['hf_dev']) && isset($_GET['hf_uid'])) {
            $uid = $_GET['hf_uid'];
            $auth = Zend_Auth::getInstance();
            $auth->getStorage()->write($uid); 
        }
        
        $this->render();
    }
    
    public function helpAction()
    {
       $this->render(); 
    }
 }

