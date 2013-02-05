<?php

require_once 'Facebook/Rest2.php';

class Facebook_Client2
{
    public $api_key;
    public $secret;
    public $app_id;
    public $app_name;
    public $user_id;

    public $rest;

    protected static $_instance;

    /**
     * get renren object
     *
     * @return Facebook_Client
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self(APP_KEY, APP_SECRET, APP_ID, APP_NAME);
        }

        return self::$_instance;
    }

    public function setUser($user_id, $session_key)
    {
        $this->user_id = $user_id;
        $this->rest = new Facebook_Rest2($this->api_key, $this->secret, $session_key);
    }

    public function getUser()
    {
        try {
            $data = $this->rest->user_getInfo();
            if(isset($data)) {
                $user = array();
                $user['uid'] = $data['id'];
                $user['name'] = $data['name'];
                $user['email'] = isset($data['email']) ? $data['email'] : '';
                $user['sex'] = ($data['gender'] == 'male' ? 1 : 0);
                $user['tinyurl'] = 'http://graph.facebook.com/' . $data['id'] . '/picture';
                $user['headurl'] = 'http://graph.facebook.com/' . $data['id'] . '/picture?type=large';
                return $user;
            }
        }
        catch (Exception $e) {
            err_log('[Facebook_Client::getUser]: ' . $e->getMessage());
        }

        return null;
    }

    public function getFriendIds()
    {
        try {
            $data = $this->rest->friends_get();
            if ($data && isset($data['data'])) {
            	$fids = array();
                foreach ($data['data'] as $key =>$finfo) {
					$fids[] = $finfo['id'];
                }
                return $fids;
            }
        }
        catch (Exception $e) {
            err_log('[Facebook_Client::getFriendIds]: ' . $e->getMessage());
        }

        return null;
    }

    public function getFriends()
    {
        try {
            $data = $this->rest->friends_get();
            if ($data && isset($data['data'])) {
                $friends = array();
                foreach ($data['data'] as $v) {
                   $friends[$v['id']] = array('uid' => $v['id'], 'name' => $v['name'], 'thumbnail' => 'http://graph.facebook.com/' . $v['id'] . '/picture?type=square');
                }
                return $friends;
            }
        }
        catch (Exception $e) {
            err_log('[Facebook_Client::getFriends]: ' . $e->getMessage());
        }

        return null;
    }

    public function getIsFan()
    {
    	try {
    		$data = $this->rest->get_isfan();
    		foreach( $data['data'] as $key => $val ) {
    			if( $val['id'] == APP_ID ) {
    				return true;
    			}
    		}
    		return false;
    	}
    	catch( Exception $e  ) {
    		err_log('[Facebook_Client::getIsFan]: ' . $e->getMessage());
    	}
    	return null;
    }

    public function getPermissions()
    {
    	try {
    		$data = $this->rest->get_permissions();
    		if ($data && isset($data['data'][0])) {
    		    return $data['data'][0];
    		}
    	}
    	catch( Exception $e  ) {
    		err_log('[Facebook_Client::getPermissions]: ' . $e->getMessage());
    	}
    	return null;
    }

    public function __construct($api_key, $secret, $app_id, $app_name)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->app_id = $app_id;
        $this->app_name = $app_name;
    }

}