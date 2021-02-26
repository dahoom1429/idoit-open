<div id="popup-objectpurge">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
		<span>[{$headline}]</span>
	</h3>

	<div class="popup-content">
		<p class="m5">[{$message}]</p>
		<p class="m5">
			[{isys type="lang" ident="LC__CMDB__CATG__VIRTUAL_HOST_DISSOLVE_ACTIONTEXT_1"}]

			<select id="object_action" name="object_action" class="input input-mini">
				<option value="1">[{isys type="lang" ident="LC__CMDB__RECORD_STATUS__DELETED" modifier="strtolower"}]</option>
				<option value="2">[{isys type="lang" ident="LC__CMDB__RECORD_STATUS__ARCHIVED" modifier="strtolower"}]</option>
			</select>

			[{isys type="lang" ident="LC__CMDB__CATG__VIRTUAL_HOST_DISSOLVE_ACTIONTEXT_2"}]
		</p>

		<ul class="p0 m0 mb10 text-shadow border-bottom border-top list-style-none">
			[{foreach $objects as $i => $title}]
				<li class="p5">
					<label class="display-block mouse-pointer"><input type="checkbox" checked="checked" class="objcheck" name="object[]" value="[{$i}]" /> [{$title}]</label>
				</li>
			[{/foreach}]
		</ul>
		<button type="button" class="btn ml5 mr5" onclick="$$('.objcheck').each(function(c){c.checked=true;});">[{isys type="lang" ident="LC__UNIVERSAL__ALL_OBJECTS"}]</button>
		<button type="button" class="btn" onclick="$$('.objcheck').each(function(c){c.checked=false;});">[{isys type="lang" ident="LC__UNIVERSAL__NO_OBJECT"}]</button>
	</div>

	<div class="popup-footer">
		<button id="popup-objpurge-accept" type="button" class="btn mr5">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-objectpurge'),
			$accept_button = $('popup-objpurge-accept');

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		$accept_button.on('click', function () {
			$('objects').setValue($$('.objcheck:checked').invoke('getValue').join(','));
			$('devirtualize_action').setValue($('object_action').getValue());

			popup_close();
		});

		$popup.select('li').each(function ($li, i) {
			$li.setStyle({background: (i % 2 ? '#eee' : 'ddd')});
		});

		if (!$('objects')) {
			$('isys_form').insert(new Element('input', {type: 'hidden', id: 'objects', name: 'objects'}));
		}

		if (!$('devirtualize_action')) {
			$('isys_form').insert(new Element('input', {type: 'hidden', id: 'devirtualize_action', name: 'devirtualize_action'}));
		}
	})();
</script>