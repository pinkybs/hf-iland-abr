<?php

class LinktotalController extends Zend_Controller_Action
{
	function vaild()
	{
		
	}
	
	public function indexAction()
	{
		$data = $this->_request->getParams('');
		$startdays = $data['starttime'];
		$enddays = $data['endtime'];
		$linkID = $data['linkid'];

		if ((empty($startdays) && empty($enddays)) || empty($linkID)) {
			return;
		}
		
		if ($linkID < 1 || $linkID > 900) {
			return;
		}
		
		$db = Hapyfish2_Island_Stat_Dal_LinkTotal::getDefaultInstance();
		$checkData = $db->checkLinkData($startdays, $enddays);
			
		$needData = array();
		foreach ($checkData as $link) {
			$linkMsk = '';
			$linkMsk = json_decode($link);

			foreach ($linkMsk as $keyMsk => $msk) {
				if ($linkID == $keyMsk) {
					//$needData = $msk;
					$newData = explode('|', $msk);
					$uniqueCount += $newData[0];
					$allCount += $newData[1];
					break;
				}
			}
		}
		
		$this->view->starttime = $startdays;
		$this->view->endtime = $enddays;
		$this->view->linkID = $linkID;
		$this->view->uniquenum = $uniqueCount;
		$this->view->allnum = $allCount;
	}
}