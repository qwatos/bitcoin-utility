<?php

namespace App;

/**
 * Provides credentials to access the wallet
 */
interface CredentialsProviderInterface
{
	/**
	 * @return CredentialsInterface
	 */
	public function getCredentials(): CredentialsInterface;
}
