<div id="popup-sanzone">
	<h3 class="popup-header">
		<img src="[{$dir_images}]icons/close-circle.png" class="fr mouse-pointer popup-closer" alt="x" />
		<span>[{isys type="lang" ident="LC__CATD__SANPOOL_DEVICES"}]</span>
	</h3>

	<div class="popup-content">
		<div class="m5" style="height: 250px; overflow:auto;">
			[{$browser}]
		</div>
		<p class="border-top p5">
			<span id="selection-text-label">[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]</span> <strong id="selection-text">[{$selFull|default:$selNoSelection}]</strong>
		</p>
	</div>

	<div class="popup-footer">
		<button type="button" id="popup-sanzone-save" class="btn mr5">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script language="JavaScript" type="text/javascript">
	(function () {
		'use strict';

		var $container = $('popup-sanzone'),
			$tree = $('ldev_browser'),
			$selection_text = $('selection-text'),
			selected_devices = [],
			selected_raids = [],
			devices = '[{$deviceList}]'.evalJSON(),
			raids = '[{$raidList}]'.evalJSON();

		$container.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		$tree.on('change', 'input[type="checkbox"]', function () {
			idoit.callbackManager.triggerCallback('popup-sanzone-refresh-selection');
		});

		$('popup-sanzone-save').on('click', function () {
			var $peText = $('[{$name}]'),
				$peHidden = $('[{$name}]__HIDDEN'),
				$peHiddenRaid = $('[{$name}]__HIDDEN2');

			if ($peText && $peHidden && $peHiddenRaid) {
				$peText.value = $selection_text.innerHTML;
				$peHidden.value = Object.toJSON(selected_devices);
				$peHiddenRaid.value = Object.toJSON(selected_raids);
			}

			popup_close();
		});

		idoit.callbackManager.registerCallback('popup-sanzone-refresh-selection', function () {
			var $devices = $tree.select('input[name="devicesInPool\[\]"]'),
				$raids = $tree.select('input[name="raidsInPool\[\]"]'),
				text = [];

			selected_devices = [];
			selected_raids = [];

			if ($devices.length) {
				$devices.each(function($el) {
					var val = $el.readAttribute('value');

					if ($el.checked) {
						text.push(devices[val]);
						selected_devices.push(val);
					}
				});
			}

			if ($raids.length) {
				$raids.each(function($el) {
					var val = $el.readAttribute('value');

					if ($el.checked) {
						text.push(raids[val]);
						selected_raids.push(val);
					}
				});
			}

			if (text.length === 0) {
				$selection_text.update('[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED"}]');
			} else {
				if (text.length === 1) {
					$('selection-text-label').update('[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]');
				} else {
					$('selection-text-label').update('[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECTS"}]');
				}

				$selection_text.update(text.join(', '));
			}
		});

		idoit.callbackManager.triggerCallback('popup-sanzone-refresh-selection');
	})();
</script>