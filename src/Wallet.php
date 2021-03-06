<?php

namespace App;

class Wallet implements WalletInterface
{

	/** @var string */
	private $privateKey;

	/** @var string */
	private $publicKey;

	/**
	 * @param string $privateKey
	 * @param string $publicKey
	 */
	public function __construct(string $privateKey, string $publicKey)
	{
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
	}

	/**
	 * @return string
	 */
	public function getPrivateKey(): string
	{
		return $this->privateKey;
	}

	/**
	 * @return string
	 */
	public function getPublicKey(): string
	{
		return $this->publicKey;
	}
}
