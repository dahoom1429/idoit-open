<div id="auth">
	<h3 class="p5 gradient border-bottom">[{$auth_title}]</h3>

	<table class="contentTable" style="border-top: none;">
		<tr>
			<td class="key">[{isys type="f_label" name="C__AUTH__PERSON_SELECTION" ident="LC__MODULE__AUTH__PERSON_AND_PERSONGROUPS"}]</td>
			<td class="value">[{isys type="f_popup"
                    name="C__AUTH__PERSON_SELECTION"
                    p_strPopupType="browser_object_ng"
                    secondSelection=false
                    catFilter="C__CATS__PERSON;C__CATS__PERSON_GROUP"
                    callback_abort="$('person-group-loader').simulate('click');"
                    callback_detach="$('person-group-loader').simulate('click');"
                    callback_accept="$('person-group-loader').simulate('click');"
                }]</td>
		</tr>
		<tr>
			<td class="key"></td>
            <td class="value">
                <button id="person-group-loader" type="button" class="btn ml20">
                    <img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" />
                    <span>[{isys type="lang" ident="LC__AUTH_GUI__LOAD_RIGHTS"}]</span>
                </button>
            </td>
		</tr>
	</table>

	<div class="m10">
		<p class="mt10">[{isys type="lang" ident="LC__AUTH_GUI__INHERITED_RIGHTS_MESSAGE" p_bHtmlEncode=false}]</p>

		<div id="no_object_selected" class="mt15 border p5 box-green">
			[{isys type="lang" ident="LC__AUTH_GUI__PLEASE_SELECT_OBJECT"}]
		</div>

		<div id="path_table" class="mt15 border" style="display:none;">

		</div>
		<button id="new_path" type="button" class="btn mt15 hide">
			<img src="[{$dir_images}]icons/silk/add.png" class="mr5" />
			<span>[{isys type="lang" ident="LC__AUTH_GUI__NEW_RIGHT"}]</span>
		</button>
	</div>
</div>

<script>
	[{assign var="base_dir" value=$config.base_dir}]
	[{include file="$base_dir/src/tools/js/auth/configuration.js"}]

	// Setting some translations...
	idoit.Translate.set('LC__AUTH_GUI__REFERS_TO', '[{isys type="lang" ident="LC__AUTH_GUI__REFERS_TO"}]');
	idoit.Translate.set('LC__UNIVERSAL__REMOVE', '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]');
	idoit.Translate.set('LC__UNIVERSAL__COPY', '[{isys type="lang" ident="LC__UNIVERSAL__COPY"}]');
	idoit.Translate.set('LC__UNIVERSAL__LOADING', '[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');
	idoit.Translate.set('LC__UNIVERSAL__ALL', '[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]');
	// Translations for the table-header.
	idoit.Translate.set('LC__AUTH_GUI__CONDITION', '[{isys type="lang" ident="LC__AUTH_GUI__CONDITION"}]');
	idoit.Translate.set('LC__AUTH_GUI__PARAMETER', '[{isys type="lang" ident="LC__AUTH_GUI__PARAMETER"}]');
	idoit.Translate.set('LC__AUTH_GUI__ACTION', '[{isys type="lang" ident="LC__AUTH_GUI__ACTION"}]');

	var config;

	// Callback function for the object-browser which selects persons / person-groups.
	$('person-group-loader').on('click', function (ev) {
		var $button = ev.findElement('button');

		if ($F('C__AUTH__PERSON_SELECTION__HIDDEN').blank()) {
            $('no_object_selected').show();
            $('path_table').hide();
			$('new_path').addClassName('hide');
			return;
		} else {
            $('no_object_selected').hide();
            $('path_table').show();
        }

		$button
			.disable()
			.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
			.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

		new Ajax.Request('[{$ajax_url}]&func=retrieve_paths',
			{
				parameters:{
					module_id:'[{$module_id}]',
					obj_id:$F('C__AUTH__PERSON_SELECTION__HIDDEN')
				},
				method:'post',
				onSuccess:function (response) {
					var json = response.responseJSON;

					$button
						.enable()
						.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_refresh.png')
						.next('span').update('[{isys type="lang" ident="LC__AUTH_GUI__LOAD_RIGHTS"}]');

					if (json.success) {
						// Delete the current AuthConfiguration instance and create a new one.
						try {
							config = new AuthConfiguration('path_table', {
								ajax_url:'[{$ajax_url}]',
								rights:[{$auth_rights}],
								methods:[{$auth_methods}],
								paths:json.data.paths,
								inherited_paths:json.data.group_paths,
								wildchar:'[{$auth_wildchar}]',
								empty_id:'[{$auth_empty_id}]',
								edit_mode:1
							});
						} catch (e) {
							idoit.Notify.warning(e, {life:5});
						}

						$('new_path')
							.removeClassName('hide')
							.stopObserving()
							.on('click', config.create_new_path.bindAsEventListener(config));
					} else {
						$('path_table').update(
							new Element('div', {className:'p5 box-red'}).update(json.message)
						);
					}
				}.bind(this)
			});
	});
</script>

<style type="text/css">
	#auth #path_table {
		width: 100%;
	}

	#auth #path_table thead {
		height: 30px;
	}

	#auth #path_table tr.inactive {
		background: #e8e8e8;
	}

	#auth #path_table th {
		text-align: center;
		padding: 2px;
	}

	#auth #path_table td {
		border-top: 1px solid #888888;
		padding: 3px;
	}

	#auth #path_table th.border-left,
	#auth #path_table td.border-left {
		text-align: left;
		padding-left: 10px;
		border-left-color: #ccc;
	}
</style>