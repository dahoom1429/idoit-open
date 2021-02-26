<style type="text/css">
	#net-collision-checker-result ul {
		margin: 0 15px;
	}
</style>

<table class="contentTable m0">
	[{if $supernets}]
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__CATS__NET__ASSIGNED_SUPERNET"}]</td>
		<td class="value"><span style="margin-left:20px;">[{$supernets}]</span></td>
	</tr>
	<tr>
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	[{/if}]
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__TYPE" name="C__CATS__NET__TYPE__VIEW"}]</td>
		<td class="value">[{isys type="f_dialog" p_strTable="isys_net_type" name="C__CATS__NET__TYPE" id="C__CATS__NET__TYPE" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr class="IPv4 IP">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__NET" name="C__CATS__NET__NET_V4"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__NET__NET_V4" p_strClass="input input-mini"}]
			<span class="[{if isys_glob_is_edit_mode()}]fl p5[{else}]ml5 mr5[{/if}]">/</span>
			[{isys type="f_text" name="C__CATS__NET__CIDR" p_strClass="input input-mini ml5" p_nSize="2" p_bInfoIconSpacer="0" p_nMaxLen="2"}]
		</td>
	</tr>
	<tr class="IPv4 IP">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__MASK" name="C__CATS__NET__MASK_V4"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__NET__MASK_V4"}]
			<select class="input input-mini" id="netmask_selection" name="netmask_selection" style="margin-left:19px; [{if !isys_glob_is_edit_mode()}]display:none;[{/if}]"></select>
			<input type="hidden" value="[{$net_id}]" name="net_id" id="net_id">
		</td>
	</tr>
	<tr class="IPv4 IP">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__ADDRESS_RANGE" name="C__CATS__NET__ADDRESS_RANGE_FROM_V4"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__NET__ADDRESS_RANGE_FROM_V4" p_strClass="input-mini" p_bReadonly="1"}]
			<span class="[{if isys_glob_is_edit_mode()}]fl p5[{else}]ml5 mr5[{/if}]">-</span>
			[{isys type="f_text" name="C__CATS__NET__ADDRESS_RANGE_TO_V4" p_strClass="input-mini" p_bReadonly="1" p_bInfoIconSpacer="0"}]
		</td>
	</tr>

	[{if isys_glob_is_edit_mode()}]
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__CATS__NET__CHECK_NET_COLLISION"}]</td>
		<td class="value">
			<a id="net-collision-checker" class="btn ml20">
				<img src="[{$dir_images}]icons/silk/zoom.png" class="mr5"/><span>[{isys type="lang" ident="LC__CMDB__CATS__NET__CHECK"}]</span>
			</a>

			<div id="net-collision-checker-result" class="ml10" style="padding:3px;"></div>
		</td>
	</tr>
	[{/if}]

	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>

	<tr class="IPv4 IPv6 IP">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__DEF_GW" name="C__CATS__NET__DEF_GW_V4"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATS__NET__DEF_GW_V4" p_bSort=false}]</td>
	</tr>

	[{* IPv6 below this comment *}]

	<tr class="IPv6 IP">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__NET" name="C__CATS__NET__NET_V6"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__NET__NET_V6" p_strClass="input-small"}]
            <span class="[{if isys_glob_is_edit_mode()}]fl p5[{else}]ml5 mr5[{/if}]">/</span>
			[{isys type="f_text" name="C__CATS__NET__NET_V6_CIDR" p_bInfoIconSpacer="0" p_strClass="input-mini"}]
		</td>
	</tr>
	<tr class="IPv6 IP">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__ADDRESS_RANGE" name="C__CATS__NET__ADDRESS_RANGE_FROM"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__NET__ADDRESS_RANGE_FROM" p_strClass="input-small" p_bReadonly=1}]
            <span class="[{if isys_glob_is_edit_mode()}]fl p5[{else}]ml5 mr5[{/if}]">/</span>
			[{isys type="f_text" name="C__CATS__NET__ADDRESS_RANGE_TO" p_strClass="input-small" p_bInfoIconSpacer="0" p_bReadonly=1}]
		</td>
	</tr>

	[{* Standard stuff for all net-types below this comment *}]

	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__NET__REVERSE_DNS" name="C__CATS__NET__REVERSE_DNS"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__NET__REVERSE_DNS" id="C__CATS__NET__REVERSE_DNS" p_strClass="input-small"}]</td>
	</tr>
	<tr class="IP IPv4 IPv6">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__DNS_SERVER" name="C__CATS__NET__ASSIGNED_DNS_SERVER__VIEW"}]</td>
		<td class="value">
			[{isys
			name="C__CATS__NET__ASSIGNED_DNS_SERVER"
			type="f_popup"
			p_strPopupType="browser_object_ng"
			multiselection=true
			secondSelection="true"
			secondList="isys_cmdb_dao_category_s_net::object_browser2"
			secondListFormat="isys_cmdb_dao_category_s_net::format_selection"}]
		</td>
	</tr>
	<tr class="IP IPv4 IPv6">
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__DNS_DOMAIN" name="C__CATS__NET__DNS_DOMAIN"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATS__NET__DNS_DOMAIN"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__LAYER2_NET" name="C__CATS__NET__LAYER2"}]</td>
		<td class="value">[{isys name="C__CATS__NET__LAYER2" type="f_popup" p_strPopupType="browser_object_ng" multiselection=true}]</td>
	</tr>
</table>

<script>
	(function () {
		'use strict';

		var $net_v4 = $('C__CATS__NET__NET_V4'),
			$net_v4_cidr = $('C__CATS__NET__CIDR'),
			$net_type = $('C__CATS__NET__TYPE'),
			$address_range_from = $('C__CATS__NET__ADDRESS_RANGE_FROM_V4'),
			$address_range_to = $('C__CATS__NET__ADDRESS_RANGE_TO_V4'),
			$net_mask = $('C__CATS__NET__MASK_V4'),
			$net_mask_select = $('netmask_selection'),
			$net_collision_check = $('net-collision-checker'),
			$net_collision_check_result = $('net-collision-checker-result');

		var $net_v6 = $('C__CATS__NET__NET_V6'),
			$net_v6_cidr = $('C__CATS__NET__NET_V6_CIDR');

		var correct_range = ['0', '128', '192', '224', '240', '248', '252', '254', '255'],
			min_netmask = '0.0.0.0',
			max_netmask = '255.255.255.255';

		var calcBits2Val = function(p_bits){
			var val,
				bits = 8,
				bitval = 0;

			val = bits - p_bits;

			while(val != bits){
				bitval += Math.pow(2, val);
				val+=1;
			}

			return bitval;
		};

		var calcNetmask = function (cidr) {
			var net_mask = [];

			if (cidr > 32) {
				$net_v4_cidr.setValue(32);
				cidr = 32;
			}

			if (cidr >= 8) {
				net_mask[0] = 255;
				cidr -= 8;
			} else if (cidr <= 0) {
				net_mask[0] = 0;
			} else {
				net_mask[0] = calcBits2Val(cidr);
				cidr = 0;
			}

			if (cidr >= 8) {
				net_mask[1] = 255;
				cidr -= 8;
			} else if (cidr <= 0) {
				net_mask[1] = 0;
			} else {
				net_mask[1] = calcBits2Val(cidr);
				cidr = 0;
			}

			if (cidr >= 8) {
				net_mask[2] = 255;
				cidr -= 8;
			} else if (cidr <= 0) {
				net_mask[2] = 0;
			} else {
				net_mask[2] = calcBits2Val(cidr);
				cidr = 0;
			}

			if (cidr >= 8) {
				net_mask[3] = 255;
			} else if (cidr == 0) {
				net_mask[3] = 0;
			} else {
				net_mask[3] = calcBits2Val(cidr);
			}

			net_mask = net_mask.join('.');

			$net_mask.setValue(net_mask);

			$net_mask_select.setValue(IPv4.ip2long(net_mask));

			setNetArea();
		};

		var setNetArea = function () {
			var full_bits = 255,
				net_parts = $net_v4.getValue().split('.'),
				net_mask_parts = $net_mask.getValue().split('.'),
				ip_tmp = {},
				tmp = [];

			if (net_parts.length != 4 || $net_v4.getValue().blank()) {
				$net_v4.setValue('0.0.0.0').highlight();

				net_parts = $net_v4.getValue().split('.');
			}

			if (net_mask_parts.length != 4 || $net_mask.getValue().blank()) {
				$net_mask.setValue($net_mask_select.down('option:selected').innerHTML).highlight();

				net_mask_parts = $net_mask.getValue().split('.');
			}

			$net_v4.removeClassName('box-red');
			$net_mask.removeClassName('box-red');

			if ($net_v4_cidr.getValue() == 31) {
				tmp[0] = net_parts[0] & net_mask_parts[0].toString(2);
				tmp[1] = net_parts[1] & net_mask_parts[1].toString(2);
				tmp[2] = net_parts[2] & net_mask_parts[2].toString(2);
				tmp[3] = net_parts[3] & net_mask_parts[3].toString(2);

				ip_tmp.from = tmp.join('.');
				ip_tmp.from_long = IPv4.ip2long(ip_tmp.from);

				tmp[0] = net_parts[0] | (~net_mask_parts[0].toString(2) & full_bits);
				tmp[1] = net_parts[1] | (~net_mask_parts[1].toString(2) & full_bits);
				tmp[2] = net_parts[2] | (~net_mask_parts[2].toString(2) & full_bits);
				tmp[3] = net_parts[3] | (~net_mask_parts[3].toString(2) & full_bits);

				ip_tmp.to = tmp.join('.');
				ip_tmp.to_long = IPv4.ip2long(ip_tmp.to);

				if (ip_tmp.from_long > ip_tmp.to_long) {
					$address_range_from.setValue(ip_tmp.to);
					$address_range_to.setValue(ip_tmp.from);
				} else {
					$address_range_from.setValue(ip_tmp.from);
					$address_range_to.setValue(ip_tmp.to);
				}

				return;
			}

			if ($net_v4_cidr.getValue() == 32) {
				$address_range_from.setValue($net_v4.getValue());

				$address_range_to.setValue($address_range_from.getValue());
				return;
			}

			tmp[0] = net_parts[0].toString(2) & net_mask_parts[0].toString(2);
			tmp[1] = net_parts[1].toString(2) & net_mask_parts[1].toString(2);
			tmp[2] = net_parts[2].toString(2) & net_mask_parts[2].toString(2);
			tmp[3] = (net_parts[3].toString(2) & net_mask_parts[3].toString(2)) + 1;

			ip_tmp.from = tmp.join('.');
			ip_tmp.from_long = IPv4.ip2long(ip_tmp.from);

			tmp[0] = net_parts[0].toString(2) | (~net_mask_parts[0].toString(2) & full_bits);
			tmp[1] = net_parts[1].toString(2) | (~net_mask_parts[1].toString(2) & full_bits);
			tmp[2] = net_parts[2].toString(2) | (~net_mask_parts[2].toString(2) & full_bits);
			tmp[3] = (net_parts[3].toString(2) | (~net_mask_parts[3].toString(2) & full_bits)) - 1;

			ip_tmp.to = tmp.join('.');
			ip_tmp.to_long = IPv4.ip2long(ip_tmp.to);

			if (ip_tmp.from_long > ip_tmp.to_long) {
				$address_range_from.setValue(ip_tmp.to);
				$address_range_to.setValue(ip_tmp.from);
			} else {
				$address_range_from.setValue(ip_tmp.from);
				$address_range_to.setValue(ip_tmp.to);
			}

			if ($net_v4_cidr.getValue() > 0) {
				tmp = $address_range_from.getValue().split('.');
				tmp[3] --;

				$net_v4.setValue(tmp.join('.'));
			}
		};

		var calcNetMaskBit = function (p_val) {
			var bit = 7;
			var counter_bits = 7;
			var bit_value = 0;

			while (p_val > bit_value) {
				bit_value += Math.pow(2, bit);
				bit--;
			}
			counter_bits = counter_bits - bit;
			return counter_bits;
		};

		var set_netmask_options = function(min_mask, max_mask){
			var mask_arr = max_mask.split('.'),
				min_value = IPv4.ip2long(min_mask),
				max_value = IPv4.ip2long(max_mask),
				counter = 3,
				bits = 0,
				new_val,
				netmask_value = IPv4.ip2long($net_mask.getValue());

			if (! $net_v4) {
				return;
			}

			var selIndex = 0,
				found = false,
				new_ip;

			if($('net_id').value == '[{$smarty.const.C__OBJ__NET_GLOBAL_IPV4}]'){
				$net_mask_select.hide();
				return;
			}

			while (min_value < max_value) {
				if (mask_arr[counter] > 0) {
					$net_mask_select.insert(new Element('option', {value:max_value}).update(IPv4.long2ip(max_value)));

					// Determine Bits.
					bits = calcNetMaskBit(mask_arr[counter]) - 1;
					new_val = calcBits2Val(bits);

					if (max_value == netmask_value) {
						found = true;
					}

					if (!found) {
						selIndex++;
					}
				} else {
					new_val = 0;
				}

				for (var i = 0; i < mask_arr.length; i++) {
					if(i == counter){
						mask_arr[i] = new_val;
					}
				}

				if(new_val == 0){
					counter--;
				}

				new_ip = mask_arr.join('.');
				max_value = IPv4.ip2long(new_ip);
			}

			$net_mask_select.insert(new Element('option', {value:min_value}).update(IPv4.long2ip(min_value)));
		};

		// Validation of IPv6 addresses.
		var validate_ipv6 = function() {
			var errors = false;

			if (checkipv6($net_v6.getValue())) {
				$net_v6.setStyle({background:''});
			} else {
				$net_v6.setStyle({background:'#f99'});
				errors = true;
			}

			return !errors;
		};

		var calculate_ipv6_range = function () {
			var ip = $net_v6.getValue(),
				cidr = $('C__CATS__NET__NET_V6_CIDR').getValue();

			if (ip.length >= 2 && cidr.length >= 1) {
				new Ajax.Request('?call=ipv6&ajax=1', {
					parameters: {
						method: 'calculate_ipv6_range',
						ip: ip,
						cidr: cidr
					},
					method: 'post',
					onSuccess: function (transport) {
						var json = transport.responseJSON;
						$('C__CATS__NET__ADDRESS_RANGE_FROM').value = json.from;
						$('C__CATS__NET__ADDRESS_RANGE_TO').value = json.to;
					}
				});
			} else {
				// Display a notice, because the IP address or the CIDR was empty.
			}
		};

		if ($net_type) {
			$net_type.on('change', function () {
                showNetTypeFields($net_type.getValue());
			});

			$net_type.simulate('change');
		} else {
            showNetTypeFields('[{$net_type}]');
        }

		function showNetTypeFields(type) {
            $$('.contentTable .IP').invoke('addClassName', 'hide');

            switch (type) {
                case '[{$smarty.const.C__CATS_NET_TYPE__IPV6}]':
                    $$('.contentTable .IPv6').invoke('removeClassName', 'hide');
                    break;
                case '[{$smarty.const.C__CATS_NET_TYPE__IPV4}]':
                    $$('.contentTable .IPv4').invoke('removeClassName', 'hide');
                    break;
                default:
                    $$('.contentTable :not(.IPv4,.IPv6)').invoke('removeClassName', 'hide');
            }
        }

		if ($net_collision_check) {
			$net_collision_check.on('click', function () {
				var ip_from = $address_range_from.getValue(),
					ip_to = $address_range_to.getValue();

				$net_collision_check
					.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
					.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');


				if (isNaN(IPv4.ip2long(ip_from)) || isNaN(IPv4.ip2long(ip_to))) {
					return;
				}

				new Ajax.Request('?call=net&m=check_net_collision&ajax=1',
					{
						parameters: {
							from: ip_from,
							to: ip_to,
							net_type: $net_type.getValue(),
							obj_id: '[{$net_id}]'
						},
						method: 'post',
						onSuccess: function (transport) {
							var json = transport.responseJSON;

							$net_collision_check
								.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/zoom.png')
								.next('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET__CHECK"}]');

							$net_collision_check_result.removeClassName('box-green').removeClassName('box-red');

							if (json.success) {
								if (json.data === null) {
									$net_collision_check_result
										.addClassName('box-green')
										.update('[{isys type="lang" ident="LC__CMDB__CATS__NET__CHECK_NO_COLLISIONS"}]');
								} else {
									$net_collision_check_result
										.addClassName('box-red')
										.update('[{isys type="lang" ident="LC__CMDB__CATS__NET__CHECK_COLLISIONS_DETECTED"}]: ' + json.data.join(', '));
								}

								$net_collision_check_result.setStyle({display: 'inline'})
							}
						}
					});
			});
		}

		if ($net_v4_cidr) {
			$net_v4_cidr.on('change', function () {
				calcNetmask($net_v4_cidr.getValue());
			});
		}

		if ($net_collision_check_result) {
			$net_collision_check_result.hide();
		}

		if ($net_mask_select) {
			$net_mask_select.on('change', function () {
				var ip = $net_mask_select.down('option:selected').innerHTML,
					counter = 0,
					bits = 0;

				$net_mask.setValue(ip);

				ip.split('.').each(function(val){
					bits += calcNetMaskBit(val);
					counter++;
				});

				$net_v4_cidr.setValue(bits);
				calcNetmask(bits);
			});
		}

		if ($net_v4) {
			$net_v4.on('change', function () {
				$net_v4.removeClassName('box-red');

				if (! IPv4.valid_ip($net_v4.getValue())) {
					$net_v4.addClassName('box-red');
				} else {
					setNetArea();
				}
			});
		}

		if ($net_mask) {
			$net_mask.on('change', function () {
				var cidr, net_mask_parts;

				net_mask_parts = $net_mask.getValue().split('.');

				if ((! IPv4.valid_ip($net_mask.getValue())) || correct_range.indexOf(net_mask_parts[0]) == -1 || correct_range.indexOf(net_mask_parts[1]) == -1 || correct_range.indexOf(net_mask_parts[2]) == -1 || correct_range.indexOf(net_mask_parts[3]) == -1) {
					$net_mask.setValue($net_mask_select.down('option:selected').innerHTML).highlight();
				} else {
					cidr = parseInt(calcNetMaskBit(net_mask_parts[0])) + parseInt(calcNetMaskBit(net_mask_parts[1])) + parseInt(calcNetMaskBit(net_mask_parts[2])) + parseInt(calcNetMaskBit(net_mask_parts[3]));

					$net_v4_cidr.setValue(cidr);

					calcNetmask(cidr);
				}
			});
		}

		if ($net_v6) {
			$net_v6.on('change', function () {
				validate_ipv6();
				calculate_ipv6_range();
			});
		}

		if ($net_v6_cidr) {
			$net_v6_cidr.on('change', function () {
				calculate_ipv6_range();
			});

			$net_v6_cidr.on('keyup', function () {
				var value = $net_v6_cidr.getValue().replace(/\D/, '');

				if (value > 128) {
					value = 128;
				}

				if (value < 0) {
					value = 0;
				}

				$net_v6_cidr.setValue(value);
			});
		}

		[{if isys_glob_is_edit_mode()}]
		set_netmask_options(min_netmask, max_netmask);

		if ($net_v4_cidr) {
			$net_v4_cidr.simulate('change');
		} else if ($net_v6_cidr) {
			$net_v6_cidr.simulate('change');
		}

		var $dns_input = $('C__CATS__NET__DNS_DOMAIN'),
			dns_domain_chosen = null;

		// Function for refreshing the DNS domain chosen.
		idoit.callbackManager
			.registerCallback('cmdb-cats-net-dns_domain-update', function (selected) {
				if (dns_domain_chosen !== null) {
					dns_domain_chosen.destroy();
				}

				$dns_input.setValue(selected).fire('chosen:updated');
				dns_domain_chosen = new Chosen($dns_input, {
					disable_search_threshold: 10,
					search_contains:          true
				});
			})
			.triggerCallback('cmdb-cats-net-dns_domain-update', $F('C__CATS__NET__DNS_DOMAIN'));
		[{/if}]
	})();
</script>
