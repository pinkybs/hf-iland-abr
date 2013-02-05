<?php

require_once 'Ming/OAuth/Mingoauth.php';

class Ming_Rest
{
    public $key;
    public $secret;
    public $app_id;
    public $app_name;
    public $user_id;

    public $ming;

    protected static $_instance;

    public function __construct($key, $secret, $session_key)
    {
        $this->key = $key;
        $this->secret = $secret;
        if ($session_key) {
            $aryOauthKey = explode('_', $session_key);
            $otoken = $aryOauthKey[0];
            $osecret = $aryOauthKey[1];
            $this->ming = new MingOAuth($key, $secret, $otoken, $osecret);
        }
        else {
            throw new Exception('construct:session_key can not empty');
        }
    }

    public function setUser($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * single instance of Ming_Rest
     *
     * @return Ming_Rest
     */
    public static function getInstance($session_key)
    {
        try {
            if (self::$_instance == null) {
                self::$_instance = new self(APP_KEY, APP_SECRET, $session_key);
            }
        }
        catch (Exception $e) {
            info_log('getInstance:'.$e->getMessage(), 'err_ming_Rest'.date('Ymd'));
        }
        return self::$_instance;
    }

    public function ming_getUser()
    {
        try {
            $data = $this->ming->get('GET/user/showinfo');
            if(isset($data['user'])) {
                $user = array();
                $t = $data['user'];
                $user['uid'] = $t['id'];
                $user['name'] = empty($t['username']) ? $t['full_name'] : $t['username'];
                $user['email'] = $t['email'];
                $user['sex'] = $t['gender'] == 1 ? 1 : 0;
                $user['tinyurl'] = $t['avatar'];
                $user['headurl'] = $t['avatar'];

                //debug_log(json_encode($data['user']));
                return $user;
            }
        }
        catch (Exception $e) {
            info_log('ming_getUser:'.$e->getMessage(), 'err_ming_Rest'.date('Ymd'));
        }

        return null;
    }

    public function ming_getAppFriendIds()
    {
        try {
            $data = $this->ming->get('GET/user/appfriend');
            if($data && is_array($data)) {
                $fids = array();
                foreach ($data as $v) {
                   $fids[] = $v['id'];
                }
                return $fids;
            }
        }
        catch (Exception $e) {
            info_log('ming_getFriendIds:'.$e->getMessage(), 'err_ming_Rest'.date('Ymd'));
        }
        return null;
    }


    public function ming_postFeed($feed)
    {
        try {
            /*$params['message'] = $feed['message'];//must have
            $params['link'] = $feed['link'];//must have
            $params['picture'] = $feed['picture'];
            $params['name'] = $feed['name'];
            $params['caption'] = $feed['caption'];
            $params['description'] = $feed['description'];
            $params['actions'] = $feed['actions'];
            $params['channel'] = $feed['channel'];*/
            $params = array();
            foreach ($feed as $key=>$val) {
                $params[$key] = $val;
            }
            $data = $this->ming->post('POST/user/feed', $feed);
            return $data;
        }
        catch (Exception $e) {
            info_log('ming_postFeed:'.$e->getMessage(), 'err_ming_Rest'.date('Ymd'));
        }
        return null;
    }

    public function ming_postRequest($message, $link)
    {
        try {

            $params = array();
            $params['message'] = $message;//must have
            $params['link'] = $link;//must have
            $data = $this->ming->post('POST/user/request', $params);
            return $data;
        }
        catch (Exception $e) {
            info_log('ming_postRequest:'.$e->getMessage(), 'err_ming_Rest'.date('Ymd'));
        }
        return null;
    }

}