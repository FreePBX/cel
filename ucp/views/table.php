<div class="col-md-12">
	<script>var extension = "<?php echo $_REQUEST['sub']?>";</script>
	<?php if(!empty($message)) { ?>
		<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
	<?php } ?>
	<table id="cel-grid"
		data-sort-name="eventtime"
		data-sort-order="desc"
        data-url="index.php?quietmode=1&amp;module=cel&amp;command=grid&amp;extension=<?php echo htmlentities($ext)?>"
		data-cookie-id-table="ucp-cel-table"
	 data-maintain-selected="true"
        data-show-columns="true"
        data-show-toggle="true"
        data-toggle="table"
        data-pagination="true"
       			data-side-pagination="server"
				data-show-refresh="true"
				data-silent-sort="false"
				data-mobile-responsive="true"
				data-check-on-init="true"
				data-min-width="992"
        class="table table-hover">
    <thead>
        <tr class="cdr-header">
			<th data-field="eventtime" data-sortable="true" data-formatter="UCP.Modules.Cel.formatDate"><?php echo _("Date")?></th>
            <th data-field="cid_num" data-sortable="true"><?php echo _("Caller")?></th>
			<th data-field="exten" data-sortable="true"><?php echo _("Dialed")?></th>
			<th data-field="duration" data-formatter="UCP.Modules.Cel.formatDuration"><?php echo _("Duration")?></th>
			<?php if($showPlayback) {?>
				<th data-field="file" data-formatter="UCP.Modules.Cel.formatPlayback"><?php echo _("Playback")?></th>
			<?php }
			if($showdownload) {
				?>
				<th data-field="file" data-formatter="UCP.Modules.Cel.formatControls"><?php echo _("Controls")?></th>
			<?php } ?>
        </tr>
    </thead>
	</table>
</div>
<div class="modal fade" id="callpreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _("Call Detail")?></h4>
			</div>
			<div class="modal-body">
				<table id="cel-detail-grid"
					data-cookie="true"
					data-cookie-id-table="ucp-cel-detail-table"
					data-show-columns="true"
	        data-show-toggle="true"
	        data-toggle="table"
	        data-pagination="true"
	        data-search="true"
					data-sort-order="asc"
					data-sort-name="timestamp"
					data-mobile-responsive="true"
					data-check-on-init="true"
					data-min-width="992"
					>
					<thead>
						<th data-field="timestamp" data-sortable="true" data-formatter="UCP.Modules.Cel.formatDate"><?php echo _("Time")?></th>
						<th data-field="eventtype" data-sortable="true" ><?php echo _("Event Type")?></th>
						<th data-field="uniqueid" data-sortable="true" ><?php echo _("UniqueID")?></th>
						<th data-field="linkedid" data-sortable="true" ><?php echo _("LinkedID")?></th>
						<th data-field="cid_num" data-sortable="true" ><?php echo _("Cid num")?></th>
						<th data-field="exten" data-sortable="true" ><?php echo _("Extension")?></th>
						<th data-field="context" data-sortable="true" ><?php echo _("Context")?></th>
						<th data-field="channame" data-sortable="true"><?php echo _("Channle Name")?></th>
					</thead>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close")?></button>
			</div>
		</div>
	</div>
</div>
<script><?php echo $script?></script>
