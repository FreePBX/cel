<div class="col-md-10">
	<h3><?php echo _('Search')?></h3>
	<div class="celsearch">
		<div id="message" class="alert" style="display:none;"></div>
		<form role="form">
			<div class="form-group">
				<div><input type="radio" name="searchtype" value="date"/> <label for="date" class="help"><?php echo _('Date Range')?> <i class="fa fa-question-circle"></i></label></div>
				<div class="input-group">
				<input name="datefrom" class="form-control datepicker" id="datefrom" value="<?php echo $_REQUEST['datefrom'] ? $_REQUEST['datefrom'] : date('Y-m-d')?>">
				<div class="input-group-addon">to</div>
				<input name="dateto" class="form-control datepicker" id="dateto" value="<?php echo $_REQUEST['dateto'] ? $_REQUEST['dateto'] : date('Y-m-d')?>">
				</div>
				<span class="help-block help-hidden" data-for="date"><?php echo _('Date range of call')?></span>
			</div>
			<div class="form-group">
				<div><input type="radio" name="searchtype" value="callerid"/> <label for="callerid" class="help"><?php echo _('Caller ID')?> <i class="fa fa-question-circle"></i></label></div>
				<input name="callerid" class="form-control" id="callerid" value="<?php echo $_REQUEST['callerid']?>">
				<span class="help-block help-hidden" data-for="callerid"><?php echo _('Caller ID of a call participant')?></span>
			</div>
			<div class="form-group">
				<div><input type="radio" name="searchtype" value="extension"/> <label for="exten" class="help"><?php echo _('Dialed Number')?> <i class="fa fa-question-circle"></i></label></div>
				<input name="exten" class="form-control" id="exten" value="<?php echo $_REQUEST['exten']?>">
				<span class="help-block help-hidden" data-for="exten"><?php echo _('Extension or DID dialed')?></span>
			</div>
			<div class="form-group">
				<?php
				$applications = array(
					'conference' => 'Conference',
					'queue' => 'Queue',
					'voicemail' => 'Voicemail',
					'voicemailmain' => 'Voicemail Main',
				);
				?>
				<div><input type="radio" name="searchtype" value="application"/> <label for="application" class="help"><?php echo _('Application')?> <i class="fa fa-question-circle"></i></label></div>
				<select name="application" id="application" class="form-control">
					<?php foreach ($applications as $application => $display) {?>
						<option value="<?php echo $application?>" <?php echo ($application == $_REQUEST['application']) ? 'selected' : ''?>><?php echo $display?></option>
					<?php }?>
				</select>
				<span class="help-block help-hidden" data-for="application"><?php echo _('Application executed by a call participant')?></span>
			</div>
			<span class="input-group-btn">
				<button class="btn btn-default" type="submit" id="search-btn"><?php echo _('Search')?></button>
			</span>
		</form>
	</div>
</div>
<script>
$(function() {
//	$(".datepicker").datepicker({
//		dateFormat: "yy-mm-dd",
//		maxDate: "0d"
//	});
});
</script>

<?php
$html = '';
$html.= form_open($_SERVER['REQUEST_URI'], 'class="fpbx-submit"');
$html.= form_hidden('action', 'search');

$html.= '<h2>Search</h2>';

$table = new CI_Table;

$label = fpbx_label(_('Date Range'), _('Date range of call'));
$table->add_row(form_radio('searchtype', 'date', (!isset($_REQUEST['searchtype']) || $_REQUEST['searchtype'] == 'date')), $label, form_input('datefrom', $_REQUEST['datefrom'] ? $_REQUEST['datefrom'] : date('Y-m-d'), 'class="datepicker"'), form_input('dateto', $_REQUEST['dateto'] ? $_REQUEST['dateto'] : date('Y-m-d'), 'class="datepicker"'));

$label = fpbx_label(_('Caller ID'), _('Caller ID of a call participant'));
$table->add_row(form_radio('searchtype', 'callerid', ($_REQUEST['searchtype'] == 'callerid')), $label, form_input('callerid', $_REQUEST['callerid']));

$label = fpbx_label(_('Dialed Number'), _('Extension or DID dialed'));
$table->add_row(form_radio('searchtype', 'extension', ($_REQUEST['searchtype'] == 'extension')), $label, form_input('exten', $_REQUEST['exten']));

$label = fpbx_label(_('Application'), _('Application executed by a call participant'));
$applications = array(
	'conference' => 'Conference',
	'queue' => 'Queue',
	'voicemail' => 'Voicemail',
	'voicemailmain' => 'Voicemail Main',
);
$table->add_row(form_radio('searchtype', 'application', ($_REQUEST['searchtype'] == 'application')), $label, form_dropdown('application', $applications, $_REQUEST['application']));

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

//echo $html;
?>
