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
//foreach ($channels as $uniqueid => $channel) {
foreach ($calls as $callid => $call) {
	$html.= '<tr>';
	$html.= '<td>';
	$html.= $call['starttime'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= $call['endtime'];
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
			$html.= 'Dialed ' . $action['dest'] . ' (' . $action['starttime'] . ' - ' . $action['stoptime'] . ')';
			$html.= '</td></tr>';
			break;
		case 'bridge':
			$html.= '<tr><td>';
			$html.= 'Joined Bridge ' . $action['bridge'] . ' (' . $action['starttime'] . ' - ' . $action['stoptime'] . ')';
			$html.= '</td></tr>';
			foreach ($action['members'] as $member) {
				$html.= '<tr><td>';
				$html.= 'Spoke to ' . $member['dest'] . ' (' . $member['entertime'] . ' - ' . $member['exittime'] . ')';
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
