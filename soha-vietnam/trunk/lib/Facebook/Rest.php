<?

require_once 'Facebook/Rest/Exception.php';

class Facebook_Rest
{
    public $api_key;
    public $secret;
    public $session_key;
    public $v;
    public $server_addr;
    public $method;

    const CONNECT_TIMEOUT = 4;
    const TIMEOUT = 4;
    const DNS_CACHE_TIMEOUT = 600;
    const RETRIES = 3;

    public function __construct($api_key, $secret, $session_key = null)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->v = '1.0';
        $this->last_call_id = 0;
        $this->session_key = $session_key;

        $this->server_addr = 'http://api.facebook.com/restserver.php';
    }

    /**
     * Returns the requested info fields for the requested set of users.
     *
     * @param array $uids    An array of user ids
     * @param array $fields  An array of info field names desired
     *
     * @return array  An array of user objects
     */
    public function users_getInfo($uids, $fields = null)
    {
        $params = array('uids' => $uids);
        if (!$fields) {
            $fields = array('uid', 'name', 'sex');
        }
        $params['fields'] = $fields;
        $params['locale'] = 'en_US';

        return $this->call_method('facebook.users.getInfo', $params);
    }

    /**
     * Returns whether or not the user corresponding to the current
     * session object has the give the app basic authorization.
     *
     * @return boolean  true if the user has authorized the app
     */
    public function users_isAppUser($uid = null)
    {
        $params = array();
        if ($uid) {
            $params['uid'] = $uid;
        }

        return $this->call_method('facebook.users.isAppUser', $params);
    }

    /**
     * Returns the friends id of the session user, who are also users
     * of the calling application.
     *
     * @return array  An array of friends id also using the app
     */
    public function friends_getAppUsers()
    {
        return $this->call_method('facebook.friends.getAppUsers', array());
    }

    public function fql_query($query)
    {
        $params = array();
        $params['query'] = $query;

        return $this->call_method('facebook.fql.query', $params);
    }

    public function pages_isFan($uid, $page_id)
    {
        $params = array();
        $params['uid'] = $uid;
        $params['page_id'] = $page_id;

        return $this->call_method('facebook.pages.isFan', $params);
    }

    //===========================================================================================================

    public function call_method($method, $params)
    {
        $data = $this->post_request($method, $params);
        $result = $this->convert_result($data, $method, $params);

        if (is_array($result) && isset($result['error_code'])) {
            if (isset($result['error_msg'])) {
                $error_msg = $result['error_msg'];
            } else {
                $error_msg = '';
            }
            throw new Facebook_Rest_Exception($error_msg, $result['error_code']);
        }

        return $result;
    }

    protected function convert_result($data, $method, $params)
    {
        $is_xml = (empty($params['format']) || strtolower($params['format']) != 'json');
        return ($is_xml) ? $this->convert_xml_to_result($data, $method, $params) : json_decode($data, true);
    }

    protected function convert_xml_to_result($xml, $method, $params)
    {
        $sxml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return self::convert_simplexml_to_array($sxml);
    }

    public static function convert_simplexml_to_array($sxml)
    {
        $arr = array();
        if ($sxml) {
            $is_list = false;
            foreach ($sxml as $k => $v) {
                if ($sxml['list']) {
                    $arr[] = self::convert_simplexml_to_array($v);
                } else {
                    if (isset($arr[$k])) {
                        $is_list = true;
                        break;
                    }
                    $arr[$k] = self::convert_simplexml_to_array($v);
                }
            }

            if ($is_list) {
                $arr = array();
                foreach ($sxml as $k => $v) {
                    $arr[] = self::convert_simplexml_to_array($v);
                }
            }
        }
        if (sizeof($arr) > 0) {
            return $arr;
        } else {
            return (string)$sxml;
        }
    }

    private function xml_to_array($xml)
    {
        $array = (array)(simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA));
        foreach ($array as $key => $item){
            $array[$key]  = $this->struct_to_array((array)$item);
        }

        return $array;
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

    public function post_request($method, $params)
    {
        $this->finalize_params($method, $params);
        $post_string = $this->create_post_string($method, $params);
        //echo $post_string.'<br /><br />';
        //echo $this->server_addr . '?' . $post_string;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->server_addr);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //max connect time
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        //max curl execute time
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        //cache dns 1 hour
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, self::DNS_CACHE_TIMEOUT);
        //renren can get and send data encoding by gzip
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        $ua = 'PHP-cURL/HapyFish-FBRest/1.0';
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
            throw new Facebook_Rest_Exception("HTTP Error: " . $error, $errno);
        }

        //echo $result;
        //debug_log($method . ': ' . $result);
        return $result;
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

    private function create_post_string($method, $params)
    {
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key.'='.urlencode($val);
        }
        return implode('&', $post_params);
    }

}