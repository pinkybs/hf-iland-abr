<?php

require_once 'Facebook/Rest.php';

class Facebook_Client
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
     * @return Xiaonei_Renren
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
        $this->rest->session_key = $session_key;
    }

    public function verifySignature($fb_params, $expected_sig)
    {
        return Facebook_Rest::generate_sig($fb_params, $this->secret) == $expected_sig;
    }

    public function getAddUrl($next = null)
    {
        return 'http://www.facebook.com/tos.php?v=1.0&canvas&api_key=' . $this->api_key . ($next ? '&next=' . urlencode($next) : '');
    }

    public function getUser()
    {
        try {
            $data = $this->rest->users_getInfo($this->user_id);
            if(isset($data[0])) {
                $user = $data[0];
                $user['sex'] = ($user['sex'] == 'male' ? 1 : 0);
                $user['tinyurl'] = 'http://graph.facebook.com/' . $user['uid'] . '/picture';
                $user['headurl'] = 'http://graph.facebook.com/' . $user['uid'] . '/picture?type=large';
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
            $data = $this->rest->friends_getAppUsers();
            if (isset($data['uid'])) {
                return array($data['uid']);
            } else if(is_array($data)) {
                return $data;
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
            $data = $this->rest->friends_getLists();
            if ($data && is_array($data)) {
                $friends = array();
                foreach ($data as $v) {
                   $friends[$v['id']] = array('uid' => $v['flid'], 'name' => $v['name'], 'thumbnail' => 'http://graph.facebook.com/' . $v['flid'] . '/picture?type=square');
                }
                return $friends;
            }
        }
        catch (Exception $e) {
            err_log('[Facebook_Client::getFriends]: ' . $e->getMessage());
        }

        return null;
    }
    
    public function fqlQueryPermission()
    {
        $query = "SELECT email,bookmarked,publish_stream FROM permissions WHERE uid=" . $this->user_id;
        try {
            $data = $this->rest->fql_query($query);
            if ($data && is_array($data)) {
                return $data;
            }
        }
        catch (Exception $e) {
            err_log('[Facebook_Client::getFriends]: ' . $e->getMessage());
        }

        return null;
    }
    
    public function pages_isFan()
    {
        try {
            $data = $this->rest->pages_isFan($this->user_id, $this->app_id);
            return $data;
        }
        catch (Exception $e) {
            err_log('[Facebook_Client::getFriends]: ' . $e->getMessage());
        }

        return null;
    }
    
    public function __construct($api_key, $secret, $app_id, $app_name)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->app_id = $app_id;
        $this->app_name = $app_name;

        $this->rest = new Facebook_Rest($api_key, $secret);
    }

}