
<!--Dump Range-->
<div class="element-container">
	<div class="">
		<div class="row form-group">
			<div class="col-md-6">
				<label class="control-label" for="cel_dump_days"><?php echo _("Backup Days") ?></label>
			</div>
			<div class="col-md-6">
				<input type="number" class="form-control" id="cel_dump_days" name="cel_dump_days" value="<?php echo isset($cel_dump_days)?$cel_dump_days:'0'?>">
			</div>
		</div>
	</div>
</div>
<!--END Dump Range-->

<!--Restore Advanced Settings-->
<div class="element-container">
	<div class="">
		<div class="row form-group">
			<div class="col-md-6">
				<label class="control-label" for="cel_advancedsettings"><?php echo _("Restore Advanced Settings") ?></label>
			</div>
			<div class="col-md-6">
				<span class="radioset">
					<?php $cel_advancedsettings=isset($cel_advancedsettings)?$cel_advancedsettings:'no'?>
					<input type="radio" name="cel_advancedsettings" id="cel_advancedsettingsyes" value="yes" <?php echo ($cel_advancedsettings == "yes"?"CHECKED":"") ?>>
					<label for="cel_advancedsettingsyes"><?php echo _("Yes");?></label>
					<input type="radio" name="cel_advancedsettings" id="cel_advancedsettingsno" value="no" <?php echo ($cel_advancedsettings == "yes"?"":"CHECKED") ?>>
					<label for="cel_advancedsettingsno"><?php echo _("No");?></label>
				</span>
			</div>
		</div>
	</div>
</div>
<!--END Restore Advanced Settings-->
