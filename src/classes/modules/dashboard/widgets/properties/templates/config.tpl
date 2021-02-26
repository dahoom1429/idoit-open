<h3 class="mb5">[{isys type="lang" ident="LC__MASS_CHANGE__CHOOSE_OBJECTS"}]</h3>
[{isys
	id="widget-popup-config-object"
	name="widget-popup-config-object"
	type="f_popup"
	p_strPopupType="browser_object_ng"
	p_bInfoIconSpacer=0
	p_strSelectedID=$config_data.obj_id
	multiselection=1}]

<br class="cb" />

<h3 class="mt15 mb5">[{isys type="lang" ident="LC__REPORT__FORM__SELECT_PROPERTY"}]</h3>
[{isys
	type="f_property_selector"
	p_bInfoIconSpacer=1
	name="list"
	p_strStyle="width:200px;"
	selector_size="small"
	preselection=$config_data.selected_props
	provide=$provide
	dynamic_properties=true
	check_sorting=true
	grouping=false
	sortable=true
	p_consider_rights=true
	p_bInfoIconSpacer=0}]

<script type="text/javascript">
	window.update_widget_properties = function (ev) {
		var selected_props = new Hash,
			selected_prop_ids = [],
			counter = 0,
			query = '',
			list_config = [];

		ev.findElement('button')
			.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
			.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

		$$('.selected-field').each(function (el) {

			var arr_1 = new Hash,
				arr_2 = new Hash,
				arr_3 = new Hash,
				prop_const = el.readAttribute('data-catconst'),
				prop_key = el.readAttribute('data-propkey'),
				prop_cattype = el.readAttribute('data-cattype');

			selected_prop_ids.push(parseInt(el.readAttribute('data-propid')));


			arr_3.set(0, prop_key);
			arr_2.set(prop_const, arr_3);
			arr_1.set(prop_cattype, arr_2);

			selected_props.set(counter, arr_1);
			counter++;
		});

		new Ajax.Request('[{$ajax_url}]',
			{
				parameters: {
					properties: Object.toJSON(selected_props),
					objID: $F('widget-popup-config-object__HIDDEN')
				},
				method: 'post',
				asynchronous: false,
				onSuccess: function (response) {
					var json = response.responseJSON;

					if (json.success) {
						query = json.data.list_query;
						list_config = json.data.list_config;
					}
				}.bind(this)
			});

		$('widget-popup-config-changed').setValue('1');
		$('widget-popup-config-hidden').setValue(Object.toJSON({
			'obj_id': $F('widget-popup-config-object__HIDDEN'),
			'selected_props': selected_props,
			'list_query': query,
			'config': list_config
		}));
	};

	$('widget-popup-accept').on('click', window.update_widget_properties.bindAsEventListener());
</script>