<?php
/**
 * Console Application Framework
 *
 * @license BSD 3-Clause
 * @author Lee Keitel
 *
 * Quickly create a PHP console application by registering commands and
 * their callback functions.
 */
namespace Onesimus\Console;

require __DIR__.'/../vendor/autoload.php';

use Onesimus\Readline\Readline;

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

	private $readline;

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

		$this->readline = new Readline();
	}

	public function setPrompt($prompt = '')
	{
		$this->prompt = $prompt;
	}

	public function getPrompt()
	{
		return $this->prompt;
	}

	public function setBanner($banner = '')
	{
		$this->banner = $banner;
	}

	public function getBanner()
	{
		return $this->banner;
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

	public function getUsage($command)
	{
		if ($this->isCommand($command)) {
			return $this->commands[$command]['usage'];
		}
	}

	public function executeCommand($command, $args = '')
	{
		if ($this->isCommand($command)) {
			if ($this->commands[$command]['args'] && !$args) {
				$this->printUsage($command);
			} else {
				$this->commands[$command]['func']($args);
				return true;
			}
		} else {
			echo "Command '\033[34m{$command}\033[39m' not recognized\n";
		}
		return false;
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

		    $this->executeCommand($command, $args);
		}
	}

	public function isCommand($command)
	{
		return array_key_exists($command, $this->commands);
	}

	public function readStdIn($prompt = null)
	{
		$prompt = $prompt !== null ? $prompt : $this->prompt;
		return $this->readline->readLine($prompt);
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
