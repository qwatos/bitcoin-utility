<?php

namespace App\Credentials;

class Password implements HavePasswordInterface
{

	/** @var string */
	private $password;

	/**
	 * @param string $password
	 */
	public function __construct($password)
	{
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}
}
