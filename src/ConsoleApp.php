<?php
/**
 * Lexicon Manager
 *
 * @license MIT
 * @author Lee Keitel
 *
 * Organize and manage a lexicon for a constructed or even natural language.
 */
namespace onesimus\console;

class ConsoleApp
{
	private $prompt = 'console> ';
	private $banner = 'Console App';
	private $commands = [];
	private $commandHistory = [];
	private $defaultOptions = [
		"registerHelp" => true,
		"registerHistory" => true
	];

	public function __construct($options = [])
	{
		$app = $this;
		$options = array_merge($options, $this->defaultOptions);

		if ($options['registerHelp']) {
			if ($options['registerHelp'] === true) {
				$options['registerHelp'] = 'help';
			}
			$this->registerCommand($options['registerHelp'], '', function($args) use (&$app) {
				if ($args && $app->isCommand($args)) {
					$app->printUsage($args);
				} else {
					foreach ($app->commands as $command => $data) {
						$app->printUsage($command);
					}
				}
			});
		}

		if ($options['registerHistory']) {
			if ($options['registerHistory'] === true) {
				$options['registerHistory'] = 'history';
			}
			$this->registerCommand($options['registerHistory'], '', function($args) use (&$app) {
				if ($args) {
					if (isset($app->commandHistory[$args-1])) {
						$app->executeCommand($app->commandHistory[$args-1]);
					}
				} else {
					foreach ($app->commandHistory as $id => $command) {
						$id++;
						echo "{$id} {$command}\n";
					}
				}
			});
		}
	}

	public function prompt($prompt = null)
	{
		if ($prompt === null) {
			return $this->prompt;
		} else {
			$this->prompt = $prompt;
		}
	}

	public function banner($banner = null)
	{
		if ($banner === null) {
			return $this->banner;
		} else {
			$this->banner = $banner;
		}
	}

	public function registerCommand($command, $args, $function)
	{
		if (!$this->isCommand($command)) {
			$this->commands[$command]['func'] = $function;
			$this->commands[$command]['args'] = $args;
			$this->commands[$command]['usage'] = '';
		} else {
			return false;
		}

		return true;
	}

	public function setUsage($command, $usage)
	{
		if ($this->isCommand($command)) {
			$this->commands[$command]['usage'] = $usage;
		}
	}

	public function executeCommand($command, $args = '')
	{
		if ($this->isCommand($command)) {
			if ($this->commands[$command]['args'] && !$args) {
				$this->printUsage($command);
			}
			return $this->commands[$command]['func']($args);
		} else {
			echo "Command '{$command}' not recognized\n";
		}

		return true;
	}

	public function run()
	{
		echo $this->banner;

		while (true) {
		    $statement = $this->readStdIn();
		    if (!$statement) {
		        continue;
		    }
		    $this->addCommandHistory($statement);

		    $statement = explode(' ', $statement, 2);

		    if (count($statement) == 2) {
		        $command = $statement[0];
		        $args = $statement[1];
		    } else {
		        $command = $statement[0];
		        $args = '';
		    }

		    if ($this->isCommand($command)) {
				if ($this->commands[$command]['args'] && !$args) {
					$this->printUsage($command);
					continue;
				}
				$this->commands[$command]['func']($args);
				$this->echoNewLine();
			} else {
				echo "Command '{$command}' not recognized";
            	$this->echoNewLine(2);
			}
		}
	}

	public function isCommand($command)
	{
		return array_key_exists($command, $this->commands);
	}

	private function readStdIn($prompt = null)
	{
		$prompt = $prompt ?: $this->prompt;
		echo $prompt;
		return stream_get_line(STDIN, 1024, PHP_EOL);
	}

	private function addCommandHistory($line)
	{
		if (count($this->commandHistory) == 0 || $line !== $this->commandHistory[count($this->commandHistory)-1]) {
			$this->commandHistory []= $line;
		}
	}

	private function echoNewLine($times = 1)
	{
	    for ($i = 0; $i < $times; $i++) {
	        echo "\n";
	    }
	}

	private function printUsage($command)
	{
		if ($this->commands[$command]['usage']) {
			echo 'Usage: '.$this->commands[$command]['usage']."\n";
		}
	}
}
