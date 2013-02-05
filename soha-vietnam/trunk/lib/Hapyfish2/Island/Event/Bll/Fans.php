<?php

class Hapyfish2_Island_Event_Bll_Fans
{
	public static function getPage($i = 1)
	{
		$data = array();
		$url = 'http://jianghu.taobao.com/u/NDAxMTQ5NDA0/user/front_my_fans_list.htm?page=' . $i;

		//创建一个新的cURL资源
		$ch = curl_init ();

		//设置URL和相应选项
		curl_setopt ( $ch, CURLOPT_URL, $url );
		//获取数据返回
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		// 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, true );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		//是否输出header(0输出)
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		$ua = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);

		//是否输出body(0输出)
		curl_setopt ( $ch, CURLOPT_NOBODY, 0 );

		//运行cUrl
		$content = curl_exec ( $ch );

		//关闭cURL资源，并且释放系统资源
		curl_close ( $ch );

		if ($content === false) {
			return $data;
		}

		preg_match_all ( '|<li data-uid="(.*)"|U', $content, $datas);

		if(!empty($datas[1])) {
			foreach ($datas[1] as $v) {
				$data[] = $v;
			}
		}

		return $data;
	}

	public static function saveFile($data, $file = null)
	{
		if (empty($data)) {
			return;
		}

		if (!$file) {
			$file = LOG_DIR . '/fans-' . date('Ymd', time()) . '.log';
		}

		$content = join(",", $data);
		$content .= "\n";
		file_put_contents($file, $content, FILE_APPEND);
	}

	public static function getAll($max = 100)
	{
		$data = self::getPage(1);
		for($i = 1; $i <= $max; $i++) {
			$data = self::getPage($i);
			self::saveFile($data);
			//self::syncRemoteDb($data);
			self::savelocaldb($data);

			sleep(5);
		}
	}

	public static function create_post_string($params)
    {
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key . '=' . urlencode($val);
        }
        return implode('&', $post_params);
    }

	public static function savelocaldb($data)
	{
		try {
			$dalFans = Hapyfish2_Island_Event_Dal_Fans::getDefaultInstance();
			$fan = array();
			foreach ($data as $uid) {
				$fan[] = '(' . $uid . ')';
			}

			$dalFans->insertFan($fan);
		}
		catch (Exception $e) {
			info_log($e->getMessage(), 'fans_ins_error');
		}
	}

    public static function syncRemoteDb($data)
	{
		try {

			$ch = curl_init();
            $url = 'http://island.hapyfish.com/testevent/syncfansdb';
            $postBody = false;
            $content = join(",", $data);
			$postParam = array('fansdata' => $content);
            $postBody = self::create_post_string($postParam);
            //$postBody = http_build_query($postParam);
            $method = 'POST';
            $request = array('url' => $url, 'method' => $method, 'body' => $postBody, 'headers' => false);
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($postBody) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
            }

            // We need to set method even when we don't have a $postBody 'DELETE'
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $cURLVersion = curl_version();
            $ua = 'PHP-cURL/' . $cURLVersion['version'] . ' ' . $ua;
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt($ch, CURLOPT_HEADER, true);
            //curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            $data = @curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = @curl_errno($ch);
            //$error = @curl_error($ch);
            @curl_close($ch);
            return $data;
		}
		catch (Exception $e) {
			info_log($e->getMessage(), 'fans_sync_error');
			info_log($e->getTraceAsString(), 'fans_sync_error');
		}
	}
}