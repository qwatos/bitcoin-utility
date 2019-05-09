<?php

namespace App\Action;

use App\Action\Response\TransactionInfo;
use App\Exception\Fatality;

class Transaction extends BaseAction
{
	/**
	 * @param string $transactionId
	 * @return TransactionInfo
	 * @throws Fatality
	 */
	public function getTransactionInfo($transactionId)
    {
		return $this->getBitcoinClient()->getTransactionInfo($transactionId);
	}
}
