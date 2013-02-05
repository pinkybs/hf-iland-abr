<?php

require_once 'Ming/OAuth/Mingoauth.php';

class Ming_OAuthRest
{
    public $key;
    public $secret;
    public $ming;

    public function __construct($oauthToken = null, $oauthSecret = null)
    {
        $this->key = APP_KEY;
        $this->secret = APP_SECRET;
        if ($oauthToken) {
            $this->ming = new MingOAuth($this->key, $this->secret, $oauthToken, $oauthSecret);
        }
        else {
            $this->ming = new MingOAuth($this->key, $this->secret);
        }
    }

    public function getAuthorizeURL($oauthCallbackUrl)
    {
        try {
            $request_token = $this->ming->getRequestToken($oauthCallbackUrl);
            if ($request_token && isset($request_token['oauth_token'])) {
                $otoken = $request_token['oauth_token'];
                $osecret = $request_token['oauth_token_secret'];
                $t = time();
                $oauthKey = $otoken.'_'.$osecret;
                $sig = md5($oauthKey . $t . APP_SECRET);
                $hfOauth = base64_encode($oauthKey) . '_' . $t . '_' . $sig;
		        //P3P privacy policy to use for the iframe document
		        //for IE
		        header('P3P: CP=CAO PSA OUR');
                setcookie('hf_oauth', $hfOauth , 0, '/', str_replace('http://', '.', HOST));

                $url = $this->ming->getAuthorizeURL($request_token);
                if (200 == $this->ming->http_code && $url) {
                    return $url;
                }
                else {
                    throw new Exception('step2geturl:'.json_encode($this->ming->http_info));
                }
            }
            else {
                throw new Exception('step1gettoken:'.json_encode($request_token));
            }
        }
        catch (Exception $e) {
            info_log('getAuthorizeURL:'.$e->getMessage(), 'err_ming_OAuthRest'.date('Ymd'));
        }

        return null;
    }

    public function getAccessToken($oauthVerifier)
    {
        try {
            $access_token = $this->ming->getAccessToken($oauthVerifier);
            if (200 == $this->ming->http_code && $access_token
                    && isset($access_token['user_id']) && isset($access_token['oauth_token']) && isset($access_token['oauth_token_secret'])) {
                return $access_token;
            }
            else {
                throw new Exception(json_encode($this->ming->http_info).'-'.json_encode($access_token));
            }
        }
        catch (Exception $e) {
            info_log('getAccessToken:'.$e->getMessage(), 'err_ming_OAuthRest'.date('Ymd'));
        }
        return null;
    }

}