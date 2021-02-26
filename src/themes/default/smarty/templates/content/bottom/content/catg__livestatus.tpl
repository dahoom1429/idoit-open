<h3 class="gradient p5 border-bottom">[{isys type="lang" ident="LC__CATG__LIVESTATUS__HOST_STATE"}]</h3>
<table class="contentTable">
	<tr>
		<td>
			<div id="catg_livestatus_host_state">
				<img src="[{$dir_images}]ajax-loading.gif" class="ml5 mr5 vam" /><span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
			</div>
		</td>
	</tr>
</table>

<h3 class="border-top border-bottom gradient p5">[{isys type="lang" ident="LC__CATG__LIVESTATUS__SERVICE_STATE"}]</h3>
<table class="contentTable">
	<tr>
		<td>
			<div id="catg_livestatus_service_state">
				<img src="[{$dir_images}]ajax-loading.gif" class="ml5 mr5 vam" /><span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
			</div>
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var states = '[{$states}]'.evalJSON();

		// Load the host state.
		new Ajax.Request('?ajax=1&call=monitoring_livestatus&func=load_livestatus_state', {
			parameters: {
				'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$obj_id}]',
				force:1
			},
			method: "post",
			onSuccess: function (transport) {
				var json = transport.responseJSON,
					container = $('catg_livestatus_host_state');

				if (json.success) {

					if (json.data.hasOwnProperty('host_state')) {
						container
							.up('tr')
							.update(new Element('td', {className:'key'}).update('[{isys type="lang" ident="LC__CATG__LIVESTATUS__CURRENT_STATE"}]'))
							.insert(new Element('td', {className:'value ' + json.data.host_state.color})
								.update(new Element('img', {className:'vam ml20 mr5', src:'[{$dir_images}]' + json.data.host_state.icon}))
								.insert(new Element('span', {className:'vam'}).update(json.data.host_state.state)));
					} else {
						container
							.up('tr')
							.update(new Element('td', {className:'key'}).update('[{isys type="lang" ident="LC__CATG__LIVESTATUS__CURRENT_STATE"}]'))
							.insert(new Element('td', {className:'value ' + json.data.state.color})
								.update(new Element('img', {className:'vam ml20 mr5', src:'[{$dir_images}]' + json.data.state.icon}))
								.insert(new Element('span', {className:'vam'}).update(json.data.state.state)));
					}

				} else {
					container.update(new Element('div', {className:'m5 p5 box-red'}).update(json.message)).show();
				}
			}
		});

		// Load the service states.
		new Ajax.Request('?ajax=1&call=monitoring_livestatus&func=query_livestatus',
			{
				parameters:{
					host_id:'[{$livestatus_host}]',
					query:'["GET services","Filter: host_name = [{$hostname}]","Columns: host_services_with_info"]'
				},
				method:'post',
				onSuccess:function (response) {
					var json = response.responseJSON,
						container = $('catg_livestatus_service_state'),
						table,
						item,
						key;

					if (json.success) {
						table = new Element('table');

						if (Object.isUndefined(json.data[0]) || Object.isUndefined(json.data[0][0]) || json.data[0][0].length == 0) {
							container
								.update(new Element('img', {src:'[{$dir_images}]icons/silk/information.png', className:'vam ml5 mr5'}))
								.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__CATG__LIVESTATUS__NO_DATA"}]'));
						} else {
							for (key in json.data[0][0]) {
								if (json.data[0][0].hasOwnProperty(key)) {
									item = json.data[0][0][key];

									table.insert(new Element('tr')
										.update(new Element('td', {className:'key'}).update( item[0] ))
										.insert(new Element('td', {className:'value'}).update( new Element('span', {className: 'ml20 ' + states[item[1]].color}).update( item[3] ) )));
								}
							}

							container.update(table);
						}
					} else {
						container.update(new Element('div', {className:'m5 p5 box-red'}).update(json.message)).show();
					}
				}.bind(states)
			});
	}());
</script>