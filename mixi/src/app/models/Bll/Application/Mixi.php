<?php

require_once 'Bll/Application/Abstract.php';


class Bll_Application_Mixi extends Bll_Application_Abstract
{
    /**
     * application owner id
     * @var string
     */
    protected $_ownerId;
    
    /**
     * application viewer id
     * @var string
     */
    protected $_viewerId;

	/**
     * mixi top url
     * @var string
     */
    protected $_topUrl;
    
    /**
     * mixi top url params
     * @var array
     */
    protected $_topParams;
    
    /**
     * Instance of Bll_Application_Plugin_Broker
     * @var Bll_Application_Plugin_Broker
     */
    protected $_plugins = null;
    
    const OWNER  = 'OWNER';
    const VIEWER = 'VIEWER';
    
    /**
     * Singleton instance, if null create an new one instance.
     *
     * @param Zend_Controller_Action $actionController
     * @return Bll_Application
     */
    public static function newInstance(Zend_Controller_Action $actionController)
    {
        if (null === self::$_instance) {
            self::$_instance = new Bll_Application_Mixi($actionController);
        }

        return self::$_instance;
    }
    
    /**
     * get owner id
     *
     * @return string
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }
    
    /**
     * get viewer id
     *
     * @return string
     */
    public function getViewerId()
    {
        return $this->_viewerId;
    }
    
    /**
     * check is owner
     * 
     * @param string $uid
     * @return bool
     */
    public function isOwner($uid)
    {
        return $uid == $this->_ownerId;
    }
    
    /**
     * check is viewer
     * 
     * @param string $uid
     * @return bool
     */
    public function isViewer($uid)
    {
        return $uid == $this->_viewerId;
    }
    
    /**
     * check viewer and owner is same person
     * 
     * @return bool
     */
    public function isSamePerson()
    {
        return $this->_ownerId == $this->_viewerId;
    }
    
    private function _parseTopParams()
    {
        if (!empty($this->_topUrl)) {
            $info = parse_url($this->_topUrl);
            if ($info['query']) {
                parse_str($info['query'], $this->_topParams);
            }
        }
    }
    
    public function getTopParam($name)
    {
        if(isset($this->_topParams[$name])) {
            return $this->_topParams[$name];
        }
        
        return '';
    }
    
    public function getInvite()
    {
        return $this->getTopParam('invite');
    }
    
    /**
     * Register a plugin.
     *
     * @param  Bll_Application_Plugin_Interface $plugin
     * @param  int $stackIndex Optional; stack index for plugin
     * @return Bll_Application
     */
    public function registerPlugin(Bll_Application_Plugin_Interface $plugin, $stackIndex = null)
    {
        $this->_plugins->registerPlugin($plugin, $stackIndex);
        return $this;
    }
    
    public function autoRegisterPlugin()
    {
        return $this->registerPluginByName($this->_appName);
    }
    
    public function registerPluginByName($appName)
    {
        if (!empty($appName)) {
            $name = ucfirst($appName);
            $pluginFile = 'Bll/Application/Plugin/' . $name . '.php';            
            if (is_file(MODELS_DIR . '/' . $pluginFile)) {
                require_once $pluginFile;
                $pluginClassName = 'Bll_Application_Plugin_' . $name;
                $plugin = new $pluginClassName();                
                $this->_plugins->registerPlugin($plugin, null);
                return true;
            }
        }
        return false;
    }

    /**
     * Unregister a plugin.
     *
     * @param  string|Bll_Application_Plugin_Interface $plugin Plugin class or object to unregister
     * @return Bll_Application
     */
    public function unregisterPlugin($plugin)
    {
        $this->_plugins->unregisterPlugin($plugin);
        return $this;
    }
    
    /**
     * _init()
     *
     * @return void
     */
    protected function _init()
    {
        $this->_plugins = new Bll_Application_Plugin_Broker();
        $this->_topParams = array();
        
        $request = $this->getRequest();
        if (! $request->isPost()) {
            debug_log(__LINE__ . 'is not post');
            echo 'Please use post method。';
            exit;
        }
        
        $nonce = $request->getPost('nonce');
        if (!$nonce) {
            debug_log(__LINE__ . 'has not nonce');
            echo "There has not para nonce。";
            exit;
        }
        
        require_once 'Bll/Nonce.php';
        $valid = Bll_Nonce::isValid($nonce, $data);
        if (!$valid) {
            debug_log(__LINE__ . 'para nonce check failure');
            echo "para nonce check failure。";
            exit;
        }
        
        $uid = $data['owner_id'];
        $f = Bll_User::isFibbden($uid);
        if ($f) {
            echo 'あなたは（ID：' . $uid . '）規則違反操作を行ったため、アプリのご使用をとめさせていただくます。';
            exit;
        }
        
        $this->_appId = $data['app_id'];
        $this->_ownerId = $data['owner_id'];
        $this->_viewerId = $data['viewer_id'];
        $this->_appName = $data['app_name'];

        $this->_topUrl = $request->getPost('top_url');
        $this->_parseTopParams();
        
        $request->setParam('uid', $this->_ownerId);
    }
    
    protected function _getUser($data)
    {
        $user = array();
        $user['uid'] = $data['id'];
        $user['name'] = $data['displayName'];
        $thumbnailUrl = $data['thumbnailUrl'];
        if ($thumbnailUrl == 'http://img.mixi.jp/img/basic/common/noimage_member76.gif') {
            $tinyurl = 'http://img.mixi.jp/img/basic/common/noimage_member40.gif';
        } else {
            $tinyurl = str_replace('s.jpg', 'm.jpg', $thumbnailUrl);
        }
        
        $user['headurl'] = $thumbnailUrl;
        $user['tinyurl'] = $tinyurl;
        
        $gender = isset($data['gender']) ? $data['gender'] : '';
        if ($gender == 'MALE') {
            $sex = '1';
        } else if ($gender == 'FEMALE') {
            $sex = '0';
        } else {
            //not set or public
            $sex = '';
        }
        
        $user['birth'] = isset($data['dateOfBirth']) ? $data['dateOfBirth'] : '';
        
        return $user;
    }

    protected function _updateInfo($data)
    {
        $user = $this->_getUser($data['user']);
        if (!$user) {
            debug_log(__LINE__ . ': redirect404()');
            $this->redirect404();
        }

        $uid = $user['uid'];
        
        if (Bll_Cache_User::isUpdated($uid)) {
            //return;
        } 
        
        Bll_User::updatePerson($user);
        
        $this->_plugins->postUpdatePerson($uid);
                
        $fids = $data['friends'];
        if (!empty($fids)) {            
            Bll_Friend::updateFriends($uid, $fids);
            $this->_plugins->updateAppFriendship($uid, $fids);
            
            //update user acheviment friends numner count
            $bllDock = new Bll_Island_Dock();
            $bllDock->getPower($uid);
        }
        
        Bll_Cache_User::setUpdated($uid);
    }

    /**
     * run() - main mothed
     *
     * @return void
     */
    public function run()
    {
        $request = $this->getRequest();
        
        $viewerInfo = Zend_Json::decode($request->getPost('viewer_info'));
                
        if ($viewerInfo) {
            $this->_updateInfo($viewerInfo);
        }
        
        // set cookie
        $expries = time() + 3*24*60*60;
        $path = '/';
        $params = session_get_cookie_params();

        //P3P privacy policy to use for the iframe document
        //for IE
        header('P3P: CP=CAO PSA OUR');
        
         // start session
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($this->_viewerId); 
        
        setcookie('app_mixi_uid', $this->_viewerId, $expries, $path, $params['domain']);
        require_once 'Bll/Secret.php';
        $sig = Bll_Secret::getSecretResult($this->_viewerId);
        setcookie('app_mixi_sig', $sig, $expries, $path, $params['domain']);

        setcookie('app_top_url_' . $this->_appName, $this->_topUrl, $expries, $path, $params['domain']);
        setcookie('app_top_url', $this->_topUrl, $expries, $path, $params['domain']);
        
        //$mixi_platform_api_url = $request->getParam('mixi_platform_api_url') . '&rpc_mode=1';
        //we found on rpc_replay.html on mixi api platform
        //url like: http://5b405adcd06c95e57e81f1bd7758e9826d123267.app0.mixi-platform.com/gadgets/files/container/rpc_relay.html
        
        $mixi_platform_api_url = $request->getParam('mixi_platform_api_url', '');
        if ($mixi_platform_api_url) {
            $url_ifo = parse_url($mixi_platform_api_url);
            $mixi_platform_api_url = $url_ifo['scheme'] . '://' . $url_ifo['host'] . '/gadgets/files/container/rpc_relay.html';
            setcookie('mixi_platform_api_url', $mixi_platform_api_url, $expries, $path, $params['domain']);
        }        

        $this->_plugins->postRun($this);
    }
    
    /**
     * check signature is valid
     * 
     * @param output array $parameters
     * @return bool
     * 
     *  opensocial_owner_id         eg: 13915816
     *  opensocial_viewer_id        eg: 13915816
     *  opensocial_app_id           eg: 1325
     *  opensocial_app_url          eg: http://mixitest.linno.jp/static/apps/parking/mixitest.xml
     *
     *  oauth_token                 ''[empty]
     *  oauth_consumer_key          mixi.jp
     *  xoauth_signature_publickey  mixi.jp
     *  oauth_signature_method      RSA-SHA1
     *  oauth_nonce                 eg: e1b0d08891eb95c0
     *  oauth_timestamp             eg: 1245164539
     *  oauth_signature             eg: rr40jvcsPjJ+bI0cFh+eKlVgEj+iXCMihUBTPEPcDoO+IkUwA4YSjFHlpWRloYHMigc1prH1YHFDm3TmeTXojQtjZi+P6PEbyzronrocPxrEb2S6Hsmb+g262c1EjhMyEzcRZAXscKuFkIUsuOVI/fzMRPM1HBQ7arBW8jGv8rg=
     *
     */
    public static function isValidSignature(&$parameters)
    {
        require_once 'osapi/external/OAuth.php';
        //Build a request object from the current request
        $request = OAuthRequest::from_request(null, null, array_merge($_GET, $_POST));
        $xoauth_signature_publickey = $request->get_parameter('xoauth_signature_publickey');
        //Initialize the new signature method
        if ($xoauth_signature_publickey == 'mixi.jp') {
            require_once 'osapi/external/MixiSignatureMethodOld.php';
            $signature_method = new MixiSignatureMethodOld();
        } else {
            require_once 'osapi/external/MixiSignatureMethod.php';
            $signature_method = new MixiSignatureMethod();
        }
        
        //Check the request signature
        $signature = rawurldecode($request->get_parameter('oauth_signature'));
        @$signature_valid = $signature_method->check_signature($request, null, null, $signature);
        //$signature_valid = true;
        
        if ($signature_valid) {
            $parameters = array(
                'app_id'    => $request->get_parameter('opensocial_app_id'),
                'owner_id'  => $request->get_parameter('opensocial_owner_id'),
                'viewer_id' => $request->get_parameter('opensocial_viewer_id')
            );
        }
        else {
            $parameters = array();
        }
        
        return $signature_valid;
    }
}