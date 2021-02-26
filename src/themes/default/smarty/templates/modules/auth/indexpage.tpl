<div id="auth">
	<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__AUTH__OVERVIEW__RIGHTS_SCHEMA"}]</h2>
	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="lang" ident="LC__MODULE__AUTH__OVERVIEW__BY_MODULE"}]</td>
			<td class="value pl20">
				<div class="input-group input-size-small">
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" class="condition_filter" name="condition_filter" data-method="load_all_module_paths" checked="checked" />
					</div>
					[{isys type="f_dialog" name="condition_filter_module"}]
				</div>
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__MODULE__AUTH__OVERVIEW__BY_PERSON_OR_PERSONGROUPS"}]</td>
			<td class="value pl20">
				<div class="input-group input-size-small">
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" class="condition_filter" name="condition_filter" data-method="load_all_object_paths" />
					</div>
					[{isys type="f_popup" name="condition_filter_object"}]
				</div>
			</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td class="value">
				<button id="auth-path-loader" type="button" class="btn ml20">
					<img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" />
					<span class="vam">[{isys type="lang" ident="LC__AUTH_GUI__LOAD_RIGHTS"}]</span>
				</button>
			</td>
		</tr>
	</table>

	<div id="auth-path-results" class="p10"></div>
</div>

<script>
	[{assign var="base_dir" value=$config.base_dir}]
	[{include file="$base_dir/src/tools/js/auth/configuration.js"}]

	var $load_button = $('auth-path-loader');

	$('condition_filter_module', 'condition_filter_object__VIEW').invoke('on', 'change', function () {
		this.up('.input-group').down('.condition_filter').simulate('click');
	});

	// Setting some translations...
	idoit.Translate.set('LC__AUTH_GUI__REFERS_TO', '[{isys type="lang" ident="LC__AUTH_GUI__REFERS_TO"}]');
	idoit.Translate.set('LC__UNIVERSAL__REMOVE', '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]');
	idoit.Translate.set('LC__UNIVERSAL__COPY', '[{isys type="lang" ident="LC__UNIVERSAL__COPY"}]');
	idoit.Translate.set('LC__UNIVERSAL__LOADING', '[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');
	idoit.Translate.set('LC__UNIVERSAL__ALL', '[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]');
	// Translations for the table-header.
	idoit.Translate.set('LC__MODULE__AUTH__PERSON_AND_PERSONGROUPS', '[{isys type="lang" ident="LC__MODULE__AUTH__PERSON_AND_PERSONGROUPS"}]');
	idoit.Translate.set('LC__AUTH_GUI__CONDITION', '[{isys type="lang" ident="LC__AUTH_GUI__CONDITION"}]');
	idoit.Translate.set('LC__AUTH_GUI__PARAMETER', '[{isys type="lang" ident="LC__AUTH_GUI__PARAMETER"}]');
	idoit.Translate.set('LC__AUTH_GUI__ACTION', '[{isys type="lang" ident="LC__AUTH_GUI__ACTION"}]');

	$load_button.on('click', function () {
		var radio = $$('input.condition_filter:checked');

		if (radio.length == 1) {
			if (radio[0].readAttribute('data-method') == 'load_all_object_paths' && $F('condition_filter_object__HIDDEN').blank()) {
				idoit.Notify.info('[{isys type="lang" ident="LC__AUTH_GUI__NO_SELECTION"}]', {life:5});
				return;
			}

			$load_button
				.disable()
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			new Ajax.Request('[{$ajax_handler_url}]&func=' + radio[0].readAttribute('data-method'), {
				parameters:{
					module_id:$F('condition_filter_module'),
					obj_id:$F('condition_filter_object__HIDDEN')
				},
				method:'post',
				onComplete:function (response) {
					var json = response.responseJSON,
						i,
						div = $('auth-path-results').update();

					$load_button
						.enable()
						.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_refresh.png')
						.next('span').update('[{isys type="lang" ident="LC__AUTH_GUI__LOAD_RIGHTS"}]');

					if (json.success) {

						if (json.data.method == 'obj-id') {

							if ((Object.isArray(json.data.modules) && json.data.modules.length === 0) || (typeof json.data.modules == 'object' && Object.keys(json.data.modules).length === 0)) {
								div.insert(new Element('div', {className: 'p5 box box-blue', style:'width:100%'})
									.update(new Element('img', {src: window.dir_images + 'icons/silk/information.png', className: 'mr5'}))
									.insert(new Element('span').update('[{isys type="lang" ident="LC__AUTH__EXCEPTION__NO_RIGHTS_AT_ALL"}]')));
								return;
							}

							for (i in json.data.modules) {
								if (json.data.modules.hasOwnProperty(i)) {
									div
										.insert(new Element('h3', {className:'mt10 border gradient p5', style:'border-bottom:none; background-color:#ccc;'}).update('[{isys type="lang" ident="LC__UNIVERSAL__MODULE"}]: ' + json.data.modules[i].info.data.isys_module__title))
										.insert(new Element('div', {className:'path_tables border', id:'auth-paths-' + i}));

									new AuthConfiguration('auth-paths-' + i, {
										ajax_url: '[{$ajax_url}]&pID=' + json.data.modules[i].info.data.isys_module__const,
										rights: json.data.auth_rights,
										methods: json.data.modules[i].info.methods,
										paths: json.data.modules[i].paths,
										inherited_paths: json.data.modules[i].group_paths,
										wildchar: '[{$auth_wildchar}]',
										empty_id: '[{$auth_empty_id}]',
										edit_mode: 0
									});
								}
							}

						} else if (json.data.method == 'module-id') {

							for (i in json.data.auth_paths) {
								if (json.data.auth_paths.hasOwnProperty(i)) {
									div
										.insert(new Element('h3', {className:'mt10 border gradient p5', style:'border-bottom:none; background-color:#ccc;'}).update(json.data.auth_paths[i].person))
										.insert(new Element('div', {className:'path_tables border', id:'auth-paths-' + i}));

									new AuthConfiguration('auth-paths-' + i, {
										ajax_url: '[{$ajax_url}]&pID=' + $F('condition_filter_module'),
										rights: json.data.auth_rights,
										methods: json.data.auth_methods,
										paths: json.data.auth_paths[i].paths,
										wildchar: '[{$auth_wildchar}]',
										empty_id: '[{$auth_empty_id}]',
										edit_mode: 0
									});
								}
							}

						}
					} else {
						div.insert(new Element('div', {className:'p5 box-red'}).update(json.message));
					}
				}
			});
		}
	});
</script>

<style type="text/css">
	#auth .path_tables thead {
		height: 30px;
	}

	#auth .path_tables th {
		text-align: center;
	}

	#auth .path_tables tr.inactive {
		background:#e8e8e8;
	}

	#auth .path_tables th,
	#auth .path_tables td {
		padding: 2px;
	}

	#auth .path_tables tbody td {
		border-top: 1px solid #888888;
		padding: 3px;
	}
</style>