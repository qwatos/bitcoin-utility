<?php

namespace App\Credentials;

class Password implements HavePasswordInterface
{

	/** @var string */
	private $password;

	/**
	 * @param string $password
	 */
	public function __construct(string $password)
	{
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}
}
