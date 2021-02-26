<div class="p10" style="min-height: 400px">
	<table class="mainTable border" id="ip-table" style="table-layout: fixed">
		<colgroup>
			<col style="width:280px" />
			<col />
			<col />
			<col style="width:100px" />
		</colgroup>
		<thead>
		<tr>
			<th>IP</th>
			<th>[{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__OBJECT"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__ACTION"}]</th>
		</tr>
		</thead>
		<tbody id="ip-table-body" class="user-selectable">
		</tbody>
	</table>

	<div id="box-scroller" class="p10 v6">
		<div class="border" id="statistic-box">
			<h3 class="mouse-pointer" onclick="this.next('ul').toggleClassName('hide');">
				<img src="[{$dir_images}]icons/silk/chart_line.png" class="mr5 vam">
				<span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__STATISTIC"}]</span>
			</h3>
			<ul class="m0 list-style-none border-top">
				<li><span id="statistic-used-addresses"></span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__USED_ADDRESSES"}]</li>
				<li>
					[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS"}]
					<span id="statistic-net-address">[{$net_address}]</span>
				</li>
				<li>
					[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PREFIXLENGTH"}]
					<span id="statistic-net-subnetmask">[{$net_subnet_mask}] (/[{$net_cidr_suffix}])</span>
				</li>
				<li>
					[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_RANGE"}]
					<span id="statistic-net-ip_range">[{$address_range_from}] - [{$address_range_to}]</span>
				</li>
			</ul>
		</div>

		<br />

		<div class="border">
			<h3 class="mouse-pointer" onclick="this.next('ul').toggleClassName('hide');">
				<img src="[{$dir_images}]icons/silk/chart_pie.png" class="mr5 vam">
				<span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__LEGEND"}]</span>
			</h3>
			<ul class="m0 list-style-none border-top">
				<li class="static-address">[{isys type="lang" ident="LC__CMDB__CATG__IP__STATIC"}]</li>
				<li class="slaac">[{isys type="lang" ident="LC__CMDB__CATG__IP__SLAAC"}]</li>
				<li class="slaac-dhcp">[{isys type="lang" ident="LC__CMDB__CATG__IP__SLAAC_AND_DHCPV6"}]</li>
				<li class="dhcp-address">[{isys type="lang" ident="LC__CMDB__CATG__IP__DHCPV6"}]</li>
				<li class="dhcp-reserved-address">[{isys type="lang" ident="LC__CMDB__CATG__IP__DHCPV6_RESERVED"}]</li>
			</ul>
		</div>

		[{if is_array($zones) && count($zones)}]
		<br />

		<div class="border">
			<h3 class="mouse-pointer" onclick="this.next('ul').toggleClassName('hide');">
				<img src="[{$dir_images}]icons/silk/chart_pie.png" class="mr5 vam"><span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__ZONES"}]</span>
			</h3>
			<ul class="m0 list-style-none border-top" style="max-height: 250px; overflow-y: auto">
			[{foreach $zones as $zone}]
				<li style="background-color: [{$zone.color}]">[{$zone.title}]</li>
			[{/foreach}]
			</ul>
		</div>
		[{/if}]

		<br />

		<div class="box-green p10">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NET_SIZE_NOTICE"}]</div>

		[{if $address_conflict && !$is_global_net}]
			<div class="box-red p10 m10">
				[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_CONFLICT"}]

				[{isys type="lang" ident="LC__REPORT__VIEW__LAYER3_NETS__IP_ADDRESSES"}]: [{implode(', ', $address_conflict_ips)}]
			</div>
		[{/if}]
	</div>

	<br class="cb" />
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var $ipTableBody        = $('ip-table-body'),
		    $boxScroller      = $('box-scroller'),
		    hosts               = '[{$hosts|json_encode|escape:"javascript"}]'.evalJSON(),
		    non_addressed_hosts = '[{$non_addressed_hosts|json_encode|escape:"javascript"}]'.evalJSON(),
		    class_name          = null,
		    zones               = '[{$zones|json_encode|escape:"javascript"}]'.evalJSON();

		// Here we render the IP list.
		var render_list = function () {
			var cnt = 0, action, $tr, i, i2;

			$ipTableBody.update();

			for (i in hosts)
			{
				if (!hosts.hasOwnProperty(i))
				{
					continue;
				}

				for (i2 in hosts[i])
				{
					if (!hosts[i].hasOwnProperty(i2))
					{
						continue;
					}

					// Prepare the row css-classes.
					switch (hosts[i][i2].assignment__id)
					{
						case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6}]':
							class_name = 'dhcp-address';
							break;

						case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC_AND_DHCPV6}]':
							class_name = 'slaac-dhcp';
							break;

						case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC}]':
							class_name = 'slaac';
							break;

						case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6_RESERVED}]':
							class_name = 'dhcp-reserved-address';
							break;

						default:
						case '[{$smarty.const.C__CMDB__CATG__IP__STATIC}]':
							class_name = 'static-address';
							break;
					}

					action = new Element('button', {type: 'button', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + hosts[i][i2].list_id + ')', className:'btn btn-mini', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT" p_bHtmlEncode=false}]'})
							.update(new Element('img', {src:window.dir_images + 'icons/silk/detach.png'}));

					// Start preparing the single table-rows.
					$tr = new Element('tr', {className: class_name + ((cnt % 2) ? ' even' : ' odd')})
							.update(render_net_zone(i, hosts[i][i2].zone, (cnt%2)))
							.insert(new Element('td').update((hosts[i][i2].hostname || '') + (hosts[i][i2].domain ? ' (' + hosts[i][i2].domain + ')' : '')))
							.insert(new Element('td').update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + hosts[i][i2].isys_obj__id}).update(hosts[i][i2].isys_obj__title)))
							.insert(new Element('td').update(action));

					cnt++;

					$ipTableBody.insert($tr);
				}
			}

			$ipTableBody.insert(new Element('tr', {id: 'separator-line', className: 'used'}).insert(new Element('td', {colspan: 4})));

			// Next we will render the small IP-list of hosts with no addresses.
			for (i in non_addressed_hosts)
			{
				if (!non_addressed_hosts.hasOwnProperty(i))
				{
					continue;
				}

				switch (non_addressed_hosts[i].assignment__id)
				{
					case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6}]':
						class_name = 'dhcp-address';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC_AND_DHCPV6}]':
						class_name = 'slaac-dhcp';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC}]':
						class_name = 'slaac';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6_RESERVED}]':
						class_name = 'dhcp-reserved-address';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__STATIC}]':
					default:
						class_name = 'static-address';
						break;
				}

				action = new Element('button', {type: 'button', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + non_addressed_hosts[i].list_id + ')', className:'btn btn-mini', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT" p_bHtmlEncode=false}]'})
						.update(new Element('img', {src:window.dir_images + 'icons/silk/detach.png'}));

				$tr = new Element('tr', {className: class_name + ' used ' + ((i % 2) ? 'even' : 'odd')})
						.insert(new Element('td').update('-'))
						.insert(new Element('td').update((non_addressed_hosts[i].hostname || '') + (non_addressed_hosts[i].domain ? ' (' + non_addressed_hosts[i].domain + ')' : '')))
						.insert(new Element('td').update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + non_addressed_hosts[i].isys_obj__id}).update(non_addressed_hosts[i].isys_obj__title)))
						.insert(new Element('td').update(action));

				i++;

				$ipTableBody.insert($tr);
			}

			[{if !$has_edit_right}]
			$ipTableBody.select('button').invoke('disable');
			[{/if}]
		};

		render_list();

		$('statistic-used-addresses').update(Object.keys(hosts).length);

		var legend_scroll_at = '[{$legend_scroller}]';

		// This little snippet will move the to right boxes, while scrolling.
		$('contentWrapper').on('scroll', function() {
			var top = this.scrollTop,
			    scroll_at;

			if(legend_scroll_at != ''){
				scroll_at = parseInt(legend_scroll_at);
			} else{
				scroll_at = 100;
			}
			if (top > scroll_at) {
				$boxScroller.setStyle({top: 105 + (top - scroll_at ) + 'px'});
			} else {
				$boxScroller.setStyle({top: null});
			}
		});

		// Method for disconnecting an host object.
		var disconnect = function (obj) {
			[{if $is_global_net}]
			alert('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT_GLOBAL_NET" p_bHtmlEncode=false}]');
			[{else}]

			if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT_CONFIRMATION" p_bHtmlEncode=false}]'))
			{
				new Ajax.Call('?call=ip_addresses&method=dv6&ajax=1',
						{
							requestHeaders: {Accept: 'application/json'},
							method:         'post',
							parameters:     {
								'[{$smarty.const.C__CMDB__GET__OBJECT}]':  obj,
								'[{$smarty.const.C__CMDB__GET__OBJECT}]2': '[{$obj_id}]'
							},
							onSuccess:      function (transport) {
								var json = transport.responseText.evalJSON();

								// We got our response - Now we display the new range!
								if (json.result == 'success')
								{
									// We fill our host-hash.
									hosts = json.hosts;
									non_addressed_hosts = json.not_addressed_hosts;
								}

								// And render the list again.
								render_list();
							}.bind(this)
						});
			}
			[{/if}]
		};

		function render_net_zone(ipAddress, zoneObjID, evenOdd) {
			if (zones.hasOwnProperty(zoneObjID))
			{
				return new Element('td', {style: 'background-color:' + Color.render_rgb_from_hex(zones[zoneObjID].color, (evenOdd ? 100 : 70)), title: zones[zoneObjID].name})
						.update(new Element('span').update(ipAddress));
			}

			return new Element('td', {style: 'background-color:#' + (evenOdd ? 'eee' : 'fff')}).update(ipAddress);
		}

		// Adding the global callbacks.
		idoit.callbackManager.registerCallback('iplist_disconnect', disconnect);
	}());
</script>