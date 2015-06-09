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

	public function __construct()
	{

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
		if (!array_key_exists($command, $this->commands)) {
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
		if (array_key_exists($command, $this->commands)) {
			$this->commands[$command]['usage'] = $usage;
		}
	}

	public function executeCommand($command, $args = '')
	{
		if (array_key_exists($command, $this->commands)) {
			if ($this->commands[$command]['args'] && !$args) {
				echo 'Usage: '.$this->commands[$command]['usage'];
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
		    $this->readlineAddHistory($statement);

		    $statement = explode(' ', $statement, 2);

		    if (count($statement) == 2) {
		        $command = $statement[0];
		        $args = $statement[1];
		    } else {
		        $command = $statement[0];
		        $args = '';
		    }

		    if (array_key_exists($command, $this->commands)) {
				if ($this->commands[$command]['args'] && !$args) {
					echo 'Usage: '.$this->commands[$command]['usage']."\n";
					continue;
				}
				$this->commands[$command]['func']($args);
				$this->echoNewLine(2);
			} else {
				echo "Command '{$command}' not recognized";
            	$this->echoNewLine(2);
			}
		}
	}

	private function readStdIn($prompt = null)
	{
		$prompt = $prompt ?: $this->prompt;

		if (PHP_OS == 'WINNT') {
	    	echo $prompt;
	    	return stream_get_line(STDIN, 1024, PHP_EOL);
		} else {
			return readline($prompt);
		}
	}

	private function readlineAddHistory($line)
	{
		if (PHP_OS != 'WINNT') {
	    	readline_add_history($line);
		}
	}

	private function echoNewLine($times = 1)
	{
	    for ($i = 0; $i < $times; $i++) {
	        echo "\n";
	    }
	}
}
