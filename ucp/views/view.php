<div class="col-md-12">
	<?php if(!empty($message)) { ?>
		<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
	<?php } ?>
	<div class="row panel-group">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<a data-toggle="collapse" data-target="#celsearch" href="#" class="collapsed"><?php echo _('Search')?></a>
				</h3>
			</div>

			<div id="celsearch" class="panel-collapse collapse">
				<div class="form-group">
					<div><label for="date" class="help"><?php echo _('Date Range')?> <i class="fa fa-question-circle"></i></label></div>
					<div class="input-group">
					<input name="datefrom" type="date" max="<?php echo date('Y-m-d')?>" class="form-control search-param" id="datefrom" value="<?php echo $datefrom?>">
					<div class="input-group-addon">to</div>
					<input name="dateto" type="date" max="<?php echo date('Y-m-d')?>" class="form-control search-param" id="dateto" value="<?php echo $dateto?>">
					</div>
					<span class="help-block help-hidden" data-for="date"><?php echo _('Date range of call')?></span>
				</div>
				<div class="form-group">
					<div><label for="callerid" class="help"><?php echo _('Caller ID')?> <i class="fa fa-question-circle"></i></label></div>
					<input name="callerid" class="form-control search-param" id="callerid" value="<?php echo $callerid?>">
					<span class="help-block help-hidden" data-for="callerid"><?php echo _('Caller ID of a call participant')?></span>
				</div>
				<div class="form-group">
					<div><label for="exten" class="help"><?php echo _('Dialed Number')?> <i class="fa fa-question-circle"></i></label></div>
					<input name="exten" class="form-control search-param" id="exten" value="<?php echo $exten?>">
					<span class="help-block help-hidden" data-for="exten"><?php echo _('Extension or DID dialed')?></span>
				</div>
				<div class="form-group">
					<?php
					$apps = array(
						'' => '',
						'conference' => _('Conference'),
						'queue' => _('Queue'),
						'voicemail' => _('Voicemail'),
						'voicemailmain' => _('Voicemail Main'),
					);
					?>
					<div><label for="application" class="help"><?php echo _('Application')?> <i class="fa fa-question-circle"></i></label></div>
					<select name="application" id="application" class="form-control search-param">
						<?php foreach ($apps as $app => $display) {?>
							<option value="<?php echo $app?>" <?php echo ($app == $application) ? 'selected' : ''?>><?php echo $display?></option>
						<?php }?>
					</select>
					<span class="help-block help-hidden" data-for="application"><?php echo _('Application executed by a call participant')?></span>
				</div>
				<div class="col-sma-1">
					<span class="pull-right">
						<button class="btn btn-default" type="button" id="search-btn"><?php echo _('Search')?></button>
					</span>
				</div>
			</div>
		</div>
	</div>
</div>
