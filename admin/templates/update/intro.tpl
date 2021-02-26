<h2>i-doit Update</h2>
<p>This procedure will update your i-doit code base and database structure to a selected version (next step).</p>

<table>
	<colgroup>
		<col width="150" />
	</colgroup>
	<tr>
		<td colspan="2"><h3>Operating System</h3></td>
	</tr>
	<tr>
		<td class="key">Type</td>
		<td>[{$os.name}]</td>
	</tr>
	<tr>
		<td class="key">Version</td>
		<td>[{$os.version}]</td>
	</tr>
	<tr>
		<td class="key">PHP Version</td>
		<td>[{$smarty.const.PHP_VERSION}] (PHP 5.4.0 or higher recommended)</td>
	</tr>
	<tr>
		<td colspan="2"><h3>i-doit</h3></td>
	</tr>
	<tr>
		<td class="key">Current version</td>
		<td>[{$info.version|default:"< 1.3"}]</td>
	</tr>
	<tr>
		<td class="key">Current revision</td>
		<td>[{$info.revision|default:"< 17000"}]</td>
	</tr>
</table>