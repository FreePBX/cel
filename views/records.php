<?php
$html = '';
$html.= form_open($_SERVER['REQUEST_URI']);
echo '<pre>';
var_dump($calls);
echo '</pre>';

$html.= '<table class="table-striped">';
$html.= '<th>Time</th>';
$html.= '<th>Duration</th>';
$html.= '<th>Caller</th>';
$html.= '<th>Exten</th>';
$html.= '<th>Detail</th>';
foreach ($calls as $callid => $call) {
	$html.= '<tr>';
	$html.= '<td>';
	$html.= cel_format_date($call['starttime']);
	$html.= '</td>';
	$html.= '<td>';
	$html.= cel_format_interval($call['starttime'], $call['endtime']);
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
}

$html.= '<table>';

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
