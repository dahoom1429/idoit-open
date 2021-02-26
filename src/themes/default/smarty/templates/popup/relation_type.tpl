<div id="popup-relation-type">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
		<span>[{isys type="lang" ident="LC__CATG__RELATION__ADD_NEW_TYPE"}]</span>
	</h3>

	<div class="popup-content">
		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__CATG__RELATION__RELATION_TYPE" name="relation_type__title"}]<strong class="text-red">*</strong></td>
				<td class="value">[{isys type="f_text" name="relation_type__title" p_strValue=$smarty.post.relation_type__title p_strClass="input-small"}]</td>
			</tr>
			<tr>
				<td class="key vat">[{isys type="f_label" ident="LC__CATG__RELATION__RELATION_DESC_MASTER" name="relation_type__master"}]<strong class="text-red">*</strong></td>
				<td class="value">
					[{isys type="f_text" name="relation_type__master" p_strValue=$smarty.post.relation_type__master p_strClass="input-small"}]<br/>
					<span class="ml20">[{isys type="lang" ident="LC__UNIVERSAL__EXAMPLE"}]</span> "<em>[{isys type="lang" ident="LC__RELATION_TYPE__MASTER__DEPENDS_ON_ME"}]</em>"
				</td>
			</tr>
			<tr>
				<td class="key vat">[{isys type="f_label" ident="LC__CATG__RELATION__RELATION_DESC_SLAVE" name="relation_type__slave"}]<strong class="text-red">*</strong></td>
				<td class="value">
					[{isys type="f_text" name="relation_type__slave" p_strValue=$smarty.post.relation_type__slave p_strClass="input-small"}]<br/>
					<span class="ml20">[{isys type="lang" ident="LC__UNIVERSAL__EXAMPLE"}]</span> "<em>[{isys type="lang" ident="LC__RELATION_TYPE__SLAVE__DEPENDS_ON_ME"}]</em>"
				</td>
			</tr>
		</table>

		<p class="box-red p5 m5" id="popup-relation-type-error" style="display:none;"></p>
	</div>

	<div class="popup-footer">
		<button type="button" class="btn mr5" id="popup-relation-type-save">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-relation-type'),
			$save_button = $('popup-relation-type-save'),
			$error_div = $('popup-relation-type-error'),
			$parent_field = $('[{$parent_field}]');

		$save_button.on('click', function () {
			new Ajax.Request('?call=combobox&func=save_relation_type&ajax=1', {
				parameters: {
					relation_type__title: $F('relation_type__title'),
					relation_type__master: $F('relation_type__master'),
					relation_type__slave: $F('relation_type__slave')
				},
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON,
						index;

					$error_div.hide().update('');

					if (json.success) {
						$('[{$parent_field}]').update('');

						for (index in json.items) {
							if (json.items.hasOwnProperty(index)) {
								$parent_field.insert(new Element('option', {value: index}).update(json.items[index]));
							}
						}

						popup_close();
					} else {
						$error_div.show().update(json.message);
					}
				}
			});
		});

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});
	})();
</script>