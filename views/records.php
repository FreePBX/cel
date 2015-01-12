<?php
$html = '';
$html.= form_open($_SERVER['REQUEST_URI']);
echo '<pre>';
var_dump($channels);
var_dump($bridges);
var_dump($calls);
echo '</pre>';

$html.= '<table class="table-striped">';
$html.= '<th>Start Time</th>';
$html.= '<th>End Time</th>';
$html.= '<th>Caller</th>';
$html.= '<th>Detail</th>';
foreach ($calls as $callid => $call) {
	$html.= '<tr>';
	$html.= '<td>';
	$html.= $call['starttime']->format('Y-m-d H:i:s');
	$html.= '</td>';
	$html.= '<td>';
	$html.= $call['endtime']->format('Y-m-d H:i:s');
	$html.= '</td>';
	$html.= '<td>';
	$html.= $call['cid_num'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= '<table>';
	foreach ($call['actions'] as $action) {
		switch ($action['type']) {
		case 'dial':
			$html.= '<tr><td>';
			//$interval = date_diff($action['starttime'], $action['stoptime']);
			//$html.= $interval->format('%H:%I:%S');
			$html.= 'Dialed ' . $action['dest'] . ($action['status'] == 'NOANSWER' ? ' [No Answer]' : '') . ' (' . $action['starttime']->format('Y-m-d H:i:s') . ' - ' . $action['stoptime']->format('Y-m-d H:i:s') . ')';
			$html.= '</td></tr>';
			break;
		case 'bridge':
			$html.= '<tr><td>';
			$html.= 'Joined Bridge ' . $action['bridge'] . ' (' . $action['starttime']->format('Y-m-d H:i:s') . ' - ' . $action['stoptime']->format('Y-m-d H:i:s') . ')';
			$html.= '</td></tr>';
			foreach ($action['members'] as $member) {
				$html.= '<tr><td>';
				$html.= 'Spoke to ' . $member['dest'] . ' (' . $member['entertime']->format('Y-m-d H:i:s') . ' - ' . $member['exittime']->format('Y-m-d H:i:s') . ')';
				$html.= '</td></tr>';
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
