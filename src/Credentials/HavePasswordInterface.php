<?php

namespace App\Credentials;

use App\CredentialsInterface;

interface HavePasswordInterface extends CredentialsInterface
{
	/**
	 * @return string
	 */
	public function getPassword(): string;
}
