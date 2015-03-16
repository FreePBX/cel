<div class="col-md-10">
	<h3><?php echo _('Search')?></h3>
	<div class="celsearch">
		<div id="message" class="alert" style="display:none;"></div>
		<form role="form">
			<div class="form-group">
				<div><input type="checkbox" name="searchdate" value="1"/> <label for="date" class="help"><?php echo _('Date Range')?> <i class="fa fa-question-circle"></i></label></div>
				<div class="input-group">
				<input name="datefrom" class="form-control datepicker" id="datefrom" value="<?php echo $_REQUEST['datefrom'] ? $_REQUEST['datefrom'] : date('Y-m-d')?>">
				<div class="input-group-addon">to</div>
				<input name="dateto" class="form-control datepicker" id="dateto" value="<?php echo $_REQUEST['dateto'] ? $_REQUEST['dateto'] : date('Y-m-d')?>">
				</div>
				<span class="help-block help-hidden" data-for="date"><?php echo _('Date range of call')?></span>
			</div>
			<div class="form-group">
				<div><input type="checkbox" name="searchcallerid" value="1"/> <label for="callerid" class="help"><?php echo _('Caller ID')?> <i class="fa fa-question-circle"></i></label></div>
				<input name="callerid" class="form-control" id="callerid" value="<?php echo $_REQUEST['callerid']?>">
				<span class="help-block help-hidden" data-for="callerid"><?php echo _('Caller ID of a call participant')?></span>
			</div>
			<div class="form-group">
				<div><input type="checkbox" name="searchexten" value="1"/> <label for="exten" class="help"><?php echo _('Dialed Number')?> <i class="fa fa-question-circle"></i></label></div>
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
				<div><input type="checkbox" name="searchapplication" value="1"/> <label for="application" class="help"><?php echo _('Application')?> <i class="fa fa-question-circle"></i></label></div>
				<select name="application" id="application" class="form-control">
					<?php foreach ($applications as $application => $display) {?>
						<option value="<?php echo $application?>" <?php echo ($application == $_REQUEST['application']) ? 'selected' : ''?>><?php echo $display?></option>
					<?php }?>
				</select>
				<span class="help-block help-hidden" data-for="application"><?php echo _('Application executed by a call participant')?></span>
			</div>
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" id="search-btn"><?php echo _('Search')?></button>
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
