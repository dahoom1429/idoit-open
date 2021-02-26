<style type="text/css">
	td.value {
		position: relative;
	}

	#ip-container-left,
	#ip-separator,
	#ip-container-right {
		position: absolute;
		vertical-align: middle;
		top: 2px;
	}
</style>

<table class="contentTable m0">
	<tbody>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__ZONE__ZONE_OBJECT" name="C__CMDB__CATS__NET_ZONE__ZONE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_object_ng" name="C__CMDB__CATS__NET_ZONE__ZONE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__NET__DHCP_RANGE" name="C__CATS__NET_ZONE_RANGE_FROM"}]</td>
		<td class="value">
			<div id="ip-container-left">[{isys type="f_text" name="C__CATS__NET_ZONE_RANGE_FROM"}]</div>
			<div id="ip-separator">-</div>
			<div id="ip-container-right">[{isys type="f_text" name="C__CATS__NET_ZONE_RANGE_TO"}]</div>
		</td>
	</tr>
	</tbody>
</table>

<script type="text/javascript">
	(function () {
		'use strict';

		var $from        = $('C__CATS__NET_ZONE_RANGE_FROM'),
		    $to          = $('C__CATS__NET_ZONE_RANGE_TO'),
		    $left        = $('ip-container-left'),
		    $separator   = $('ip-separator'),
		    $right       = $('ip-container-right'),
		    reset_fields = function () {
			    $from.removeClassName('input-error').writeAttribute('title', '');
			    $to.removeClassName('input-error').writeAttribute('title', '');
		    },
		    mark_field   = function ($el, msg) {
			    new Tip(
					    $el.addClassName('input-error').writeAttribute('title', msg),
					    new Element('p', {
						    className: 'p5',
						    style:     'font-size:12px;'
					    }).update(msg),
					    {
						    showOn: 'click',
						    hideOn: 'click',
						    style:  'darkgrey'
					    });
		    };

		[{if isys_glob_is_edit_mode()}]
		// In edit mode we need to move the separator a bit down.
		$separator.setStyle({top: '5px'});

		var address_range_from = IPv4.ip2long('[{$address_range_from}]'),
		    address_range_to   = IPv4.ip2long('[{$address_range_to}]'),
		    zone_ranges        = '[{$zone_ranges|json_encode}]'.evalJSON();

		var check_ranges = function () {
			var ip_from_long = 0,
			    ip_to_long   = 0;

			reset_fields();
			Tips.hideAll();

			// Get the values from the FROM- and TO-range.
			ip_from_long = IPv4.ip2long($from.getValue());
			ip_to_long = IPv4.ip2long($to.getValue());

			// If the user inputs a higher "from" value than "to", we help him.
			if (ip_from_long > ip_to_long)
			{
				swap_fields();
				return false;
			}

			// Finally we check our data.
			if (ip_from_long < address_range_from || ip_from_long > ip_to_long)
			{
				mark_field($from, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_LIES_OUTSIDE_OF_ADDRESSRANGE" p_bHtmlEncode=false}]');
				return false;
			}

			// We check if the user input lies inside the address-range.
			if (ip_to_long > address_range_to || ip_from_long > ip_to_long)
			{
				mark_field($to, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_LIES_OUTSIDE_OF_ADDRESSRANGE" p_bHtmlEncode=false}]');
				return false;
			}

			if (zone_ranges)
			{
				// Now we come to the tricky part - Make sure no DHCP ranges overleap other DHCP ranges.
				zone_ranges.each(function (e) {
					if (e.from >= ip_from_long && e.to <= ip_to_long)
					{
						mark_field($from, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_OVERLAPS_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                           .replace(/%s/, IPv4.long2ip(e.to)));
						mark_field($to, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_OVERLAPS_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                         .replace(/%s/, IPv4.long2ip(e.to)));

						return false;
					}
					else if (ip_from_long >= e.from && ip_to_long <= e.to)
					{
						mark_field($from, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_LIES_INSIDE_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                              .replace(/%s/, IPv4.long2ip(e.to)));
						mark_field($to, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_LIES_INSIDE_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                            .replace(/%s/, IPv4.long2ip(e.to)));

						return false;
					}

					if (e.to >= ip_to_long && ip_to_long >= e.from)
					{
						mark_field($from, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_CUTS_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                       .replace(/%s/, IPv4.long2ip(e.to)));
						mark_field($to, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_CUTS_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                     .replace(/%s/, IPv4.long2ip(e.to)));

						return false;
					}
					else if (e.to >= ip_from_long && ip_from_long >= e.from)
					{
						mark_field($from, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_CUTS_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                       .replace(/%s/, IPv4.long2ip(e.to)));
						mark_field($to, '[{isys type="lang" ident="LC__CMDB__CATS__NET_DHCP__RANGE_CUTS_ANOTHER_RANGE" p_bHtmlEncode=false}]'.replace(/%s/, IPv4.long2ip(e.from))
						                                                                                                                     .replace(/%s/, IPv4.long2ip(e.to)));

						return false;
					}
				}.bind(this));
			}

			return true
		};

		// When the user inputs a higher IP address on the left side than the right, we troll him. Problem?
		var swap_fields = function () {
			var finish = function () {
				// At first, we save the left values to an array.
				var data = $to.getValue();

				// Time to swap the values from right to left.
				$to.setValue($from.getValue());

				// And now we save the values from our array to the right side.
				$from.setValue(data);

				// We call this function so the fields get validated with the new data.
				check_ranges();

				$left.setStyle({marginLeft: '0px'});
				$right.setStyle({marginLeft: ($left.getWidth() + 10) + 'px'});
			};

			// Slide the ip-fields. Problem?
			$right.morph({marginLeft: '0px'});
			$left.morph({marginLeft: ($left.getWidth() + 10) + 'px'});

			// Because the morph-callback has some weird error, we just "wait" for a bit until we call the "onFinish" method.
			finish.delay(1.1);
		};

		$from.on('change', check_ranges);
		$to.on('change', check_ranges);
		check_ranges();

		[{/if}]

		$separator.setStyle({marginLeft: ($left.getWidth() + 5) + 'px'});
		$right.setStyle({marginLeft: ($left.getWidth() + 10) + 'px'});
	})();
</script>
