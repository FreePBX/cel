<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2014 Schmooze Com Inc.
//
namespace FreePBX\modules;
class Cel extends \FreePBX_Helpers implements \BMO {
	private $message = '';

	public function __construct($freepbx = null) {
		$this->db = $freepbx->Database;
		$this->freepbx = $freepbx;
	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}

	public function doConfigPageInit($display) {
		return true;
	}

	public function myShowPage() {
		global $cdrdb;
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
		$badcontexts = array('tc-maint');

		$sql = "SELECT " . implode(", ", $fields) . " FROM cel WHERE context NOT IN ('" . implode("', '", $badcontexts) . "')";
		$res = $cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

		$channels = array();
		foreach ($res as $row) {
			$extra = json_decode($row['extra'], true);

			switch ($row['eventtype']) {
			case 'CHAN_START':
				/* New channel! */
				$channels[$row['uniqueid']] = array(
					'starttime' => $row['eventtime'],
					'cid_num' => $row['cid_num'],
					'channel' => $row['channame'],
				);
				/* Do we need channel mappings? */
				$chanmap[$row['channame']] = $row['uniqueid'];
				break;
			case 'ANSWER':
				$channels[$row['uniqueid']]['answertime'] = $row['eventtime'];
				break;
			case 'BRIDGE_ENTER':
				$bridges[$extra['bridge_id']][$row['uniqueid']] = array(
					'entertime' => $row['eventtime'],
				);
				break;
			case 'BRIDGE_EXIT':
				$bridges[$extra['bridge_id']][$row['uniqueid']]['exittime'] = $row['eventtime'];
				break;
			case 'BLINDTRANSFER':
			case 'ATTENDEDTRANSFER':
				break;
			case 'HANGUP':
				$channels[$row['uniqueid']]['hanguptime'] = $row['eventtime'];
				break;
			case 'CHAN_END':
				$channels[$row['uniqueid']]['endtime'] = $row['eventtime'];
				break;
			}
		}

		$html .= load_view(dirname(__FILE__).'/views/records.php', array("channels" => $channels, "bridges" => $bridges, "message" => $this->message));

		return $html;
	}
}
