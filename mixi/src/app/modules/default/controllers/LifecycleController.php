<?php

/**
 * application lifecycle controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/05/11    Huch
 */
class LifecycleController extends Zend_Controller_Action
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	echo 'lifecycle';
    	exit;
    }
    
    //http://developer.mixi.co.jp/appli/pc/lets_enjoy_making_mixiapp/lifecycle_event
    private function checkSignature()
    {
        require_once 'osapi/external/MixiSignatureMethod.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request(null, null, null, true);
                
        //Initialize the new signature method
        $signature_method = new MixiSignatureMethod();
        //Check the request signature
        $signature = rawurldecode($request->get_parameter('oauth_signature'));
                
        @$signature_valid = $signature_method->check_signature($request, null, null, $signature);
        
        return $signature_valid;
    }    
    
    private function isMultipleIds()
    {
        $query = $_SERVER['QUERY_STRING'];
        if (!empty($query)) {
            $a = explode('&', $query);
            $id = array();
            for($i = 0, $n = count($a); $i < $n; $i++) {
                $b = explode('=', $a[$i]);
                if (isset($b[0]) && $b[0] == 'id') {
                    if (!Bll_User::isAppUser($b[1])) {
                        $id[] = $b[1];
                    }
                }
            }
            
            //set id to array
            $_GET['id'] = $id;
        }
    }
    
    /**
     * add app action
     *
     */
    public function addappAction()
    {
        $result = false;        
        
        //$signature_valid = $this->checkSignature();  
        $signature_valid = true;              
        //info_log('lifecircle $signature_valid:'.$signature_valid, 'lifecircle');
        
        if ($signature_valid == true) {
            $this->isMultipleIds();
            //$parameters = $request->get_parameters();
            $parameters = $this->_request->getParams();
            
            $eventtype = $parameters['eventtype'];
            $opensocial_app_id = $parameters['opensocial_app_id'];
            $id = $parameters['id'];
            $mixi_invite_from = $parameters['mixi_invite_from'];
            if ($eventtype == 'event.addapp' && !empty($id) && !isset($parameters['opensocial_owner_id'])) {
                //no user invite
                if (empty($mixi_invite_from)) {
                    $result = true;
                }
                else {
                    if (!empty($id)) {
                        //invite success
                        $bllIslandUser = new Bll_Island_User();
                        $result = $bllIslandUser->inviteUidMixi($mixi_invite_from, $id);
                    }
                }
            }
        }
        
        if (!$result) {
            header("HTTP/1.1 500 Internal Server Error");
        }
        else {
            ini_set('default_charset', null);
            header('HTTP/1.1 200 OK');
        }
        
        exit;
    }
    
    /**
     * magic function
     *   if call the function is undefined,then echo undefined
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        echo 'undefined method name: ' . $methodName;
        exit;
    }

 }
