<?php
class UpgradegiftController extends Zend_Controller_Action
{
	public function init()
    {
    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }
    

    public function testsendgiftAction()
    {
    	$uid = $this->_request->getParam('uid');
    	
    	Zend_Debug::dump(Hapyfish2_Island_Event_Bll_UpgradeGift::gifttouser($uid));
    	
    	exit();
    }
    
    public function testgettfAction()
    {
    	$uid = $this->_request->getParam('uid');
    	
    	Zend_Debug::dump(Hapyfish2_Island_Event_Bll_UpgradeGift::getTF($uid));
    	
    	exit();
    }
    
    public function testsettfAction()
    {
    	$uid = $this->_request->getParam('uid');
    	
    	Zend_Debug::dump(Hapyfish2_Island_Event_Bll_UpgradeGift::setTF($uid));
    	
    	exit();
    }
    
    public function testclearAction()
    {
    	$uid = $this->_request->getParam('uid');
    	
    	Zend_Debug::dump(Hapyfish2_Island_Event_Bll_UpgradeGift::clearTF($uid));
    	
    	exit();
    }
    
    public function lupawardboxopenedAction()
    {
    	echo 'aa';
    	exit();
    }
    
}