<?php

namespace App;

use App\Action\Response\AddressInfo;
use App\Action\Response\TransactionInfo;
use App\Action\Response\AddressUnspentOutputInfo;
use App\Exception\Fatality;

/**
 * Client to the external service to obtain/push info from/to Bitcoin network
 */
interface BitcoinClientInterface
{
	/**
	 * @param string $address (Base58)
	 * @return AddressInfo
	 * @throws Fatality
	 */
	public function getAddressInfo(string $address): AddressInfo;

	/**
	 * @param string $transactionId
	 * @return TransactionInfo
	 * @throws Fatality
	 */
	public function getTransactionInfo(string $transactionId): TransactionInfo;

	/**
	 * @param string $address (Base58)
	 * @return AddressUnspentOutputInfo[]
	 * @throws Fatality
	 */
	public function getAddressUnspentOutputInfoList(string $address): array;

	/**
	 * @param string $transactionHex
	 * @throws Fatality
	 */
	public function pushTransaction(string $transactionHex);
}
