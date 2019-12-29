<?php

namespace App\Renderer;

use Symfony\Component\Console\Output\OutputInterface;
use App\Action\Response\Address;
use App\Action\Response\AddressInfo;
use App\Action\Response\TransactionInfo;
use App\Action\Response\Transfer;
use App\Action\Response\WalletCreated;
use App\RendererInterface;

class Console implements RendererInterface
{
	/** @var OutputInterface */
	protected $output;

	/**
	 * @param OutputInterface $output
	 * @return static
	 */
	public function setup(OutputInterface $output): Console
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * @param Address $addressResponse
	 */
	public function renderAddress(Address $addressResponse)
	{
		$this->output->writeln("Address: {$addressResponse->address}");
	}

	/**
	 * @param AddressInfo $addressInfo
	 */
	public function renderAddressInfo(AddressInfo $addressInfo)
    {
		$this->renderAddress($addressInfo);

		$this->output->writeln("Balance = {$addressInfo->balance} BTC");

		if (empty($addressInfo->transactionList)) {
			$this->output->writeln('Transactions: none yet');
			return;
		}

		$trIndex = 0;
		$this->output->writeln('Transactions:');
		foreach ($addressInfo->transactionList as $transaction) {
			if ($trIndex++) { $this->output->write(PHP_EOL); }

			$this->renderTransactionInfo($transaction, "\t");
		}
    }

	/**
	 * @param TransactionInfo $transactionInfo
	 * @param string $prefix
	 */
	public function renderTransactionInfo(TransactionInfo $transactionInfo, string $prefix = '')
	{
		$this->output->writeln($prefix . $transactionInfo->time->format('Y-m-d H:i:s') . "\thash = {$transactionInfo->hash}" .
			($transactionInfo->confirmed ? '' : ', not confirmed'));

		$this->output->writeln($prefix . "Inputs:");
		foreach ($transactionInfo->inputList as $txInput) {
			$this->output->writeln($prefix . "\t#{$txInput->n}: {$txInput->value} BTC from address {$txInput->address}");
		}

		$this->output->writeln($prefix . "Outputs:");
		foreach ($transactionInfo->outputList as $txOutput) {
			$this->output->writeln($prefix . "\t#{$txOutput->n}: {$txOutput->value} BTC to address {$txOutput->address}" .
				($txOutput->spent ? ', spent' : ''));
		}
	}

	/**
	 * @param WalletCreated $walletCreatedResponse
	 */
	public function renderWalletCreated(WalletCreated $walletCreatedResponse)
	{
		$this->renderAddress($walletCreatedResponse);

		$this->output->writeln("Wallet has been saved to {$walletCreatedResponse->walletPath}");
	}

	/**
	 * @param Transfer $transferResponse
	 */
	public function renderTransfer(Transfer $transferResponse)
	{
		$this->output->writeln("Address in the wallet to transfer from: {$transferResponse->address}");
		$this->output->writeln("Address to transfer to: {$transferResponse->addressTo}");
		$this->output->writeln("Success: transaction hash = {$transferResponse->hash} created, take a time");
	}
}
