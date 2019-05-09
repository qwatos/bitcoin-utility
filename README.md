# bitcoin-utility

This repository contains a simple extendable PHP console utility.

The purpose of this utility is provide to you full control on your own Bitcoins without external wallets.

You can generate new Bitcoin address with keys stored in strangely named archive (wallet), encrypted with password.
Then you can see some basic info on your address and transactions corresponding with this address,
	or transfer some BTC to another address, providing amount and fee yourself
	(don't forget to remember the password to your wallet - there is no way to restore it).

## Dependencies

Requires PHP version 7.x because of https://github.com/Bit-Wasp/bitcoin package requirements.

Depends on https://blockchain.info web resource functionality via https://github.com/blockchain/api-v1-client-php package.

## Install

  composer install qwatos/bitcoin-utility

## Usage

	php bin/console wallet create --file=<<<Path to wallet>>>
	- create the private/public keys for new Bitcoin address and store them in a regular archive (wallet), encrypted with password

	php bin/console wallet address --file=<<<Path to wallet>>>
	- obtain the Base58 Bitcoin address from the wallet

	php bin/console wallet info --file=<<<Path to wallet>>>
	- obtain basic information about Bitcoin address from the wallet and transactions corresponding with it

	php bin/console address <<<Base58 address>>>
	- obtain basic information about Bitcoin address and transactions corresponding with it

	php bin/console transaction <<<Transaction hash>>>
	- obtain basic information about Bitcoin transaction

	php bin/console transfer --file=<<<Path to wallet>>> --to=<<<Base58 address>>> --amount=<<<Amount>>> --fee=<<<Fee>>>
	- perform P2PKH payment from Bitcoin address in your wallet to another address, amount and fee in format like 0.00000000

	extra option --out==<<<Path to log>>> redirect console output to the file

## Extending the implementations

  BitcoinClientInterface - defines the functions to access Bitcoin network
  - now via blockchain/blockchain package

  CredentialsInterface - contains credentials to access the wallet
  - now the password is enough to access the archive
  - may be the keys, fingers ot retina in the future :)

  CredentialsProviderInterface - provides credentials to access the wallet
  - now reading the password from Win/Unix console
  - may be getting from the form in web application in the future

  WalletInterface - contains keys for Bitcoin address

  WalletProviderInterface - provides access to wallet with keys
  - now zip archive in file system, encrypted with password
  - may be special key storage, DB or QR-code in the future

  RendererInterface - outputs program results to user
  - now writing to STDOUT or file
  - may be using HTML or template engine like TWIG in the future
