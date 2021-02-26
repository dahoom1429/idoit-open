<script type="text/javascript">
	function check(el) {
		if (el.checked=="") el.checked="checked";
	}
</script>
<h2>Available Updates</h2>

[{if !$licence_error}]
	<h3>New Versions</h3>

	New version can be retrieved via <a href="[{$site_url}]">[{$site_url}]</a>.

	[{if is_array($g_update)}]

		There is a newer version available:

		<h3>[{$g_update.title}] - [{$g_update.version}]</h3>
		<p>Date: [{$g_update.release}] (rev [{$g_update.revision}])</p>
		<input type="hidden" name="download" value="1" />
		<input type="hidden" name="dl_file" value="[{$g_update.filename}]" />
		<input type="button" class="button" name="btn_download"  value="Download" onClick="if (!$('isys_form').down('[name=\'download\']').getValue().blank()) {$('isys_form').submit();}" />
	[{else}]

		[{if $g_downloaded}]
			Download successfull.
		[{else}]

			[{if $g_update_message}]
				<p class="bold message [{$g_update_message_class}]">[{$g_update_message}]</p>
			[{else}]
				<br /><br />
				<input type="hidden" id="check_update" name="check_update" value="false" />
				<input type="button" class="button" name="btn_check"  value="Check for a new version" onClick="$('check_update').setValue('true'); $('isys_form').submit();" />
				<br />
				or enter the URL of an update package:
				<input type="text" style="border:1px solid #888;width:180px;" placeholder="[{$site_url}]/downloads/*" name="download" value="" />
				<input type="button" name="btn_download" class="button" value="Download and extract" onClick="$('isys_form').submit();" />
			[{/if}]
		[{/if}]

	[{/if}]
	</p>


	<h3>You can update to the following already downloaded versions:</h3>
	<table cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
		<colgroup>
			<col width="100" />
		</colgroup>
		<thead>
			<tr>
				<th>Use</th>
				<th>Name</th>
				<th>Version</th>
				<th>Requirements</th>
			</tr>
		</thead>
		<tbody>
			[{if is_array($g_dirs) && count($g_dirs)>0}]
				[{foreach from=$g_dirs item=l_dir}]
				[{counter print=false assign="i"}]
				[{if $l_dir.changelog != "n/a"}]
				<div id="changelog_[{$l_dir.revision}]" class="changelog" style="display:none;">
					<div class="innerchangelog">
						<div class="header">
							<div class="header_left">Changelog</div>
							<div class="header_right">
								<a class="link" onclick="new Effect.Fade('changelog_[{$l_dir.revision}]', {duration:0.2});">close</a>
							</div>
							<div style="clear:both"></div>
						</div>
						<div class="bottom"><pre>[{$l_dir.changelog}]</pre></div>
					</div>
				</div>
				[{/if}]

				<tr class="[{cycle values="even,odd"}]" [{*onclick="check($('dir_[{$i}]'));"*}]>
					<td>
						<input type="radio" id="dir_[{$i}]" name="dir" value="[{$l_dir.directory}]" [{if !empty($l_dir.revision) && $l_dir.revision < $l_dir.requirement.revision}]disabled="disabled"[{else}]checked="checked"[{/if}] />
					</td>
					<td>

					[{if $l_dir.revision < $g_info.revision}]<span style="color:#005e20;">[{/if}]
					[{if $l_dir.revision eq $g_info.revision}]<span style="font-weight:bold;">[{/if}]

					[{if !empty($l_dir.revision) && $l_dir.revision < $l_dir.requirement.revision}]<span style="color:#999;">[{/if}]

						[{$l_dir.title}][{if $l_dir.changelog != "n/a"}] (<a class="link" onclick="new Effect.Appear('changelog_[{$l_dir.revision}]', {duration:0.3});">see changelog</a>)[{/if}]

					</span>

					</td>
					<td>
						[{if !empty($l_dir.revision)}]
							[{$l_dir.version}] (rev [{$l_dir.revision}])
						[{/if}]
						</td>
					<td>
						[{if !empty($l_dir.revision)}]
							[{if $l_dir.revision < $l_dir.requirement.revision}]
								<strong style="color:red;">
									[{$l_dir.requirement.version}]
									[{if $l_dir.requirement.revision}] rev [{$l_dir.requirement.revision}][{/if}]
								</strong>
							[{else}]
								[{$l_dir.requirement.version}]
								[{if $l_dir.requirement.revision}] rev [{$l_dir.requirement.revision}][{/if}]
							[{/if}]
						[{/if}]
					</td>
				</tr>

				[{/foreach}]
			[{else}]
				<tr>
					<td colspan="2"><span>There don't seem to be any versions to update to.</span></td>
				</tr>
			[{/if}]
		</tbody>
	</table>

	<p>Select a version and click "Next >>" to go to the next step.</p>
	<span>Your current version is: <strong>[{$g_info.version|default:"<= 0.9"}] (rev [{$g_info.revision|default:"<= 2500"}])</strong></span>

[{else}]
	<br />
	<div class="exception p10">
		[{$licence_error}]
	</div>
[{/if}]
