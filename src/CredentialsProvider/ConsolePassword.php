<?php

namespace App\CredentialsProvider;

use App\CredentialsInterface;
use App\CredentialsProviderInterface;
use App\Credentials\Password;

class ConsolePassword implements CredentialsProviderInterface
{
	const
		UNIX_BASH_PATH = '/usr/bin/env bash',
		WINDOWS_POWER_SHELL_PATH = 'C:\Windows\system32\WindowsPowerShell\v1.0\powershell.exe'
	;

	/**
	 * @return CredentialsInterface
	 */
	public function getCredentials()
	{
		$prompt = 'Enter the password for wallet';
		if (preg_match('/^win/i', PHP_OS)) {
			if (null === $password = $this->getPasswordWithWindowsPowerShell($prompt)) {
				$password = $this->getPasswordWithWindowsCscript($prompt);
			}
		} else {
			if (null === $password = $this->getPasswordWithUnixReadline($prompt)) {
				$password = $this->getPasswordWithUnixBash($prompt);
			}
		}

		return new Password($password);
	}

	/**
	 * @param string $prompt
	 * @return string | null
	 */
	protected function getPasswordWithWindowsPowerShell($prompt)
	{
		if (!file_exists(self::WINDOWS_POWER_SHELL_PATH)) {
			return null;
		}

		$pwd = shell_exec(self::WINDOWS_POWER_SHELL_PATH .
			' -Command "$Password=Read-Host -assecurestring \"' . $prompt . '\" ; $PlainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto([System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($Password)) ; echo $PlainPassword;"');
		$pwd = explode("\n", $pwd);

		return $this->removeBOM($pwd[0]);
	}

	/**
	 * @param string $prompt
	 * @return string | null
	 */
	protected function getPasswordWithWindowsCscript($prompt)
	{
		$vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
		file_put_contents(
			$vbscript, 'wscript.echo(InputBox("'
				. addslashes($prompt)
				. '", "", "' . $prompt . '"))');
		$command = "cscript //nologo " . escapeshellarg($vbscript);
		$password = rtrim(shell_exec($command));
		unlink($vbscript);

		return $password;
	}

	/**
	 * @param string $prompt
	 * @return string | null
	 */
	protected function getPasswordWithUnixReadline($prompt)
	{
		if (!function_exists('\\readline_callback_handler_install')) {
			return null;
		}

		\readline_callback_handler_install('', function(){});
		echo "$prompt: ";
		$password = '';
		while (true) {
			$strChar = stream_get_contents(STDIN, 1);
			if ($strChar === "\r" || $strChar === "\n") {
				break;
			}
			$password .= $strChar;
			echo '*';
		}
		\readline_callback_handler_remove();
		echo PHP_EOL;

		return $password;
	}

	/**
	 * @param string $prompt
	 * @return string | null
	 */
	protected function getPasswordWithUnixBash($prompt)
	{
		$command = self::UNIX_BASH_PATH . " -c 'echo OK'";
		if (rtrim(shell_exec($command)) !== 'OK') {
			trigger_error("Can't invoke bash");
			return null;
		}
		$command = self::UNIX_BASH_PATH . " -c 'read -s -p \""
			. addslashes($prompt)
			. "\" mypassword && echo \$mypassword'";
		$password = rtrim(shell_exec($command));
		echo PHP_EOL;

		return $password;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	protected static function removeBOM($str) {
		return substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)
			? substr($str, 3)
			: $str;
	}
}
