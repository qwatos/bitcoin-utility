<?php

namespace App\BitcoinClient;

use DateTime;
use DateTimeZone;
use BitWasp\Bitcoin\Bitcoin;
use Blockchain\Blockchain;
use Blockchain\Exception\Error as BlockchainError;
use Blockchain\Exception\ApiError as BlockchainApiError;
use Blockchain\Exception\HttpError as BlockchainHttpError;
use Blockchain\Explorer\Input as BlockchainTransactionInput;
use Blockchain\Explorer\Output as BlockchainTransactionOutput;
use Blockchain\Explorer\UnspentOutput as BlockchainTransactionUnspentOutput;
use Blockchain\Explorer\Transaction as BlockchainTransaction;
use App\BitcoinClientInterface;
use App\Action\Response\AddressInfo;
use App\Action\Response\TransactionInfo;
use App\Action\Response\TransactionInputInfo;
use App\Action\Response\TransactionOutputInfo;
use App\Action\Response\AddressUnspentOutputInfo;
use App\Exception\Fatality;
use App\Action\Traits\Base58AddressTrait;

class BlockChainInfo implements BitcoinClientInterface
{
	use Base58AddressTrait;

	/**
	 * @param string $address
	 * @return AddressInfo
	 * @throws Fatality
	 */
	public function getAddressInfo($address)
	{
		$network = Bitcoin::getNetwork();

		$this->getBase58AddressFromString($address, $network);

		try {
			$addressInfo = $this->getBlockChainApi()->Explorer->getAddress($address);
		} catch (BlockchainHttpError $e) {
			throw new Fatality("Blockchain HTTP error: {$e->getMessage()}", $e);
		} catch (BlockchainError $e) {
			throw new Fatality("Blockchain API error: {$e->getMessage()}", $e);
		}

		$addressResponse = new AddressInfo($address, $addressInfo->final_balance);

		if (!empty($addressInfo->transactions)) {
			/** @var BlockchainTransaction $transaction */
			foreach ($addressInfo->transactions as $transaction) {
				$addressResponse->addTransaction($this->convertBlockchainTransactionToTransactionInfo($transaction));
			}
		}

		return $addressResponse;
	}

	/**
	 * @param string $transactionId
	 * @return TransactionInfo
	 * @throws Fatality
	 */
	public function getTransactionInfo($transactionId)
	{
		try {
			$transaction = $this->getBlockChainApi()->Explorer->getTransaction($transactionId);
		} catch (BlockchainHttpError $e) {
			throw new Fatality("Blockchain HTTP error: {$e->getMessage()}", $e);
		} catch (BlockchainError $e) {
			throw new Fatality("Blockchain API error: {$e->getMessage()}", $e);
		}

		return $this->convertBlockchainTransactionToTransactionInfo($transaction);
	}

	/**
	 * @param string $address
	 * @return AddressUnspentOutputInfo[]
	 * @throws Fatality
	 */
	public function getAddressUnspentOutputInfoList($address)
	{
		try {
			return array_map(
				function (BlockchainTransactionUnspentOutput $output) {
					return new AddressUnspentOutputInfo($output->tx_output_n, $output->value, $output->tx_hash);
				},
				$this->getBlockChainApi()->Explorer->getUnspentOutputs([$address])
			);
		} catch (BlockchainHttpError $e) {
			throw new Fatality("Blockchain HTTP error: {$e->getMessage()}", $e);
		} catch (BlockchainError $e) {
			throw new Fatality("Blockchain API error: {$e->getMessage()}", $e);
		}
	}

	/**
	 * @param string $transactionHex
	 * @throws Fatality
	 */
	public function pushTransaction($transactionHex)
	{
		try {
			$this->getBlockChainApi()->Push->TX($transactionHex);
		} catch (BlockchainHttpError $e) {
			throw new Fatality("Blockchain HTTP error: {$e->getMessage()}", $e);
		} catch (BlockchainError $e) {
			if (false === strpos($e->getMessage(), 'Transaction Submitted')) { // fix the Blockchain library issue
				throw new Fatality("Blockchain error: {$e->getMessage()}", $e);
			}
		} catch (BlockchainApiError $e) {
			throw new Fatality("Blockchain API error: {$e->getMessage()}", $e);
		}
	}

	/**
	 * @param BlockchainTransaction $transaction
	 * @return TransactionInfo
	 */
	protected function convertBlockchainTransactionToTransactionInfo($transaction)
	{
		$time = (new DateTime())
			->setTimestamp($transaction->time)
			->setTimezone(new DateTimeZone('Europe/Moscow'));

		$transactionResponse = new TransactionInfo($time, $transaction->hash, (bool) $transaction->block_height);

		foreach ($transaction->inputs as $txInput) {
			/** @var BlockchainTransactionInput $txInput */
			$transactionResponse->addInput(new TransactionInputInfo($txInput->n, $txInput->value, $txInput->address));
		}

		foreach ($transaction->outputs as $txOutput) {
			/** @var BlockchainTransactionOutput $txOutput */
			$transactionResponse->addOutput(
				new TransactionOutputInfo($txOutput->n, $txOutput->value, $txOutput->address, $txOutput->spent)
			);
		}

		return $transactionResponse;
	}

	/**
	 * @return Blockchain
	 */
	protected function getBlockChainApi()
	{
		static $blockChain = null;

		return $blockChain ?: $blockChain = new Blockchain($apiCode = null);
	}
}
