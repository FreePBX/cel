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

		$sql = "SELECT " . implode(", ", $fields) . " FROM cel WHERE context NOT IN ('" . implode("', '", $badcontexts) . "') ORDER BY id";
		$res = $cdrdb->getAll($sql, DB_FETCHMODE_ASSOC);

		$channels = array();
		foreach ($res as $row) {
			$extra = json_decode($row['extra'], true);

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
				$channels[$row['uniqueid']]['channel'] = $row['channame'];
				$channels[$row['uniqueid']]['extension'] = $row['exten'];
				if ($row['linkedid'] != $row['uniqueid']) {
					$channels[$row['uniqueid']]['linkedid'] = $row['linkedid'];
				}

				if ($row['uniqueid'] == $row['linkedid']) {
					$calls[$row['uniqueid']]['starttime'] = new \DateTime($row['eventtime']);
					$calls[$row['uniqueid']]['cid_num'] = $row['cid_num'];
					$calls[$row['uniqueid']]['extension'] = $row['exten'];
				}
				break;
			case 'ANSWER':
				$channels[$row['uniqueid']]['answertime'] = new \DateTime($row['eventtime']);
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
			case 'BLINDTRANSFER':
				$calls[$extra['transferee_channel_uniqueid']]['actions'][] = array(
					'type' => 'transfer',
					'transfertype' => 'blind',
					'starttime' => new \DateTime($row['eventtime']),
					'stoptime' => new \DateTime($row['eventtime']),
					'dest' => $extra['extension'],
				);
				break;
			case 'ATTENDEDTRANSFER':
				$calls[$extra['transferee_channel_uniqueid']]['actions'][] = array(
					'type' => 'transfer',
					'transfertype' => 'attended',
					'starttime' => new \DateTime($row['eventtime']),
					'stoptime' => new \DateTime($row['eventtime']),
					'dest' => $channels[$extra['transfer_target_channel_uniqueid']]['cid_num'],
				);
				break;
			case 'HANGUP':
				$channels[$row['uniqueid']]['hanguptime'] = new \DateTime($row['eventtime']);
				$channels[$row['uniqueid']]['hangupcause'] = $extra['hangupcause'];
				if ($extra['dialstatus']) {
					$channels[$row['uniqueid']]['dialstatus'] = $extra['dialstatus'];
				}
				break;
			case 'CHAN_END':
				$channels[$row['uniqueid']]['endtime'] = new \DateTime($row['eventtime']);

				if ($row['uniqueid'] == $row['linkedid']) {
					$calls[$row['uniqueid']]['endtime'] = new \DateTime($row['eventtime']);
				}
				break;
			case 'LINKEDID_END':
				/* This event doesn't really have useful information. */
				break;
			}
		}

		foreach ($channels as $uniqueid => $channel) {
			if ($channel['linkedid']) {
				if (($localmap = $localmaps[substr($channel['channel'], 0, -2)]) && $localmap['owner'] == $channel['linkedid']) {
					continue;
				}

				if (!$channel['answertime']) {
					continue;
				}

				/* This channel is part of another call. */
				$callid = $channel['linkedid'];

				$call = $calls[$callid];

				$call['actions'][] = array(
					'type' => 'call',
					'starttime' => $channel['starttime'],
					'stoptime' => $channel['endtime'],
					'dest' => $channel['cid_num'],
					'status' => $channels[$callid]['dialstatus'],
				);
			} else {
				$callid = $uniqueid;

				$call = $calls[$callid];

				foreach ($bridges as $bridgeid => $bridge) {
					if (isset($bridge[$callid])) {
						$action = array(
							'type' => 'bridge',
							'starttime' => $bridge[$callid]['entertime'],
							'stoptime' => $bridge[$callid]['exittime'],
							'bridge' => $bridgeid,
						);

						foreach ($bridge as $linkid => $link) {
							if ($linkid == $callid) {
								continue;
							}

							if (($localmap = $localmaps[substr($channels[$linkid]['channel'], 0, -2)]) && $localmap['owner'] == $channels[$linkid]['linkedid']) {
								continue;
							}

							if ($action['stoptime'] > $link['entertime'] && $link['exittime'] > $action['starttime']) {
								$member = array(
									'dest' => $channels[$linkid]['cid_num'],
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

			$calls[$callid] = $call;
		}

		foreach ($calls as $callid => $call) {
			usort($call['actions'], function($a, $b) {
				if ($a['starttime'] == $b['starttime']) {
					return 0;
				}

				return $a['starttime'] < $b['starttime'] ? -1 : 1;
			});

			$calls[$callid] = $call;
		}

		$html .= load_view(dirname(__FILE__).'/views/records.php', array("channels" => $channels, "bridges" => $bridges, "calls" => $calls, "message" => $this->message));

		return $html;
	}
}
