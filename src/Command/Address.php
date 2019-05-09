<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use App\Action\Address as AddressAction;
use App\Renderer\Console as ConsoleRenderer;

class Address extends BaseCommand
{
	/**
	 * @inheritDoc
	 */
	protected static $defaultName = 'address';

	/** @var AddressAction */
	protected $action;

	/**
	 * @param AddressAction $action
	 * @param ConsoleRenderer $renderer
	 */
	public function __construct(AddressAction $action, ConsoleRenderer $renderer)
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
			->addArgument('address', InputArgument::REQUIRED, 'bitcoin address to look at')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output)
    {
		$this->renderer->renderAddressInfo(
			$this->action->getAddressInfo($input->getArgument('address'))
		);
    }
}
