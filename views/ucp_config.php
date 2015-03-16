<script>
$('.extension-checkbox').change(function(event){
	var ext = $(this).data('extension');
	var name = $(this).data('name');
	if($(this).is(':checked')) {
		$('#cel-ext-list').append('<div class="cel-extensions" data-extension="'+ext+'"><label><input type="checkbox" name="ucp|cel[]" value="'+ext+'" checked> '+name+' &lt;'+ext+'&gt;</label><br /></div>');
	} else {
		$('.cel-extensions[data-extension="'+ext+'"]').remove();
	}
});
</script>
<div id="cel-ext-list" class="extensions-list">
<?php foreach($fpbxusers as $fpbxuser) {?>
	<div class="cel-extensions" data-extension="<?php echo $fpbxuser['ext']?>">
		<label>
			<input type="checkbox" name="ucp|cel[]" value="<?php echo $fpbxuser['ext']?>" <?php echo $fpbxuser['selected'] ? 'checked' : '' ?>> <?php echo $fpbxuser['data']['name']?> &lt;<?php echo $fpbxuser['ext']?>&gt;
		</label>
		<br />
	</div>
<?php } ?>
</div>
