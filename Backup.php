<?php
namespace FreePBX\modules\Cel;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$backupDetails = $this->FreePBX->Backup->getAll($id);
		if (isset($backupDetails['celStartDate']) && isset($backupDetails['celEndDate'])) {
			$startDate = $backupDetails['celStartDate'];
			$endDate = $backupDetails['celEndDate'];
			$query = 'eventtime between "' . $startDate . '" and "' . $endDate . '"';
			$dumpOtherOptions[] = "--where='" . $query . "'";
		}
		
		$dumpOtherOptions[] = '--opt --compact --skip-lock-tables --skip-triggers --no-create-info';
		$dumpOtherOptions = implode(" ", $dumpOtherOptions);
		$fileObj = $this->dumpTableIntoFile('cel', 'cel', $dumpOtherOptions, true);
		$this->addDirectories([$fileObj->getPath()]);
		$this->addConfigs([
			'settings' => $this->dumpAdvancedSettings()
		]);
	}
}
