<div id="popup-location-error">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
		<span>[{isys type="lang" ident="LC__CMDB__CATG__SPATIALLY_CONNECTED_OBJECTS_ERROR_1"}]</span>
	</h3>

	<div class="popup-content p10">
		<p>[{isys type="lang" ident="LC__CMDB__CATG__SPATIALLY_CONNECTED_OBJECTS_ERROR_2"}]</p>
	</div>

	<div class="popup-footer">
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/>
			<span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-location-error');

		$popup.on('click', '.popup-closer', function () {
			popup_close();
		});
	})();
</script>