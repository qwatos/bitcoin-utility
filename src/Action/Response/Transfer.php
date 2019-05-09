<?php

namespace App\Action\Response;

class Transfer extends Address
{
	/** @var string */
	public $addressTo;

	/** @var string */
	public $hash;

	/**
	 * @param string $address
	 * @param string $addressTo
	 * @param string $hash
	 */
	public function __construct($address, $addressTo, $hash)
	{
		parent::__construct($address);
		$this->addressTo = $addressTo;
		$this->hash = $hash;
	}
}
