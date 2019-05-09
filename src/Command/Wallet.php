<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\BitcoinClient\BlockChainInfo;
use App\CredentialsProvider\ConsolePassword;
use App\WalletProvider\Zip;
use App\Action\Wallet as WalletAction;
use App\Renderer\Console as ConsoleRenderer;

class Wallet extends BaseCommand
{
	/**
	 * @inheritDoc
	 */
	protected static $defaultName = 'wallet';

	/** @var Zip */
	private $walletProvider;

	/** @var WalletAction */
	protected $action;

	/**
	 * @param ConsolePassword $credentialsProvider
	 * @param Zip $walletProvider
	 * @param BlockChainInfo $bitcoinClient
	 * @param ConsoleRenderer $renderer
	 */
	public function __construct(ConsolePassword $credentialsProvider, Zip $walletProvider, BlockChainInfo $bitcoinClient, ConsoleRenderer $renderer)
	{
		parent::__construct($renderer);
		$this->action = new WalletAction($credentialsProvider, $walletProvider, $bitcoinClient);
		$this->walletProvider = $walletProvider;
	}

	/**
	 * @inheritDoc
	 */
	protected function configure()
	{
		parent::configure();
		$this
			->addArgument('action', InputArgument::REQUIRED, 'create - create new address/wallet, address - get address from wallet, info - get info on address from wallet')
			->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'path to wallet file')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output)
    {
		$this->walletProvider->setWalletPath($input->getOption('file'));

		switch ($input->getArgument('action')) {
			case 'create':
				$walletCreatedResponse = $this->action->createWallet();
				$walletCreatedResponse->walletPath = $this->walletProvider->getWalletPath();
				$this->renderer->renderWalletCreated($walletCreatedResponse);
				break;
			case 'address':
				$this->renderer->renderAddress($this->action->getAddressFromWallet());
				break;
			case 'info':
				$this->renderer->renderAddressInfo($this->action->getAddressInfoFromWallet());
				break;
			default:
				$output->writeln('Unknown command');
		}
    }
}
