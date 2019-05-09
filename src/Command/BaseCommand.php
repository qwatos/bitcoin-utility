<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use App\Exception\Fatality;
use App\Renderer\Console as ConsoleRenderer;

abstract class BaseCommand extends Command
{
	/** @var ConsoleRenderer */
	protected $renderer;

	/**
	 * @param ConsoleRenderer $renderer
	 */
	public function __construct(ConsoleRenderer $renderer)
	{
		parent::__construct();
		$this->renderer = $renderer;
	}

	/**
	 * @inheritDoc
	 */
	protected function configure()
	{
		$this
			->addOption('out', 'o', InputOption::VALUE_OPTIONAL, 'path to file to put the output to')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
    {
		try {
			if ($outputPath = $input->getOption('out')) {
				if (!$fp = fopen($outputPath, 'w')) {
					throw new Exception("Failed to open file $outputPath\n");
				}
				$output = new StreamOutput($fp);
			}

			$this->renderer->setup($output);

			$this->doExecute($input, $output);
		} catch (Fatality $e) {
			$output->writeln("{$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
			$output->writeln(str_replace(getcwd() . '\\', '', $e->getTraceAsString()));
		} catch (Exception $e) {
			$message = $e->getMessage() ?: get_class($e);
			$output->writeln("Error: $message at {$e->getFile()}:{$e->getLine()}");
			$output->writeln(str_replace(getcwd() . '\\', '', $e->getTraceAsString()));
		}
    }

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	abstract protected function doExecute(InputInterface $input, OutputInterface $output);
}
