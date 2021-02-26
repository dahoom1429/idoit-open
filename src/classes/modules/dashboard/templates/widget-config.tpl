<div id="widget-config-popup" class="p5">
    <p class="mb10">[{$description}]</p>

	[{isys type="f_dialog" id="widget-config-dialog" p_strClass="input-small" p_arData=$widget_selection p_bDbFieldNN=true p_bInfoIconSpacer=0 disableInputGroup=true}]
	<button type="button" id="widget-config-add-button" class="btn ml5">
		<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__DASHBOARD__WIDGET_CONFIGURATION__ADD_SELECTED_WIDGET"}]</span>
	</button>

	<div class="mt10 gradient p5 border">[{isys type="lang" ident="LC__MODULE__DASHBOARD__WIDGET_CONFIGURATION__SELECTED_WIDGETS"}]</div>
	<ul id="widget-config-list">
		[{foreach from=$widget_list item=widget}]
		<li data-id="[{$widget.row_id}]">
			<img src="[{$dir_images}]icons/silk/cross.png" class="fr delete mouse-pointer" alt="[{isys type="lang" ident="LC__WIDGET__REMOVE"}]" title="[{isys type="lang" ident="LC__WIDGET__REMOVE"}]" />
			<span class="handle">&nbsp;&nbsp;&nbsp;</span>
			[{$widget.title}]
		</li>
		[{/foreach}]
	</ul>

	[{if $is_allowed_to_administrate_dashboard}]
	<h3 class="border p5 gradient text-shadow mt10">[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__HEADLINE"}]</h3>

	<table class="twoColumns mb10">
		<tr>
			<td class="vat">
				<h4>[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_DEFAULT__HEADLINE"}]</h4>
				<p>[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_DEFAULT__DESCRIPTION" p_bHtmlEncode=false}]</p>
				<div id="define-standard-result" class="mt5 mr5 p5 hide"></div>
				<button type="button" class="btn btn-small mt10" id="define-standard-button">
					<img src="[{$dir_images}]icons/silk/application_view_icons.png" class="mr5" /><span>[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_DEFAULT__BUTTON"}]</span>
				</button>
			</td>
			<td class="vat">
				<h4>[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_OTHERS__HEADLINE"}]</h4>
				<p>[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_OTHERS__DESCRIPTION" p_bHtmlEncode=false}]</p>
				[{isys name="overwrite-other-contacts" type="f_popup" p_strPopupType="browser_object_ng" catFilter="C__CATS__PERSON" p_strClass="input-small" p_bInfoIconSpacer=0 multiselection=true}]
                <br class="cb" />

				<div id="overwrite-others-result" class="mt5 mr5 p5 hide"></div>

				<button type="button" class="btn btn-small mt10" id="overwrite-others-button">
					<img src="[{$dir_images}]icons/silk/pencil.png" class="mr5" /><span>[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_OTHERS__BUTTON"}]</span>
				</button>
			</td>
		</tr>
	</table>

	[{/if}]
</div>

<script type="text/javascript">
	window.automatic_save = false;
	window.deletions = [];

	var define_standard_button = $('define-standard-button'),
		overwrite_others_button = $('overwrite-others-button');

	reset_observer = function reset_observer () {
		Sortable.destroy('widget-config-list');

		Sortable.create('widget-config-list', {
			handle:'handle'
		});
	};

	$('widget-config-add-button').on('click', function () {
		var widget_li = new Element('li', {'data-id':$F('widget-config-dialog')}).update(
				'<img src="[{$dir_images}]icons/silk/cross.png" class="fr delete mouse-pointer" alt="[{isys type="lang" ident="LC__WIDGET__REMOVE"}]" title="[{isys type="lang" ident="LC__WIDGET__REMOVE"}]" />' +
				'<span class="handle">&nbsp;&nbsp;&nbsp;</span> ' +
				$$('#widget-config-dialog option:selected')[0].innerHTML);

		$('widget-config-list').insert(widget_li);

		reset_observer();
	});

	$('widget-config-list').on('click', '.delete', function (ev) {
		var el = ev.findElement().up('li');

		window.deletions.push(el.readAttribute('data-id'));

		el.remove();
	});

	$('widget-popup-accept').on('click', function() {
		var widgets = new Hash,
			sort_cnt = 0;

		$('widget-config-list').select('li').each(function (el) {
			widgets.set(sort_cnt, el.readAttribute('data-id'));
			sort_cnt ++;
		}.bind(this));

		new Ajax.Request('[{$ajax_url}]', {
			parameters: {
				widgets:Object.toJSON(widgets.toJSON()),
				deletions:Object.toJSON(window.deletions)
			},
			method: 'post',
			onSuccess: function (response) {
				var json = response.responseJSON;

				if (json.success) {
					location.reload();
				} else {
					alert(json.message);
				}
			}
		});
	});

	if (define_standard_button) {
		define_standard_button.on('click', function () {
			if (! confirm('[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_DEFAULT__CONFIRM" p_bHtmlEncode=false}]')) {
				return;
			}

			var result_container = $('define-standard-result').removeClassName('box-green').removeClassName('box-red').addClassName('hide');

			define_standard_button
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			var widgets = new Hash,
				sort_cnt = 0;

			$('widget-config-list').select('li').each(function (el) {
				widgets.set(sort_cnt, el.readAttribute('data-id'));
				sort_cnt ++;
			}.bind(this));

			new Ajax.Request('[{$define_default_ajax_url}]', {
				parameters: {
					widgets:Object.toJSON(widgets.toJSON())
				},
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON;

					define_standard_button
						.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/application_view_icons.png')
						.next('span').update('[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_DEFAULT__BUTTON"}]');

					if (json.success) {
						result_container.addClassName('box-green').update('[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_DEFAULT__SAVED"}]').removeClassName('hide');
					} else {
						result_container.addClassName('box-red').update(json.message).removeClassName('hide');
					}
				}.bind(this)
			});
		});
	}

	if (overwrite_others_button) {
		overwrite_others_button.on('click', function () {
			if (! confirm('[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_OTHERS__CONFIRM" p_bHtmlEncode=false}]')) {
				return;
			}

			var result_container = $('overwrite-others-result').removeClassName('box-green').removeClassName('box-red').addClassName('hide');

			overwrite_others_button
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			var widgets = new Hash,
				sort_cnt = 0;

			$('widget-config-list').select('li').each(function (el) {
				widgets.set(sort_cnt, el.readAttribute('data-id'));
				sort_cnt ++;
			}.bind(this));

			new Ajax.Request('[{$overwrite_dashboard_ajax_url}]', {
				parameters: {
					widgets:Object.toJSON(widgets.toJSON()),
					users:$F('overwrite-other-contacts__HIDDEN')
				},
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON;

					overwrite_others_button
						.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/pencil.png')
						.next('span').update('[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_OTHERS__BUTTON"}]');

					if (json.success) {
						result_container.addClassName('box-green').update('[{isys type="lang" ident="LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS__DEFINE_OTHERS__SAVED"}]').removeClassName('hide');
					} else {
						result_container.addClassName('box-red').update(json.message).removeClassName('hide');
					}
				}.bind(this)
			});
		});
	}

	reset_observer();
</script>
