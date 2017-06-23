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

		$db_hash = array('mysql' => 'mysql', 'postgres' => 'pgsql');
		$dbt = !empty($dbt) ? $dbt : 'mysql';
		$db_type = $db_hash[$dbt];
		$this->db_table = !empty($db_table) ? $db_table : "cel";
		$db_name = !empty($db_name) ? $db_name : "asteriskcdrdb";
		$db_host = !empty($db_host) ? $db_host : "localhost";
		$db_port = empty($db_port) ? '' :  ';port=' . $db_port;
		$db_user = empty($db_user) ? $amp_conf['AMPDBUSER'] : $db_user;
		$db_pass = empty($db_pass) ? $amp_conf['AMPDBPASS'] : $db_pass;
		try {
			$this->cdrdb = new \DB(new \Database($db_type.':host='.$db_host.$db_port.';dbname='.$db_name.';charset=utf8',$db_user,$db_pass));
		} catch(\Exception $e) {
			throw new \Exception('Unable to connect to CDR Database using string:'.$db_type.':host='.$db_host.$db_port.';dbname='.$db_name.';charset=utf8,'.$db_user.','.$db_pass);
		}
	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}

	public function ucpDelGroup($id,$display,$data) {
	}

	public function ucpAddGroup($id, $display, $data) {
		$this->ucpUpdateGroup($id,$display,$data);
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
		$conf['cel_general_additional.conf'][] = array(
			'enable=yes',
			'apps=confbridge,meetme,mixmonitor,queue,stopmixmonitor,voicemail,voicemailmain',
			'events=all',
			'dateformat=%F %T',
		);

		return $conf;
	}

	public function writeConfig($conf){
		$this->FreePBX->WriteConfig($conf);
	}

	public function getActionBar($request) {
		if(!version_compare($this->astver , '12', 'ge')){return array();}
		$buttons = array(
			'reset' => array(
				'name' => 'reset',
				'id' => 'reset',
				'value' => _('Reset')
			),
			'submit' => array(
				'name' => 'submit',
				'id' => 'submit',
				'value' => _('Search')
			)
		);

		return $buttons;
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
		switch ($action) {
		case "getJSON":
			header('Content-Type: application/json');
			switch($_REQUEST['jdata']){
				case 'results':
					if (!empty($_REQUEST['datefrom']) && !empty($_REQUEST['dateto'])) {
						$filters['datefrom'] = $_REQUEST['datefrom'];
						$filters['dateto'] = $_REQUEST['dateto'];
					}
					if (!empty($_REQUEST['callerid'])) {
						$filters['callerid'] = $_REQUEST['callerid'];
					}
					if (!empty($_REQUEST['exten'])) {
						$filters['exten'] = $_REQUEST['exten'];
					}
					if (!empty($_REQUEST['application'])) {
						$filters['application'] = $_REQUEST['application'];
					}
					$calls = $this->getCalls($filters);
					echo json_encode($calls);
					exit();
					break;
				default:
					echo json_encode(array('error'=> 'invalid request'));
					exit();
					break;
			}
			break;
		case "playrecording":
			$this->playRecording();
			break;
		case "":
			$html.= load_view(dirname(__FILE__).'/views/search.php', array("message" => $this->message));

			break;
		case "search":
			if (!empty($_REQUEST['datefrom']) && !empty($_REQUEST['dateto'])) {
				$filters['datefrom'] = $_REQUEST['datefrom'];
				$filters['dateto'] = $_REQUEST['dateto'];
			}

			if (!empty($_REQUEST['callerid'])) {
				$filters['callerid'] = $_REQUEST['callerid'];
			}

			if (!empty($_REQUEST['exten'])) {
				$filters['exten'] = $_REQUEST['exten'];
			}

			if (!empty($_REQUEST['application'])) {
				$filters['application'] = $_REQUEST['application'];
			}

			$calls = $this->getCalls($filters);

			$html.= load_view(dirname(__FILE__).'/views/search.php', array("message" => $this->message));
			$html.= load_view(dirname(__FILE__).'/views/results.php', array("calls" => $calls, "message" => $this->message));

			break;
		}

		return $html;
	}

	/**
	* Get the Number of Pages by limit for extension
	* @param {int} $extension The Extension to lookup
	* @param {int} $limit=100 The limit of results per page
	*/
	public function getPages($extension,$search='',$limit=100) {
		if(!empty($search)) {
			//cid_num LIKE '%" . $callerid . "%' OR cid_name LIKE '%" . $callerid . "%'
			//(eventtype = 'APP_START' OR eventtype = 'APP_END') AND appname IN ('" . implode("', '", $application) . "')
			$sql = "SELECT COUNT(DISTINCT linkedid) as count from ".$this->db_table." WHERE (cid_num = :extension OR exten = :extension) AND (cid_num LIKE :search OR cid_name LIKE :search)";
			$sth = $this->cdrdb->prepare($sql);
			$sth->execute(array(':extension' => $extension, ':search' => '%'.$search.'%'));
		} else {
			$sql = "SELECT COUNT(DISTINCT linkedid) as count from ".$this->db_table." WHERE (cid_num = :extension OR exten = :extension)";
			$sth = $this->cdrdb->prepare($sql);
			$sth->execute(array(':extension' => $extension));
		}
		$res = $sth->fetch(\PDO::FETCH_ASSOC);
		$total = $res['count'];
		if(!empty($total)) {
			return ceil($total/$limit);
		} else {
			return false;
		}
	}

	public function getUCPCalls($extension,$page=1,$orderby='date',$order='desc',$search='',$limit=100) {
		$start = ($limit * ($page - 1));
		$end = $limit;
		switch($orderby) {
			case 'description':
				$orderby = 'clid';
			break;
			case 'duration':
				$orderby = 'duration';
			break;
			case 'date':
			default:
				$orderby = 'timestamp';
			break;
		}
		$order = ($order == 'desc') ? 'desc' : 'asc';
		if(!empty($search)) {
			$sql = "SELECT distinct(linkedid), ".$this->db_table.".*, UNIX_TIMESTAMP(eventtime) As timestamp FROM ".$this->db_table." WHERE (cid_num = :extension OR exten = :extension) ORDER by $orderby $order LIMIT $start,$end";
			$sth = $this->cdrdb->prepare($sql);
			$sth->execute(array(':extension' => $extension));
		} else {
			$sql = "SELECT distinct(linkedid), ".$this->db_table.".*, UNIX_TIMESTAMP(eventtime) As timestamp FROM ".$this->db_table." WHERE (cid_num = :extension OR exten = :extension) ORDER by $orderby $order LIMIT $start,$end";
			$sth = $this->cdrdb->prepare($sql);
			$sth->execute(array(':extension' => $extension));
		}
		$calls = $sth->fetchAll(\PDO::FETCH_ASSOC);
		return $calls;
	}


	public function getCalls($filters, $extension = NULL) {
		$requestingExtension = $extension;
		if(!empty($this->calls)) {
			return $this->calls;
		}
		global $amp_conf;

		include_once("crypt.php");
		$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'CorrectHorseBatteryStaple';

		$crypt = new \Crypt();

		$badcontexts = array('tc-maint');

		$sql = "SELECT DISTINCT linkedid" .
			" FROM ".$this->db_table .
			" WHERE context NOT IN ('" . implode("', '", $badcontexts) . "')" .
		($extension ? " AND (cid_num = '" . $extension . "' OR exten = '" . $extension . "')" : "");
		$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);
		$linkedids = array();
		foreach ($res as $row) {
			$linkedids[] = $row['linkedid'];
		}

		if ($filters['datefrom'] || $filters['dateto']) {
			$datefrom = (!empty($filters['datefrom']) ? $filters['datefrom'] : date('Y-m-d')) . ' 00:00:00';
			$dateto = (!empty($filters['dateto']) ? $filters['dateto'] : date('Y-m-d')) . ' 23:59:59';
			$sql = "SELECT linkedid" .
				" FROM ".$this->db_table .
				" WHERE linkedid IN ('" . implode("', '", $linkedids) . "') AND eventtime BETWEEN '" . $datefrom . "' AND '" . $dateto . "'";
			$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

			$filterlinkedids = array();
			foreach ($res as $row) {
				$filterlinkedids[] = $row['linkedid'];
			}
			$linkedids = array_intersect($linkedids, $filterlinkedids);
		}

		if ($filters['callerid']) {
			$callerid = $filters['callerid'];
			$sql = "SELECT linkedid" .
				" FROM ".$this->db_table .
				" WHERE linkedid IN ('" . implode("', '", $linkedids) . "') AND (cid_num LIKE '%" . $callerid . "%' OR cid_name LIKE '%" . $callerid . "%')";
			$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

			$filterlinkedids = array();
			foreach ($res as $row) {
				$filterlinkedids[] = $row['linkedid'];
			}
			$linkedids = array_intersect($linkedids, $filterlinkedids);
		}

		if ($filters['exten']) {
			$extension = $filters['exten'];
			$sql = "SELECT linkedid" .
				" FROM ".$this->db_table.
				" WHERE linkedid IN ('" . implode("', '", $linkedids) . "') AND exten LIKE '%" . $extension . "%'";
			$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

			$filterlinkedids = array();
			foreach ($res as $row) {
				$filterlinkedids[] = $row['linkedid'];
			}
			$linkedids = array_intersect($linkedids, $filterlinkedids);
		}

		if ($filters['application']) {
			$application = $filters['application'];
			if ($application == 'conference') {
				$application = array('confbridge', 'meetme');
			} else {
				$application = array($application);
			}
			$sql = "SELECT linkedid" .
				" FROM ".$this->db_table.
				" WHERE linkedid IN ('" . implode("', '", $linkedids) . "') AND (eventtype = 'APP_START' OR eventtype = 'APP_END') AND appname IN ('" . implode("', '", $application) . "')";
			$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

			$filterlinkedids = array();
			foreach ($res as $row) {
				$filterlinkedids[] = $row['linkedid'];
			}
			$linkedids = array_intersect($linkedids, $filterlinkedids);
		}



		$fields = array(
			'eventtype',
			'eventtime',
			'uniqueid',
			'linkedid',
			'cid_name',
			'cid_num',
			'exten',
			'context',
			'appname',
			'appdata',
			'channame',
			'peer',
			'extra',
		);

		$calls = array();

		/* Grab channels that are associated via an attended transfer */
		$sql = "SELECT extra" .
			" FROM ".$this->db_table.
			" WHERE eventtype = 'ATTENDEDTRANSFER' AND linkedid IN ('" . implode("', '", $linkedids) . "')";
		$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

		foreach ($res as $row) {
			$extra = json_decode($row['extra'], true);

			if (!in_array($extra['transferee_channel_uniqueid'], $linkedids)) {
				$linkedids[] = $extra['transferee_channel_uniqueid'];
			}
			if (!in_array($extra['channel2_uniqueid'], $linkedids)) {
				$linkedids[] = $extra['channel2_uniqueid'];
			}
		}

		$sql = "SELECT " . implode(", ", $fields) .
			", UNIX_TIMESTAMP(eventtime) As timestamp FROM ".$this->db_table.
			" WHERE linkedid IN ('" . implode("', '", $linkedids) . "')" .
			" ORDER BY id";
		$res = $this->cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

		$channels = array();
		foreach ($res as $row) {
			$extra = json_decode($row['extra'], true);

			$calls[$row['linkedid']]['records'][] = $row;

			switch ($row['eventtype']) {
			case 'CHAN_START':
				if (substr($row['channame'], 0, 6) == "Local/") {
					/* Ugh... */
					$mapname = substr($row['channame'], 0, -2);
					if (substr($row['channame'], -2, 2) == ";1") {
						if ($row['uniqueid'] != $row['linkedid']) {
							$localmaps[$mapname]['owner'] = $row['linkedid'];
						}
						$localmaps[$mapname]['one'] = $row['uniqueid'];
					} else {
						$localmaps[$mapname]['two'] = $row['uniqueid'];
					}
				}

				/* New channel! */
				$channels[$row['uniqueid']]['starttime'] = new \DateTime($row['eventtime']);
				$channels[$row['uniqueid']]['cid_num'] = $row['cid_num'];
				$channels[$row['uniqueid']]['cid_name'] = $row['cid_name'];
				$channels[$row['uniqueid']]['channel'] = $row['channame'];
				$channels[$row['uniqueid']]['extension'] = $row['exten'];
				if ($row['linkedid'] != $row['uniqueid']) {
					$channels[$row['uniqueid']]['linkedid'] = $row['linkedid'];
				}

				if ($channels[$row['linkedid']]['successor']) {
					/* Linked ID channel has a successor. */
					$channels[$row['uniqueid']]['owner'] = $channels[$row['linkedid']]['successor'];
				}

				if ($row['uniqueid'] == $row['linkedid']) {
					$calls[$row['uniqueid']]['starttime'] = new \DateTime($row['eventtime']);
					$calls[$row['uniqueid']]['src'] = $this->channelCallerID($channels[$row['uniqueid']]);
					$calls[$row['uniqueid']]['extension'] = $row['exten'];
				}
				break;
			case 'CHAN_END':
				$channels[$row['uniqueid']]['endtime'] = new \DateTime($row['eventtime']);

				if ($row['uniqueid'] == $row['linkedid']) {
					$callid = $row['uniqueid'];

					$call = $calls[$callid];

					$calls[$row['uniqueid']]['endtime'] = new \DateTime($row['eventtime']);

					if ($channels[$row['uniqueid']]['hanguptime']) {
						$call['actions'][] = array(
							'type' => 'hangup',
							'starttime' => $channels[$row['uniqueid']]['hanguptime'],
							'stoptime' => $channels[$row['uniqueid']]['endtime'],
							'src' =>  $this->channelCallerID($channels[$row['uniqueid']]),
						);
					}

					$calls[$callid] = $call;
				} else {
					$callid = $row['linkedid'];

					$call = $calls[$callid];

					if (substr($channels[$row['uniqueid']]['channel'], 0, 12) == 'DAHDI/pseudo') {
						continue;
					}

					if (($localmap = $localmaps[substr($channels[$row['uniqueid']]['channel'], 0, -2)]) && $localmap['owner'] == $row['linkedid']) {
						continue;
					}

					$call['actions'][] = array(
						'type' => 'call',
						'starttime' => $channels[$row['uniqueid']]['starttime'],
						'stoptime' => ($channels[$row['uniqueid']]['answertime'] ? $channels[$row['uniqueid']]['answertime'] : $channels[$row['uniqueid']]['endtime']),
						'src' =>  $this->channelCallerID($channels[($channels[$row['uniqueid']]['owner'] ? $channels[$row['uniqueid']]['owner'] : $callid)]),
						'dest' =>  $this->channelCallerID($channels[$row['uniqueid']]),
						'status' => $channels[$callid]['dialstatus'],
					);

					if ($channels[$row['uniqueid']]['answertime']) {
						$call['actions'][] = array(
							'type' => 'answer',
							'starttime' => $channels[$row['uniqueid']]['answertime'],
							'stoptime' => $channels[$row['uniqueid']]['hanguptime'],
							'src' =>  $this->channelCallerID($channels[$row['uniqueid']]),
							'status' => $channels[$callid]['dialstatus'],
						);

						if ($channels[$row['uniqueid']]['hanguptime']) {
							$call['actions'][] = array(
								'type' => 'hangup',
								'starttime' => $channels[$row['uniqueid']]['hanguptime'],
								'stoptime' => $channels[$row['uniqueid']]['endtime'],
								'src' =>  $this->channelCallerID($channels[$row['uniqueid']]),
							);
						}
					}

					$calls[$callid] = $call;
				}
				break;
			case 'LINKEDID_END':
				/* Override the endtime of the call. */
				$calls[$row['linkedid']]['endtime'] = new \DateTime($row['eventtime']);
				break;
			case 'ANSWER':
				$channels[$row['uniqueid']]['answertime'] = new \DateTime($row['eventtime']);
				/* Update the Caller ID, because it may have changed. */
				$channels[$row['uniqueid']]['cid_num'] = $row['cid_num'];
				$channels[$row['uniqueid']]['cid_name'] = $row['cid_name'];
				break;
			case 'HANGUP':
				$channels[$row['uniqueid']]['hanguptime'] = new \DateTime($row['eventtime']);
				$channels[$row['uniqueid']]['hangupcause'] = $extra['hangupcause'];
				if ($extra['dialstatus']) {
					$channels[$row['uniqueid']]['dialstatus'] = $extra['dialstatus'];
				}
				break;
			case 'BRIDGE_ENTER':
				if (($localmap = $localmaps[substr($row['channame'], 0, -2)]) && $row['uniqueid'] == $localmap['two']) {
					$uniqueid = $localmap['owner'];
				} else {
					$uniqueid = $row['uniqueid'];
				}
				$bridges[$extra['bridge_id']][$uniqueid]['entertime'] = new \DateTime($row['eventtime']);
				break;
			case 'BRIDGE_EXIT':
				if (($localmap = $localmaps[substr($row['channame'], 0, -2)]) && $row['uniqueid'] == $localmap['two']) {
					$uniqueid = $localmap['owner'];
				} else {
					$uniqueid = $row['uniqueid'];
				}
				$bridges[$extra['bridge_id']][$uniqueid]['exittime'] = new \DateTime($row['eventtime']);
				break;
			case 'ATTENDEDTRANSFER':
				if ($row['uniqueid'] == $row['linkedid'] || $channels[$row['linkedid']]['successor'] == $row['uniqueid']) {
					/* The owner (or successor) of the channel is giving up control to the transferee. */
					$channels[$row['linkedid']]['successor'] = $extra['transferee_channel_uniqueid'];
				}

				$calls[$row['linkedid']]['actions'][] = array(
					'type' => 'transfer',
					'transfertype' => 'attended',
					'starttime' => new \DateTime($row['eventtime']),
					'stoptime' => new \DateTime($row['eventtime']),
					'transferer' =>  $this->channelCallerID($channels[$row['uniqueid']]),
					'transferee' =>  $this->channelCallerID($channels[$extra['transferee_channel_uniqueid']]),
					'dest' =>  $this->channelCallerID($channels[$extra['transfer_target_channel_uniqueid']]),
				);
				break;
			case 'BLINDTRANSFER':
				if ($row['uniqueid'] == $row['linkedid'] || $channels[$row['linkedid']]['successor'] == $row['uniqueid']) {
					/* The owner (or successor) of the channel is giving up control to the transferee. */
					$channels[$row['linkedid']]['successor'] = $extra['transferee_channel_uniqueid'];
				}

				$calls[$row['linkedid']]['actions'][] = array(
					'type' => 'transfer',
					'transfertype' => 'blind',
					'starttime' => new \DateTime($row['eventtime']),
					'stoptime' => new \DateTime($row['eventtime']),
					'transferer' =>  $this->channelCallerID($channels[$row['uniqueid']]),
					'transferee' =>  $this->channelCallerID($channels[$extra['transferee_channel_uniqueid']]),
					'dest' => 'Extension ' . $extra['extension'],
				);
				break;
			case 'APP_START':
				$channels[$row['uniqueid']]['apps'][] = array(
					'appname' => $row['appname'],
					'appdata' => $row['appdata'],
					'starttime' => new \DateTime($row['eventtime']),
				);
				break;
			case 'APP_END':
				/* Can two applications be executing on a channel at once?  I don't think so. */
				$channels[$row['uniqueid']]['apps'][count($channels[$row['uniqueid']]['apps']) - 1]['stoptime'] = new \DateTime($row['eventtime']);
				break;
			case 'PARK_START':
				$calls[$row['linkedid']]['actions'][] = array(
					'type' => 'park',
					'starttime' => new \DateTime($row['eventtime']),
					'stoptime' => new \DateTime($row['eventtime']),
					'src' => $this->channelCallerID($channels[$row['uniqueid']]),
					'dest' => $extra['parking_lot'],
				);
				break;
			case 'PARK_END':
				$calls[$row['linkedid']]['actions'][] = array(
					'type' => 'unpark',
					'starttime' => new \DateTime($row['eventtime']),
					'stoptime' => new \DateTime($row['eventtime']),
					'src' => $this->channelCallerID($channels[$row['uniqueid']]),
					'reason' => $extra['reason'],
				);
				break;
			default:
				break;
			}
		}

		foreach ($channels as $uniqueid => $channel) {
			if ($channel['linkedid']) {
				$callid = $channel['linkedid'];

				$call = $calls[$callid];
			} else {
				$callid = $uniqueid;

				$call = $calls[$callid];

				if(!empty($bridges) && is_array($bridges)) {
					foreach ($bridges as $bridgeid => $bridge) {
						if (isset($bridge[$callid])) {
							$action = array(
								'type' => 'bridge',
								'starttime' => $bridge[$callid]['entertime'],
								'stoptime' => $bridge[$callid]['exittime'],
								'bridge' => $bridgeid,
								'members' => array(),
							);

							foreach ($bridge as $linkid => $link) {
								if ($linkid == $callid) {
									continue;
								}

								$channame = substr($channels[$linkid]['channel'], 0, -2);
								if (isset($localmaps[$channame]) && ($localmap = $localmaps[$channame]) && $localmap['owner'] == $channels[$linkid]['linkedid']) {
									continue;
								}

								if ($action['stoptime'] > $link['entertime'] && $link['exittime'] > $action['starttime']) {
									$member = array(
										'dest' => $channels[$linkid],
										'entertime' => ($link['entertime'] < $action['starttime'] ? $action['starttime'] : $link['entertime']),
										'exittime' => ($link['exittime'] > $action['stoptime'] ? $action['stoptime'] : $link['exittime']),
									);

									$action['members'][] = $member;
								}

							}

							if (count($action['members']) > 0) {
								$call['actions'][] = $action;
							}
						}
					}
				}
			}

			if (is_array($channel['apps'])) {
				foreach ($channel['apps'] as $app) {
					if ($app['appname'] == 'MixMonitor') {
						$args = explode(',', $app['appdata']);
						if ($args[0]) {
							$mon_dir = !empty($amp_conf['MIXMON_DIR']) ? $amp_conf['MIXMON_DIR'] : $amp_conf['ASTSPOOLDIR'] . '/monitor';
							$recording = $mon_dir . '/' . $args[0];
							dbug($recording);
							$recordingfile = $crypt->encrypt($recording, $REC_CRYPT_PASSWORD);
							$call['recordings'][$recordingfile] = file_exists($recording);
						}
					}

					if (($dest = $this->parseApplication($app['appname'], $app['appdata']))) {
						$call['actions'][] = array(
							'type' => 'application',
							'starttime' => $app['starttime'],
							'stoptime' => $app['stoptime'],
							'src' =>  $this->channelCallerID($channel),
							'dest' => $dest,
						);
					}
				}
			}

			$call['actions'] = is_array($call['actions']) ? $call['actions'] : array();
			foreach($call['actions'] as &$c) {
				if(!is_object($c['starttime'])) {
					$c['starttime'] = new \DateTime($c['starttime']);
				}
				if(!is_object($c['stoptime'])) {
					$c['stoptime'] = new \DateTime($c['stoptime']);
				}
				$st = $c['starttime']->format("U");
				$et = $c['stoptime']->format("U");
				$c['duration'] = $et - $st;
				$c['timestamp'] = $st;
				$c['detail'] = $c['src'] . " " . $c['dest'];
			}

			$calls[$callid] = $call;
		}

		foreach ($calls as $callid => $call) {
			$call['actions'] = is_array($call['actions']) ? $call['actions'] : array();
			usort($call['actions'], function($a, $b) {
				if ($a['starttime'] == $b['starttime']) {
					if ($b['type'] == 'transfer') {
						/* Transfer should come before others. */
						return 1;
					}

					return 0;
				}

				return $a['starttime'] < $b['starttime'] ? -1 : 1;
			});
			if(!is_object($call['endtime'])) {
				$call['endtime'] = new \DateTime($call['endtime']);
			}
			if(!is_object($call['starttime'])) {
				$call['starttime'] = new \DateTime($call['starttime']);
			}
			$call['duration'] = $call['endtime']->format('U') - $call['starttime']->format('U');
			$call['timestamp'] = $call['starttime']->format('U');
			$call['requestingExtension'] = $requestingExtension;
			$calls[$callid] = $call;
		}
		$this->calls = $calls;
		return $this->calls;
	}

	private function channelCallerID($channel) {
		return ($channel['cid_name'] ? $channel['cid_name'] : 'Unknown') . ' <' . $channel['cid_num'] . '>';
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

	function playRecording() {
		include_once("audio.php");
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

	/**
	 * Tear apart the file name to get our correct path
	 * @param  string $recordingFile The recording file
	 * @return string                The full path
	 */
	private function processPath($recordingFile) {
		if(empty($recordingFile)) {
			return '';
		}
		$spool = $this->FreePBX->Config->get('ASTSPOOLDIR');
		$mixmondir = $this->FreePBX->Config->get('MIXMON_DIR');
		$rec_parts = explode('-',$recordingFile);
		$fyear = substr($rec_parts[3],0,4);
		$fmonth = substr($rec_parts[3],4,2);
		$fday = substr($rec_parts[3],6,2);
		$monitor_base = $mixmondir ? $mixmondir : $spool . '/monitor';
		$recordingFile = "$monitor_base/$fyear/$fmonth/$fday/" . $recordingFile;
		//check to make sure the file size is bigger than 44 bytes (header size)
		if(file_exists($recordingFile) && is_readable($recordingFile) && filesize($recordingFile) > 44) {
			return $recordingFile;
		}
		return '';
	}
}
