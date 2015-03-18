<?php 
$applications = array(
	'' => '',
	'conference' => _('Conference'),
	'queue' => _('Queue'),
	'voicemail' => _('Voicemail'),
	'voicemailmain' => _('Voicemail Main'),
);
$default = $_REQUEST['application']?$_REQUEST['application']:'';
foreach ($applications as $key => $value) {
	$approws .= '<option value="'.$key.'" '.($default == $key?"SELECTED":"").'>'.$value.'</option>';
}

?>
<h2><?php echo _("Search")?></h2>
<form class="fpbx-submit" action="" method="post" name='CELSearch' id='CELSearch'>
<input type="hidden" name="action" id="action" value="search">

<!--Date Range-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="daterange"><?php echo _("Date Range") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="daterange"></i>
					</div>
					<div class="col-md-9">
						<div class="row">
							<div class="col-md-6">
								<input type="date" max="<?php echo date('Y-m-d')?>" class="form-control" id="datefrom" name="datefrom" value="<?php echo $_REQUEST['datefrom'] ? $_REQUEST['datefrom'] : ''?>" placeholder="<?php echo _('From')?>">
							</div>
							<div class="col-md-6">
								<input type="date" max="<?php echo date('Y-m-d')?>" class="form-control" id="dateto" name="dateto" value="<?php echo $_REQUEST['dateto'] ? $_REQUEST['dateto'] : ''?>" placeholder="<?php echo _('To')?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="daterange-help" class="help-block fpbx-help-block"><?php echo _("Date range of calls")?></span>
		</div>
	</div>
</div>
<!--END Date Range-->
<!--Caller ID-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="callerid"><?php echo _("Caller ID") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="callerid"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="callerid" name="callerid" value="<?php echo $_REQUEST['callerid']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="callerid-help" class="help-block fpbx-help-block"><?php echo _("Caller ID of a call participant")?></span>
		</div>
	</div>
</div>
<!--END Caller ID-->
<!--Dialed Number-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="exten"><?php echo _("Dialed Number") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="exten"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="exten" name="exten" value="<?php echo $_REQUEST['exten']?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="exten-help" class="help-block fpbx-help-block"><?php echo _("Extension or DID dialed")?></span>
		</div>
	</div>
</div>
<!--END Dialed Number-->
<!--Application-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="application"><?php echo _("Application") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="application"></i>
					</div>
					<div class="col-md-9">
						<select class="form-control" id="application" name="application">
							<?php echo $approws ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="application-help" class="help-block fpbx-help-block"><?php echo _("Application executed by a call participant")?></span>
		</div>
	</div>
</div>
<!--END Application-->
</form>
