<?php

namespace App;

/**
 * Contains keys for Bitcoin address
 */
interface WalletInterface
{
	/**
	 * @return string
	 */
	public function getPrivateKey(): string;

	/**
	 * @return string
	 */
	public function getPublicKey(): string;
}
