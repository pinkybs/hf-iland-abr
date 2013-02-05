<?php

/**
 * Operations Index Controller(modules/Operations/controllers/Operations_IndexController.php)
 * Operations Index
 *
 * @create    2010/07/09    hwq
 */
class StatusController extends Zend_Controller_Action
{
	protected $_secretKey;
    /**
     * page init
     *
     */
    function init()
    {
        $p = $this->_request->getParam('p');
        if ( $p != 496700 ) {
            echo '不准非法进入！';
            exit;
        }
        $controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
        $this->view->staticUrl = Zend_Registry::get('static');
        //$auth = Zend_Auth::getInstance();
        //$this->uid = $auth->getIdentity();
        
		$this->_secretKey = Zend_Registry::get('secret');


    }

    /**
     * manager index controller index action
     *
     */
    public function indexAction()
    {

        $this->view->title = '快乐岛主运营后台';

    }

    
   public function updateaccountAction()
    {
        info_log('updateaccount ************ start ************** ', "updateaccount");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        $aryparams['uid'] = $params['uid'];
        $aryparams['cleantype'] = $params['cleantype'];
        if (!$this->_validate($params['validate'], $aryparams)) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $str = substr($params['uid'],0,strlen($params['uid'])-1);
            $aryParams = split(",", $str);
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->updateIslandPeopleStatus($aryParams,$params['cleantype']);
        }
        info_log('updateaccount ************ end ************** ', "updateaccount");
		echo $result;
        //return $result;
		exit;
    }
    
    
   public function checkforbiddenAction()
   {
        info_log('checkforbidden ************ start ************** ', "checkforbidden");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        $ary['uid'] = $params['uid'];
        if (!$this->_validate($params['validate'], $ary)) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $str = substr($params['uid'],0,strlen($params['uid'])-1);
            $aryParams = split(",", $str);
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->checkForbidden($aryParams);

        }
        info_log('updateaccount ************ end ************** ', "checkforbidden");
        //return $result;
        echo $result;
		exit;
    }
    
    
   public function cleanuserinfoAction()
   {
        info_log('cleanUserInfo ************ start ************** ', "cleanUserInfo");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        $ary['uid'] = $params['uid'];
        if (!$this->_validate($params['validate'], $ary)) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $str = substr($params['uid'],0,strlen($params['uid'])-1);
            $aryParams = split(",", $str);
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->cleanUserInfo($aryParams);
        }
        info_log('cleanUserInfo ************ end ************** ', "cleanUserInfo");
        //return $result;
        echo $result;
        exit;
    }
    
   public function resumeuserinfoAction()
   {
        info_log('resumeUserInfo ************ start ************** ', "resumeUserInfo");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        $ary['uid'] = $params['uid'];
        if (!$this->_validate($params['validate'], $ary)) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $str = substr($params['uid'],0,strlen($params['uid'])-1);
            $aryParams = split(",", $str);
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->resumeUserInfo($aryParams);
        }
        info_log('resumeUserInfo ************ end ************** ', "resumeUserInfo");
        //return $result;
        echo $result;
        exit;
    }
    
   public function givepresentAction()
   {
        info_log('givepresent ************ start ************** ', "givepresent");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        $ary['uid'] = $params['uid'];
        $ary['present'] = $params['present'];
        if (!$this->_validate($params['validate'], $ary)) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $str = substr($params['uid'],0,strlen($params['uid'])-1);
            $aryParams = split(",", $str);
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->givePresent($aryParams,$ary['present']);
        }
        info_log('givepresent ************ end ************** ', "givepresent");
        //return $result;
        echo $result;
        exit;
    }
    
   public function givemoneyAction()
   {
        info_log('givemoney ************ start ************** ', "givemoney");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        $ary['uid'] = $params['uid'];
        $ary['coin'] = $params['coin'];
        $ary['gold'] = $params['gold'];
        if (!$this->_validate($params['validate'], $ary)) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $str = substr($params['uid'],0,strlen($params['uid'])-1);
            $aryParams = split(",", $str);
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->addMoney($aryParams,$params['gold'],$params['coin']);
        }
        info_log('givemoney ************ end ************** ', "givemoney");
        //return $result;
        echo $result;
        exit;
    }
    
   public function getpresentAction()
   {
        info_log('getpresent ************ start ************** ', "getpresent");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        if (!$this->_validate($params['validate'],array('1'))) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->getPresentInfo();
        }
        info_log('getpresent ************ end ************** ', "getpresent");
        //return $result;
        echo $result;
        exit;
    }
    
    public function updatenoticeAction()
    {
        info_log('updatenotice ************ start ************** ', "updatenotice");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        if (!$this->_validate($params['validate'],$params['info'])) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->updateAdvertisement($params['info']);
        }
        info_log('updatenotice ************ end ************** ', "updatenotice");
        //return $result;
        echo $result;
        exit;
    }
    
    public function getnoticeAction()
    {
        info_log('getnotice ************ start ************** ', "getnotice");
        $result = '';
        $params = $this->_request->getParam('params');
        $params = Zend_Json::decode($params);
        if (!$this->_validate($params['validate'],array('1'))) {
           echo '0';
           exit(0);
        }
        if(!empty($params)){
            //bll upateaccount
            $bllOperations = new Bll_Island_Operations();
            $result = $bllOperations->getNotice();
        }
        info_log('getnotice ************ end ************** ', "getnotice");
        //return $result;
        echo $result;
        exit;
    }
    
    /**
     * validate params validation
     *
     * @param string $validString
     * @param array $aryParam
     * @return boolean
     */
    private function _validate($validString, $aryParam)
    {
        $str = '';
        ksort($aryParam);
        foreach ($aryParam as $k => $v) {
            $str .= "$k=$v";
        }
        return $validString == md5($str . $this->_secretKey['validationKey']);
    }

}