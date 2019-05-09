<?php

namespace App\Action\Response;

class TransactionInputInfo
{
	/** @var int */
	public $n;

	/** @var string */
	public $value;

	/** @var string */
	public $address;

	/**
	 * @param int $n
	 * @param string $value
	 * @param string $address
	 */
	public function __construct($n, $value, $address)
	{
		$this->n = $n;
		$this->value = $value;
		$this->address = $address;
	}
}
