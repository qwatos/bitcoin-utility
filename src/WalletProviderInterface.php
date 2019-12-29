<?php

namespace App;

use App\Exception\Fatality;

/**
 * Provides access to wallet with keys
 */
interface WalletProviderInterface
{
	/**
	 * @param CredentialsInterface $credentials
	 * @return static
	 * @throws Fatality
	 */
	public function setCredentials(CredentialsInterface $credentials): WalletProviderInterface;

	/**
	 * @param WalletInterface $wallet
	 * @throws Fatality
	 */
	public function save(WalletInterface $wallet);

	/**
	 * @return WalletInterface
	 * @throws Fatality
	 */
	public function load(): WalletInterface;
}
