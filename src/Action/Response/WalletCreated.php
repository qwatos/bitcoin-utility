<?php

namespace App\Action\Response;

class WalletCreated extends Address
{
	/** @var string */
	public $walletPath;

	/**
	 * @param string $address
	 * @param string | null $walletPath
	 */
	public function __construct($address, $walletPath = null)
	{
		parent::__construct($address);
		$this->walletPath = $walletPath;
	}
}
