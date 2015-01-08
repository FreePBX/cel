<?php
$html = '';
$html.= form_open($_SERVER['REQUEST_URI']);
echo '<pre>';
var_dump($channels);
var_dump($bridges);
echo '</pre>';

$html.= '<table class="table-striped">';
$html.= '<th>Start Time</th>';
$html.= '<th>End Time</th>';
$html.= '<th>Caller</th>';
$html.= '<th>Callees</th>';
foreach ($channels as $uniqueid => $channel) {
	$html.= '<tr>';
	$html.= '<td>';
	$html.= $channel['starttime'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= $channel['endtime'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= $channel['cid_num'];
	$html.= '</td>';
	$html.= '<td>';
	$html.= '<table>';
	foreach ($bridges as $bridgeid => $bridge) {
		if (isset($bridge[$uniqueid])) {
			$html.= '<tr><td>Bridge ' . $bridgeid . '</td></tr>';
			foreach ($bridge as $linkedid => $link) {
				$html.= '<tr><td>' . $link['entertime'] . ' - ' . $link['exittime'] . ' (' . $channels[$linkedid]['cid_num'] . ')' . '</td></tr>';
			}
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
