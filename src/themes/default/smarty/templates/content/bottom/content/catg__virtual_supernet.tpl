[{if $net}]

	<style type="text/css">
		#ip-table tr.reserved.line1 {
			background-color:#abefab !important
		}
		#ip-table tr.reserved:hover {
			background-color:#abefab !important
		}

		#ip-table tr.reserved.line1:hover {
			background-color:#9fed9f !important
		}

		#table-scroller {
            position: absolute;
            right: 0;
			top: 0;
            width: 30%;
		}
	</style>

	<table id="ip-table" class="contentInfoTable mainTable border m5" cellspacing="0" cellpadding="0">
		<colgroup>
			<col style="width:75px;" />
		</colgroup>
		<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__OBJTYPE__SUPERNET"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__SUBNET"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__CONTACT"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__STATISTIC"}]</th>
			</tr>
		</thead>
		<tbody id="ip-table-body" class="user-selectable">

		<tr class="default-gateway" style="line-height: 30px;">
			<td colspan="4">
				<img src="[{$dir_images}]icons/silk/world.png" class="vam" /> <strong>[{$net.isys_cats_net_list__address}]/[{$net.isys_cats_net_list__cidr_suffix}]</strong> ([{$net.isys_cats_net_list__mask}])
			</td>
		</tr>

		[{foreach from=$subnets item=row}]
		<tr class="subnet" style="height: 30px;" data-id="[{$row.isys_cats_net_list__id}]" data-from="[{$row.isys_cats_net_list__address_range_from}]" data-to="[{$row.isys_cats_net_list__address_range_to}]">
			<td style="vertical-align:middle; text-align:center;"><img src="[{$dir_images}]icons/silk/chart_organisation.png" /></td>
			<td>
	            <span class="bold">[{$row.title}]</span> ([{$row.isys_cats_net_list__mask}])<br />
				<span class="grey">[{$row.isys_cats_net_list__address_range_from}] - [{$row.isys_cats_net_list__address_range_to}]</span>
			</td>
			<td>
				[{$row.primary_contact}]
			</td>
			<td>
				[{if $row.isys_net_type__const == 'C__CATS_NET_TYPE__IPV4'}]
					<span class="used-addresses">[{$row.used_adresses}]</span>/<span class="all-addresses">[{$row.isys_cats_net_list__address_range_to_long-$row.isys_cats_net_list__address_range_from_long}]</span>
					[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__USED_ADDRESSES"}]
				[{else}]
					<span class="used-addresses">[{$row.used_adresses}]</span>
					[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__USED_ADDRESSES"}]
				[{/if}]
			</td>
		</tr>

		[{/foreach}]
		</tbody>
	</table>

	<div id="table-scroller" class="m5">
	    <div class="border bg-white">
			<h3 class="header gradient p5">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__LEGEND"}]</h3>

			<p class="p5 default-gateway">Supernet</p>
	        <hr />
			<p class="p5">Subnet</p>
		</div>

		<br />

	    <div class="border bg-white">
	        <h3 class="header gradient p5">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__STATISTIC"}]</h3>

	        <p class="m5"><span id="statistic-used-addresses">0</span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__USED_ADDRESSES"}]</p>
	        <hr />

	        [{if $row.isys_net_type__const == 'C__CATS_NET_TYPE__IPV4'}]

	        <p class="m5"><span id="statistic-free-addresses">0</span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__FREE_ADDRESSES"}]</p>
	        <hr />
	        <p class="m5"><span id="statistic-total-addresses">0</span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TOTAL"}]</p>
	        <hr />

	        [{/if}]

	        <p class="m5">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS"}] [{$net.isys_cats_net_list__address}]</p>
	        <hr />
	        <p class="m5">[{isys type="lang" ident="LC__CATP__IP__SUBNETMASK"}] [{$net.isys_cats_net_list__mask}] (/[{$net.isys_cats_net_list__cidr_suffix}])</p>
	        <hr />
	        <p class="m5">[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_RANGE"}] [{$net.isys_cats_net_list__address_range_from}] - [{$net.isys_cats_net_list__address_range_to}]</p>
	    </div>
	</div>

	<br class="cb" />

	<script type="text/javascript">
		var // subnet_masks = '[{$subnet_masks}]'.evalJSON(),
			free_ranges = '[{$free_ranges}]'.evalJSON(),
			used_addresses = 0,
			all_addresses = 0,
	        previous,
	        $new_net_button = new Element('a', {className:'btn btn-small vam new-net-button'})
	            .update(new Element('img', {className:'mr5', src:'[{$dir_images}]icons/silk/add.png'}))
	            .insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATG__SUPERNET__NEW_NET"}]')),
	        el_used_addresses = $('statistic-used-addresses'),
	        el_free_addresses = $('statistic-free-addresses'),
	        el_total_addresses = $('statistic-total-addresses');

		$$('.used-addresses').each(function(el) {
	        used_addresses += parseInt(el.innerHTML);
		});

		$$('.all-addresses').each(function(el) {
	        all_addresses += parseInt(el.innerHTML);
		});

		if (el_used_addresses) {
	        el_used_addresses.update(used_addresses);
	    }

	    if (el_free_addresses) {
	        el_free_addresses.update(all_addresses - used_addresses);
	    }

		if (el_total_addresses) {
	        el_total_addresses.update(all_addresses);
	    }

	    window.search_free_subnet_range = function (element, from, to) {
		    var found = false,
				free_range_row,
				address_amount;

	        for (var i in free_ranges) {
	            if (free_ranges.hasOwnProperty(i)) {
	                if (from === false && ((ip2long('[{$net.isys_cats_net_list__address_range_from}]') - 1) <= free_ranges[i].from && free_ranges[i].to <= to)) {
	                    // Search for free ranges at the end of the supernet.
	                    found = 1;
	                } else if (to === false && (from <= free_ranges[i].from && to <= free_ranges[i].to)) {
	                    // Search for free ranges at the beginning of the supernet.
	                    found = 2;
	                } else if (from <= free_ranges[i].from && free_ranges[i].to <= to) {
	                    // Search for free ranges between to subnets.
	                    found = 1;
	                }

		            address_amount = (free_ranges[i].to - free_ranges[i].from);

	                free_range_row = new Element('tr', {className:'reserved', 'data-from':long2ip(free_ranges[i].from), 'data-to':long2ip(free_ranges[i].to), 'data-address-amount':address_amount})
			                .update(
					            new Element('td')
			                ).insert(
			                    new Element('td', {colspan:2}).update(
	                                new Element('p', {style:'margin-top:3px;'}).update('[{isys type="lang" ident="LC__CMDB__CATG__SUPERNET__FREE_RANGE"}]: ' + long2ip(free_ranges[i].from) + ' - ' + long2ip(free_ranges[i].to) + '<br />= ' + address_amount + ' [{isys type="lang" ident="LC__REPORT__VIEW__LAYER3_NETS__IP_ADDRESSES"}]')
			                    )
	                        ).insert(
	                            new Element('td')// .update($new_net_button.outerHTML)
	                        );

	                if (found == 1) {
	                    element.insert({before: free_range_row});
		                return;
	                } else if (found == 2) {
	                    element.insert({after: free_range_row});
	                    return;
	                }
	            }
	        }
	    };

		var last = 0,
			last_el;

	    $$('tr.subnet').each(function(el, i) {
		    var from = IPv4.ip2long(el.readAttribute('data-from')),
				to = IPv4.ip2long(el.readAttribute('data-to'));

		    // Find out, where to add the "free-range" row...
		    if (i == 0) {
			    // This is the first found element - Just look for any "free IP range" with a smaller "to" range than this "from".
			    window.search_free_subnet_range(el, false, from);
	            last = to;
	            last_el = el;
	        } else {
	            window.search_free_subnet_range(el, last, from);
	            last = to;
	            last_el = el;
	        }
	    });

		// This is the last found element - Just look for any "free IP range" with a bigger "from" range than this "to".
		if (last_el && last > 0) {
			window.search_free_subnet_range(last_el, last, false);
	    }

		// Finally add the even/odd classes.
		$$('table#ip-table tr').each(function (el, i) {
			if ((i%2) == 0) {
				el.addClassName('line1');
			}
		});


	    window.add_new_layer3_net = function () {
		    $$('a.new-net-button').invoke('show');
		    $$('div.new-net-form').invoke('remove');

		    var td = this.hide().up();


		    td.insert(
			    new Element('div', {className:'new-net-form'})
				    .update(new Element('input', {name:'new_net_ip', id:'new_net_ip', className:'input', value:td.up().readAttribute('data-from'), placeholder:'[{isys type="lang" ident="LC__CATP__IP__ADDRESS"}]'}))
				    .insert(new Element('select', {name:'new_net_cidr', id:'new_net_cidr', className:'input input-mini', value:td.up().readAttribute('data-to')}))
		    );

	        $('new_net_ip').on('change', function () {
	            if (IPv4.valid_ip(this.value)) {
		            this.setStyle({background:'#FBFBFB'});
	            } else {
		            this.setStyle({background:'#FFDDDD'});
	            }
	        });

	        $('new_net_ip').focus();
	    };

		$$('.new-net-button').invoke('on', 'click', window.add_new_layer3_net);
	</script>
[{else}]
	<div class="p10">
		[{isys type="lang" ident="LC__CMDB__CATG__SUPERNET__NO_NET_FOUND"}]
	</div>
[{/if}]
