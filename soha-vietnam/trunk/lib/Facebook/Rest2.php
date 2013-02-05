<?
require_once 'Facebook/Rest/Exception.php';

class Facebook_Rest2
{
    protected $api_key;
    protected $secret;
    protected $access_token;
    protected $v;
    protected $server_addr;
    protected $method;

    const CONNECT_TIMEOUT = 4;
    const TIMEOUT = 4;
    const DNS_CACHE_TIMEOUT = 600;
    const RETRIES = 3;

    public function __construct($api_key, $secret, $access_token = null)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->v = '1.0';
        $this->access_token = $access_token;
        $this->server_addr = 'https://graph.facebook.com/';
    }

    /**
     * Returns the requested info fields for the requested set of users.
     *
     * @param array $uids    An array of user ids
     * @param array $fields  An array of info field names desired
     *
     * @return array  An array of user objects
     */
    public function user_getInfo($uid = '', $params = null)
    {
    	$url = $this->server_addr . 'me';
        if ($uid) {
			$url = $this->server_addr . $uid;
        }
        return $this->call_method($url, array('access_token'=>$this->access_token, 'fields'=>'id,name,gender,email'));
    }

    /**
     * Returns the friends id of the session user, who are also users
     * of the calling application.
     *
     * @return array  An array of friends id also using the app
     */
    public function friends_get($uid = '', $params = null)
    {
    	$url = $this->server_addr . 'me/friends';
        if ($uid) {
			$url = $this->server_addr . $uid . '/friends';
        }
        return $this->call_method($url, array('access_token'=>$this->access_token));
    }

    public function get_isfan($uid='', $token=null)
    {
    	$url = $this->server_addr . 'me/likes';
    	if( $uid ) {
    		$url = $this->server_addr . $uid . '/likes';
    	}
    	return $this->call_method($url, array('access_token'=>$this->access_token));
    }

    public function get_permissions($uid='', $token=null)
    {
    	$url = $this->server_addr . 'me/permissions';
    	if( $uid ) {
    		$url = $this->server_addr . $uid . '/permissions';
    	}
    	return $this->call_method($url, array('access_token'=>$this->access_token));
    }

    //===========================================================================================================

    public function call_method($url, $params)
    {
        $data = $this->post_request($url, $params);
        $result = $this->convert_result($data, $params);
        if (is_array($result) && isset($result['error'])) {
            if (isset($result['error']['message'])) {
                $error_msg = $result['error']['message'];
            } else {
                $error_msg = '';
            }
			//info_log($result['error']['type'].$error_msg ,'apiCallerr');
            throw new Facebook_Rest_Exception($error_msg . $result['error']['type']);
        }

        return $result;
    }

	public function post_request($url, $params=null)
    {

        $post_string = $this->create_post_string($params);
        $reqUrl = $url . '?' . $post_string;
        //echo $reqUrl.'<br /><br />';
        //echo $url . '?' . $post_string;
        //exit;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $reqUrl);
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //max connect time
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        //max curl execute time
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        //cache dns 1 hour
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, self::DNS_CACHE_TIMEOUT);
        //renren can get and send data encoding by gzip
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        $ua = 'PHP-cURL/HapyFish-FBRest/2.0';
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $retries = self::RETRIES;
        $result = false;
		while (($result === false) && (--$retries > 0)) {
			$result = @curl_exec($ch);
		}

        $errno = @curl_errno($ch);
        $error = @curl_error($ch);
        curl_close($ch);

        if ($errno != CURLE_OK) {
            info_log('curl'. $error, 'fb_api_call_failed');
            throw new Facebook_Rest_Exception("HTTP Error: " . $error, $errno);
        }

        //echo $result;
        //debug_log($method . ': ' . $result);
        return $result;
    }

    protected function convert_result($data, $params)
    {
        return json_decode($data, true);
    }

    private function struct_to_array($item)
    {
        if (!is_string($item)) {
            $item = (array)$item;
            foreach ($item as $key => $val) {
                $item[$key]  =  $this->struct_to_array($val);
            }
        }

        return $item;
    }

    public static function generate_sig($params_array, $secret)
    {
        $str = '';
        ksort($params_array);
        foreach ($params_array as $k => $v) {
            $str .= "$k=$v";
        }
        $str .= $secret;

        return md5($str);
    }

    private function convert_array_values_to_csv(&$params)
    {
        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
        }
    }

    private function add_standard_params($method, &$params)
    {
        $params['method'] = $method;
        $params['session_key'] = $this->session_key;
        $params['api_key'] = $this->api_key;
        $params['call_id'] = microtime(true);
        if ($params['call_id'] <= $this->last_call_id) {
            $params['call_id'] = $this->last_call_id + 0.001;
        }
        $this->last_call_id = $params['call_id'];
        if (!isset($params['v'])) {
            $params['v'] = $this->v;
        }
    }

    private function finalize_params($method, &$params)
    {
        $this->add_standard_params($method, $params);
        //we need to do this before signing the params
        $this->convert_array_values_to_csv($params);
        $params['sig'] = self::generate_sig($params, $this->secret);
    }

    private function create_post_string($params)
    {
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key.'='.urlencode($val);
        }
        return implode('&', $post_params);
    }

}