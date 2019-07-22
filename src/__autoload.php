<?php declare(strict_types=1);




spl_autoload_register(function ($class)
{
	$parts = explode("\\", $class);

	if (array_shift($parts) == "Observability")
	{
		$path = join(DIRECTORY_SEPARATOR, $parts);

		if (file_exists(__DIR__.DIRECTORY_SEPARATOR.$path.'.class.php'))
			require(__DIR__.DIRECTORY_SEPARATOR.$path.'.class.php');
		else
		{
			echo "<pre>{$class}".PHP_EOL;
			print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			print_r((__DIR__.DIRECTORY_SEPARATOR.$path.'.class.php').PHP_EOL);
			print_r($parts);
			die(__FILE__);
		}
	}

});
