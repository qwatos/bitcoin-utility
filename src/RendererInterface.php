<?php

namespace App;

use App\Action\Response\Address;
use App\Action\Response\AddressInfo;
use App\Action\Response\TransactionInfo;
use App\Action\Response\Transfer;
use App\Action\Response\WalletCreated;

/**
 * Outputs program results to user
 */
interface RendererInterface
{
	/**
	 * @param Address $addressResponse
	 */
	public function renderAddress(Address $addressResponse);

	/**
	 * @param AddressInfo $addressInfo
	 */
	public function renderAddressInfo(AddressInfo $addressInfo);

	/**
	 * @param TransactionInfo $transactionInfo
	 * @param string $prefix
	 */
	public function renderTransactionInfo(TransactionInfo $transactionInfo, string $prefix = '');

	/**
	 * @param WalletCreated $walletCreatedResponse
	 */
	public function renderWalletCreated(WalletCreated $walletCreatedResponse);

	/**
	 * @param Transfer $transferResponse
	 */
	public function renderTransfer(Transfer $transferResponse);
}
