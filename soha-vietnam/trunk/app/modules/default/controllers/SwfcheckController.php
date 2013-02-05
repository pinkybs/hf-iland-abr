<?php

class SwfcheckController extends Zend_Controller_Action
{
    public function init()
    {
    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }

    protected function echoResult($data)
    {
    	header("Cache-Control: no-store, no-cache, must-revalidate");
    	echo json_encode($data);
    	exit;
    }

    /**
     * init swf
     *
     */
    public function initswfAction()
    {
    	require (CONFIG_DIR . '/swfconfig-check.php');
        $this->echoResult($swfResult);
    }

    /**
     * init user Action
     *
     */
    function inituserAction()
    {
		//header("Cache-Control: max-age=2592000");
    	echo Hapyfish2_Island_Bll_BasicInfo::getInitVoData();
		exit;
    }

 }