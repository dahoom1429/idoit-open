<div id="ocs-popup">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
		<span>[{isys type="lang" ident="LC__MODULE__OCS_IMPORT__CATEGORY_SELECTION"}]</span>
	</h3>

	<div class="popup-content">
			<table class="m5">
				[{foreach $categories as $key => $value}]
				<tr>
					<td>
						<label><input class="categories" type="checkbox" name="category[]" value="[{$value}]" checked="checked"> [{isys type="lang" ident=$key}]</label>
					</td>
				</tr>
				[{/foreach}]
			</table>
	</div>

	<div class="popup-footer">
		<button id="ocs-popup-import-button" type="button" class="btn mr5">
			<img src="[{$dir_images}]icons/silk/database_copy.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__IMPORT"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('ocs-popup'),
			$import_button = $('ocs-popup-import-button');

		$import_button.on('click', function () {
			ocs_multi_import();
		});

		$popup.on('click', '.popup-closer', function () {
			popup_close();
		})
	})();
</script>