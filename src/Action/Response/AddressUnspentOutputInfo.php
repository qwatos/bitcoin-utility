<?php

namespace App\Action\Response;

class AddressUnspentOutputInfo
{
	/** @var int */
	public $n;

	/** @var string */
	public $value;

	/** @var string */
	public $hash;

	/**
	 * @param int $n
	 * @param string $value
	 * @param string $hash
	 */
	public function __construct($n, $value, $hash)
	{
		$this->n = $n;
		$this->value = $value;
		$this->hash = $hash;
	}
}
