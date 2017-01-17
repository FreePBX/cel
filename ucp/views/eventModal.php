<table class="cel-detail-grid"
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
	data-check-on-init="true">
	<thead>
		<th data-field="timestamp" data-sortable="true" data-formatter="UCP.Modules.Cel.formatDate"><?php echo _("Time")?></th>
		<th data-field="duration" data-sortable="true" data-formatter="UCP.Modules.Cel.formatDuration"><?php echo _("Duration")?></th>
		<th data-field="detail" data-sortable="true"><?php echo _("Detail")?></th>
	</thead>
</table>
