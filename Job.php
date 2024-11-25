<?php
namespace FreePBX\modules\Cel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
class Job implements \FreePBX\Job\TaskInterface {
	public static function run(InputInterface $input, OutputInterface $output) {
		$tz = @date_default_timezone_get();
		date_default_timezone_set($tz);
		$date = date('Y-m-d', strtotime('-1 days'));
		\FreePBX::Cel()->cleanTransientCELData($date);
		return true;
	}
}
