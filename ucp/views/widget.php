<div class="col-md-12">
	<script>var extension = "<?php echo $_REQUEST['sub']?>";</script>
	<?php if(!empty($message)) { ?>
		<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
	<?php } ?>
	<table class="cel-grid"
		data-url="index.php?quietmode=1&amp;module=cel&amp;command=grid&amp;extension=<?php echo htmlentities($ext)?>"
		data-cache="false"
		data-cookie="true"
		data-cookie-id-table="ucp-cel-table"
		data-maintain-selected="true"
		data-show-columns="true"
		data-show-toggle="true"
		data-toggle="table"
		data-pagination="true"
		data-sort-order="desc"
		data-sort-name="timestamp"
		data-side-pagination="server"
		data-show-refresh="true"
		data-silent-sort="false"
		data-mobile-responsive="true"
		data-check-on-init="true"
		class="table table-hover">
		<thead>
			<tr class="cdr-header">
				<th data-field="timestamp" data-sortable="true" data-formatter="UCP.Modules.Cel.formatDate"><?php echo _("Date")?></th>
				<th data-field="src" data-sortable="true"><?php echo _("Caller")?></th>
				<th data-field="extension" data-sortable="true"><?php echo _("Dialed")?></th>
				<th data-field="duration" data-sortable="true" data-formatter="UCP.Modules.Cel.formatDuration"><?php echo _("Duration")?></th>
				<?php if($showPlayback) {?>
					<th data-field="playback" data-formatter="UCP.Modules.Cel.formatPlayback"><?php echo _("Playback")?></th>
				<?php } ?>
				<th data-field="controls" data-formatter="UCP.Modules.Cel.formatControls"><?php echo _("Controls")?></th>
			</tr>
		</thead>
	</table>
</div>
<div class="modal fade" id="callpreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
</div>
<script><?php echo $script?></script>
