<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\BitcoinClient\BlockChainInfo;
use App\CredentialsProvider\ConsolePassword;
use App\WalletProvider\Zip;
use App\Action\Transfer as TransferAction;
use App\Renderer\Console as ConsoleRenderer;

class Transfer extends BaseCommand
{
	/**
	 * @inheritDoc
	 */
	protected static $defaultName = 'transfer';

	/** @var Zip */
	private $walletProvider;

	/** @var TransferAction */
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
		$this->action = new TransferAction($credentialsProvider, $walletProvider, $bitcoinClient);
		$this->walletProvider = $walletProvider;
	}

	/**
	 * @inheritDoc
	 */
	protected function configure()
	{
		parent::configure();
		$this
			->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'path to wallet file')
			->addOption('to', 't', InputOption::VALUE_REQUIRED, 'bitcoin address to transfer to from the wallet')
			->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'amount (BTC) to transfer')
			->addOption('fee', 'fee', InputOption::VALUE_REQUIRED, 'the fee (BTC) to give to bitcoin network')
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

		$this->renderer->renderTransfer(
			$this->action->transfer(
				$input->getOption('to'),
				$input->getOption('amount'),
				$input->getOption('fee')
			)
		);
	}
}
