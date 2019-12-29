<?php

namespace App\WalletProvider;

use ZipArchive;
use App\CredentialsInterface;
use App\Credentials\HavePasswordInterface;
use App\Exception\Fatality;
use App\WalletInterface;
use App\WalletProviderInterface;
use App\Wallet;

class Zip implements WalletProviderInterface
{
	const WALLET_NAME = 'auto.jpg';

	/** @var string */
	private $walletPath;

	/** @var HavePasswordInterface */
	private $credentials;

	/**
	 * @param string | null $walletPath
	 * @return static
	 */
	public function setWalletPath($walletPath = null): WalletProviderInterface
	{
		$this->walletPath = $walletPath ?: getcwd() . DIRECTORY_SEPARATOR . 'wallet.zip';

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWalletPath(): string
	{
		return $this->walletPath;
	}

	/**
	 * @param CredentialsInterface $credentials
	 * @return static
	 * @throws Fatality
	 */
	public function setCredentials(CredentialsInterface $credentials): WalletProviderInterface
	{
		if (!$credentials instanceof HavePasswordInterface) {
			throw new Fatality('Error: failed to obtain the password from credentials');
		}

		$this->credentials = $credentials;

		return $this;
	}

	/**
	 * @param WalletInterface $wallet
	 * @throws Fatality
	 */
	public function save(WalletInterface $wallet)
	{
		$zip = new ZipArchive();
		if (true !== $zip->open($this->walletPath, ZipArchive::CREATE)) {
			throw new Fatality("Error: failed to open file <{$this->walletPath}>");
		}

		$zip->addFromString(static::WALLET_NAME, json_encode([
			'private_key' => $wallet->getPrivateKey(),
			'public_key' => $wallet->getPublicKey(),
		]));
		$zip->setPassword($this->credentials->getPassword());
		$zip->setEncryptionIndex(0, ZipArchive::EM_AES_256);
		$zip->close();
	}

	/**
	 * @return WalletInterface
	 * @throws Fatality
	 */
	public function load(): WalletInterface
	{
		if (false === file_exists($this->walletPath)) {
			throw new Fatality("Error: failed to open file <{$this->walletPath}>");
		}

		$zip = new ZipArchive();
		if (true !== $zip->open($this->walletPath)) {
			throw new Fatality("Error: failed to open archive <{$this->walletPath}>");
		}

		$zip->setPassword($this->credentials->getPassword());
		$zip->setEncryptionIndex(0, ZipArchive::EM_AES_256);
		if (false === $text = $zip->getFromIndex(0)) {
			throw new Fatality("Error: failed to extract archive <{$this->walletPath}>");
		}

		$data = json_decode($text, true);

		return new Wallet(
			$data['private_key'],
			$data['public_key']
		);
	}
}
