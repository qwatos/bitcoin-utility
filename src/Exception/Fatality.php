<?php

namespace App\Exception;

use Exception;

class Fatality extends Exception
{
	/**
	 * @param string $message
	 * @param Exception $previous
	 */
	public function __construct($message = '', Exception $previous = null) {
		parent::__construct($message, $code = 0, $previous);
	}
}
