<?php

namespace App\Action\Response;

class TransactionOutputInfo extends TransactionInputInfo
{
	/** @var bool */
	public $spent;

	/**
	 * @param int $n
	 * @param string $value
	 * @param string $address
	 * @param bool $spent
	 */
	public function __construct(int $n, string $value, string $address, bool $spent)
	{
		parent::__construct($n, $value, $address);
		$this->spent = $spent;
	}
}
