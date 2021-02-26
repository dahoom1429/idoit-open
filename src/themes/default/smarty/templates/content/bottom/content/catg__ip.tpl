<input type="hidden" name="C__CATG__IP__ID" value="[{$ip_id}]">
<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__NET__TYPE' ident="LC__CMDB__CATS__NET__TYPE"}]</td>
		<td class="value">[{isys type="f_dialog" p_strTable="isys_net_type" name="C__NET__TYPE" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CATP__IP__PRIMARY"}] / [{isys type="lang" ident="LC__CATP__IP__ACTIVE"}]</td>
		<td class="value">
			[{isys type="f_dialog" name="C__CATP__IP__PRIMARY" p_bDisabled="[{if $smarty.get.catgID eq $smarty.const.C__CATG__OVERVIEW}]1[{/if}]" p_bDbFieldNN="1" p_strClass="input input-mini"}]
			<span style="[{if isys_glob_is_edit_mode()}]float:left; padding:4px 7px 5px 8px;[{else}]margin:0 5px;[{/if}]">/</span>
			[{isys type="f_dialog" name="C__CATP__IP__ACTIVE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__IP__NET__VIEW' ident="LC__CMDB__NET_ASSIGNMENT"}]</td>
		<td class="value">
			<div class="ml20 input-group input-size-normal">
			[{isys
				title="LC__BROWSER__TITLE__NET"
				name="C__CATG__IP__NET"
				id="C__CATG__IP__NET"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				callback_accept="idoit.callbackManager.triggerCallback('net_assigned');"
				callback_detach="idoit.callbackManager.triggerCallback('net_detached');"
				disableInputGroup=true
				p_bInfoIconSpacer=0}]

			<input type="hidden" value="[{$net}]" name="old_net_id" id="old_net_id" />

			[{if isys_glob_is_edit_mode() && $smarty.const.C__MODULE__PRO}]
				<a href="javascript:" id="net_list_button" class="input-group-addon input-group-addon-clickable"><span>[{isys type='lang' ident='LC__CMDB__CATG__IP__LOAD_IP_NET'}]</span></a>
			[{/if}]
			</div>
		</td>
	</tr>
</table>

<table class="contentTable form mt5 ip-[{$smarty.const.C__CATS_NET_TYPE__IPV4}]" [{if $type != $smarty.const.C__CATS_NET_TYPE__IPV4}]style="display:none;"[{/if}]>
	<tr id="ip_range">
		<td class="key">[{isys type='f_label' name='C__CATP__IP__ADDRESS_V4_FROM' ident="LC__CMDB__CATS__NET__ADDRESS_RANGE"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATP__IP__ADDRESS_V4_FROM" p_strClass="input input-mini" p_bReadonly="1"}]
			<span style="[{if isys_glob_is_edit_mode()}]float:left; padding:4px 7px 5px 8px;[{else}]margin:0 5px;[{/if}]">-</span>
			[{isys type="f_text" name="C__CATP__IP__ADDRESS_V4_TO" p_strClass="input input-mini" p_bReadonly="1" p_bInfoIconSpacer=0 inputGroupMarginClass=''}]
			<input type="hidden" id="ip_ranges" value="[{$ip_ranges}]"/>
			<input type="hidden" id="used_ips" value="[{$used_ips}]"/>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__IP__ZONE' ident="LC__CMDB__CATS__NET__ZONE"}]</td>
		<td class="value">
			<div class="[{if isys_glob_is_edit_mode()}]input-group input-size-small[{/if}] ml20">
				[{isys type="f_dialog" name="C__CATG__IP__ZONE" p_bDbFieldNN=1}]
				[{if isys_glob_is_edit_mode()}]
				<div class="input-group-addon">
					<div class="cmdb-marker" style="background-color:[{$current_zone_color}]; height:100%; width:100%; margin:0;"></div>
				</div>
				[{else}]
					<div class="cmdb-marker vam" style="background-color:[{$current_zone_color}]; height:18px; margin:-2px 0 0; float:none; display: inline-block;"></div>
				[{/if}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATP__IP__ASSIGN' ident="LC__CATP__IP__ASSIGN"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATP__IP__ASSIGN" id="ipv4_assign" p_bDbFieldNN=1}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATP__IP__ADDRESS_V4' ident="LC__CMDB__CATG__IP__IPV4_ADDRESS"}]</td>
		<td class="value">
			<div class="ml20 input-group input-size-small">
				[{isys type="f_text" name="C__CATP__IP__ADDRESS_V4" p_onChange="idoit.callbackManager.triggerCallback('validate_ip');" p_strClass="reEnableWhenNetAssigned ipv4" p_bInfoIconSpacer=0 disableInputGroup=true}]

				[{if $smarty.const.C__MODULE__PRO && isys_glob_is_edit_mode()}]
				<a href="javascript:" class="input-group-addon input-group-addon-clickable" id="btn_ip_calc">
					<img src="[{$dir_images}]icons/silk/arrow_refresh.png"/>
				</a>
				<a href="javascript:" class="input-group-addon input-group-addon-clickable" id="btn_detach_ip">
					<img class="vam" src="[{$dir_images}]icons/silk/detach.png"/>
				</a>
				[{/if}]
			</div>

			<span id="C__CATG__IP__MESSAGES" class="input ml5 fl" style="display:none; width:auto; padding-top:4px;"></span>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATP__IP__SUBNETMASK_V4' ident="LC__CATP__IP__SUBNETMASK"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATP__IP__SUBNETMASK_V4" p_strClass="reEnableWhenNetAssigned input input-mini ipv4" p_bReadonly="1"}]
			<span style="[{if isys_glob_is_edit_mode()}]float:left; padding:4px 7px 5px 8px;[{else}]margin:0 5px;[{/if}]">/</span>
			[{isys type="f_text" name="C__CATS__NET__CIDR" p_bInfoIconSpacer="0" p_strClass="input input-mini" inputGroupMarginClass='' p_bReadonly=true}]
		</td>
	</tr>
</table>

<table class="contentTable form mt5 ip-[{$smarty.const.C__CATS_NET_TYPE__IPV4}] ip-[{$smarty.const.C__CATS_NET_TYPE__IPV6}]" [{if $type != $smarty.const.C__CATS_NET_TYPE__IPV6 && $type != $smarty.const.C__CATS_NET_TYPE__IPV4}]style="display:none;"[{/if}]>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__IP__GW__CHECK' ident="LC__CATG__IP__DEFAULT_GATEWAY_FOR_THE_NET"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__IP__GW__CHECK" p_bDbFieldNN="1"}]</td>
    </tr>
</table>

<table class="contentTable form mt5 ip-[{$smarty.const.C__CATS_NET_TYPE__IPV6}]" [{if $type != $smarty.const.C__CATS_NET_TYPE__IPV6}]style="display:none;"[{/if}]>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IP__IPV6_ASSIGNMENT' ident="LC__CMDB__CATG__IP__IPV6_ASSIGNMENT"}]</td>
		<td class="value">[{isys type='f_dialog' p_bDbFieldNN="1"  name='C__CMDB__CATG__IP__IPV6_ASSIGNMENT' p_strClass="reEnableWhenNetAssigned input input-mini ipv6"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IP__IPV6_ADDRESS' ident="LC__CMDB__CATG__IP__IPV6_ADDRESS"}]</td>
		<td class="value">
			<div class="ml20 input-group input-size-normal">
				[{isys type="f_text" id="C__CMDB__CATG__IP__IPV6_ADDRESS" name="C__CMDB__CATG__IP__IPV6_ADDRESS" p_strClass="reEnableWhenNetAssigned input input-small ipv6" p_bInfoIconSpacer=0 disableInputGroup=true}]
				[{if isys_glob_is_edit_mode()}]
				<a href="javascript:" class="input-group-addon input-group-addon-clickable" id="btn_ip_calc_v6">
					<img src="[{$dir_images}]icons/navbar/recycle_icon.png"/>
				</a>
				[{/if}]
			</div>
			<div class="fl">
				<span id="C__CATG__IPV6__MESSAGES" class="input input-mini ml5 fl" style="display:none;padding-top:4px;">
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IP__IPV6_SCOPE' ident="LC__CMDB__CATG__IP__IPV6_SCOPE"}]</td>
		<td class="value">[{isys type='f_popup' p_strPopupType='dialog_plus' name='C__CMDB__CATG__IP__IPV6_SCOPE' tab='6' p_strClass="reEnableWhenNetAssigned input input-mini ipv6"}]</td>
	</tr>
</table>

<table class="contentTable form mt5" id="ip_additional" [{if $type != $smarty.const.C__CATS_NET_TYPE__IPV4 && $type != $smarty.const.C__CATS_NET_TYPE__IPV6}]style="display:none;"[{/if}]>
	<tr>
		<td class="key vat">[{isys type='f_label' name='C__CATP__IP__HOSTNAME' ident="LC__CATP__IP__HOSTNAME_FQDN"}]</td>
		<td class="value pl20">
			[{if isys_glob_is_edit_mode()}]
				<div class="mb5 input-group input-size-normal">
					[{isys type="f_text" name="C__CATP__IP__HOSTNAME" disableInputGroup=false}]
					<div class="input-group-addon input-group-addon-unstyled" style="min-width:10px;">.</div>
					[{isys type="f_text" name="C__CATG__IP__DOMAIN" disableInputGroup=false}]
				</div>
			[{else}]
				<!-- LF: This should remain as a "one-liner" so no spaces appear (like "hostname . domain") -->
				[{$hostname}][{if !empty($domain)}]<span>.</span>[{$domain}][{/if}]
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type='f_label' name='C__CATP__IP__HOSTNAME_ADDITIONAL[]' ident='LC__CATG__IP__ALIASES'}]</td>
		<td class="value pl20">
			[{if isys_glob_is_edit_mode()}]
				<div id="hostnameList">
					[{foreach $hostname_pairs as $pair}]
						<div class="mb5 input-group input-size-normal">
							[{isys type="f_text" name="C__CATP__IP__HOSTNAME_ADDITIONAL[]" p_strValue=$pair.host}]
							<div class="input-group-addon input-group-addon-unstyled" style="min-width:10px;">.</div>
							[{isys type="f_text" name="C__CATP__IP__DOMAIN_ADDITIONAL[]" p_strValue=$pair.domain}]
						</div>

						<button type="button" class="fl ml5 btn"><img src="[{$dir_images}]icons/silk/delete.png" /></button>

						<br class="cb" />
					[{/foreach}]
				</div>

				<button type="button" id="fqdnFormAdder" class="btn">
					<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_ENTRY"}]</span>
				</button>
			[{else}]
				[{foreach $hostname_pairs as $pair}]
					[{$pair.host}].[{$pair.domain}]<br />
				[{/foreach}]
			[{/if}]
		</td>
	</tr>
	<tr class="IP">
		<td class="key">[{isys type='f_label' name='C__CATG__IP__ASSIGNED_DNS_SERVER__VIEW' ident="LC__CMDB__CATS__NET__DNS_SERVER"}]</td>
		<td class="value">
			[{isys
				name="C__CATG__IP__ASSIGNED_DNS_SERVER"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter="C__CATG__IP"
				multiselection=true
				secondSelection="true"
				secondList="isys_cmdb_dao_category_g_ip::object_browser"
				secondListFormat="isys_cmdb_dao_category_g_ip::format_selection"}]
		</td>
	</tr>
	<tr class="IP">
		<td class="key">[{isys type="f_label" name="C__CATP__IP__SEARCH_DOMAIN" ident="LC__CMDB__CATS__NET__SEARCH_DOMAIN"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATP__IP__SEARCH_DOMAIN"}]</td>
	</tr>
</table>

<table class="contentTable mt5">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__IP__ASSIGNED_PORTS' ident="LC__CATG__IP__ASSIGNED_PORT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__IP__ASSIGNED_PORTS" emptyMessage="LC__CATG__IP__NO_PORTS_WARNING"}]</td>
	</tr>
</table>

<script type="text/javascript">
	// Initializing without "var" vor global scope.
	net_range = '[{$net_range|default:"[]"}]'.evalJSON();
	dhcp_dynamic_ranges = '[{$dhcp_dynamic_ranges|default:"[]"}]'.evalJSON();
	dhcp_reserved_ranges = '[{$dhcp_reserved_ranges|default:"[]"}]'.evalJSON();
	used_ips = '[{$used_ips|default:"[]"}]'.evalJSON();
	global_net_ipv4_title = '[{$global_net_ipv4_title}]';
	global_net_ipv6_title = '[{$global_net_ipv6_title}]';
	ip_unique_check = '[{$ip_unique_check}]';

	(function () {
		"use strict";

		var $btn_ip_calc = $('btn_ip_calc'),
			$btn_detach_ip = $('btn_detach_ip'),
			$ip_message = $('C__CATG__IP__MESSAGES'),
            $netSelectionView = $('C__CATG__IP__NET__VIEW'),
			$net_selection_hidden = $('C__CATG__IP__NET__HIDDEN'),
            $netSelectionConfig = $('C__CATG__IP__NET__CONFIG'),
			$fqdnAdderButton = $('fqdnFormAdder'),
			$fqdnList = $('hostnameList'),
			$primaryDomain = $('C__CATG__IP__DOMAIN'),
			$net_selection_detach,
			$subnet_v4 = $('C__CATP__IP__SUBNETMASK_V4'),
			$cidr = $('C__CATS__NET__CIDR'),
			$netType = $('C__NET__TYPE'),
			$netZone = $('C__CATG__IP__ZONE'),
			netZoneOptions = '[{$zone_options|json_encode|escape:"javascript"}]'.evalJSON();

        if ($netType && $netSelectionView && $netSelectionConfig) {
            $netType.on('change', function () {
                var configuration = JSON.parse($netSelectionConfig.getValue()),
                    SuggestionInstance = $netSelectionView.retrieve('suggestion'),
                    suggestionOptions;

                configuration['[{isys_popup_browser_object_ng::C__CUSTOM_FILTERS}]'] = {
                    'idoit\\Component\\Browser\\Filter\\NetTypeFilter': [$netType.getValue()]
                };

                $netSelectionConfig.setValue(JSON.stringify(configuration));

                // After we updated the object-browser configuration, we need to do the same for the suggestion:
                if (SuggestionInstance.hasOwnProperty('suggestion')) {
                    try {
                        // We use "parseQuery" because the parameters are hold as a query string.
                        suggestionOptions = SuggestionInstance.suggestion.options.parameters.parseQuery();

                        delete suggestionOptions.search;

                        // @todo  Think of something "nicer"... The Prototype Autocompleter is a problem :(
                        suggestionOptions.customFilters = 'idoit\\Component\\Browser\\Filter\\NetTypeFilter:' + $netType.getValue();

                        // We now pass the filters back to the suggestion.
                        SuggestionInstance.suggestion.options.defaultParams = Object.toQueryString(suggestionOptions);
                    } catch (e) {

                    }
                }
            });
        }

		if ($net_selection_hidden)
		{
			$net_selection_detach = $net_selection_hidden.next('.detach');

			// ID-3771 Wrap the detach event inside a condition that will only return true when no global net is selected.
			$net_selection_detach.writeAttribute('onclick', "if($F('C__CATG__IP__NET__HIDDEN') != '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]' && $F('C__CATG__IP__NET__HIDDEN') != '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]' ) { " + $net_selection_detach.readAttribute('onclick') + " }")
		}

		// Validation of the ip address.
		var validate_ip = function () {
			var ipv4_assignment = $('ipv4_assign').getValue(),
				ipv4 = $('C__CATP__IP__ADDRESS_V4').getValue(),
				ipv4_empty = (ipv4 === '...'),
				ipv4_long = IPv4.ip2long(ipv4),
				found_free_ipv4 = false,
				dhcp_dynamic_exists = (dhcp_dynamic_ranges != null),
				dhcp_reserved_exists = (dhcp_reserved_ranges != null);

			if (ipv4.blank())
			{
				isys_glob_enable_save();
                $ip_message.hide();
				return;
			}

			// Check for valid ip address
			if (ipv4_long == 0) {
				ip_messages(1, 10);
				return;
			}

			// First we handle the case, that we've got an empty IP.
			if (ipv4_empty) {
				switch (ipv4_assignment) {
					// Empty "static" or "unnumbered" IP? No problem.
					case '[{$smarty.const.C__CATP__IP__ASSIGN__STATIC}]':
					case '[{$smarty.const.C__CATP__IP__ASSIGN__UNNUMBERED}]':
						ip_messages(2, 0);
						return;

					// Empty "dhcp" or "dhcp-reserved" is also okay, when there's a according range.
					case '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP}]':
						ip_messages(2, 0);
						return;

					case '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP_RESERVED}]':
						ip_messages(2, 0);
						return;
				}
			}

			// Is net-range empty?
			if (!net_range.from || !net_range.to) {
				ip_messages(1, 8);
				return;
			}

			// We have to check if the IP-address lies inside the net-range. For all assignment types!
			if (ipv4_long < net_range.from || net_range.to < ipv4_long) {
				ip_messages(1, 9);
				return;
			}

			// And now we handle the more complex part.
			switch (ipv4_assignment) {
				// Only thing to check here is, if the IP address is inside our net-range.
				case '[{$smarty.const.C__CATP__IP__ASSIGN__UNNUMBERED}]':
				case '[{$smarty.const.C__CATP__IP__ASSIGN__STATIC}]':
					if (ipv4_long >= net_range.from && ipv4_long <= net_range.to) {
						is_ipv4_free(ipv4);

						return;
					}
					ip_messages(1, 3);
					break;

				// For "dhcp" we have to check if the IP address is inside the DHCP-range and is free.
                case '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP}]':
                    if (dhcp_dynamic_exists) {
                        // Check, if the IP address is inside one of our dynamic dhcp ranges.
                        dhcp_dynamic_ranges.each(function (i) {
                            // Check if the IP address is inside our dynamic DHCP range.
                            if ((ipv4_long >= i.from && ipv4_long <= i.to) || !used_ips.in_array(ipv4_long))
                            {
                                found_free_ipv4 = true;
                            }
                        }.bind(this));

                        is_ipv4_free(ipv4);
                    } else {
                        is_ipv4_free(ipv4);
                    }
                    break;

				case '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP_RESERVED}]':
					if (dhcp_reserved_exists) {
						// Check, if the IP address is inside one of our dynamic dhcp ranges.
						dhcp_reserved_ranges.each(function(i) {
							// Check if the IP address is inside our dynamic DHCP range.
							if ((ipv4_long >= i.from && ipv4_long <= i.to) || !used_ips.in_array(ipv4_long)) {
								found_free_ipv4 = true;
							}
						}.bind(this));

						is_ipv4_free(ipv4);
					} else {
						is_ipv4_free(ipv4);
					}
			}
		};

		var reset_ip = function (id_const) {
			$(id_const).setValue('');
		};

		var reset_subnetmask = function (id_const) {
			reset_ip(id_const);
			$cidr.setValue('');
		};

		var reset_range = function (id_const_from, id_const_to) {
			reset_ip(id_const_from);
			reset_ip(id_const_to);
		};

		var reset_all = function (id_const_ip, id_const_subnetmask, id_const_from, id_const_to) {
			reset_ip(id_const_ip);
			reset_subnetmask(id_const_subnetmask);
			reset_range(id_const_from, id_const_to);
		};

		var net_assigned_v4 = function () {
			new Ajax.Request('?ajax=1&call=net', {
				parameters: {
					id: $net_selection_hidden.getValue()
				},
				method: "post",
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					if (json)
					{
						isys_glob_enable_save();

						$('C__CATG__IP__NET__VIEW').setValue(json.object_browser_title);

						// ID-3771 Remove the "disabled" class (and add it later, if necessary).
						$net_selection_detach.removeClassName('disabled');

						if ($net_selection_hidden.getValue().blank()) {
							idoit.Notify.info('[{isys type="lang" ident="LC__CATG__IP__GLOBAL_NET_SELECTED" p_bHtmlEncode=0}]', {life:10});
							$net_selection_hidden.setValue('[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]');
						}

						if (json.isys_cats_net_list__isys_net_type__id == '[{$smarty.const.C__CATS_NET_TYPE__IPV6}]') {
							alert('[{isys type="lang" ident="LC__CATG__IP__WRONG_NET_WARNING" p_bHtmlEncode=0}]');
							isys_glob_disable_save();
							return;
						}

						if ($net_selection_hidden.getValue() == '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]') {
							$net_selection_detach.addClassName('disabled');
						}

						// IPv4.
						$subnet_v4.setValue(json.isys_cats_net_list__mask);
						$cidr.setValue(json.isys_cats_net_list__cidr_suffix);
						$('C__CATP__IP__ADDRESS_V4_FROM').setValue(json.isys_cats_net_list__address_range_from);
						$('C__CATP__IP__ADDRESS_V4_TO').setValue(json.isys_cats_net_list__address_range_to);

						// Set DNS Server.
						var dns_server_ids = $('C__CATG__IP__ASSIGNED_DNS_SERVER__HIDDEN').getValue().evalJSON();
						var dns_server_view = $('C__CATG__IP__ASSIGNED_DNS_SERVER__VIEW').getValue() + ', ';

						json.assigned_dns_server.each(function (s) {
							dns_server_ids.push(parseInt(s.id));
							dns_server_view += s.details + ', ';
						});

						$('C__CATG__IP__ASSIGNED_DNS_SERVER__HIDDEN').setValue(Object.toJSON(dns_server_ids));
						$('C__CATG__IP__ASSIGNED_DNS_SERVER__VIEW').setValue(dns_server_view.replace(/^[,\s]+/, '').replace(/[,\s]+$/, ''));

						// Object for multiselectbox
						var $dns_domain = $('C__CATP__IP__SEARCH_DOMAIN');

						if ($F('old_net_id') != $net_selection_hidden.getValue()) {
							$dns_domain.setValue([]);

							if ($netType.disabled) {
								var selected_dns_domains = [];

								json.assigned_dns_domain.each(function (item) {
									selected_dns_domains.push(item.getValue());
								});

								$dns_domain.setValue(selected_dns_domains).fire('chosen:updated');
							}
						}

						net_range.from = IPv4.ip2long(json.isys_cats_net_list__address_range_from);
						net_range.to = IPv4.ip2long(json.isys_cats_net_list__address_range_to);

						if (json.used_ips) {
							used_ips = json.used_ips;
						}

						if (json.dhcp_ranges) {
							if (json.dhcp_ranges.C__NET__DHCP_DYNAMIC) {
								dhcp_dynamic_ranges = json.dhcp_ranges.C__NET__DHCP_DYNAMIC;
							}
							if (json.dhcp_ranges.C__NET__DHCP_RESERVED) {
								dhcp_reserved_ranges = json.dhcp_ranges.C__NET__DHCP_RESERVED;
							}
						}

						$('old_net_id').setValue($net_selection_hidden.getValue());

						validate_ip();
					}

				}
			});
		};

		var net_assigned_v6 = function() {
			new Ajax.Request('?ajax=1&call=net',
				{
					parameters: {
						id: $net_selection_hidden.getValue()
					},
					method: "post",
					onSuccess: function(transport) {
						var json = transport.responseText.evalJSON();

						if (json) {
							isys_glob_enable_save();

							$('C__CATG__IP__NET__VIEW').setValue(json.object_browser_title);

							// ID-3771 Remove the "disabled" class (and add it later, if necessary).
							$net_selection_detach.removeClassName('disabled');

							if ($net_selection_hidden.getValue().blank()) {
								idoit.Notify.info('[{isys type="lang" ident="LC__CATG__IP__GLOBAL_NET_SELECTED" p_bHtmlEncode=0}]', {life:10});
								$net_selection_hidden.setValue('[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]');
							}

							if (json.isys_cats_net_list__isys_net_type__id == '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]') {
								alert('[{isys type="lang" ident="LC__CATG__IP__WRONG_NET_WARNING" p_bHtmlEncode=0}]');
								isys_glob_disable_save();
								return;
							}

							if ($net_selection_hidden.getValue() == '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]') {
								$net_selection_detach.addClassName('disabled');
							}

							// Object for multiselectbox
							var $dns_domain = $('C__CATP__IP__SEARCH_DOMAIN');

							if ($F('old_net_id') != $net_selection_hidden.getValue()) {
								$dns_domain.setValue([]);

								if ($netType.disabled) {
									var selected_dns_domains = [];

									json.assigned_dns_domain.each(function (item) {
										selected_dns_domains.push(item.getValue());
									});

									$dns_domain.setValue(selected_dns_domains).fire('chosen:updated');
								}
							}

							net_range.from = json.isys_cats_net_list__address_range_from;
							net_range.to = json.isys_cats_net_list__address_range_to;

							$('old_net_id').setValue($net_selection_hidden.getValue());
						}
					}
				});
		};

		var net_find_free_ipv4 = function () {
			isys_glob_enable_save();
			$btn_ip_calc.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

			new Ajax.Request('?call=calc_ip_address&func=find_free_v4&ajax=1', {
				parameters: {
					net_obj_id: ((!$net_selection_hidden.getValue().blank()) ? $net_selection_hidden.getValue() : '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]'),
					ip_assignment: $('ipv4_assign').getValue(),
					zone: $netZone.getValue()
				},
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					$btn_ip_calc.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_refresh.png');

					if (json.success)
					{
						if (json.data === false)
						{
							ip_messages(1, 3);
						}
						else if (json.data == 'no_static')
						{
							ip_messages(1, 7);
						}
						else
						{
							if (!json.data.blank() && json.data != null)
							{
								$('C__CATP__IP__ADDRESS_V4').setValue(json.data);
							}

							// We set the IP address blank, when the chosen type is "unnumbered".
							if ($('ipv4_assign').getValue() == '[{$smarty.const.C__CATP__IP__ASSIGN__UNNUMBERED}]')
							{
								reset_ip('C__CATP__IP__ADDRESS_V4');
							}

							validate_ip();
						}
					}
				}
			});
		};

		var net_find_free_ipv6 = function (){
			isys_glob_enable_save();
			new Ajax.Request('?call=ipv6&ajax=1',
				{
					parameters: {
						'method': 'find_free_v6',
						'net_obj_id': ((! $net_selection_hidden.getValue().blank()) ? $net_selection_hidden.getValue() : '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]'),
						'ip_assignment': $('C__CMDB__CATG__IP__IPV6_ASSIGNMENT').getValue()
					},
					method: 'post',
					onSuccess: function(transport){
						var json = transport.responseJSON;

						if (json !== false) {
							$('C__CMDB__CATG__IP__IPV6_ADDRESS').setValue(json);
							$('C__CATG__IPV6__MESSAGES').update('[{isys type="lang" ident="LC__UNIVERSAL__AVAILABLE"}]')
								.setStyle({backgroundColor: '#99FF99'})
								.setOpacity(1)
								.show();
						} else {
							$('C__CATG__IPV6__MESSAGES').update('[{isys type="lang" ident="LC__CMDB__CATG__IP__NO_IP_FOUND"}]')
								.setStyle({backgroundColor: '#FF9999'})
								.setOpacity(1)
								.show();
						}

						window.setTimeout(function() {
							$('C__CATG__IPV6__MESSAGES').fade();
						}, 1000);
					}
				});
		};

		var validate_ipv6 = function (){
			// Validation of IPv6 addresses.
			var el = $('C__CMDB__CATG__IP__IPV6_ADDRESS');

			el.setStyle({background:''});

			isys_glob_enable_save();

			if (el.getValue().clean().empty()) {
				$('C__CATG__IPV6__MESSAGES').update('[{isys type="lang" ident="LC__UNIVERSAL__AVAILABLE"}]')
					.setStyle({backgroundColor: '#99FF99'})
					.setOpacity(1)
					.show();

				window.setTimeout(function() {
					$('C__CATG__IPV6__MESSAGES').fade();
				}, 1000);
				return;
			}

			if (checkipv6(el.getValue())) {
				new Ajax.Request('?call=ipv6&ajax=1',
					{
						parameters: {
							'method': 'is_ipv6_inside_range',
							'net_from': net_range.from,
							'net_to': net_range.to,
							'address': el.getValue()
						},
						method: "post",
						onSuccess: function(transport) {
							var inside_net_range = transport.responseText.evalJSON();

							// The IPv6 address lies outside the net-range.
							if (! inside_net_range) {
								el.setStyle({background:'#f99'});
								isys_glob_disable_save();
								return;
							}

							$('C__CATG__IPV6__MESSAGES').update('[{isys type="lang" ident="LC__UNIVERSAL__AVAILABLE"}]')
								.setStyle({backgroundColor: '#99FF99'})
								.setOpacity(1)
								.show();

							window.setTimeout(function() {
								$('C__CATG__IPV6__MESSAGES').fade();
							}, 1000);
						}.bind(this)
					});

                new Ajax.Request('?call=ipv6&ajax=1',
                    {
                        parameters: {
                            'method': 'find_ipv6_matching_layer3net',
                            'address': el.getValue()
                        },
                        method: "post",
                        onSuccess: function(transport) {
                            var matchingNetData = transport.responseText.evalJSON();

                            $('C__CATG__IP__NET__HIDDEN').setValue(matchingNetData.id);
                            $('C__CATG__IP__NET__VIEW').setValue(matchingNetData.obj_type + ' >> ' + matchingNetData.title);

                            $('C__CATG__IPV6__MESSAGES').update('[{isys type="lang" ident="LC__UNIVERSAL__AVAILABLE"}]')
                                .setStyle({backgroundColor: '#99FF99'})
                                .setOpacity(1)
                                .show();

                            window.setTimeout(function() {
                                $('C__CATG__IPV6__MESSAGES').fade();
                            }, 1000);
                        }
                    });
			} else {
				el.setStyle({background:'#f99'});
				isys_glob_disable_save();
			}
		};

		window.detach_ip = function () {
			if ($('ipv4_assign'))
			{
				reset_ip('C__CATP__IP__ADDRESS_V4');
			}
			else if ($('ipv6_assign'))
			{
				$('C__CATP__IP__ADDRESS_V6').setValue('');
			}

			validate_ip();
		};

		// Loads IP network of the currently selected net.
		var load_net_list = function() {
			var logbook_ele = $('LogbookCommentary');

			if ($net_selection_hidden.getValue())
			{

				if(logbook_ele.next() == undefined){
					// create new div element
					var ip_net_list = new Element('div', {id:'net_list', className:'mt10'});
					logbook_ele.up().insert(ip_net_list);
				}
				else{
					var ip_net_list = $('net_list');
				}

				new Ajax.Request('[{$ajax_url_ip_list}]', {
					parameters: {
						net_object:$net_selection_hidden.getValue(),
						net_type:$netType.getValue()
					},
					method: 'post',
					onSuccess:function (transport) {
						ip_net_list.update(transport.responseText);
					}.bind(this)
				});
			}
		};

		var ip_messages = function(msg_level, error_level) {
			var message,
				boxCssClass;

			switch(msg_level) {
				case 1:
					boxCssClass= 'box-red';

					switch(error_level) {
						case 1:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_IS_NOT_INSIDE_RANGE_OF_DHCP"}]';
							break;
						case 2:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_IS_INSIDE_RANGE_OF_DHCP"}]';
							break;
						case 3:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_IS_NOT_AVAILABLE"}]'.replace(':net', $netSelectionView.getValue());
							break;
						case 4:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_NO_RANGE_AVAILABLE_FOR"}]: [{isys type="lang" ident="LC__CMDB__CATS__NET__DHCP"}]';
							break;
						case 5:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_NO_RANGE_AVAILABLE_FOR"}]: [{isys type="lang" ident="LC__CATP__IP__ASSIGN__DHCP_RESERVED"}]';
							break;
						case 6:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_NO_RANGE_AVAILABLE_FOR"}]: [{isys type="lang" ident="LC__CMDB__CATS__NET__DHCP"}], [{isys type="lang" ident="LC__CATP__IP__ASSIGN__DHCP_RESERVED"}]';
							break;
						case 7:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_NO_RANGE_AVAILABLE_FOR"}]: [{isys type="lang" ident="LC__CATP__IP__ASSIGN__STATIC"}]';
							break;
						case 8:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__NET_NO_RANGE"}]';
							break;
						case 9:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_IS_OUTSIDE_RANGE"}]';
							break;
						case 10:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__WRONG_IP_ADDRESS"}]';
							break;
					}

					// Because of the wrong data, we don't allow the user to save.
					isys_glob_disable_save();
					break;

				case 2:
					// We enable the "save"-button and display a nice message to the user.
					isys_glob_enable_save();

					message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_IS_AVAILABLE"}]'.replace(':net', $netSelectionView.getValue());
					boxCssClass= 'box-green';
					break;

				case 3:
					// We enable the "save"-button and display a nice message to the user.
					isys_glob_enable_save();

					message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_IS_AVAILABLE"}]'.replace(':net', $netSelectionView.getValue());
					boxCssClass= 'box-yellow';

					switch(error_level) {
						case 1:
							message = '[{isys type="lang" ident="LC__CMDB__CATG__IP__IP_ALREADY_IN_USE"}]'.replace(':net', $netSelectionView.getValue());
							break;
					}

					break;
			}

			$ip_message.update(message).removeClassName('box-green').removeClassName('box-yellow').removeClassName('box-red').addClassName(boxCssClass).show();
		};

		var is_ipv4_free = function (ipv4) {
            $ip_message.removeClassName('box-green').removeClassName('box-yellow').removeClassName('box-red')
                       .update(new Element('img', {src:window.dir_images + 'ajax-loading.gif', className:'vam'}))
                       .insert(new Element('span', {className:'ml5 vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

			new Ajax.Request('?call=calc_ip_address&func=is_free_v4&ajax=1', {
				parameters: {
					net_obj_id: ((!$net_selection_hidden.getValue().blank()) ? $net_selection_hidden.getValue() : '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]'),
					objID: '[{$smarty.get.objID|strip_tags|escape}]',
					ip: ipv4
				},
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					if (json.net)
					{
						$('C__CATG__IP__NET__HIDDEN').setValue(json.net.netID);

						// At first, we take a look of which type we are currently editing.
						if ($netType.getValue() == '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]')
						{
							net_assigned_v4();
						}
					}

					if (json.success)
					{
						ip_messages(2, 0);
					}
					else
					{
						ip_messages(3, 1);
					}
				}
			});
		};

		[{if isys_glob_is_edit_mode()}]

			[{if $smarty.const.C__MODULE__PRO}]
				$('net_list_button').on('click', function () {
					load_net_list();
				});
			[{/if}]

			idoit.callbackManager.registerCallback('validate_ip', function () {
				validate_ip();
			}).registerCallback('net_assigned', function () {
				if ($net_selection_hidden.getValue() > 0) {
					// At first, we take a look of which type we are currently editing.
					if ($netType.getValue() == '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]') {
						net_assigned_v4();
					}

					if ($netType.getValue() == '[{$smarty.const.C__CATS_NET_TYPE__IPV6}]') {
						net_assigned_v6();
					}

					$netZone.fire('load:fromLayer3');

					validate_ip();
				}
			}).registerCallback('net_detached', function () {
				reset_all('C__CATP__IP__ADDRESS_V4', 'C__CATP__IP__SUBNETMASK_V4', 'C__CATP__IP__ADDRESS_V4_FROM', 'C__CATP__IP__ADDRESS_V4_TO');

				if ($netType.getValue() == '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]') {
					$net_selection_hidden.setValue('[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]');
				} else if ($netType.getValue() == '[{$smarty.const.C__CATS_NET_TYPE__IPV6}]') {
					$net_selection_hidden.setValue('[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]');
				}

				idoit.callbackManager.triggerCallback('net_assigned');
			});

			if ($btn_ip_calc) {
				new Tip(
					$btn_ip_calc,
					new Element('p', {className: 'p5', style: 'font-size:12px;'}).update('[{isys type="lang" ident="LC__CATG__IP__CALCULATE_FREE_ADDRESS" p_bHtmlEncode=false}]'),
					{stem: false, style: 'darkgrey'});

				$btn_ip_calc.on('click', function () {
					net_find_free_ipv4();
				});
			}

			if ($btn_detach_ip) {
				new Tip(
					$btn_detach_ip,
					new Element('p', {className: 'p5', style: 'font-size:12px;'}).update('[{isys type="lang" ident="LC__CATG__IP__DETACH_IP_ADDRESS" p_bHtmlEncode=false}]'),
					{stem: false, style: 'darkgrey'}
				);

				$btn_detach_ip.on('click', window.detach_ip.bindAsEventListener());
			}

			if($('btn_ip_calc_v6')) {
				$('btn_ip_calc_v6').on('click', function() {
					net_find_free_ipv6();
				});
			}

			$('ipv4_assign').on('change', function() {
				net_find_free_ipv4();
			});

			$('C__CMDB__CATG__IP__IPV6_ASSIGNMENT').on('change', function() {
				net_find_free_ipv6();
			});

			$('C__CMDB__CATG__IP__IPV6_ADDRESS').on('change', function() {
				validate_ipv6();
			});

			if ($netType) {
				$netType.on('change', function () {
					var net = $netType.getValue();

					$('contentBottomContent').select('.form').invoke('hide');
                    $('contentBottomContent').select('.ip-' + net).invoke('show');

					if (net == '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]' || net == '[{$smarty.const.C__CATS_NET_TYPE__IPV6}]') {
						$('ip_additional').show();

						if (net == '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]') {
							if ($net_selection_hidden.getValue() == '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]') {
								$net_selection_hidden.setValue('[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]');
								$('C__CATG__IP__NET__VIEW').setValue(global_net_ipv4_title);
							}
							net_assigned_v4();
						} else if (net == '[{$smarty.const.C__CATS_NET_TYPE__IPV6}]') {
							if ($net_selection_hidden.getValue() == '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]') {
								$net_selection_hidden.setValue('[{$smarty.const.C__OBJ__NET_GLOBAL_IPV6}]');
								$('C__CATG__IP__NET__VIEW').setValue(global_net_ipv6_title);
							}
							net_assigned_v6();
						}
					} else {
						$('ip_additional').hide();
					}
				});

				$netType.simulate('change');
			}

			var $dns_input = $('C__CATP__IP__SEARCH_DOMAIN'),
				dns_domain_chosen = null;

			// Function for refreshing the DNS domain chosen.
			idoit.callbackManager
				.registerCallback('cmdb-catg-ip-dns_domain-update', function (selected) {
					if (dns_domain_chosen !== null) {
						dns_domain_chosen.destroy();
					}

					$dns_input.setValue(selected).fire('chosen:updated');
					dns_domain_chosen = new Chosen($dns_input, {
						disable_search_threshold: 10,
						search_contains:          true
					});
				})
				.triggerCallback('cmdb-catg-ip-dns_domain-update', $F('C__CATP__IP__SEARCH_DOMAIN'));

			$netZone.on('load:fromLayer3', function () {
				new Ajax.Request('?call=zone&func=retrieve_zone_ranges&ajax=1', {
					parameters: {
						objID: $net_selection_hidden.getValue()
					},
					method:     'post',
					onSuccess:  function (xhr) {
						var json = xhr.responseJSON;

						if (is_json_response(xhr) && json.success)
						{
							$netZone.update(new Element('option', {value:-1}).update(' - '));

							for (i in json.data)
							{
								if (json.data.hasOwnProperty(i))
								{
									netZoneOptions[json.data[i].id] = {
										"color": json.data[i].color,
										"domain": json.data[i].domain
									};

									$netZone.insert(new Element('option', {value: json.data[i].id})
										.update(json.data[i].name + ' (' + json.data[i].from_ip + ' - ' + json.data[i].to_ip + ')'))
								}
							}
						}
						else
						{
							idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
						}
					}
				});
			});

		$netZone.on('change', function () {
			var color = 'transparent';

			if (netZoneOptions.hasOwnProperty($netZone.getValue()) && netZoneOptions[$netZone.getValue()]['color'].indexOf('#') === 0)
			{
				color = netZoneOptions[$netZone.getValue()]['color'];
			}

			// Change the color, according to the zone color.
			$netZone.up('.input-group').down('.cmdb-marker').setStyle({backgroundColor: color});

			// Also set the primary Domain according to the one, defined in the zone options.
			if (netZoneOptions[$netZone.getValue()]['domain']) {
				$primaryDomain.setValue(netZoneOptions[$netZone.getValue()]['domain']);
			}

			$btn_ip_calc.simulate('click');
		});

		if ($fqdnAdderButton && $fqdnList) {
			$fqdnAdderButton.on('click', function () {
				$fqdnList
					.insert(new Element('div', {className:'mb5 input-group input-size-normal'})
						.update(new Element('input', {className:'input', type:'text', placeholder:'', name:'C__CATP__IP__HOSTNAME_ADDITIONAL[]'}))
						.insert(new Element('div', {className:'input-group-addon input-group-addon-unstyled', style:'min-width:10px;'}).update('.'))
						.insert(new Element('input', {className:'input', type:'text', placeholder:'', name:'C__CATP__IP__DOMAIN_ADDITIONAL[]'})))
					.insert(new Element('button', {type:'button', className:'fl ml5 btn'})
						.update(new Element('img', {src:window.dir_images + 'icons/silk/delete.png'})))
					.insert(new Element('br', {className:'cb'}));
			});

			$fqdnList.on('click', 'button', function (ev) {
				var $button = ev.findElement('button');

				$button.previous('.input-group').remove();
				$button.next('br').remove();
				$button.remove();
			});
		}
		[{/if}]
	})();
</script>
