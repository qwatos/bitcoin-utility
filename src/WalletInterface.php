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
	public function getPrivateKey();

	/**
	 * @return string
	 */
	public function getPublicKey();
}
