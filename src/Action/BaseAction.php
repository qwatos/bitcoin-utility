<?php

namespace App\Action;

use App\BitcoinClientInterface;
use App\BitcoinClient\BlockChainInfo;

abstract class BaseAction
{
	/** @var BitcoinClientInterface */
	private $bitcoinClient;

	/**
	 * @param BlockChainInfo $bitcoinClient
	 */
	public function __construct(BlockChainInfo $bitcoinClient)
	{
		$this->bitcoinClient = $bitcoinClient;
	}

	/**
	 * @return BitcoinClientInterface
	 */
	protected function getBitcoinClient()
	{
		return $this->bitcoinClient;
	}
}
