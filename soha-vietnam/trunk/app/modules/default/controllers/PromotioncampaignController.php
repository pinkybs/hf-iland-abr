<?php

class PromotioncampaignController extends Zend_Controller_Action
{

    public function init()
    {
    }

	protected function vaild()
	{
	}

    protected function echoResult($data)
    {
    	$data['errno'] = 0;
    	echo json_encode($data);
    	exit;
    }

    protected function echoError($errno, $errmsg)
    {
    	$result = array('errno' => $errno, 'errmsg' => $errmsg);
    	echo json_encode($result);
    	exit;
    }

    public function noopAction()
    {
    	$data = array('id' => SERVER_ID, 'time' => time());
    	$this->echoResult($data);
    }

    public function joinAction()
    {
        $campaignId = (int)$this->_request->getParam('hf_fromcamp');
        if (empty($campaignId)) {
            echo 'Thanks.';
            exit;
        }

        /*if (!array_key_exists($campaignId, Hapyfish2_Island_Stat_Bll_Campaign::$aryCampaignInfo)) {
            echo 'Thank U.';
            exit;
        }*/
        if ($campaignId>500 || $campaignId<1) {
            echo 'Thank U.';
            exit;
        }

        $gameUrl = 'http://i.taobao.com/apps/show.htm?appkey=' . APP_KEY . '&hf_fromcamp='.base64_encode($campaignId);
        $clientIp = $this->getClientIP();
        Hapyfish2_Island_Stat_Bll_Campaign::fromCampaignPv($campaignId, $clientIp);

        $this->_redirect($gameUrl);
        exit();
    }

    protected function getClientIP()
    {
    	$ip = false;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) {
				array_unshift($ips, $ip);
				$ip = false;
			}
			for ($i = 0, $n = count($ips); $i < $n; $i++) {
				if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
    }
}