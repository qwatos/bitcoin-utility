<?php

namespace App\Action\Response;

class AddressInfo extends Address
{
	/** @var string */
	public $balance;

	/** @var TransactionInfo[] */
	public $transactionList;

	/**
	 * @param string $address
	 * @param string $balance
	 */
	public function __construct($address, $balance)
	{
		parent::__construct($address);
		$this->balance = $balance;
	}

	/**
	 * @param TransactionInfo $transaction
	 * @return static
	 */
	public function addTransaction(TransactionInfo $transaction)
	{
		$this->transactionList[] = $transaction;

		return $this;
	}
}
