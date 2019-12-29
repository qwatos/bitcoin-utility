<?php

namespace App\Action;

use App\BitcoinClientInterface;

abstract class BaseAction
{
	/** @var BitcoinClientInterface */
	private $bitcoinClient;

	/**
	 * @param BitcoinClientInterface $bitcoinClient
	 */
	public function __construct(BitcoinClientInterface $bitcoinClient)
	{
		$this->bitcoinClient = $bitcoinClient;
	}

	/**
	 * @return BitcoinClientInterface
	 */
	protected function getBitcoinClient(): BitcoinClientInterface
	{
		return $this->bitcoinClient;
	}
}
