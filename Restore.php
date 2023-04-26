<?php
namespace FreePBX\modules\Cel;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$this->importAdvancedSettings($configs['settings']);
		$files = $this->getFiles();
		$tablename = $this->FreePBX->Config->get('CELDBTABLENAME') ? $this->FreePBX->Config->get('CELDBTABLENAME') : 'cel';
		$dbhandle = $this->FreePBX->Cel->getCelDbHandle();
		$dbhandle->query("TRUNCATE $tablename");
		return $this->restoreDataFromDump($tablename, $this->tmpdir, $files);
	}
	public function processLegacy($pdo, $data, $tables, $unknownTables){
		global $amp_conf;
		$cdrname = $this->FreePBX->Config->get('CELDBNAME') ? $this->FreePBX->Config->get('CELDBNAME') : 'asteriskcdrdb';
		$tablename = $this->FreePBX->Config->get('CELDBTABLENAME') ? $this->FreePBX->Config->get('CELDBTABLENAME') : 'cel';
		$cdrhost = $this->FreePBX->Config->get('CDRDBHOST') ? $this->FreePBX->Config->get('CDRDBHOST') : $amp_conf['AMPDBHOST'];
		$cdruser = $this->FreePBX->Config->get('CDRDBUSER') ? $this->FreePBX->Config->get('CDRDBUSER') : $amp_conf['AMPDBUSER'];
		$cdrpass = $this->FreePBX->Config->get('CDRDBPASS') ? $this->FreePBX->Config->get('CDRDBPASS') : $amp_conf['AMPDBPASS'];
		$cdrport = $this->FreePBX->Config->get('CDRDBPORT');

		try {
				$connection = new \Database('mysql:dbname='.$cdrname.';host='.$cdrhost, $cdruser,$cdrpass);
		} catch(\Exception $e) {
				return array("status" => false, "message" => $e->getMessage());
		}
		$sth = $connection->query("SHOW TABLES");
		$res = $sth->fetchAll(\PDO::FETCH_ASSOC);

		foreach($res as $loadedTables){
				if ($loadedTables['Tables_in_asteriskcdrdb'] == $tablename){
						$truncate = "DROP TABLE asteriskcdrdb.".$tablename;
						$this->FreePBX->Database->query($truncate);
						$loadedTables = $pdo->query("ALTER TABLE asterisktemp.".$tablename." RENAME TO asteriskcdrdb.".$tablename);
				}
		}
	}
}
