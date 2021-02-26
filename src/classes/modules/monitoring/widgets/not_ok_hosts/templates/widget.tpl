<h3 class="border-bottom border-grey p5 gradient text-shadow">[{$title}]</h3>

[{if $error}]
	<div class="box-yellow m5 p5"><img src="[{$dir_images}]icons/silk/error.png" class="vam mr5" /><span class="vam">[{$error}]</span></div>
[{else}]
	[{if count($hosts)}]
		<p class="p5 text-yellow"><img src="[{$dir_images}]icons/silk/error.png" class="vam mr5" />[{isys type="lang" ident="LC__MONITORING__WIDGET__NOT_OK_HOSTS__FOUND_HOSTS_THAT_ARE_NOT_OK"}]</p>

		<div class="p5">
			<table class="mainTable border border-grey">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__ID"}]</th>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</th>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</th>
				</tr>
				</thead>
				<tbody>
				[{foreach $hosts as $host}]
					<tr class="[{cycle values="line0,line1"}]">
						<td>[{$host.obj_id}]</td>
						<td><span>[{$host.obj_type_title}] &raquo; [{$host.obj_title}]</span><a href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$host.obj_id}]" class="ml10"><img src="[{$dir_images}]icons/silk/link.png" class="vam" /></a></td>
						<td class="[{$host.state.color}]"><img src="[{$dir_images}][{$host.state.icon}]" class="vam mr5 mouse-help" title="[{$host.state_info}]" /><span class="vam">[{$host.state.state}]</span></td>
					</tr>
				[{/foreach}]
				</tbody>
			</table>
		</div>
	[{else}]
		<p class="p5 green"><img src="[{$dir_images}]icons/silk/tick.png" class="vam mr5" />[{isys type="lang" ident="LC__MONITORING__WIDGET__NOT_OK_HOSTS__ALL_HOSTS_OK"}]</p>
	[{/if}]

	[{if $services === false}]
		<p class="p5">[{isys type="lang" ident="LC__MONITORING__WIDGET__NOT_OK_HOSTS__NO_SERVICES"}]</p>
	[{elseif count($services)}]
		<p class="p5 text-yellow"><img src="[{$dir_images}]icons/silk/error.png" class="vam mr5" />[{isys type="lang" ident="LC__MONITORING__WIDGET__NOT_OK_HOSTS__FOUND_SERVICES_THAT_ARE_NOT_OK"}]</p>

		<div class="p5">
			<table class="mainTable border border-grey">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__ID"}]</th>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</th>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</th>
					<th>[{isys type="lang" ident="LC__MONITORING__WIDGET__NOT_OK_HOSTS__SERVICE"}]</th>
				</tr>
				</thead>
				<tbody>
				[{foreach $services as $service}]
					<tr class="[{cycle values="line0,line1"}]">
						<td>[{$service.obj_id}]</td>
						<td><span>[{$service.obj_type_title}] &raquo; [{$service.obj_title}]</span><a href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$service.obj_id}]" class="ml10"><img src="[{$dir_images}]icons/silk/link.png" class="vam" /></a></td>
						<td class="[{$service.state.color}]"><img src="[{$dir_images}][{$service.state.icon}]" class="vam mr5 mouse-help" title="[{$service.state_info}]" /><span class="vam">[{$service.state.state}]</span></td>
						<td>
							[{foreach $service.host_service as $host_service}]
							<div class="fl border border-[{$host_service.state.color}] [{$host_service.state.color}] mr5" style="padding:1px 3px;">
								<img src="[{$dir_images}][{$host_service.state.icon}]" class="vam mr5 mouse-help" title="[{$host_service.state_info}] [{isys type="lang" ident="LC__UNIVERSAL__ON"}] '[{$host_service.app_type_title|escape}] &raquo; [{$host_service.app_title|escape}]'" width="14px" height="14px" /><span class="vam">[{$host_service.state.state}] <strong>[{$host_service.service}]</strong></span>
							</div>
							[{/foreach}]
							[{foreach $service.inherited_service as $inherited_service}]
							<div class="fl border border-[{$inherited_service.state.color}] [{$inherited_service.state.color}] mr5" style="padding:1px 3px;">
								<img src="[{$dir_images}][{$inherited_service.state.icon}]" class="vam mr5 mouse-help" title="[{$inherited_service.state_info}] [{isys type="lang" ident="LC__UNIVERSAL__ON"}] '[{$inherited_service.app_type_title|escape}] &raquo; [{$inherited_service.app_title|escape}]'" width="14px" height="14px" /><span class="vam">[{$inherited_service.state.state}] <strong>[{$inherited_service.service}]</strong></span>
							</div>
							[{/foreach}]
							<br class="cb" />
						</td>
					</tr>
				[{/foreach}]
				</tbody>
			</table>
		</div>
	[{else}]
		<p class="p5 green"><img src="[{$dir_images}]icons/silk/tick.png" class="vam mr5" />[{isys type="lang" ident="LC__MONITORING__WIDGET__NOT_OK_HOSTS__ALL_SERVICES_OK"}]</p>
	[{/if}]
[{/if}]