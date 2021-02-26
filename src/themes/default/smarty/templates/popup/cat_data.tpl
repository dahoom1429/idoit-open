<style type="text/css">
	#data-list {
		overflow-y: auto;
	}

	#data-list label {
		padding: 5px;
		display: block;
		cursor: pointer;
	}

	#data-list label span {
		margin-left: 5px;
	}
</style>

<div id="popup-cat-data">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png" />
		<span>[{$browser_title}]</span>
	</h3>

	<div class="popup-content" style="height:332px;">
		<p class="m5">[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]: <strong>[{$obj_title}]</strong></p>

		[{if is_array($data) && count($data) > 0}]
		<ul id="data-list" class="list-style-none border-top m0 p0" style="height:306px;">
			[{foreach $data as $key => $value}]
			<li>
				<label>
					<input type="checkbox" name="item[]" value="[{$key}]" [{if is_array($preselection) && in_array($key, $preselection)}]checked="checked"[{/if}]/>
					<span>[{$value}]</span>
				</label>
			</li>
			[{/foreach}]
		</ul>
		[{else}]
		<p class="box-blue p5 m5">
			<img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}].</span>
		</p>
		[{/if}]
	</div>

	<div class="popup-footer">
		<button type="button" class="btn mr5" id="popup-cat-data-accept">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<script language="JavaScript" type="text/javascript">
	(function () {
		var $popup = $('popup-cat-data'),
			$data_list = $('data-list'),
			$accept_button = $('popup-cat-data-accept');

		if ($data_list) {
			$accept_button.on('click', function () {
				var $view = $("[{$view_field}]"),
					$hidden = $("[{$hidden_field}]"),
					data_view = [],
					data_hidden = [];

				// Iterate through all checkboxes and get the values.
				$data_list.select('input[type=checkbox]').each(function ($checkbox) {
					if ($checkbox.checked) {
						data_hidden.push($checkbox.getValue());
						data_view.push($checkbox.next().textContent);
					}
				});

				// Write our new values to the fields.
				$hidden.setValue(Object.toJSON(data_hidden));
				$view.setValue(data_view.join(', '));

				popup_close();
			});

			$data_list.select('li').each(function($li, i) {
				$li.setStyle({background:(i%2 ? '#fff' : '#eee')});
			});
		}

		// Close the popup, when clicking ".popup-closer" elements.
		$popup.select('.popup-closer').invoke('on', 'click', function() {
			popup_close();
		});
	})();
</script>