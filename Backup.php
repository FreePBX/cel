<?php
namespace FreePBX\modules\Cel;
use Symfony\Component\Process\Process;
use FreePBX\modules\Backup as Base;
use Symfony\Component\Filesystem\Filesystem;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$fs = new Filesystem();
		$tmpdir = sys_get_temp_dir().'/dbdump';
		$fs->remove($tmpdir);
		$fs->mkdir($tmpdir);

		global $amp_conf;
		$cdrname = $this->FreePBX->Config->get('CELDBNAME') ? $this->FreePBX->Config->get('CELDBNAME') : 'asteriskcdrdb';
		$tablename = $this->FreePBX->Config->get('CELDBTABLENAME') ? $this->FreePBX->Config->get('CELDBTABLENAME') : 'cel';
		$cdrhost = $this->FreePBX->Config->get('CDRDBHOST') ? $this->FreePBX->Config->get('CDRDBHOST') : $amp_conf['AMPDBHOST'];
		$cdruser = $this->FreePBX->Config->get('CDRDBUSER') ? $this->FreePBX->Config->get('CDRDBUSER') : $amp_conf['AMPDBUSER'];
		$cdrpass = $this->FreePBX->Config->get('CDRDBPASS') ? $this->FreePBX->Config->get('CDRDBPASS') : $amp_conf['AMPDBPASS'];
		$cdrport = $this->FreePBX->Config->get('CDRDBPORT');

		$command = [fpbx_which('mysqldump')];
		if(!empty($cdrhost)){
				$command[] = '--host';
				$command[] = $cdrhost;
		}
		if(!empty($cdrport)){
				$command[] = '--port';
				$command[] = $cdrport;
		}
		if(!empty($cdruser)){
				$command[] = '--user';
				$command[] = $cdruser;
		}
		if(!empty($cdrpass)){
				$command[] = '-p'.$cdrpass;
		}
		$command[] = $cdrname;
		$command[] = '--opt';
		$command[] = '--compact';
		$command[] = '--table';
		$command[] = $tablename;
		$command[] = '--skip-lock-tables';
		$command[] = '--skip-triggers';
		$command[] = '--no-create-info';
		$command[] = '>';
		$command[] = $tmpdir.'/cel.sql';
		$command = implode(" ", $command);
		$process= new Process($command);
		$process->disableOutput();
		$process->mustRun();
		$fileObj = new \SplFileInfo($tmpdir . '/cel.sql');
		$this->addSplFile($fileObj);
		$this->addDirectories([$fileObj->getPath()]);
		$this->addConfigs([
			'settings' => $this->dumpAdvancedSettings()
		]);

		$this->addGarbage($tmpdir);
	}
}
