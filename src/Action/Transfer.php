<?php

namespace App\Action;

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use App\Action\Response\AddressUnspentOutputInfo;
use App\Action\Response\TransactionOutputInfo;
use App\Action\Response\Transfer as TransferResponse;
use App\Action\Traits\Base58AddressTrait;
use App\BitcoinClientInterface;
use App\CredentialsProviderInterface;
use App\Exception\Fatality;
use App\WalletProviderInterface;

class Transfer extends BaseAction
{
	use Base58AddressTrait;

	const BTC_PRECISION = 8;

	/** @var CredentialsProviderInterface */
	private $credentialsProvider;

	/** @var WalletProviderInterface */
	private $walletProvider;

	/**
	 * @param CredentialsProviderInterface $credentialsProvider
	 * @param WalletProviderInterface $walletProvider
	 * @param BitcoinClientInterface $bitcoinClient
	 */
	public function __construct(CredentialsProviderInterface $credentialsProvider, WalletProviderInterface $walletProvider, BitcoinClientInterface $bitcoinClient)
	{
		$this->credentialsProvider = $credentialsProvider;
		$this->walletProvider = $walletProvider;
		parent::__construct($bitcoinClient);
	}

	/**
	 * @param string $addressTo
	 * @param string $amountBtc
	 * @param string $feeBtc
	 * @return TransferResponse
	 * @throws Fatality
	 */
	public function transfer($addressTo, $amountBtc, $feeBtc)
    {
		$network = Bitcoin::getNetwork();

		$wallet = $this->walletProvider
			->setCredentials($this->credentialsProvider->getCredentials())
			->load();

		$privFactory = new PrivateKeyFactory();
		$privateKey = $privFactory->fromWif($wallet->getPrivateKey(), $network);
		$publicKeyFrom = $privateKey->getPublicKey();
		$base58AddressFrom = new PayToPubKeyHashAddress($publicKeyFrom->getPubKeyHash());

		$base58AddressTo = $this->getBase58AddressFromString($addressTo, $network);

		if (!$amountSatoshi = $this->convertBtcToSatoshi($amountBtc, 'amount')) {
			throw new Fatality('Error: amount to transfer should be positive');
		}

		$this->convertBtcToSatoshi($feeBtc, 'fee');

		$txOutputList = $this->getBitcoinClient()->getAddressUnspentOutputInfoList($base58AddressFrom->getAddress($network));
		$balanceBtc = $this->getTransactionOutputBalance($txOutputList);
		$totalAmountBtc = bcadd($amountBtc, $feeBtc, self::BTC_PRECISION);
		$restBtc = bcsub($balanceBtc, $totalAmountBtc, self::BTC_PRECISION);
		if (bccomp($restBtc, 0, self::BTC_PRECISION) < 0) {
			throw new Fatality('Error: amount of previous transactions is not enough');
		}
		$restSatoshi = $this->convertBtcToSatoshi($restBtc, 'the rest');

		$txOutputList = $this->getTransactionOutputListMatched($txOutputList, $totalAmountBtc);
		if (empty($txOutputList)) {
			throw new Fatality('Error: amount of previous transactions is not enough, something strange happens');
		}

		// Spend from P2PKH
		$txOut = new TransactionOutput(
			$amountSatoshi + $restSatoshi,
			$scriptPubKey = ScriptFactory::scriptPubKey()->payToPubKeyHash($privateKey->getPubKeyHash())
		);

		$txBuilder = TransactionFactory::build()
			->payToAddress($amountSatoshi, $base58AddressTo);

		if ($restSatoshi) {
			$txBuilder->payToAddress($restSatoshi, $base58AddressFrom);
		}

		/** @var AddressUnspentOutputInfo $txOutput */
		foreach ($txOutputList as $txOutput) {
			$txBuilder->input($txOutput->hash, $txOutput->n);
		}

		$transaction = $txBuilder->get();

		$signer = new Signer($transaction);
		$input = $signer->input(0, $txOut);
		$input->sign($privateKey);
		$signed = $signer->get();

		if (!$input->verify()) {
			throw new Fatality('Error: new transaction is not verified');
		}

		$this->getBitcoinClient()->pushTransaction($signed->getHex());

		return new TransferResponse(
			$base58AddressFrom->getAddress($network),
			$base58AddressTo->getAddress($network),
			$signed->getTxId()->getHex()
		);
	}

	/**
	 * @param AddressUnspentOutputInfo[] $txOutputList
	 * @return string
	 */
	protected function getTransactionOutputBalance(array $txOutputList)
	{
		return array_reduce($txOutputList, function ($summa, AddressUnspentOutputInfo $txOutput) {
			return bcadd($summa, $txOutput->value, self::BTC_PRECISION);
		}, 0);
	}

	/**
	 * @param AddressUnspentOutputInfo[] $txOutputList
	 * @param string $amount
	 * @return AddressUnspentOutputInfo[]
	 */
	protected function getTransactionOutputListMatched(array $txOutputList, $amount)
	{
		usort($txOutputList, function (AddressUnspentOutputInfo $txOutput1, AddressUnspentOutputInfo $txOutput2) {
			return bccomp($txOutput1->value, $txOutput2->value, self::BTC_PRECISION);
		});

		// try to utilize a single output
		$txOutputListMatched = [];
		foreach ($txOutputList as $txOutput) {
			if (bccomp($amount, $txOutput->value, self::BTC_PRECISION) <= 0) {
				return [$txOutput];
			}
		}

		// otherwise use outputs with less amount
		$amountFound = 0;
		foreach ($txOutputList as $txOutput) {
			$amountFound = bcadd($amountFound, $txOutput->value, self::BTC_PRECISION);
			$txOutputListMatched[] = $txOutput;
			if (bccomp($amount, $amountFound, self::BTC_PRECISION) <= 0) {
				return $txOutputListMatched;
			}
		}

		return [];
	}

	/**
	 * @param string $txId
	 * @param int $vout
	 * @return TransactionOutputInfo
	 * @throws Fatality
	 */
	protected function getTransactionOutputInfo($txId, $vout)
	{
		$transactionResponse = $this->getBitcoinClient()->getTransactionInfo($txId);

		$txOutputList = array_filter($transactionResponse->outputList, function (TransactionOutputInfo $out) use ($vout) {
			return (int) $out->n == (int) $vout;
		});
		if (empty($txOutputList)) {
			throw new Fatality("Error: vout $vout is not found in previous transaction");
		}

		/** @var TransactionOutputInfo $txOutput */
		$txOutput = $txOutputList[0];
		if ($txOutput->spent) {
			throw new Fatality("Error: vout $vout of previous transaction is already spent");
		}

		return $txOutput;
	}

	/**
	 * @param string $amount
	 * @param string $amountName
	 * @return int
	 * @throws Fatality
	 */
	protected function convertBtcToSatoshi($amount, $amountName)
	{
		if (!preg_match('/^\d+(\.\d{1,' . self::BTC_PRECISION . '})?$/', $amount)) {
			throw new Fatality("Error: invalid $amountName format");
		}

		return (int) bcmul($amount, '100000000', 0);
	}
}
