<div id="welcome-config">
	<table class="contentTable">
		<tr>
			<td class="key">
				[{isys type="f_label" name="widget-popup-config-animate" ident="LC__WIDGET__WELCOME__CONFIG__ANIMATE"}]
			</td>
			<td class="value">
				<input name="widget-popup-config-animate" id="widget-popup-config-animate" class="ml20 mt5" type="checkbox" [{if $rules.animate}]checked="checked"[{/if}] />
			</td>
		</tr>
		<tr>
			<td class="key" style="vertical-align:top;">
				[{isys type="f_label" name="widget-popup-config-salutation" ident="LC__WIDGET__WELCOME__CONFIG__SALUTATION"}]
			</td>
			<td class="value">
				<div class="ml20">
					[{foreach from=$salutation_options key=key item=option}]
					<label>
						<input type="radio" class="mr5 vam radio" name="widget-popup-config-salutation" value="[{$key}]" [{if $rules.salutation == $key}]checked="checked"[{/if}] />[{$option[0]|escape:'html'}]
					</label><br />
					<span class="ml20 mb5">[{$option[1]}]</span><br />
					[{/foreach}]
				</div>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	var on_value_change = function () {
			var radio = $$('#welcome-config input.radio:checked'),
				value = {
					"animate":$('widget-popup-config-animate').checked,
					"salutation":(radio.length > 0) ? radio[0].getValue() : 'a'
				};

			$('widget-popup-config-changed').setValue('1');
			$('widget-popup-config-hidden').setValue(Object.toJSON(value));
		};

	on_value_change();
	$$('#widget-popup-config-animate,#welcome-config input.radio').invoke('on', 'change', on_value_change);
</script>