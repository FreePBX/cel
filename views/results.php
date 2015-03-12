<?php
$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'CorrectHorseBatteryStaple';

$html = '';
$html.= form_open($_SERVER['REQUEST_URI']);

$html.= '<h3>Search returned ' . count($calls) . ' calls</h3>';

$html.= '<table class="table-striped">';
$html.= '<th></th>';
$html.= '<th>Time</th>';
$html.= '<th>Duration</th>';
$html.= '<th>Recordings</th>';
$html.= '<th>Caller</th>';
$html.= '<th>Dialed #</th>';
$html.= '<th>Detail</th>';
$row = 0;
foreach ($calls as $callid => $call) {
	$row++;
	$html.= '<tr class="call">';
	$html.= '<td>+</td>';
	$html.= '<td>';
	$html.= cel_format_date($call['starttime']);
	$html.= '</td>';
	$html.= '<td>';
	$html.= cel_format_interval($call['starttime'], $call['endtime']);
	$html.= '</td>';
	$html.= '<td>';
	foreach ($call['recordings'] as $recording => $exists) {
		if ($exists) {
			$html.= '<div class="recording">';
			$crypt = new Crypt();
			// Encrypt the complete file
			$audio = urlencode($crypt->encrypt($recording, $REC_CRYPT_PASSWORD));
			$recurl=$_SERVER['SCRIPT_NAME']."?quietmode=1&display=cel&action=playrecording&filename=$audio";
			$html.= "<a href=\"#\" onClick=\"javascript:recording_play($row,'$recurl','$callid'); return false;\"><img src=\"assets/cdr/images/cdr_sound.png\" alt=\"Call recording\" /></a>";
			$html.= '<div id="playback-'.$row.'" class="playback" style="display:none;">
				<div id="jquery_jplayer_'.$row.'" class="jp-jplayer"></div>
				<div id="jp_container_'.$row.'" class="jp-audio">
					<div class="jp-type-single">
						<div class="jp-gui jp-interface">
							<ul class="jp-controls">
								<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
								<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
								<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
								<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
								<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
								<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
							</ul>
							<div class="jp-progress">
								<div class="jp-seek-bar">
									<div class="jp-play-bar"></div>
								</div>
							</div>
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>
							<div class="jp-time-holder">
								<div class="jp-current-time"></div>
								<div class="jp-duration"></div>
								<ul class="jp-toggles">
									<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
									<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
								</ul>
							</div>
						</div>
						<div class="jp-details">
							<ul>
								<li><span class="jp-title"></span></li>
							</ul>
						</div>
						<div class="jp-no-solution">
							<span>Update Required</span>
							To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
						</div>
					</div>
				</div>
			</div>';
			$html.= '</div>';
		} else {
			$html.= "<p style='color:red'>Archived</p>";
		}
	}
	$html.= '</td>';
	$html.= '<td>';
	$html.= $call['src'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= $call['extension'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= '<table>';
	foreach ($call['actions'] as $action) {
		switch ($action['type']) {
		case 'call':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['src'] . ' called ' . $action['dest'] . ($action['status'] == 'NOANSWER' ? ' [No Answer]' : '');
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'answer':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['src'] . ' answered';
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'hangup':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['src'] . ' hung up';
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'transfer':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['transferer'] . ' transferred [' . $action['transfertype'] . '] ' . $action['transferee'] . ' to ' . $action['dest'];
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'application':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['src'] . ' ' . $action['dest'];
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'park':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['src'] . ' parked in lot ' . $action['dest'];
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'unpark':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= cel_format_date($action['starttime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= cel_format_interval($action['starttime'], $action['stoptime']);
			$html.= '</td>';
			$html.= '<td>';
			$html.= $action['src'] . ' unparked [' . $action['reason'] . ']';
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'bridge':
/*
			foreach ($action['members'] as $member) {
				$html.= '<tr>';
				$html.= '<td>';
				$html.= cel_format_date($member['entertime']);
				$html.= '</td>';
				$html.= '<td>';
				$html.= cel_format_interval($action['entertime'], $action['exittime']);
				$html.= '</td>';
				$html.= '<td>';
				$html.= 'Bridged to ' . $member['dest'];
				$html.= '</td>';
				$html.= '</tr>';
			}
*/
			break;
		}
	}
	$html.= '</table>';
	$html.= '</td>';
	$html.= '</tr>';
	$html.= '<tr style="display:none;" class="cel">';
	$html.= '<td colspan="8">';
	$html.= '<table class="table-striped">';
	$celcols = array(
		'eventtime' => 'Timestamp',
		'eventtype' => 'Event Type',
		'uniqueid' => 'Unique ID',
		'linkedid' => 'Linked ID',
		'cid_name' => 'CID Name',
		'cid_num' => 'CID Num',
		'exten' => 'Exten',
		'context' => 'Context',
		'channame' => 'Channel',
	);
	$html.= '<tr>';
	foreach ($celcols as $coldesc) {
		$html.= '<th>';
		$html.= $coldesc;
		$html.= '</th>';
	}
	$html.= '</tr>';

	foreach ($call['records'] as $record) {
		$html.= '<tr>';
		foreach ($celcols as $colkey => $coldesc) {
			$html.= '<td>';
			$html.= $record[$colkey];
			$html.= '</td>';
		}
		$html.= '</tr>';
	}
	$html.= '</table>';
	$html.= '</td>';
	$html.= '</tr>';
}

$html.= '</table>';

$html.= '<script>
$(function() {
	$("tr.call").click(function(event) {
		if ($(event.target).parents(".recording").size() > 0) {
			return false;
		}

		$visible = $(this).next("tr.cel").is(":visible");
		$("tr.cel").hide();
		$("tr.call").find("td:first").html("+");

		if (!$visible) {
			$(this).find("td:first").html("-");
			$(this).next("tr.cel").show();
		}
	});
});
</script>';

$html.= form_close();

echo $html;

function cel_format_date($date) {
	if ($date) {
		return $date->format('Y-m-d H:i:s');
	}

	return '';
}

function cel_format_interval($start, $stop) {
	if (($interval = date_diff($start, $stop))) {
		return $interval->format('%H:%I:%S');
	}

	return '00:00:00';
}

?>
