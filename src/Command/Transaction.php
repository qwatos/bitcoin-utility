<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Action\Transaction as TransactionAction;
use App\Renderer\Console as ConsoleRenderer;

class Transaction extends BaseCommand
{
	/**
	 * @inheritDoc
	 */
	protected static $defaultName = 'transaction';

	/** @var TransactionAction */
	protected $action;

	/**
	 * @param TransactionAction $action
	 * @param ConsoleRenderer $renderer
	 */
	public function __construct(TransactionAction $action, ConsoleRenderer $renderer)
	{
		parent::__construct($renderer);
		$this->action = $action;
	}

	/**
	 * @inheritDoc
	 */
	protected function configure()
	{
		parent::configure();
		$this
			->addArgument('id', InputArgument::REQUIRED, 'transaction hash')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output)
    {
		$this->renderer->renderTransactionInfo(
			$this->action->getTransactionInfo($input->getArgument('id'))
		);
	}
}
