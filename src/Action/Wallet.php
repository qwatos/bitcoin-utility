<?php

namespace App\Action;

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Key\Factory\PublicKeyFactory;
use App\Action\Response\Address as AddressResponse;
use App\Action\Response\AddressInfo as AddressInfoResponse;
use App\Action\Response\WalletCreated as WalletCreatedResponse;
use App\BitcoinClientInterface;
use App\CredentialsProviderInterface;
use App\Exception\Fatality;
use App\WalletProviderInterface;
use App\Wallet as WalletObject;

class Wallet extends BaseAction
{
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
	 * @return WalletCreatedResponse
	 * @throws Fatality
	 */
	public function createWallet(): WalletCreatedResponse
	{
		$network = Bitcoin::getNetwork();
		$privFactory = new PrivateKeyFactory();

		$random = new Random();
		$privateKey = $privFactory->generateCompressed($random);
		$publicKey = $privateKey->getPublicKey();

		/*$output->writeln("Private key");
		$output->writeln(" - WIF: " . ($xz = $privateKey->toWif($network)));
		$output->writeln(" - Hex: " . $privateKey->getBuffer()->getHex());

		$output->writeln("Public Key");
		$output->writeln(" - Hex: " . $publicKey->getBuffer()->getHex());
		$output->writeln(" - Hash: " . $publicKey->getPubKeyHash()->getHex());*/

		$base58Address = new PayToPubKeyHashAddress($publicKey->getPubKeyHash());

		$wallet = new WalletObject(
			$privateKey->toWif($network),
			$publicKey->getBuffer()->getHex()
		);

		$this->walletProvider
			->setCredentials($this->credentialsProvider->getCredentials())
			->save($wallet);

		return new WalletCreatedResponse($base58Address->getAddress($network));
	}

	/**
	 * @return AddressResponse
	 * @throws Fatality
	 */
	public function getAddressFromWallet(): AddressResponse
	{
		$network = Bitcoin::getNetwork();

		$base58Address = $this->getBase58AddressFromWallet();

		return new AddressResponse($base58Address->getAddress($network));
	}

	/**
	 * @return AddressInfoResponse
	 * @throws Fatality
	 */
	public function getAddressInfoFromWallet(): AddressInfoResponse
	{
		$network = Bitcoin::getNetwork();

		$base58Address = $this->getBase58AddressFromWallet();

		return $this->getBitcoinClient()->getAddressInfo($base58Address->getAddress($network));
	}

	/**
	 * @return PayToPubKeyHashAddress
	 * @throws Fatality
	 */
	protected function getBase58AddressFromWallet(): PayToPubKeyHashAddress
	{
		$wallet = $this->walletProvider
			->setCredentials($this->credentialsProvider->getCredentials())
			->load();

		$publicKeyFactory = new PublicKeyFactory();
		$publicKey = $publicKeyFactory->fromHex($wallet->getPublicKey());

		return new PayToPubKeyHashAddress($publicKey->getPubKeyHash());
	}
}
