<?php

namespace App\Action\Response;

class Address
{
	/** @var string */
	public $address;

	/**
	 * @param string $address
	 */
	public function __construct(string $address)
	{
		$this->address = $address;
	}
}
