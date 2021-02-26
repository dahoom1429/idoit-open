<h3 class="gradient p5 border-bottom">[{isys type="lang" ident="LC__CATG__NDO__HOST_STATE"}]</h3>
<table class="contentTable">
	<tr>
		<td colspan="2">
			<div id="catg_ndo_host_state">
				<img src="[{$dir_images}]ajax-loading.gif" class="ml5 mr5 vam" /><span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="C__CATG__NDO__STATUS_CGI" ident="LC__CATG__NDO__STATUS_CGI"}]
		</td>
		<td class="value">
			[{isys type="f_link" name="C__CATG__NDO__STATUS_CGI" p_bDisabled=true}]
		</td>
	</tr>
</table>

<h3 class="border-top border-bottom gradient p5">[{isys type="lang" ident="LC__CATG__NDO__SERVICE_STATE"}]</h3>
<table class="contentTable">
	<tr>
		<td>
			<div id="catg_ndo_service_state">
				<img src="[{$dir_images}]ajax-loading.gif" class="ml5 mr5 vam" /><span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
			</div>
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		// Load the host state.
		new Ajax.Request('?ajax=1&call=monitoring_ndo&func=load_ndo_state', {
			parameters: {
				'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$obj_id}]',
				force:1
			},
			method: "post",
			onSuccess: function (transport) {
				var json = transport.responseJSON,
					container = $('catg_ndo_host_state');

				if (! is_json_response(transport)) {
					idoit.Notify.error('The ajax request did not answer with JSON! Message: ' + transport.responseText);
				}

				if (json.success) {

					if (json.data.hasOwnProperty('host_state')) {
						container
							.up('tr')
							.update(new Element('td', {className:'key'}).update('[{isys type="lang" ident="LC__CATG__NDO__CURRENT_STATE"}]'))
							.insert(new Element('td', {className:'value ' + json.data.host_state.color})
								.update(new Element('img', {className:'vam ml20 mr5', src:'[{$dir_images}]' + json.data.host_state.icon}))
								.insert(new Element('span', {className:'vam'}).update(json.data.host_state.state)));
					} else {
						container
							.up('tr')
							.update(new Element('td', {className:'key'}).update('[{isys type="lang" ident="LC__CATG__NDO__CURRENT_STATE"}]'))
							.insert(new Element('td', {className:'value text-blue'})
								.update(new Element('img', {className:'vam ml20 mr5', src:'[{$dir_images}]icons/silk/information.png'}))
								.insert(new Element('span', {className:'vam'}).update('No data received')));
					}
				} else {
					container.update(new Element('div', {className:'p5 box-red'}).update(json.message)).show();
				}
			}
		});

		// Load the service states.
		new Ajax.Request('?ajax=1&call=monitoring_ndo&func=load_ndo_service',
			{
				parameters:{
					'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$obj_id}]',
					force:1
				},
				method:'post',
				onSuccess:function (response) {
					var json = response.responseJSON,
						services,
						container = $('catg_ndo_service_state'),
						table,
						item,
						key;

					if (json.success) {
						table = new Element('table');

						services = json.data.services;

						if (Object.isUndefined(json.data) || Object.isUndefined(services) || services.length == 0) {
							container
								.update(new Element('img', {src:'[{$dir_images}]icons/silk/information.png', className:'vam ml5 mr5'}))
								.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__CATG__NDO__NO_DATA"}]'));
						} else {
							for (key in services) {
								if (services.hasOwnProperty(key)) {
									item = services[key];

									table.insert(new Element('tr')
										.update(new Element('td', {className:'key'}).update( item.name ))
										.insert(new Element('td', {className:'value'}).update( new Element('span', {className: 'ml20 ' + item.state.color}).update( item.check_command ) )));
								}
							}

							container.update(table);
						}
					} else {
						container.update(new Element('div', {className:'p5 box-red'}).update(json.message)).show();
					}
				}
			});
	}());
</script>