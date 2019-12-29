<?php

namespace App\Action\Response;

use DateTime;

class TransactionInfo
{
	/** @var DateTime */
	public $time;

	/** @var string */
	public $hash;

	/** @var bool */
	public $confirmed;

	/** @var TransactionInputInfo[] */
	public $inputList;

	/** @var TransactionOutputInfo[] */
	public $outputList;

	/**
	 * @param DateTime $time
	 * @param string $hash
	 * @param bool $confirmed
	 */
	public function __construct(DateTime $time, string $hash, bool $confirmed)
	{
		$this->time = $time;
		$this->hash = $hash;
		$this->confirmed = $confirmed;
	}

	/**
	 * @param TransactionInputInfo $input
	 * @return static
	 */
	public function addInput(TransactionInputInfo $input): TransactionInfo
	{
		$this->inputList[] = $input;

		return $this;
	}

	/**
	 * @param TransactionOutputInfo $output
	 * @return static
	 */
	public function addOutput(TransactionOutputInfo $output): TransactionInfo
	{
		$this->outputList[] = $output;

		return $this;
	}
}
