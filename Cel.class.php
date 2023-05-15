<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2014 Schmooze Com Inc.
//
namespace FreePBX\modules;
class Cel extends \FreePBX_Helpers implements \BMO {
	private $message = '';
	private $calls;
	private $db_table = 'cel';
	public $cdrdb = null;

	public function __construct($freepbx = null) {
		$amp_conf = \FreePBX::$conf;
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;

		$config = $this->FreePBX->Config;
		$this->astver = $config->get('ASTVERSION');
		$db_name = $config->get('CDRDBNAME');
		$db_host = $config->get('CDRDBHOST');
		$db_port = $config->get('CDRDBPORT');
		$db_user = $config->get('CDRDBUSER');
		$db_pass = $config->get('CDRDBPASS');
		$db_table = $config->get('CELDBTABLENAME');
		$dbt = $config->get('CDRDBTYPE');
		$mixmon = \FreePBX::Config()->get('MIXMON_DIR',true);
		$spool = \FreePBX::Config()->get('ASTSPOOLDIR',true);
		$this->directory = $mixmon ? $mixmon . '/' : $spool . '/monitor/';
		$db_hash = array('mysql' => 'mysql', 'postgres' => 'pgsql');
		$dbt = !empty($dbt) ? $dbt : 'mysql';
		$db_type = $db_hash[$dbt];
		$this->db_table = !empty($db_table) ? $db_table : "cel";
		$db_name = !empty($db_name) ? $db_name : "asteriskcdrdb";
		$db_host = !empty($db_host) ? $db_host : (!empty($amp_conf['AMPDBHOST']) ?  $amp_conf['AMPDBHOST'] : 'localhost');
		$db_port = empty($db_port) ? '' :  ';port=' . $db_port;
		$db_user = empty($db_user) ? $amp_conf['AMPDBUSER'] : $db_user;
		$db_pass = empty($db_pass) ? $amp_conf['AMPDBPASS'] : $db_pass;
		try {
			$this->cdrdb = new \DB(new \Database($db_type.':host='.$db_host.$db_port.';dbname='.$db_name.';charset=utf8',$db_user,$db_pass));
		} catch(\Exception $e) {
			throw new \Exception('Unable to connect to CDR Database');
		}

		//Set the CDR session timezone to GMT if CDRUSEGMT is true
		$use_gmt = $config->get('CDRUSEGMT');
		if (isset($use_gmt) && $use_gmt) {
			$sql = "SET time_zone = '+00:00'";
			$sth = $this->cdrdb->prepare($sql);
			$ret = $sth->execute();
		}
	}

	public function getCelDbHandle() {
		// Simply returns the DB Handle created in __construct
		return $this->cdrdb;
	}

	public function getDbTable() {
		return $this->db_table;
	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){
		$settings = [];
		$id = $backupObj->getBackupId();
		if($id){
			$settings = $this->backupSettingsGet($id);
		}
		//Something for the SQL file though this shouldn't be a thing
		$id = isset($id)?$id:'cel-'.time();
		$advancedsettings = [
			'CEL_ENABLED' => $this->config->get('CEL_ENABLED'),
			'CELDBNAME' => $this->config->get('CELDBNAME'),
			'CELDBTABLENAME' => $this->config->get('CELDBTABLENAME'),
		];
		$age = isset($settings['cel_dump_days'])?$settings['cel_dump_days']:0;
		$advanced = isset($settings['cel_advancedsetting'])?$settings['cel_advancedsetting']:'no';
		if($advanced == 'yes'){
			$backupObj->addConfigs($advancedsettings);
		}
		$structureFile = sprintf('%s/structure-%s.sql.gz',sys_get_temp_dir(),$id);
		$dataFile = sprintf('%s/data-%s.sql.gz',sys_get_temp_dir(),$id);
		$db_name = $this->config->get('CDRDBNAME');
		$db_host = $this->config->get('CDRDBHOST');
		$db_port = $this->config->get('CDRDBPORT');
		$db_user = $this->config->get('CDRDBUSER');
		$db_pass = $this->config->get('CDRDBPASS');
		$db_table = $this->config->get('CELDBTABLENAME');
		$files = [];
		exec(sprintf('mysqldump --no-data --user=%s --password=%s --host=%s %s %s | gzip -c > %s',$db_user,$db_pass,$db_host,$db_name,$db_table,$structureFile),$out,$ret);
		if($ret === 0){
			$parts = pathinfo($structureFile);
			$files[] = [
				'type' => 'dbstructure',
				'filename' => $parts['basename'],
				'path' => $parts['dirname'],
			];
			$backupObj->addGarbage($structureFile);
		}

		if($age == 0){
			//TODO: Does this need escaping?
			exec(sprintf('mysqldump --no-create-info --user=%s --password=%s --host=%s %s %s | gzip -c > %s',$db_user,$db_pass,$db_host,$db_name,$db_table,$dataFile),$out,$ret);
		}
		if($age > 0){
			$age = '-w"STR_TO_DATE(eventtime, \'%Y-%m-%d %H:%i:%s\') > NOW() - INTERVAL '.$age.' DAY"';
			exec(sprintf('mysqldump --no-create-info --user=%s --password=%s --host=%s %s %s %s | gzip -c > %s',$db_user,$db_pass,$db_host,$age,$db_name,$db_table,$dataFile),$out,$ret);
		}
		if($ret === 0){
			$parts = pathinfo($dataFile);
			$files[] = [
				'type' => 'dbdata',
				'filename' => $parts['basename'],
				'path' => $parts['dirname'],
			];
			$backupObj->addGarbage($dataFile);
		}
		$backupObj->addFiles($files);
	}

	public function restore($backup){

	}

	public function ucpDelGroup($id,$display,$data) {
	}

	public function ucpAddGroup($id, $display, $data) {
		$this->ucpUpdateGroup($id,$display,$data);
	}

	/* UCP template to get the user assigned vm extension details
	* @defaultexten is the default_extensionof the userman userid
	* @userid is userman user id
	* @widget is an array we need to replace few item based on the userid
	*/
	public function getWidgetListByModule($defaultexten, $userid,$widget) {
		// if the widget_type_id is not defaultextension and widget_type_id is not in extensions
		// then return only the defaultexten details
		$widgets = array();
		$widget_type_id = $widget['widget_type_id'];// this will be an extension number
		$enabled = $this->FreePBX->Ucp->getCombinedSettingByID($userid,'Cel','enable');
		if (!$enabled) {
			return false;
		}
		$extensions = $this->FreePBX->Ucp->getCombinedSettingByID($userid,'Cel','assigned');
		if(in_array($widget_type_id,$extensions)){
			// nothing to do return the same widget
			return $widget;
		}else {// sent the default extension
			$data = $this->FreePBX->Core->getDevice($defaultexten);
			if(empty($data) || empty($data['description'])) {
				$data = $this->FreePBX->Core->getUser($defaultexten);
				$name = $data['name'];
			} else {
				$name = $data['description'];
			}
			$widget['widget_type_id'] = $defaultexten;
			$widget['name'] = $name;
			return $widget;
		}
	return false;
	}


	public function ajaxRequest($req, &$setting) {
		switch ($req) {
			case 'report':
				$setting['changesession'] = true;
			return true;
			break;
			case 'gethtml5':
			case 'playback':
			return true;
			break;

			default:
			return false;
			break;
		}
	}
	public function ajaxCustomHandler() {
		switch($_REQUEST['command']) {
		case "playback":
			$media = $this->FreePBX->Media();
			$media->getHTML5File($_REQUEST['file']);
		break;
		}
	}
	public function ajaxHandler() {
		switch ($_REQUEST['command']) {
			case 'report':
				$return = $this->cel_getreport($_REQUEST);
				return $return;
			break;
			case "gethtml5":
				$media = $this->FreePBX->Media();
				$file = isset($_SESSION['cel']['recordings'][$_REQUEST['uniqueid']]['file']) ? $_SESSION['cel']['recordings'][$_REQUEST['uniqueid']]['file'] : '';
				if (!empty($file) && file_exists($file)) {
					$media->load($file);
					$files = $media->generateHTML5();
					$final = array();
					foreach($files as $format => $name) {
						$final[$format] = "ajax.php?module=cel&command=playback&file=".$name;
					}
					return array("status" => true, "files" => $final);
				} else {
					return array("status" => false, "message" => _("File does not exist"));
				}
			break;
			default:
				return array('status' => 'error', 'message' => _("Invalid Command"));
			break;
		}
	}

	public function ucpUpdateGroup($id,$display,$data) {
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'group') {
			if(!empty($_POST['ucp_cel'])) {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','assigned',$_POST['ucp_cel']);
			} else {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','assigned',array('self'));
			}
			if(!empty($_POST['cel_enable']) && $_POST['cel_enable'] == "yes") {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','enable',true);
			} else {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','enable',false);
			}
			if(!empty($_REQUEST['cel_download']) && $_REQUEST['cel_download'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','download',true);
			} else {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','download',false);
			}
			if(!empty($_REQUEST['cel_playback']) && $_REQUEST['cel_playback'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','playback',true);
			} else {
				$this->FreePBX->Ucp->setSettingByGID($id,'Cel','playback',false);
			}
		}
	}

	/**
	* Hook functionality from userman when a user is deleted
	* @param {int} $id      The userman user id
	* @param {string} $display The display page name where this was executed
	* @param {array} $data    Array of data to be able to use
	*/
	public function ucpDelUser($id, $display, $ucpStatus, $data) {

	}

	/**
	* Hook functionality from userman when a user is added
	* @param {int} $id      The userman user id
	* @param {string} $display The display page name where this was executed
	* @param {array} $data    Array of data to be able to use
	*/
	public function ucpAddUser($id, $display, $ucpStatus, $data) {
		$this->ucpUpdateUser($id, $display, $ucpStatus, $data);
	}

	/**
	* Hook functionality from userman when a user is updated
	* @param {int} $id      The userman user id
	* @param {string} $display The display page name where this was executed
	* @param {array} $data    Array of data to be able to use
	*/
	public function ucpUpdateUser($id, $display, $ucpStatus, $data) {
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'user') {
			if(!empty($_POST['ucp_cel'])) {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','assigned',$_POST['ucp_cel']);
			} else {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','assigned',null);
			}
			if(!empty($_POST['cel_enable']) && $_POST['cel_enable'] == "yes") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','enable',true);
			} elseif(!empty($_POST['cel_enable']) && $_POST['cel_enable'] == "no") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','enable',false);
			} elseif(!empty($_POST['cel_enable']) && $_POST['cel_enable'] == "inherit") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','enable',null);
			}
			if(!empty($_REQUEST['cel_download']) && $_REQUEST['cel_download'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','download',true);
			} elseif(!empty($_POST['cel_download']) && $_POST['cel_download'] == "no") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','download',false);
			} elseif(!empty($_POST['cel_download']) && $_POST['cel_download'] == "inherit") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','download',null);
			}
			if(!empty($_REQUEST['cel_playback']) && $_REQUEST['cel_playback'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','playback',true);
			} elseif(!empty($_POST['cel_playback']) && $_POST['cel_playback'] == "no") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','playback',false);
			} elseif(!empty($_POST['cel_playback']) && $_POST['cel_playback'] == "inherit") {
				$this->FreePBX->Ucp->setSettingByID($id,'Cel','playback',null);
			}
		}
	}

	/**
	* get the Admin display in UCP
	* @param array $user The user array
	*/
	public function ucpConfigPage($mode, $user, $action) {
		if(empty($user)) {
			$enable = ($mode == 'group') ? true : null;
			$download = ($mode == 'group') ? true : null;
			$playback = ($mode == 'group') ? true : null;
		} else {
			if($mode == 'group') {
				$enable = $this->FreePBX->Ucp->getSettingByGID($user['id'],'Cel','enable');
				$enable = !($enable) ? false : true;
				$celassigned = $this->FreePBX->Ucp->getSettingByGID($user['id'],'Cel','assigned');
				$celassigned = !empty($celassigned) ? $celassigned : array('self');
				$download = $this->FreePBX->Ucp->getSettingByGID($user['id'],'Cel','download');
				$playback = $this->FreePBX->Ucp->getSettingByGID($user['id'],'Cel','playback');
			} else {
				$enable = $this->FreePBX->Ucp->getSettingByID($user['id'],'Cel','enable');
				$celassigned = $this->FreePBX->Ucp->getSettingByID($user['id'],'Cel','assigned');
				$download = $this->FreePBX->Ucp->getSettingByID($user['id'],'Cel','download');
				$playback = $this->FreePBX->Ucp->getSettingByID($user['id'],'Cel','playback');
			}
		}
		$celassigned = !empty($celassigned) ? $celassigned : array();


		$ausers = array();
		if($action == "showgroup" || $action == "addgroup") {
			$ausers['self'] = _("User Primary Extension");
		}
		if($action == "addgroup") {
			$celassigned = array('self');
		}
		foreach(core_users_list() as $list) {
			$ausers[$list[0]] = $list[1] . " &#60;".$list[0]."&#62;";
		}
		$html[0] = array(
			"title" => _("Call Event Logging"),
			"rawname" => "cel",
			"content" => load_view(dirname(__FILE__)."/views/ucp_config.php",array("mode" => $mode, "enabled" => $enable, "ausers" => $ausers, "celassigned" => $celassigned, "playback" => $playback,"download" => $download))
		);
		return $html;
	}

	public function genConfig() {

		$conf['cel_general_additional.conf']['general'] = array(
			'enable=yes',
			'apps=confbridge,meetme,mixmonitor,queue,stopmixmonitor,voicemail,voicemailmain',
			'events=all',
			'dateformat=%F %T'
		);

		$usegmt = ($this->FreePBX->Config->get('CDRUSEGMT') != true)?'no':'yes';

		$conf['cel_odbc.conf']['cel'] = array(
			'connection=asteriskcdrdb',
			'loguniqueid=yes',
			'table=cel',
			'usegmtime='. $usegmt,
			'#include cel_odbc_custom.conf'
		);

		return $conf;
	}

	public function writeConfig($conf){
		$this->FreePBX->WriteConfig($conf);
	}

	public function doConfigPageInit($display) {
		return true;
	}

	public function myShowPage() {
		$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
		$html = '';

		if(!version_compare($this->astver , '12', 'ge')){
			return "<div class='alert alert-danger'>"._("The CEL module requires an Asterisk version of 12.0 or higher.")."</div>";
		}
			$html.= load_view(dirname(__FILE__).'/views/page.cel_view.php', array("message" => $this->message));
	return $html;
	}

	public function cel_getreport($request,$ext = null) {

		$dateto = !empty($request['dateto']) ? filter_var($request['dateto'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : '';
		$datefrom= !empty($request['datefrom']) ? filter_var($request['datefrom'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : '';
		$source = !empty($request['source']) ? filter_var($request['source'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : '';
		$ext = !empty($request['extension']) ? filter_var($request['extension'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : $ext;
		$destination = !empty($request['destination']) ? filter_var($request['destination'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : '';
		$application = !empty($request['application']) ? filter_var($request['application'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : '';
		$sort = !empty($request['sort']) ? filter_var($request['sort'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : 'eventtime';
		$order = !empty($request['order']) ? filter_var($request['order'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) : 'DESC';
		$limit = !empty($request['limit']) ? filter_var($request['limit'], FILTER_SANITIZE_NUMBER_INT) : 100;
		$offset = !empty($request['offset']) ? filter_var($request['offset'], FILTER_SANITIZE_NUMBER_INT) : 0;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT cel.linkedid FROM cel WHERE 1";
		//$sql = "SELECT DISTINCT cel.linkedid FROM cel WHERE 1";
		$vars = array();
		if(!empty($dateto)){
			$dateto = $dateto. ' 23:59:59';
			$sql .=" AND eventtime <= :dateto";
		}
		if(!empty($datefrom)){
			$datefrom = $datefrom." 00:00:00";
			$sql .= " AND eventtime >= :datefrom";
		}
		if(!empty($source)){
			$sql .=" AND (cid_num LIKE :source OR cid_name LIKE :source)";
		}
		// this is for UCP
		if(!empty($ext)){
			$sql .=" AND (cid_num LIKE :ext OR cid_name LIKE :ext)";
		}
		if(!empty($destination)){
			$sql .= " AND exten LIKE :destination";
		}
		if(!empty($application)) {
			if($application == 'conference') {
				$sql .= " AND (eventtype = 'APP_START' OR eventtype = 'APP_END') AND appname like 'ConfBridge' OR appname like 'MeetMe' ";
			}else {
				$sql .= " AND (eventtype = 'APP_START' OR eventtype = 'APP_END') AND appname like :application ";
			}
		}
		if(!empty($sort)){
			switch($sort) {
				case 'cid_num':
				case 'exten':
				break;
				case 'eventtime':
				default:
					$sort = 'eventtime';
				break;
			}
			$sql .= " ORDER by $sort";
		} else {
			$sql .= " ORDER by eventtime";
		}
		if(!empty($order)){
			switch($order) {
				case 'asc':
				break;
				case 'desc':
				default:
					$order = 'DESC';
				break;
			}
			$sql .= " $order";
		} else {
			$sql .= " DESC";
		}


		//$limit = $limit+1;
		$sql .= " LIMIT $offset,$limit";

		$sth = $this->cdrdb->prepare($sql);

		if(!empty($dateto)){
			$sth->bindParam(":dateto", $dateto, \PDO::PARAM_STR);
		}
		if(!empty($datefrom)){
			$sth->bindParam(":datefrom", $datefrom, \PDO::PARAM_STR);
		}
		if(!empty($source)){
			$sth->bindParam(":source", $source, \PDO::PARAM_STR);
		}
		if(!empty($ext)){
			$sth->bindParam(":ext", $ext, \PDO::PARAM_STR);
		}
		if(!empty($destination)){
			$sth->bindParam(":destination", $destination, \PDO::PARAM_STR);
		}
		if(!empty($application) && $application != 'conference') {
			$sth->bindParam(":application", $application, \PDO::PARAM_STR);
		}

		$sth->execute();
		$records = $sth->fetchAll(\PDO::FETCH_COLUMN);

		/*
		if(count($records) === $limit) {
			$totalRows = $offset + ($limit - 1) + ($limit - 1);
			array_pop($records);
		}
		*/

		$sth = $this->cdrdb->prepare("SELECT FOUND_ROWS() as count");
		$sth->execute();
		$totalRows = $sth->fetchAll(\PDO::FETCH_COLUMN);

		$members = implode("','",$records);
		$sql = "SELECT cel.linkedid, cel.*, UNIX_TIMESTAMP(cel.eventtime) as eventunixtime FROM cel WHERE linkedid IN ('".$members."')";
		$sth = $this->cdrdb->prepare($sql);
		$sth->execute();
		//Grouped by linked id
		$rows = $sth->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
		$returnrows = array();
		$channels = array();
		$_SESSION['cel']['recordings'] = array();
		$rec = array();
		foreach($rows as $key => $array){
			unset($more);
			unset($mainrow);
			foreach($array as $row){
			//lets form the main row for display
				if($row['eventtype']=='CHAN_START' && $row['uniqueid'] == $row['linkedid'] ){
					$mainrow['eventtime'] = $row['eventtime'];
					$mainrow['timestamp'] = $row['eventunixtime'];
					$mainrow['cid_num'] = $row['cid_num'];
					$mainrow['exten'] = $row['exten'];
					$mainrow['channame'] = $row['channame'];
					$mainrow['uniqueid'] = $row['uniqueid'];
					$mainrow['parsedapp'] = $this->parseApplication($row['appname'], $row['extra']);
					//remove . from uniqueid
					$mainrow['id'] = str_replace(".",'_',$row['uniqueid']);
					$start = $row['eventunixtime'];
				}
				//lets calcurate the duration of the call LINKEDID_END
				if($row['eventtype']=='LINKEDID_END' &&  $row['uniqueid'] == $row['linkedid']){
					$mainrow['duration'] = $row['eventunixtime'] - $start;
				}
				// letus find out the recording file
				if($row['exten'] == 'recordcheck' && $row['eventtype']=='APP_START' &&  $row['uniqueid'] == $row['linkedid']){
					if ($row['appname'] == 'MixMonitor') {
						$args = explode(',', $row['appdata']);
						if ($args[0]) {
							$dates = explode('/',$args[0]);
							$mainrow['year'] = $dates[0];
							$mainrow['month'] = $dates[1];
							$mainrow['day'] = $dates[2];
							$recording = $dates[3];
							if($recording){
								$file = $this->directory . $dates[0] . '/' . $dates[1] . '/' . $dates[2] . '/' .$recording;
								$mainrow['file'] = '';
								if(file_exists($file)){
									$mainrow['file'] = $file;
									$_SESSION['cel']['recordings'][$row['uniqueid']] = array(
										'file' => $mainrow['file']
									);
									$rec['recordings'][$row['uniqueid']] = array(
										'file' => $mainrow['file']
									);
								}else {
									$mainrow['year'] ='';
									$mainrow['month'] = '';
									$mainrow['day'] = '';
								}
							}
						}
					}
				}
					$row['timestamp'] = $row['eventunixtime'];
					$more[] = $row;
			}
			$mainrow['moreinfo'] = $more;
			$returnrows[] = $mainrow;
		}
		return array(
			"total" => $totalRows,
			"rows" => array_reverse($returnrows),
			"recordings" => $rec
		);
	}

	private function parseApplication($name, $data) {
		$parsed = NULL;

		switch (strtolower($name)) {
			case 'confbridge':
			case 'meetme':
				$args = explode(',', $data);
				if ($args[0]) {
					$parsed = 'joined Conference (' . $args[0] . ')';
				}
				break;
			case 'mixmonitor':
				$args = explode(',', $data);
				if ($args[0]) {
					$parsed = 'started Recording';
				}
				break;
			case 'stopmixmonitor':
				$args = explode(',', $data);
				if ($args[0]) {
					$parsed = 'stopped Recording';
				}
				break;
			case 'queue':
				$args = explode(',', $data);
				if ($args[0]) {
					$parsed = 'entered Queue (' . $args[0] . ')';
				}
				break;
			case 'voicemail':
				$args = explode(',', $data);
				if ($args[0]) {
					$vm = explode('@', $args[0]);
					$parsed = 'entered Voicemail (' . $vm[0] . ')';
				}
				break;
			case 'voicemailmain':
				$args = explode(',', $data);
				if ($args[0]) {
					$vm = explode('@', $args[0]);
					$parsed = 'checked Voicemail (' . $vm[0] . ')';
				} else {
					$parsed = 'checked Voicemail';
				}
				break;
		}

		return $parsed;
	}

	/**
	 * Validate Monitor Path
	 * @param  string $file The full path to the file
	 * @return boolean       True if a valid path else false
	 */
	public function validateMonitorPath($file) {
		if (strpos($file, "..") !== false) {
			return false;
		}
		$mixmondir = $this->FreePBX->Config->get("MIXMON_DIR");
		$astspooldir = $this->FreePBX->Config->get("ASTSPOOLDIR");
		$mon_dir = $mixmondir ? $mixmondir : $astspooldir . '/monitor';
		if(!preg_match('/^'.str_replace("/","\/",$mon_dir).'/',$file)) {
			return false;
		}
		return true;
	}

	public function getAllRecords($page = 1, $orderby = 'date', $order = 'desc', $search = '', $limit = 100)
	{
		$start = ($limit * ($page - 1));
		$end = $limit;
		switch ($orderby) {
			case 'eventtype':
				$orderby = 'eventtype';
				break;
			case 'cid_name':
				$orderby = 'cid_name';
				break;
			case 'channame':
				$orderby = 'channame';
				break;
			default:
				$orderby = 'timestamp';
				break;
		}
		$order = ($order == 'desc') ? 'desc' : 'asc';
		if (!empty($search)) {
			$sql = "SELECT *, UNIX_TIMESTAMP(eventtime) As timestamp FROM " . $this->db_table . " WHERE (cid_name LIKE :search OR channame LIKE :search OR eventtype LIKE :search) ORDER by $orderby $order LIMIT $start,$end";
			$sth = $this->cdrdb->prepare($sql);
			$sth->execute(array(':search' => '%' . $search . '%'));
		} else {
			$sql = "SELECT *, UNIX_TIMESTAMP(eventtime) As timestamp FROM " . $this->db_table . " ORDER by $orderby $order LIMIT $start,$end";
			$sth = $this->cdrdb->prepare($sql);
			$sth->execute();
		}
		$calls = $sth->fetchAll(\PDO::FETCH_ASSOC);
		return $calls;
	}
}
