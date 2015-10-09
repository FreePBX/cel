<div class="col-md-12">
	<script>var extension = "<?php echo $_REQUEST['sub']?>", showPlayback = 1, showDownload = 1;</script>
	<?php if(!empty($message)) { ?>
		<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
	<?php } ?>
	<table id="cel-grid"
        data-url="index.php?quietmode=1&amp;module=cel&amp;command=grid&amp;extension=<?php echo $_REQUEST['sub']?>"
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
        class="table table-hover table-bordered cdr-table">
    <thead>
        <tr class="cdr-header">
						<th data-field="timestamp" data-sortable="true" data-formatter="Cel.formatDate"><?php echo _("Date")?></th>

            <th data-field="src" data-sortable="true"><?php echo _("Caller")?></th>
						<th data-field="extension" data-sortable="true"><?php echo _("Dialed")?></th>
						<th data-field="duration" data-sortable="true" data-formatter="Cel.formatDuration"><?php echo _("Duration")?></th>
						<th data-field="playback" data-formatter="Cel.formatPlayback"><?php echo _("Playback")?></th>
						<th data-field="controls" data-formatter="Cel.formatControls"><?php echo _("Controls")?></th>
        </tr>
    </thead>
	</table>
</div>
<div class="modal fade" id="callpreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document" style="height: 90%;">
		<div class="modal-content" style="height: 100%;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _("Call Detail")?></h4>
			</div>
			<div class="modal-body" style="height: 77%;overflow: auto;">
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
					>
					<thead>
						<th data-field="timestamp" data-sortable="true" data-formatter="Cel.formatDate"><?php echo _("Time")?></th>
						<th data-field="duration" data-sortable="true" data-formatter="Cel.formatDuration"><?php echo _("Duration")?></th>
						<th data-field="detail" data-sortable="true"><?php echo _("Detail")?></th>
					</thead>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close")?></button>
			</div>
		</div>
	</div>
</div>
