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
				$channels[$row['uniqueid']]['starttime'] = new \DateTime($row['eventtime']);
				$channels[$row['uniqueid']]['cid_num'] = $row['cid_num'];
				$channels[$row['uniqueid']]['channel'] = $row['channame'];
				if ($row['linkedid'] != $row['uniqueid']) {
					$channels[$row['uniqueid']]['linkedid'] = $row['linkedid'];
				}
				break;
			case 'ANSWER':
				$channels[$row['uniqueid']]['answertime'] = new \DateTime($row['eventtime']);
				break;
			case 'BRIDGE_ENTER':
				$bridges[$extra['bridge_id']][$row['uniqueid']]['entertime'] = new \DateTime($row['eventtime']);
				break;
			case 'BRIDGE_EXIT':
				$bridges[$extra['bridge_id']][$row['uniqueid']]['exittime'] = new \DateTime($row['eventtime']);
				break;
			case 'BLINDTRANSFER':
			case 'ATTENDEDTRANSFER':
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
				break;
			case 'LINKEDID_END':
				/* This event doesn't really have useful information. */
				break;
			}
		}

		foreach ($channels as $uniqueid => $channel) {
			if ($channel['linkedid']) {
				/* This channel is part of another call. */
				$callid = $channel['linkedid'];

				$call = $calls[$callid];
				$call['actions'][] = array(
					'type' => 'dial',
					'starttime' => $channel['starttime'],
					'stoptime' => $channel['endtime'],
					'dest' => $channel['cid_num'],
					'status' => $channels[$callid]['dialstatus'],
				);
			} else {
				$callid = $uniqueid;

				$call = array();
				$call['starttime'] = $channel['starttime'];
				$call['endtime'] = $channel['endtime'];
				$call['cid_num'] = $channel['cid_num'];

				foreach ($bridges as $bridgeid => $bridge) {
					if (isset($bridge[$uniqueid])) {
						$action = array(
							'type' => 'bridge',
							'starttime' => $bridge[$uniqueid]['entertime'],
							'stoptime' => $bridge[$uniqueid]['exittime'],
							'bridge' => $bridgeid,
						);

						foreach ($bridge as $linkid => $link) {
							if ($linkid == $uniqueid) {
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

						$call['actions'][] = $action;
					}
				}
			}

			$calls[$callid] = $call;
		}

		foreach ($calls as $callid => $call) {
			usort($call['actions'], function($a, $b) {
				return $a['starttime'] < $b['starttime'] ? -1 : 1;
			});

			$calls[$callid] = $call;
		}

		$html .= load_view(dirname(__FILE__).'/views/records.php', array("channels" => $channels, "bridges" => $bridges, "calls" => $calls, "message" => $this->message));

		return $html;
	}
}
