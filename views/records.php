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
	$html.= $call['starttime']->format('Y-m-d H:i:s');
	$html.= '</td>';
	$html.= '<td>';
	$interval = date_diff($call['starttime'], $call['endtime']);
	$html.= $interval->format('%H:%I:%S');
	$html.= '</td>';
	$html.= '<td>';
	$html.= $call['cid_num'];
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
			$html.= $action['starttime']->format('Y-m-d H:i:s');
			$html.= '</td>';
			$html.= '<td>';
			$interval = date_diff($action['starttime'], $action['stoptime']);
			$html.= '(' . $interval->format('%H:%I:%S') . ')';
			$html.= '</td>';
			$html.= '<td>';
			$html.= 'Called ' . $action['dest'] . ($action['status'] == 'NOANSWER' ? ' [No Answer]' : '');
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'transfer':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= $action['starttime']->format('Y-m-d H:i:s');
			$html.= '</td>';
			$html.= '<td>';
			$interval = date_diff($action['starttime'], $action['stoptime']);
			$html.= '(' . $interval->format('%H:%I:%S') . ')';
			$html.= '</td>';
			$html.= '<td>';
			$html.= 'Transferred [' . $action['transfertype'] . '] to ' . $action['dest'];
			$html.= '</td>';
			$html.= '</tr>';
			break;
		case 'bridge':
			$html.= '<tr>';
			$html.= '<td>';
			$html.= $action['starttime']->format('Y-m-d H:i:s');
			$html.= '</td>';
			$html.= '<td>';
			$interval = date_diff($action['starttime'], $action['stoptime']);
			$html.= '(' . $interval->format('%H:%I:%S') . ')';
			$html.= '</td>';
			$html.= '<td>';
			$html.= 'Joined Bridge ' . $action['bridge'];
			$html.= '</td>';
			$html.= '</tr>';
			foreach ($action['members'] as $member) {
				$html.= '<tr>';
				$html.= '<td>';
				$html.= $member['entertime']->format('Y-m-d H:i:s');
				$html.= '</td>';
				$html.= '<td>';
				$interval = date_diff($member['entertime'], $member['exittime']);
				$html.= '(' . $interval->format('%H:%I:%S') . ')';
				$html.= '</td>';
				$html.= '<td>';
				$html.= 'Bridged to ' . $member['dest'];
				$html.= '</td>';
				$html.= '</tr>';
			}
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

?>
