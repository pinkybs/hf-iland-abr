<?php

class Hapyfish2_Island_Stat_Bll_Payment
{
	public static function cal($day)
	{
		$begin = strtotime($day);
		$end = $begin + 86400;
		$amount = 0;
		$gold = 0;
		$count = 0;
		try {
			$dalPay = Hapyfish2_Island_Stat_Dal_PaymentLog::getDefaultInstance();
			for ($i = 0; $i < 4; $i++) {
				for ($j = 0; $j < 10; $j++) {
					$data = $dalPay->getPaymentLogData($i, $j, $begin, $end);
					if ($data) {
						foreach ($data as $row) {
							$amount += $row['amount'];
							$gold += $row['gold'];
							$count++;
						}
					}
				}
			}
			
			return array('amount' => $amount, 'gold' => $gold, 'count' => $count);
		} catch (Exception $e) {
			return array('amount' => $amount, 'gold' => $gold, 'count' => $count);
		}
	}

}