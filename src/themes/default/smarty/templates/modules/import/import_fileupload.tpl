<div class="p10">
	<strong class="mr5">[{isys type="lang" ident="LC_POPUP_WIZARD_FILE__FILE_UPLOAD"}]</strong>
	<img src="[{$dir_images}]please_wait.gif" class="vam ml5 mr5 hide" id="upload_loading"/>
	<input type="file" class="ml5" name="import_file" onChange="$('upload_loading').removeClassName('hide'); $('isys_form').submit(); this.disable();"/>
</div>