<div class="m10">
	<h2>[{$title}]</h2>

	<div style="display:none;margin-bottom:10px;" id="add_port">
		<a href="javascript:" onclick="new Effect.BlindUp('add_port',{duration:0.2});" class="fr">X</a>
		<h3 style="border-bottom:1px solid #ccc;">Port Details:</h3>
		<div>
			[{include file="content/bottom/content/catg__port.tpl"}]
		</div>
		<hr />
		<input type="button" onclick="" value="Hinzufügen" />
	</div>

	<div>
		<a class="fr p5 m5 bold" style="border:1px solid #ccc;" href="javascript:void('add-port');" onclick="$('add_port').setOpacity(0.2);new Effect.toggle('add_port','slide',{duration:0.2,scaleContent:true,afterFinish:function(){$('add_port').setOpacity(1);}});">+ Port Hinzufügen</a>
		<fieldset>
			<legend>Ports</legend>

			<div id="port_list">
				[{foreach from=$ports item="l_port"}]
				[{counter assign="cnt"}]

				[{if $cnt<10}]
					<p class="p5" style="border-bottom:1px solid #ccc;background:#eee;">
						[{$cnt}].
						[{$l_port.isys_catg_netp_list__title}] >> <a href="[{$l_port.link}]" class="bold">[{$l_port.isys_catg_port_list__title}]</a>
						([{$l_port.isys_port_type__title}][{if $l_port.isys_catg_port_list__mac}]:: [{$l_port.isys_catg_port_list__mac}][{/if}]),
						IP-Adresse: <u>[{$l_port.isys_cats_net_ip_addresses_list__title|default:"-"}]</u>
					</p>
				[{else}]

					<p class="p5 disabled" style="border-bottom:1px solid #ccc;background:#eee;display:none">
						[{$cnt}].
						[{$l_port.isys_catg_netp_list__title}] >> <a href="[{$l_port.link}]" class="bold">[{$l_port.isys_catg_port_list__title}]</a>
						([{$l_port.isys_port_type__title}][{if $l_port.isys_catg_port_list__mac}]:: [{$l_port.isys_catg_port_list__mac}][{/if}]),
						IP-Adresse: <u>[{$l_port.isys_cats_net_ip_addresses_list__title|default:"-"}]</u>
					</p>

				[{/if}]

				[{/foreach}]

				[{if $cnt>10}]
					<p class="m5">
						<a href="javascript:void(0);" onclick="this.hide(); $$('#port_list .disabled').each(function(e){e.show()});" />... Alle Ports anzeigen</a>
					</p>
				[{/if}]
			</div>
		</fieldset>
	</div>

	<div style="display:none;margin-top:10px;margin-bottom:10px;" id="add_ip">
		<a href="javascript:" onclick="new Effect.BlindUp('add_ip',{duration:0.2});" class="fr">X</a>
		<h3 style="border-bottom:1px solid #ccc;">IP-Addressen Details:</h3>
		<div>
			[{include file="content/bottom/content/catg__ip.tpl"}]
		</div>
		<hr />
		<input type="button" onclick="" value="Hinzufügen" />
	</div>

	<div class="mt5">
		<a class="fr p5 m5 bold" style="border:1px solid #ccc;" href="javascript:void('add-ip');" onclick="$('add_ip').setOpacity(0.2);new Effect.toggle('add_ip','slide',{duration:0.2,scaleContent:true,afterFinish:function(){$('add_ip').setOpacity(1);}});">+ IP-Adresse Hinzufügen</a>

		<fieldset>
			<legend>IP Adressen</legend>

			[{foreach from=$ips item="l_ip"}]

			<p class="p5" style="border-bottom:1px solid #ccc;background:#eee;">
				[{counter start=1}].
				Adresse: <u><a href="[{$l_ip.link}]">[{$l_ip.isys_cats_net_ip_addresses_list__title|default:"-"}]</a></u>,
				Hostname: [{$l_ip.isys_catg_ip_list__hostname|default:"-"}],
				Netzmaske: [{$l_ip.isys_catg_ip_list__mask|default:"-"}]
			</p>

			[{/foreach}]

		</fieldset>
	</div>

	<div style="display:none;margin-top:10px;margin-bottom:10px;" id="add_ip">
		<a href="javascript:" onclick="new Effect.BlindUp('add_ip',{duration:0.2});" class="fr">X</a>
		<h3 style="border-bottom:1px solid #ccc;">IP-Addressen Details:</h3>
		<div>
			[{include file="content/bottom/content/catg__ip.tpl"}]
		</div>
		<hr />
		<input type="button" onclick="" value="Hinzufügen" />
	</div>

	<div class="mt5">
		<a class="fr p5 m5 bold" style="border:1px solid #ccc;" href="javascript:void('add-interface');" onclick="$('add_port').setOpacity(0.2);new Effect.toggle('add_port','slide',{duration:0.2,scaleContent:true,afterFinish:function(){$('add_interface').setOpacity(1);}});">+ Interface Hinzufügen</a>
		<fieldset>
			<legend>Interfaces</legend>

			[{foreach from=$ifaces item="l_iface"}]

			<p class="p5" style="border-bottom:1px solid #ccc;background:#eee;">
				[{counter start=1}].
				<a href="[{$l_iface.link}]">[{$l_iface.isys_catg_netp_list__title}]</a> (Slot [{$l_iface.isys_catg_netp_list__slotnumber}])
			</p>

			[{/foreach}]

		</fieldset>
	</div>

	<div style="display:none;margin-top:10px;margin-bottom:10px;" id="add_interface">
		<a href="javascript:" onclick="new Effect.BlindUp('add_interface',{duration:0.2});" class="fr">X</a>
		<h3 style="border-bottom:1px solid #ccc;">Interface Details:</h3>
		<div>
			[{include file="content/bottom/content/catg__iface.tpl"}]
		</div>
		<hr />
		<input type="button" onclick="" value="Hinzufügen" />
	</div>

</div>