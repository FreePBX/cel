<?php
$html = '';
$html.= form_open($_SERVER['REQUEST_URI'], 'class="fpbx-submit"');
$html.= form_hidden('action', 'search');

$html.= '<h2>Search</h2>';

$table = new CI_Table;

$label = fpbx_label(_('Date Range'), _('Date range of call'));
$table->add_row(form_checkbox('searchdate', 1, $_REQUEST['searchdate']), $label, form_input('datefrom', $_REQUEST['datefrom'] ? $_REQUEST['datefrom'] : date('Y-m-d'), 'class="datepicker"'), form_input('dateto', $_REQUEST['dateto'] ? $_REQUEST['dateto'] : date('Y-m-d'), 'class="datepicker"'));

$label = fpbx_label(_('Caller ID'), _('Caller ID of a call participant'));
$table->add_row(form_checkbox('searchcallerid', 1, $_REQUEST['searchcallerid']), $label, form_input('callerid', $_REQUEST['callerid']));

$label = fpbx_label(_('Dialed Number'), _('Extension or DID dialed'));
$table->add_row(form_checkbox('searchexten', 1, $_REQUEST['searchexten']), $label, form_input('exten', $_REQUEST['exten']));

$label = fpbx_label(_('Application'), _('Application executed by a call participant'));
$applications = array(
	'conference' => 'Conference',
	'queue' => 'Queue',
	'voicemail' => 'Voicemail',
	'voicemailmain' => 'Voicemail Main',
);
$table->add_row(form_checkbox('searchapplication', 1, $_REQUEST['searchapplication']), $label, form_dropdown('application', $applications, $_REQUEST['application']));

$html.= $table->generate();

$html.= form_close();
$html.= br();

$html.= '<script>
$(function() {
	$(".datepicker").datepicker({
		dateFormat: "yy-mm-dd",
		maxDate: "0d"
	});
});
</script>';

echo $html;
?>
