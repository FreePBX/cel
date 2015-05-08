<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="cel_enable"><?php echo _("Allow CEL")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="cel_enable"></i>
					</div>
					<div class="col-md-9">
						<span class="radioset">
							<input type="radio" name="cel_enable" id="cel_enable_yes" value="yes" <?php echo $enabled ? 'checked' : ''?>>
							<label for="cel_enable_yes"><?php echo _('Yes')?></label>
							<input type="radio" name="cel_enable" id="cel_enable_no" value="no" <?php echo (!is_null($enabled) && !$enabled) ? 'checked' : ''?>>
							<label for="cel_enable_no"><?php echo _('No')?></label>
							<?php if($mode == "user") {?>
								<input type="radio" id="cel_enable_inherit" name="cel_enable" value='inherit'  <?php echo (is_null($enabled)) ? 'checked' : ''?>>
								<label for="cel_enable_inherit"><?php echo _('Inherit')?></label>
							<?php } ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="cel_enable-help" class="help-block fpbx-help-block"><?php echo _("Enable CEL (Call Event Logging) in UCP for this user")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="ucp_cel"><?php echo _("Allowed CEL")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="ucp_cel"></i>
					</div>
					<div class="col-md-9">
						<select data-placeholder="Extensions" id="ucp_cel" class="form-control chosenmultiselect ucp-cel" name="ucp_cel[]" multiple="multiple" <?php echo (!is_null($enabled) && !$enabled) ? "disabled" : ""?>>
							<?php foreach($ausers as $key => $value) {?>
								<option value="<?php echo $key?>" <?php echo in_array($key,$celassigned) ? 'selected' : '' ?>><?php echo $value?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="ucp_cel-help" class="help-block fpbx-help-block"><?php echo _("These are the assigned and active extensions which will show up for this user to control and edit in UCP")?></span>
		</div>
	</div>
</div>
<script>
	$("input[name=cel_enable]").change(function() {
		if($(this).val() == "yes" || $(this).val() == "inherit") {
			$(".ucp-cel").prop("disabled",false).trigger("chosen:updated");;
		} else {
			$(".ucp-cel").prop("disabled",true).trigger("chosen:updated");;
		}
	});
</script>
